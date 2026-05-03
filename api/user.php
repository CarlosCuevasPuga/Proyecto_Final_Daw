<?php
// api/user.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once 'config/db.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == 'get_user_status') {
    // Obtener el estado actual del usuario desde la BD
    $data = json_decode(file_get_contents("php://input"));
    if (!empty($data->user_id)) {
        $stmt = $conn->prepare("SELECT id, name, email, points, is_premium FROM users WHERE id = ?");
        $stmt->execute([$data->user_id]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo json_encode(array("status" => "success", "data" => $user));
        } else {
            http_response_code(404);
            echo json_encode(array("status" => "error", "message" => "User not found"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("status" => "error", "message" => "User ID required"));
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
                    echo json_encode(array("status" => "success", "message" => "Coupon purchased successfully", "new_points" => $new_points));
                } else {
                    $conn->rollBack();
                    http_response_code(400);
                    echo json_encode(array("status" => "error", "message" => "Not enough points"));
                }
            } else {
                $conn->rollBack();
                http_response_code(404);
                echo json_encode(array("status" => "error", "message" => "User or Coupon not found"));
            }
        } catch (Exception $e) {
            $conn->rollBack();
            http_response_code(500);
            echo json_encode(array("status" => "error", "message" => "Transaction failed: " . $e->getMessage()));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("status" => "error", "message" => "Incomplete data"));
    }
} elseif ($action == 'complete_route') {
    $data = json_decode(file_get_contents("php://input"));
    if (!empty($data->user_id) && !empty($data->route_id) && isset($data->lat) && isset($data->lng)) {
        // Check if route is active
        $stmt_check_active = $conn->prepare("SELECT * FROM user_active_routes WHERE user_id = ? AND route_id = ?");
        $stmt_check_active->execute([$data->user_id, $data->route_id]);
        if (!$stmt_check_active->fetch()) {
            http_response_code(400);
            echo json_encode(array("status" => "error", "message" => "Route not started or already completed"));
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
            echo json_encode(array("status" => "error", "message" => "Route destination not found"));
            exit;
        }
        
        // Calculate distance (simple Euclidean for now, could use Haversine)
        $distance = sqrt(pow($data->lat - $destination['lat'], 2) + pow($data->lng - $destination['lng'], 2));
        $threshold = 0.001; // About 100 meters (adjust as needed)
        
        if ($distance > $threshold) {
            http_response_code(400);
            echo json_encode(array("status" => "error", "message" => "You must be at the destination to complete the route"));
            exit;
        }
        
        // Check if already completed
        $stmt_check_completed = $conn->prepare("SELECT * FROM user_completed_routes WHERE user_id = ? AND route_id = ?");
        $stmt_check_completed->execute([$data->user_id, $data->route_id]);
        if ($stmt_check_completed->fetch()) {
            http_response_code(400);
            echo json_encode(array("status" => "error", "message" => "Route already completed"));
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
                echo json_encode(array("status" => "success", "message" => "Route completed! You earned " . $route['reward_points'] . " points.", "new_points" => $new_user_data['points']));
            } catch (Exception $e) {
                $conn->rollBack();
                http_response_code(500);
                echo json_encode(array("status" => "error", "message" => "Transaction failed"));
            }
        } else {
            http_response_code(404);
            echo json_encode(array("status" => "error", "message" => "Route not found"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("status" => "error", "message" => "Incomplete data"));
    }
} elseif ($action == 'start_route') {
    $data = json_decode(file_get_contents("php://input"));
    if (!empty($data->user_id) && !empty($data->route_id)) {
        // Check if already completed
        $stmt_check_completed = $conn->prepare("SELECT * FROM user_completed_routes WHERE user_id = ? AND route_id = ?");
        $stmt_check_completed->execute([$data->user_id, $data->route_id]);
        if ($stmt_check_completed->fetch()) {
            http_response_code(400);
            echo json_encode(array("status" => "error", "message" => "Route already completed"));
            exit;
        }
        
        // Check if already active
        $stmt_check_active = $conn->prepare("SELECT * FROM user_active_routes WHERE user_id = ? AND route_id = ?");
        $stmt_check_active->execute([$data->user_id, $data->route_id]);
        if ($stmt_check_active->fetch()) {
            http_response_code(400);
            echo json_encode(array("status" => "error", "message" => "Route already started"));
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
            
            echo json_encode(array("status" => "success", "message" => "Route started! Follow the path on the map."));
        } else {
            http_response_code(404);
            echo json_encode(array("status" => "error", "message" => "Route not found"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("status" => "error", "message" => "Incomplete data"));
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
        echo json_encode(array("status" => "error", "message" => "Incomplete data"));
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
        echo json_encode(array("status" => "error", "message" => "User ID required"));
    }
} elseif ($action == 'get_active_routes') {
    if (!empty($_GET['user_id'])) {
        $stmt = $conn->prepare("
            SELECT r.id, r.name, r.description, r.reward_points, r.is_premium
            FROM routes r
            JOIN user_active_routes uar ON r.id = uar.route_id
            WHERE uar.user_id = ?
        ");
        $stmt->execute([$_GET['user_id']]);
        $routes = $stmt->fetchAll();
        
        // For each route, fetch its restaurants
        foreach ($routes as &$route) {
            $stmt_rest = $conn->prepare("
                SELECT r.id, r.name, r.address, r.lat, r.lng, r.category, r.rating, rr.order_num 
                FROM restaurants r
                JOIN route_restaurants rr ON r.id = rr.restaurant_id
                WHERE rr.route_id = ?
                ORDER BY rr.order_num ASC
            ");
            $stmt_rest->execute([$route['id']]);
            $route['restaurants'] = $stmt_rest->fetchAll();
        }
        
        echo json_encode(array("status" => "success", "data" => $routes));
    } else {
        http_response_code(400);
        echo json_encode(array("status" => "error", "message" => "User ID required"));
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
        echo json_encode(array("status" => "error", "message" => "User ID required"));
    }
} else {
    http_response_code(400);
    echo json_encode(array("status" => "error", "message" => "Invalid action"));
}
?>
