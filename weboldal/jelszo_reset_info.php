<?php
session_start();
$status = $_GET["status"] ?? "";
$token  = $_GET["token"] ?? "";
$sent   = ($_GET["sent"] ?? "0") === "1";
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/fooldal.css">
    <link rel="stylesheet" href="../css/login.css">
    <link rel="icon" type="image/x-icon" href="../img/gymlog-white.png">
    <title>Jelszó visszaállítás</title>
</head>
<body class="fooldal-body">
    <?php include "nav.php"; ?>

    <main class="auth-main">
        <div class="auth-card">
            <?php if ($status === "ok"): ?>
                <h1>Ellenőrizd az e-mailjeidet</h1>
                <?php if ($sent): ?>
                    <p class="auth-info">Elküldtük a jelszó-visszaállító linket a megadott e-mail címre.</p>
                <?php else: ?>
                    <p class="auth-info">A helyi környezetben az e-mail küldés nem elérhető. Használd az alábbi linket a jelszó visszaállításához:</p>
                    <p class="auth-reset-link">
                        <a href="jelszo_uj.php?token=<?php echo htmlspecialchars($token); ?>">Jelszó visszaállítása</a>
                    </p>
                    <p class="auth-info-small">A link 1 órán belül érvényes.</p>
                <?php endif; ?>
            <?php elseif ($status === "nincs"): ?>
                <h1>Ellenőrizd az e-mailjeidet</h1>
                <p class="auth-info">Ha az e-mail címed szerepel a rendszerben, elküldtük a linket. Ellenőrizd a spam mappát is.</p>
            <?php else: ?>
                <h1>Hiba</h1>
                <p class="auth-info">Érvénytelen kérés.</p>
            <?php endif; ?>
            <div class="gombSor" style="margin-top: 20px;">
                <a href="login-html.php" class="gomb">Vissza a bejelentkezéshez</a>
            </div>
        </div>
    </main>
</body>
</html>
