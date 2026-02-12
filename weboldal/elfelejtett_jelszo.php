<?php
session_start();
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/fooldal.css">
    <link rel="stylesheet" href="../css/login.css">
    <link rel="icon" type="image/x-icon" href="../img/gymlog-white.png">
    <title>Elfelejtett jelszó</title>
</head>
<body class="fooldal-body">
    <?php include "nav.php"; ?>

    <main class="auth-main">
        <div class="auth-card">
            <h1>Elfelejtett jelszó</h1>
            <p class="auth-info">Add meg a regisztrációhoz használt e-mail címedet. E-mailben elküldjük a jelszó-visszaállító linket.</p>

            <?php if (!empty($_SESSION["hiba"])): ?>
                <p class="auth-error">
                    <?php
                        echo htmlspecialchars($_SESSION["hiba"]);
                        unset($_SESSION["hiba"]);
                    ?>
                </p>
            <?php endif; ?>

            <form action="jelszo_reset_kuldes.php" method="post" class="auth-form">
                <label>
                    E-mail
                    <input type="email" name="email" placeholder="E-mail cím" required autofocus>
                </label>
                <div class="gombSor">
                    <button type="submit">Link kérése</button>
                    <a href="login-html.php" class="gomb">Vissza</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
