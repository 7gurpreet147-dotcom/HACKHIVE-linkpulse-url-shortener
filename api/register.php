<?php

require_once __DIR__ . "/../config/db.php";

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

// Read the JSON request body.
$input = json_decode(file_get_contents("php://input"), true);

if (!is_array($input)) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Invalid request data."
    ]);

    exit;
}

// Read and normalize the submitted values.
$name = trim($input["name"] ?? "");
$email = strtolower(trim($input["email"] ?? ""));
$password = $input["password"] ?? "";
$confirmPassword = $input["confirmPassword"] ?? "";

// Validate the name.
if ($name === "") {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Name is required."
    ]);

    exit;
}

if (strlen($name) > 100) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Name must not exceed 100 characters."
    ]);

    exit;
}

// Validate the email.
if (
    strlen($email) > 255 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL)
) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Please enter a valid email address."
    ]);

    exit;
}

// Validate the password length.
// Eight characters is the minimum for this initial implementation.
if (
    !is_string($password) ||
    strlen($password) < 8 ||
    strlen($password) > 72
) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Password must be between 8 and 72 bytes."
    ]);

    exit;
}

// Validate password confirmation.
if (
    !is_string($confirmPassword) ||
    !hash_equals($password, $confirmPassword)
) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Passwords do not match."
    ]);

    exit;
}

try {
    // Check whether the email is already registered.
    $checkStmt = $pdo->prepare(
        "SELECT id
         FROM users
         WHERE email = :email
         LIMIT 1"
    );

    $checkStmt->execute([
        ":email" => $email
    ]);

    if ($checkStmt->fetch()) {
        http_response_code(409);

        echo json_encode([
            "success" => false,
            "message" => "An account with this email already exists."
        ]);

        exit;
    }

    // Hash the password before storing it.
    $passwordHash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    if ($passwordHash === false) {
        throw new RuntimeException("Password hashing failed.");
    }

    // Insert the new account.
    // The database default assigns the role "user".
    $insertStmt = $pdo->prepare(
        "INSERT INTO users (name, email, password_hash)
         VALUES (:name, :email, :password_hash)"
    );

    $insertStmt->execute([
        ":name" => $name,
        ":email" => $email,
        ":password_hash" => $passwordHash
    ]);

    http_response_code(201);

    echo json_encode([
        "success" => true,
        "message" => "Account created successfully. You can now log in.",
        "user" => [
            "id" => (int) $pdo->lastInsertId(),
            "name" => $name,
            "email" => $email
        ]
    ], JSON_UNESCAPED_SLASHES);

} catch (PDOException $e) {
    // Handle duplicate-email races and other database constraint errors.
    if (
        $e->getCode() === "23000" &&
        str_contains(strtolower($e->getMessage()), "email")
    ) {
        http_response_code(409);

        echo json_encode([
            "success" => false,
            "message" => "An account with this email already exists."
        ]);

        exit;
    }

    error_log("Registration database error: " . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Unable to create your account right now."
    ]);

} catch (Throwable $e) {
    error_log("Registration failed: " . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Unable to create your account right now."
    ]);
}