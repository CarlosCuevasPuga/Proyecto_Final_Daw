<?php
// api/user.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once 'config/db.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

function hasAdminColumn($conn) {
    try {
        $stmt = $conn->query("SHOW COLUMNS FROM users LIKE 'is_admin'");
        return $stmt && $stmt->fetch();
    } catch (Exception $e) {
        return false;
    }
}

if ($action == 'get_user_status') {
    // Obtener el estado actual del usuario desde la BD
    $data = json_decode(file_get_contents("php://input"));
    if (!empty($data->user_id)) {
        $columns = "id, name, email, points, is_premium";
        if (hasAdminColumn($conn)) {
            $columns .= ", is_admin";
        }
        $stmt = $conn->prepare("SELECT $columns FROM users WHERE id = ?");
        $stmt->execute([$data->user_id]);
        $user = $stmt->fetch();
        
        if ($user) {
            if (!isset($user['is_admin'])) {
                $user['is_admin'] = 0;
            }
            echo json_encode(array("status" => "success", "data" => $user));
        } else {
            http_response_code(404);
            echo json_encode(array("status" => "error", "message" => "Usuario no encontrado"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("status" => "error", "message" => "ID de usuario requerido"));
    }
} elseif ($action == 'get_coupons') {
    $stmt = $conn->prepare("
        SELECT c.*, r.name as restaurant_name 
        FROM coupons c 
        LEFT JOIN restaurants r ON c.restaurant_id = r.id
    ");
    $stmt->execute();
    $coupons = $stmt->fetchAll();
    echo json_encode(array("status" => "success", "data" => $coupons));
} elseif ($action == 'buy_coupon') {
    $data = json_decode(file_get_contents("php://input"));
    if (!empty($data->user_id) && !empty($data->coupon_id)) {
        try {
            $conn->beginTransaction();
            
            // Check user points and coupon cost
            $stmt = $conn->prepare("SELECT points FROM users WHERE id = ?");
            $stmt->execute([$data->user_id]);
            $user = $stmt->fetch();
            
            $stmt_c = $conn->prepare("SELECT points_cost FROM coupons WHERE id = ?");
            $stmt_c->execute([$data->coupon_id]);
            $coupon = $stmt_c->fetch();
            
            if ($user && $coupon) {
                if ($user['points'] >= $coupon['points_cost']) {
                    // Deduct points
                    $stmt_u = $conn->prepare("UPDATE users SET points = points - ? WHERE id = ?");
                    $stmt_u->execute([$coupon['points_cost'], $data->user_id]);
                    
                    // Add coupon to user
                    $stmt_uc = $conn->prepare("INSERT INTO user_coupons (user_id, coupon_id) VALUES (?, ?)");
                    $stmt_uc->execute([$data->user_id, $data->coupon_id]);
                    
                    $conn->commit();
                    $new_points = $user['points'] - $coupon['points_cost'];
                    echo json_encode(array("status" => "success", "message" => "Cupón adquirido correctamente", "new_points" => $new_points));
                } else {
                    $conn->rollBack();
                    http_response_code(400);
                    echo json_encode(array("status" => "error", "message" => "Puntos insuficientes"));
                }
            } else {
                $conn->rollBack();
                http_response_code(404);
                echo json_encode(array("status" => "error", "message" => "Usuario o cupón no encontrado"));
            }
        } catch (Exception $e) {
            $conn->rollBack();
            http_response_code(500);
            echo json_encode(array("status" => "error", "message" => "Error en la transacción: " . $e->getMessage()));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("status" => "error", "message" => "Datos incompletos"));
    }
} elseif ($action == 'complete_route') {
    $data = json_decode(file_get_contents("php://input"));
    if (!empty($data->user_id) && !empty($data->route_id) && isset($data->lat) && isset($data->lng)) {
        // Check if route is active
        $stmt_check_active = $conn->prepare("SELECT * FROM user_active_routes WHERE user_id = ? AND route_id = ?");
        $stmt_check_active->execute([$data->user_id, $data->route_id]);
        if (!$stmt_check_active->fetch()) {
            http_response_code(400);
            echo json_encode(array("status" => "error", "message" => "La ruta no ha sido iniciada o ya fue completada"));
            exit;
        }
        
        // Get the last restaurant in the route (destination)
        $stmt_dest = $conn->prepare("
            SELECT r.lat, r.lng FROM restaurants r
            JOIN route_restaurants rr ON r.id = rr.restaurant_id
            WHERE rr.route_id = ?
            ORDER BY rr.order_num DESC
            LIMIT 1
        ");
        $stmt_dest->execute([$data->route_id]);
        $destination = $stmt_dest->fetch();
        
        if (!$destination) {
            http_response_code(404);
            echo json_encode(array("status" => "error", "message" => "Destino de la ruta no encontrado"));
            exit;
        }
        
        // Calculate distance (simple Euclidean for now, could use Haversine)
        $distance = sqrt(pow($data->lat - $destination['lat'], 2) + pow($data->lng - $destination['lng'], 2));
        $threshold = 0.001; // About 100 meters (adjust as needed)
        
        if ($distance > $threshold) {
            http_response_code(400);
            echo json_encode(array("status" => "error", "message" => "Debes estar en el destino para completar la ruta"));
            exit;
        }
        
        // Check if already completed
        $stmt_check_completed = $conn->prepare("SELECT * FROM user_completed_routes WHERE user_id = ? AND route_id = ?");
        $stmt_check_completed->execute([$data->user_id, $data->route_id]);
        if ($stmt_check_completed->fetch()) {
            http_response_code(400);
            echo json_encode(array("status" => "error", "message" => "La ruta ya ha sido completada"));
            exit;
        }
        
        // Get route info
        $stmt_r = $conn->prepare("SELECT reward_points FROM routes WHERE id = ?");
        $stmt_r->execute([$data->route_id]);
        $route = $stmt_r->fetch();
        
        if ($route) {
            $conn->beginTransaction();
            try {
                // Add points
                $stmt_u = $conn->prepare("UPDATE users SET points = points + ? WHERE id = ?");
                $stmt_u->execute([$route['reward_points'], $data->user_id]);
                
                // Remove from active routes
                $stmt_del_active = $conn->prepare("DELETE FROM user_active_routes WHERE user_id = ? AND route_id = ?");
                $stmt_del_active->execute([$data->user_id, $data->route_id]);
                
                // Record completion
                $stmt_c = $conn->prepare("INSERT INTO user_completed_routes (user_id, route_id) VALUES (?, ?)");
                $stmt_c->execute([$data->user_id, $data->route_id]);
                
                $stmt_np = $conn->prepare("SELECT points FROM users WHERE id = ?");
                $stmt_np->execute([$data->user_id]);
                $new_user_data = $stmt_np->fetch();

                $conn->commit();
                echo json_encode(array("status" => "success", "message" => "¡Ruta completada! Has ganado " . $route["reward_points"] . " puntos.", "new_points" => $new_user_data['points']));
            } catch (Exception $e) {
                $conn->rollBack();
                http_response_code(500);
                echo json_encode(array("status" => "error", "message" => "Error en la transacción"));
            }
        } else {
            http_response_code(404);
            echo json_encode(array("status" => "error", "message" => "Ruta no encontrada"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("status" => "error", "message" => "Datos incompletos"));
    }
} elseif ($action == 'start_route') {
    $data = json_decode(file_get_contents("php://input"));
    if (!empty($data->user_id) && !empty($data->route_id)) {
        // Check if already completed
        $stmt_check_completed = $conn->prepare("SELECT * FROM user_completed_routes WHERE user_id = ? AND route_id = ?");
        $stmt_check_completed->execute([$data->user_id, $data->route_id]);
        if ($stmt_check_completed->fetch()) {
            http_response_code(400);
            echo json_encode(array("status" => "error", "message" => "La ruta ya ha sido completada"));
            exit;
        }
        
        // Check if already active
        $stmt_check_active = $conn->prepare("SELECT * FROM user_active_routes WHERE user_id = ? AND route_id = ?");
        $stmt_check_active->execute([$data->user_id, $data->route_id]);
        if ($stmt_check_active->fetch()) {
            http_response_code(400);
            echo json_encode(array("status" => "error", "message" => "La ruta ya ha sido iniciada"));
            exit;
        }
        
        // Get route info and check if it's premium
        $stmt_r = $conn->prepare("SELECT is_premium FROM routes WHERE id = ?");
        $stmt_r->execute([$data->route_id]);
        $route = $stmt_r->fetch();
        
        // Check if user is premium and route requires it
        if ($route && $route['is_premium']) {
            $stmt_u = $conn->prepare("SELECT is_premium FROM users WHERE id = ?");
            $stmt_u->execute([$data->user_id]);
            $user = $stmt_u->fetch();
            
            if (!$user || !$user['is_premium']) {
                http_response_code(403);
                echo json_encode(array("status" => "error", "message" => "Debes tener una suscripción premium para completar rutas premium"));
                exit;
            }
        }
        
        if ($route) {
            // Insert into active routes
            $stmt_a = $conn->prepare("INSERT INTO user_active_routes (user_id, route_id) VALUES (?, ?)");
            $stmt_a->execute([$data->user_id, $data->route_id]);
            
            echo json_encode(array("status" => "success", "message" => "¡Ruta iniciada! Sigue el camino en el mapa."));
        } else {
            http_response_code(404);
            echo json_encode(array("status" => "error", "message" => "Ruta no encontrada"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("status" => "error", "message" => "Datos incompletos"));
    }
} elseif ($action == 'cancel_route') {
    $data = json_decode(file_get_contents("php://input"));
    if (!empty($data->user_id) && !empty($data->route_id)) {
        $stmt_check_active = $conn->prepare("SELECT * FROM user_active_routes WHERE user_id = ? AND route_id = ?");
        $stmt_check_active->execute([$data->user_id, $data->route_id]);
        if (!$stmt_check_active->fetch()) {
            http_response_code(400);
            echo json_encode(array("status" => "error", "message" => "No hay ninguna ruta en progreso para cancelar"));
            exit;
        }

        $stmt_del_active = $conn->prepare("DELETE FROM user_active_routes WHERE user_id = ? AND route_id = ?");
        $stmt_del_active->execute([$data->user_id, $data->route_id]);

        echo json_encode(array("status" => "success", "message" => "Progreso de ruta cancelado correctamente."));
    } else {
        http_response_code(400);
        echo json_encode(array("status" => "error", "message" => "Datos incompletos"));
    }
} elseif ($action == 'get_completed_routes') {
    if (!empty($_GET['user_id'])) {
        $stmt = $conn->prepare("
            SELECT route_id FROM user_completed_routes WHERE user_id = ?
        ");
        $stmt->execute([$_GET['user_id']]);
        $routes = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo json_encode(array("status" => "success", "data" => $routes));
    } else {
        http_response_code(400);
        echo json_encode(array("status" => "error", "message" => "ID de usuario requerido"));
    }
} elseif ($action == 'get_active_routes') {
    if (!empty($_GET['user_id'])) {
        $stmt = $conn->prepare("
            SELECT r.id, r.name, r.description, r.reward_points, r.is_premium, uar.started_at
            FROM routes r
            JOIN user_active_routes uar ON r.id = uar.route_id
            WHERE uar.user_id = ?
        ");
        $stmt->execute([$_GET['user_id']]);
        $routes = $stmt->fetchAll();
        
        // For each route, fetch its restaurants
        foreach ($routes as &$route) {
            $stmt_rest = $conn->prepare("
                SELECT r.id, r.name, r.address, r.lat, r.lng, r.category
                FROM restaurants r
                JOIN route_restaurants rr ON r.id = rr.restaurant_id
                WHERE rr.route_id = ?
                ORDER BY r.id ASC
            ");
            $stmt_rest->execute([$route['id']]);
            $route['restaurants'] = $stmt_rest->fetchAll();
        }
        
        echo json_encode(array("status" => "success", "data" => $routes));
    } else {
        http_response_code(400);
        echo json_encode(array("status" => "error", "message" => "ID de usuario requerido"));
    }
} elseif ($action == 'get_user_coupons') {
    // Obtener los cupones que el usuario ha canjeado
    $data = json_decode(file_get_contents("php://input"));
    if (!empty($data->user_id)) {
        $stmt = $conn->prepare("
            SELECT c.id, c.title, c.description, c.points_cost, c.discount_code, c.restaurant_id, r.name as restaurant_name, uc.redeemed_at
            FROM coupons c
            JOIN user_coupons uc ON c.id = uc.coupon_id
            LEFT JOIN restaurants r ON c.restaurant_id = r.id
            WHERE uc.user_id = ?
            ORDER BY uc.redeemed_at DESC
        ");
        $stmt->execute([$data->user_id]);
        $coupons = $stmt->fetchAll();
        echo json_encode(array("status" => "success", "data" => $coupons));
    } else {
        http_response_code(400);
        echo json_encode(array("status" => "error", "message" => "ID de usuario requerido"));
    }
} elseif ($action == 'list_users') {
    // Listar todos los usuarios (solo para administradores)
    try {
        $columns = "id, name, email, points, is_premium";
        if (hasAdminColumn($conn)) {
            $columns .= ", is_admin";
        }
        $stmt = $conn->prepare("SELECT $columns FROM users ORDER BY id ASC");
        $stmt->execute();
        $users = $stmt->fetchAll();
        
        // Asegurar que is_admin existe en todos los usuarios
        foreach ($users as &$user) {
            if (!isset($user['is_admin'])) {
                $user['is_admin'] = 0;
            }
        }
        
        echo json_encode(array("status" => "success", "data" => $users));
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(array("status" => "error", "message" => "Error al obtener usuarios: " . $e->getMessage()));
    }
} elseif ($action == 'update_user') {
    // Actualizar datos de un usuario (is_admin, is_premium)
    $data = json_decode(file_get_contents("php://input"));
    if (!empty($data->user_id)) {
        try {
            $updateFields = [];
            $updateValues = [];
            
            // Verificar qué campos se van a actualizar
            if (isset($data->is_premium)) {
                $updateFields[] = "is_premium = ?";
                $updateValues[] = $data->is_premium ? 1 : 0;
            }
            if (isset($data->is_admin) && hasAdminColumn($conn)) {
                $updateFields[] = "is_admin = ?";
                $updateValues[] = $data->is_admin ? 1 : 0;
            }
            
            if (empty($updateFields)) {
                http_response_code(400);
                echo json_encode(array("status" => "error", "message" => "No hay campos para actualizar"));
                exit;
            }
            
            $updateValues[] = $data->user_id;
            $updateQuery = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->execute($updateValues);
            
            echo json_encode(array("status" => "success", "message" => "Usuario actualizado correctamente"));
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(array("status" => "error", "message" => "Error al actualizar el usuario: " . $e->getMessage()));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("status" => "error", "message" => "ID de usuario requerido"));
    }
} elseif ($action == 'delete_user') {
    // Eliminar un usuario (solo administradores)
    $data = json_decode(file_get_contents("php://input"));
    if (!empty($data->user_id)) {
        try {
            $conn->beginTransaction();
            
            // Eliminar historial de rutas completadas
            $stmt_del_routes = $conn->prepare("DELETE FROM user_completed_routes WHERE user_id = ?");
            $stmt_del_routes->execute([$data->user_id]);
            
            // Eliminar rutas activas
            $stmt_del_active = $conn->prepare("DELETE FROM user_active_routes WHERE user_id = ?");
            $stmt_del_active->execute([$data->user_id]);
            
            // Eliminar cupones canjeados
            $stmt_del_coupons = $conn->prepare("DELETE FROM user_coupons WHERE user_id = ?");
            $stmt_del_coupons->execute([$data->user_id]);
            
            // Eliminar el usuario
            $stmt_del_user = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt_del_user->execute([$data->user_id]);
            
            $conn->commit();
            echo json_encode(array("status" => "success", "message" => "Usuario eliminado correctamente"));
        } catch (Exception $e) {
            $conn->rollBack();
            http_response_code(500);
            echo json_encode(array("status" => "error", "message" => "Error al eliminar el usuario: " . $e->getMessage()));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("status" => "error", "message" => "ID de usuario requerido"));
    }
} else {
    http_response_code(400);
    echo json_encode(array("status" => "error", "message" => "Acción no válida"));
}
?>