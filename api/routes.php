<?php
// api/routes.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once 'config/db.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'list';

if ($action == 'list') {
    $stmt = $conn->prepare("SELECT * FROM routes ORDER BY id ASC");
    $stmt->execute();
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
} elseif ($action == 'restaurants') {
    $stmt = $conn->prepare("SELECT * FROM restaurants ORDER BY name ASC");
    $stmt->execute();
    $restaurants = $stmt->fetchAll();
    echo json_encode(array("status" => "success", "data" => $restaurants));
} elseif ($action == 'save') {
    $data = json_decode(file_get_contents("php://input"));
    if (empty($data->name) || empty($data->description)) {
        http_response_code(400);
        echo json_encode(array("status" => "error", "message" => "Name and description are required"));
        exit;
    }

    $isPremium = !empty($data->is_premium) ? 1 : 0;
    $rewardPoints = isset($data->reward_points) ? intval($data->reward_points) : 0;
    $duration = 0;

    if (!empty($data->id)) {
        $stmt = $conn->prepare("UPDATE routes SET name = ?, description = ?, reward_points = ?, estimated_duration_mins = ?, is_premium = ? WHERE id = ?");
        $stmt->execute([$data->name, $data->description, $rewardPoints, $duration, $isPremium, $data->id]);
        $routeId = $data->id;
    } else {
        $stmt = $conn->prepare("INSERT INTO routes (name, description, reward_points, estimated_duration_mins, is_premium) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$data->name, $data->description, $rewardPoints, $duration, $isPremium]);
        $routeId = $conn->lastInsertId();
    }

    $stmtDel = $conn->prepare("DELETE FROM route_restaurants WHERE route_id = ?");
    $stmtDel->execute([$routeId]);

    if (!empty($data->restaurants) && is_array($data->restaurants)) {
        $stmtInsert = $conn->prepare("INSERT INTO route_restaurants (route_id, restaurant_id, order_num) VALUES (?, ?, ?)");
        foreach ($data->restaurants as $restaurant) {
            if (!empty($restaurant->id)) {
                $stmtInsert->execute([$routeId, intval($restaurant->id), 0]);
            }
        }
    }

    echo json_encode(array("status" => "success", "message" => "Ruta guardada correctamente"));
} elseif ($action == 'delete') {
    $routeId = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if (!$routeId) {
        http_response_code(400);
        echo json_encode(array("status" => "error", "message" => "Route ID required"));
        exit;
    }
    $stmt = $conn->prepare("DELETE FROM routes WHERE id = ?");
    if ($stmt->execute([$routeId])) {
        echo json_encode(array("status" => "success", "message" => "Ruta eliminada correctamente"));
    } else {
        http_response_code(500);
        echo json_encode(array("status" => "error", "message" => "No se pudo eliminar la ruta"));
    }
} else {
    http_response_code(400);
    echo json_encode(array("status" => "error", "message" => "Invalid action"));
}
?>
