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
            SELECT r.id, r.name, r.address, r.lat, r.lng, r.category, rr.order_num 
            FROM restaurants r
            JOIN route_restaurants rr ON r.id = rr.restaurant_id
            WHERE rr.route_id = ?
            ORDER BY rr.order_num ASC
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
} else {
    http_response_code(400);
    echo json_encode(array("status" => "error", "message" => "Invalid action"));
}
?>
