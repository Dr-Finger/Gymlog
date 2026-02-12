<?php
session_start();
require "db.php";

header("Content-Type: application/json; charset=utf-8");

// Validáció
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["siker" => false, "uzenet" => "Hibás kérés."]);
    exit;
}

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["siker" => false, "uzenet" => "Nincs bejelentkezett felhasználó."]);
    exit;
}

$json = json_decode(file_get_contents("php://input"), true);
$nev = trim($json["nev"] ?? "");

if (!$json || !isset($json["sorok"]) || !is_array($json["sorok"]) || $nev === "") {
    echo json_encode(["siker" => false, "uzenet" => "Hiányzó vagy hibás adatok."]);
    exit;
}

// Mentés
$stmt = $conn->prepare("INSERT INTO edzesterv_mentes (felhasznaloId, nev, tartalom) VALUES (?, ?, ?)");
$tartalom = json_encode($json["sorok"], JSON_UNESCAPED_UNICODE);
$stmt->bind_param("iss", $_SESSION["user_id"], $nev, $tartalom);

if ($stmt->execute()) {
    echo json_encode(["siker" => true, "uzenet" => "Edzésterv sikeresen elmentve."]);
} else {
    echo json_encode(["siker" => false, "uzenet" => "Nem sikerült elmenteni az edzéstervet."]);
}

