<?php
// api/user.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once 'config/db.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == 'get_coupons') {
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
    if (!empty($data->user_id) && !empty($data->route_id)) {
        // Check if already completed
        $stmt_check = $conn->prepare("SELECT * FROM user_completed_routes WHERE user_id = ? AND route_id = ?");
        $stmt_check->execute([$data->user_id, $data->route_id]);
        if ($stmt_check->fetch()) {
            http_response_code(400);
            echo json_encode(array("status" => "error", "message" => "Route already completed"));
            exit;
        }
        
        $stmt_r = $conn->prepare("SELECT reward_points FROM routes WHERE id = ?");
        $stmt_r->execute([$data->route_id]);
        $route = $stmt_r->fetch();
        
        if ($route) {
            $conn->beginTransaction();
            try {
                // Add points
                $stmt_u = $conn->prepare("UPDATE users SET points = points + ? WHERE id = ?");
                $stmt_u->execute([$route['reward_points'], $data->user_id]);
                
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
} else {
    http_response_code(400);
    echo json_encode(array("status" => "error", "message" => "Invalid action"));
}
?>
