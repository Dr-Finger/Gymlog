<?php
session_start();
require "db.php";
require "functions.php";

header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_SESSION["user_id"])) {
    echo json_encode(["siker" => false, "uzenet" => "Hibás kérés."]);
    exit;
}

$json = json_decode(file_get_contents("php://input"), true);
$magassagRaw = $json["magassag"] ?? null;
$testsulyRaw = $json["testsuly"] ?? null;
$nemRaw = trim($json["nem"] ?? "");
$magassag = ($magassagRaw === "" || $magassagRaw === null) ? null : (int)$magassagRaw;
$testsuly = ($testsulyRaw === "" || $testsulyRaw === null) ? null : (int)$testsulyRaw;
$nem = $nemRaw === "" ? null : $nemRaw;

if ($magassag !== null && ($magassag < 50 || $magassag > 250)) {
    echo json_encode(["siker" => false, "uzenet" => "Érvénytelen magasság (50-250 cm)."]);
    exit;
}
if ($testsuly !== null && ($testsuly < 20 || $testsuly > 300)) {
    echo json_encode(["siker" => false, "uzenet" => "Érvénytelen testsúly (20-300 kg)."]);
    exit;
}
if ($nem !== null && !in_array($nem, ["ferfi", "no", "mas"])) {
    echo json_encode(["siker" => false, "uzenet" => "Érvénytelen nem."]);
    exit;
}

ensureProfilOszlopok($conn);
$userId = (int)$_SESSION["user_id"];
$m = $magassag ?? -1;
$t = $testsuly ?? -1;
$n = $nem ?? "";
$stmt = $conn->prepare("UPDATE felhasznalo SET magassag = NULLIF(?, -1), testsuly = NULLIF(?, -1), nem = NULLIF(?, '') WHERE id = ?");
$stmt->bind_param("iisi", $m, $t, $n, $userId);

if ($stmt->execute()) {
    echo json_encode(["siker" => true, "uzenet" => "Adatok mentve."]);
} else {
    echo json_encode(["siker" => false, "uzenet" => "Hiba a mentéskor."]);
}
