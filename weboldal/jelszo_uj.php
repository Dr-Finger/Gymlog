<?php
session_start();
require "db.php";

$token = trim($_GET["token"] ?? "");
$hiba = "";
$siker = false;

if (empty($token)) {
    $hiba = "Érvénytelen vagy lejárt link.";
} else {
    // Tábla létezés ellenőrzés
    $check = $conn->query("SHOW TABLES LIKE 'jelszo_reset'");
    if (!$check || $check->num_rows === 0) {
        $hiba = "Érvénytelen vagy lejárt link.";
    } else {
        $stmt = $conn->prepare("
            SELECT r.id, r.felhasznalo_id, r.lejarat
            FROM jelszo_reset r
            WHERE r.token = ? AND r.hasznalva = 0 AND r.lejarat > NOW()
        ");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if (!$row) {
            $hiba = "A link lejárt vagy már felhasználták. Kérj új jelszó-visszaállító linket.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/fooldal.css">
    <link rel="stylesheet" href="../css/login.css">
    <link rel="icon" type="image/x-icon" href="../img/gymlog-white.png">
    <title>Új jelszó megadása</title>
</head>
<body class="fooldal-body">
    <?php include "nav.php"; ?>

    <main class="auth-main">
        <div class="auth-card">
            <h1>Új jelszó megadása</h1>

            <?php if ($hiba): ?>
                <p class="auth-error"><?php echo htmlspecialchars($hiba); ?></p>
                <div class="gombSor" style="margin-top: 16px;">
                    <a href="elfelejtett_jelszo.php" class="gomb">Új link kérése</a>
                    <a href="login-html.php" class="gomb">Bejelentkezés</a>
                </div>
            <?php elseif (isset($_POST["mentes"])): ?>
                <?php
                $jelszo1 = $_POST["jelszo"] ?? "";
                $jelszo2 = $_POST["jelszo_ujra"] ?? "";

                if (strlen($jelszo1) < 6) {
                    $hiba = "A jelszónak legalább 6 karakter hosszúnak kell lennie.";
                } elseif ($jelszo1 !== $jelszo2) {
                    $hiba = "A két jelszó nem egyezik.";
                } else {
                    $stmt = $conn->prepare("
                        SELECT r.felhasznalo_id FROM jelszo_reset r
                        WHERE r.token = ? AND r.hasznalva = 0 AND r.lejarat > NOW()
                    ");
                    $stmt->bind_param("s", $token);
                    $stmt->execute();
                    $row = $stmt->get_result()->fetch_assoc();

                    if (!$row) {
                        $hiba = "A link lejárt vagy már felhasználták.";
                    } else {
                        $hash = password_hash($jelszo1, PASSWORD_DEFAULT);
                        $uid = (int) $row["felhasznalo_id"];
                        $upd = $conn->prepare("UPDATE felhasznalo SET jelszo = ? WHERE id = ?");
                        $upd->bind_param("si", $hash, $uid);

                        if ($upd->execute()) {
                            $mark = $conn->prepare("UPDATE jelszo_reset SET hasznalva = 1 WHERE token = ?");
$mark->bind_param("s", $token);
$mark->execute();
                            $siker = true;
                        } else {
                            $hiba = "Hiba történt a jelszó mentésekor.";
                        }
                    }
                }
                ?>
                <?php if ($siker): ?>
                    <p class="auth-success">Sikeresen megváltoztattad a jelszavad. Most már bejelentkezhetsz.</p>
                    <div class="gombSor" style="margin-top: 16px;">
                        <a href="login-html.php" class="gomb">Bejelentkezés</a>
                    </div>
                <?php elseif ($hiba): ?>
                    <p class="auth-error"><?php echo htmlspecialchars($hiba); ?></p>
                    <form action="jelszo_uj.php?token=<?php echo htmlspecialchars($token); ?>" method="post" class="auth-form">
                        <label>
                            Új jelszó
                            <input type="password" name="jelszo" placeholder="Új jelszó (min. 6 karakter)" required minlength="6">
                        </label>
                        <label>
                            Jelszó újra
                            <input type="password" name="jelszo_ujra" placeholder="Jelszó ismét" required minlength="6">
                        </label>
                        <div class="gombSor">
                            <button type="submit" name="mentes">Mentés</button>
                        </div>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <form action="jelszo_uj.php?token=<?php echo htmlspecialchars($token); ?>" method="post" class="auth-form">
                    <label>
                        Új jelszó
                        <input type="password" name="jelszo" placeholder="Új jelszó (min. 6 karakter)" required minlength="6">
                    </label>
                    <label>
                        Jelszó újra
                        <input type="password" name="jelszo_ujra" placeholder="Jelszó ismét" required minlength="6">
                    </label>
                    <div class="gombSor">
                        <button type="submit" name="mentes">Mentés</button>
                        <a href="login-html.php" class="gomb">Mégse</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
