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

// Tábla létrehozása (ha még nincs)
$conn->query("CREATE TABLE IF NOT EXISTS edzesterv_mentes (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    felhasznaloId INT(11) NOT NULL,
    nev VARCHAR(100) NOT NULL,
    tartalom LONGTEXT NOT NULL,
    letrehozva DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

// Mentés
$stmt = $conn->prepare("INSERT INTO edzesterv_mentes (felhasznaloId, nev, tartalom) VALUES (?, ?, ?)");
$tartalom = json_encode($json["sorok"], JSON_UNESCAPED_UNICODE);
$stmt->bind_param("iss", $_SESSION["user_id"], $nev, $tartalom);

if ($stmt->execute()) {
    echo json_encode(["siker" => true, "uzenet" => "Edzésterv sikeresen elmentve."]);
} else {
    echo json_encode(["siker" => false, "uzenet" => "Nem sikerült elmenteni az edzéstervet."]);
}

