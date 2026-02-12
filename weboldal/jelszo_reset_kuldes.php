<?php
session_start();
require "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: elfelejtett_jelszo.php");
    exit;
}

$email = trim($_POST["email"] ?? "");

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION["hiba"] = "Érvénytelen e-mail cím.";
    header("Location: elfelejtett_jelszo.php");
    exit;
}

// Létező felhasználó ellenőrzése
$stmt = $conn->prepare("SELECT id, nev FROM felhasznalo WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Biztonsági ok: mindig "sikeres" üzenet (ne lehessen e-mail címeket kideríteni)
if (!$user) {
    header("Location: jelszo_reset_info.php?status=nincs");
    exit;
}

// Régi tokenek törlése ugyanahhoz a felhasználóhoz
$uid = (int) $user["id"];
$conn->query("DELETE FROM jelszo_reset WHERE felhasznalo_id = $uid");

// Új token generálás
$token = bin2hex(random_bytes(32));
$lejarat = date("Y-m-d H:i:s", time() + 3600); // 1 óra

$ins = $conn->prepare("INSERT INTO jelszo_reset (token, felhasznalo_id, lejarat) VALUES (?, ?, ?)");
$ins->bind_param("sis", $token, $uid, $lejarat);

if (!$ins->execute()) {
    $_SESSION["hiba"] = "Hiba történt. Próbáld újra később.";
    header("Location: elfelejtett_jelszo.php");
    exit;
}

// Reset URL összeállítása
$protocol = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ? "https" : "http";
$host = $_SERVER["HTTP_HOST"];
$basePath = dirname($_SERVER["SCRIPT_NAME"]);
$resetUrl = $protocol . "://" . $host . $basePath . "/jelszo_uj.php?token=" . $token;

// E-mail küldés (XAMPP-ban gyakran nem működik - ilyenkor a link megjelenik az info oldalon)
$subject = "GymLog - Jelszó visszaállítás";
$message = "Szia " . htmlspecialchars($user["nev"]) . "!\n\n"
    . "Jelszó-visszaállítási kérelmet kaptunk.\n"
    . "Kattints az alábbi linkre az új jelszó megadásához (1 órán belül érvényes):\n\n"
    . $resetUrl . "\n\n"
    . "Ha nem te kérted, hagyd figyelmen kívül ezt az e-mailt.\n\n"
    . "Üdv,\nGymLog";

$headers = "From: noreply@gymlog.hu\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

$mailSent = @mail($email, $subject, $message, $headers);

$params = "status=ok&sent=" . ($mailSent ? "1" : "0");
if (!$mailSent) {
    $params .= "&token=" . urlencode($token);
}
header("Location: jelszo_reset_info.php?" . $params);
exit;
