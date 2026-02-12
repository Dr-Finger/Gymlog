<?php
session_start();
require "db.php";

header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["siker" => false, "uzenet" => "Hibás kérés."]);
    exit;
}

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["siker" => false, "uzenet" => "Jelentkezz be."]);
    exit;
}

$json = json_decode(file_get_contents("php://input"), true);
$baratsagId = (int)($json["kerelm_id"] ?? 0);

if ($baratsagId <= 0) {
    echo json_encode(["siker" => false, "uzenet" => "Érvénytelen kérelem."]);
    exit;
}

$fogadoId = (int)$_SESSION["user_id"];

$stmt = $conn->prepare("UPDATE baratsag SET status = 'accepted' WHERE id = ? AND fogado_id = ? AND status = 'pending'");
$stmt->bind_param("ii", $baratsagId, $fogadoId);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(["siker" => true, "uzenet" => "Baráti kérelem elfogadva."]);
} else {
    echo json_encode(["siker" => false, "uzenet" => "Nem található vagy már kezelt kérelem."]);
}
