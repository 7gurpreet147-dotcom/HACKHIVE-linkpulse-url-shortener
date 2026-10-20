
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

$token = $_COOKIE["LINKPULSE_VISITOR"] ?? "";

if (!is_string($token) || !preg_match('/^[a-f0-9]{64}$/', $token)) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Anonymous visitor identity not found."
    ]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);

if (
    !is_array($input) ||
    !isset($input["id"], $input["status"]) ||
    !is_numeric($input["id"]) ||
    !in_array($input["status"], ["active", "disabled"], true)
) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid link or status."
    ]);
    exit;
}

$id = filter_var($input["id"], FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid link ID."
    ]);
    exit;
}

$tokenHash = hash("sha256", $token);

try {
    $stmt = $pdo->prepare("
        UPDATE links
        SET status = :status
        WHERE id = :id
          AND user_id IS NULL
          AND anonymous_token_hash = :token_hash
    ");

    $stmt->execute([
        ":status" => $input["status"],
        ":id" => $id,
        ":token_hash" => $tokenHash
    ]);

    if ($stmt->rowCount() === 0) {
        $check = $pdo->prepare("
            SELECT id, status
            FROM links
            WHERE id = :id
              AND user_id IS NULL
              AND anonymous_token_hash = :token_hash
        ");

        $check->execute([
            ":id" => $id,
            ":token_hash" => $tokenHash
        ]);

        $link = $check->fetch(PDO::FETCH_ASSOC);

        if (!$link) {
            http_response_code(404);
            echo json_encode([
                "success" => false,
                "message" => "Link not found."
            ]);
            exit;
        }
    }

    echo json_encode([
        "success" => true,
        "message" => "Link status updated successfully."
    ]);

} catch (Throwable $e) {
    error_log("Anonymous link status update failed: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Unable to update link status."
    ]);
}
