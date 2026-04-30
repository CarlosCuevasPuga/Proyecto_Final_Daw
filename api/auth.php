<?php
// api/auth.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once 'config/db.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == 'login') {
    $data = json_decode(file_get_contents("php://input"));
    if (!empty($data->email) && !empty($data->password)) {
        $stmt = $conn->prepare("SELECT id, name, email, password_hash, points, is_premium FROM users WHERE email = ?");
        $stmt->execute([$data->email]);
        $user = $stmt->fetch();

        if ($user && password_verify($data->password, $user['password_hash'])) {
            unset($user['password_hash']);
            echo json_encode(array("status" => "success", "message" => "Login successful", "user" => $user));
        } else {
            http_response_code(401);
            echo json_encode(array("status" => "error", "message" => "Invalid email or password"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("status" => "error", "message" => "Incomplete data"));
    }
} elseif ($action == 'register') {
    $data = json_decode(file_get_contents("php://input"));
    if (!empty($data->name) && !empty($data->email) && !empty($data->password)) {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$data->email]);
        if ($stmt->fetch()) {
            http_response_code(400);
            echo json_encode(array("status" => "error", "message" => "Email already exists"));
            exit;
        }

        $password_hash = password_hash($data->password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password_hash, points, is_premium) VALUES (?, ?, ?, 0, 0)");
        if ($stmt->execute([$data->name, $data->email, $password_hash])) {
            http_response_code(201);
            echo json_encode(array("status" => "success", "message" => "User registered successfully"));
        } else {
            http_response_code(503);
            echo json_encode(array("status" => "error", "message" => "Unable to register user"));
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
