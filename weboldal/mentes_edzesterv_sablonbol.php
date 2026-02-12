<?php
session_start();
require "db.php";

header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["siker" => false, "uzenet" => "Hibás kérés."]);
    exit;
}

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["siker" => false, "uzenet" => "Jelentkezz be a mentéshez."]);
    exit;
}

$json = json_decode(file_get_contents("php://input"), true);
$edzesId = (int)($json["edzes_id"] ?? 0);

if ($edzesId <= 0) {
    echo json_encode(["siker" => false, "uzenet" => "Érvénytelen edzés."]);
    exit;
}

$stmt = $conn->prepare("SELECT nev, leiras FROM edzes WHERE id = ?");
$stmt->bind_param("i", $edzesId);
$stmt->execute();
$result = $stmt->get_result();

if (!$row = $result->fetch_assoc()) {
    echo json_encode(["siker" => false, "uzenet" => "Edzés nem található."]);
    exit;
}

$nev = trim($row["nev"]);
$tartalom = $row["leiras"];

if (!$nev || !$tartalom) {
    echo json_encode(["siker" => false, "uzenet" => "Az edzés nem menthető tervként."]);
    exit;
}

$check = json_decode($tartalom, true);
if (!is_array($check) || empty($check)) {
    echo json_encode(["siker" => false, "uzenet" => "Nincs gyakorlat az edzésben."]);
    exit;
}

$conn->query("CREATE TABLE IF NOT EXISTS edzesterv_mentes (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    felhasznaloId INT(11) NOT NULL,
    nev VARCHAR(100) NOT NULL,
    tartalom LONGTEXT NOT NULL,
    letrehozva DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

$userId = (int)$_SESSION["user_id"];
$pstmt = $conn->prepare("INSERT INTO edzesterv_mentes (felhasznaloId, nev, tartalom) VALUES (?, ?, ?)");
$pstmt->bind_param("iss", $userId, $nev, $tartalom);

if ($pstmt->execute()) {
    echo json_encode(["siker" => true, "uzenet" => "Edzésterv sikeresen mentve! Megtalálod az Edzéstervek oldalon."]);
} else {
    echo json_encode(["siker" => false, "uzenet" => "Nem sikerült elmenteni."]);
}
