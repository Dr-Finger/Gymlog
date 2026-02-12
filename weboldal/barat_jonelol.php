<?php
session_start();
require "db.php";
require "functions.php";

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
$fogadoId = (int)($json["user_id"] ?? 0);

if ($fogadoId <= 0) {
    echo json_encode(["siker" => false, "uzenet" => "Érvénytelen felhasználó."]);
    exit;
}

$keroId = (int)$_SESSION["user_id"];
if ($keroId === $fogadoId) {
    echo json_encode(["siker" => false, "uzenet" => "Magadat nem jelölheted barátnak."]);
    exit;
}

ensureBaratsagTable($conn);
$allapot = getBaratsagAllapot($conn, $keroId, $fogadoId);

if ($allapot) {
    if ($allapot["status"] === "accepted") {
        echo json_encode(["siker" => false, "uzenet" => "Már barátok vagytok."]);
        exit;
    }
    if ($allapot["status"] === "pending") {
        if ((int)$allapot["kero_id"] === $keroId) {
            echo json_encode(["siker" => false, "uzenet" => "Már küldtél baráti kérelmet."]);
        } else {
            echo json_encode(["siker" => false, "uzenet" => "Ő küldött kérést, fogadd el a Közösség oldalon."]);
        }
        exit;
    }
}

$stmt = $conn->prepare("INSERT INTO baratsag (kero_id, fogado_id, status) VALUES (?, ?, 'pending')");
$stmt->bind_param("ii", $keroId, $fogadoId);

if ($stmt->execute()) {
    echo json_encode(["siker" => true, "uzenet" => "Baráti kérelem elküldve."]);
} else {
    echo json_encode(["siker" => false, "uzenet" => "Már küldtél kérést, vagy hiba történt."]);
}
