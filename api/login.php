<?php

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/session.php";

header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-store");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "Only POST requests are allowed."
    ]);

    exit;
}

$input = json_decode(file_get_contents("php://input"), true);

if (!is_array($input)) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Invalid request data."
    ]);

    exit;
}

$email = strtolower(trim($input["email"] ?? ""));
$password = $input["password"] ?? "";

if (
    !is_string($email) ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    strlen($email) > 255 ||
    !is_string($password) ||
    $password === ""
) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Please enter a valid email and password."
    ]);

    exit;
}

try {
    $stmt = $pdo->prepare(
        "SELECT id, name, email, password_hash, role
         FROM users
         WHERE email = :email
         LIMIT 1"
    );

    $stmt->execute([
        ":email" => $email
    ]);

    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user["password_hash"])) {
        http_response_code(401);

        echo json_encode([
            "success" => false,
            "message" => "Invalid email or password."
        ]);

        exit;
    }

    // Prevent session fixation after successful authentication.
    if (!session_regenerate_id(true)) {
        throw new RuntimeException("Unable to regenerate session ID.");
    }

    // Store only the necessary authenticated user details.
    $_SESSION["user_id"] = (int) $user["id"];
    $_SESSION["user_name"] = $user["name"];
    $_SESSION["user_email"] = $user["email"];
    $_SESSION["user_role"] = $user["role"];
    $_SESSION["logged_in"] = true;

    echo json_encode([
        "success" => true,
        "message" => "Login successful.",
        "user" => [
            "id" => (int) $user["id"],
            "name" => $user["name"],
            "email" => $user["email"],
            "role" => $user["role"]
        ]
    ], JSON_UNESCAPED_SLASHES);

} catch (Throwable $e) {
    error_log("Login failed: " . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Unable to log in right now. Please try again."
    ]);
}