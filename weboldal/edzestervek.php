<?php
session_start();
require "db.php";
require "functions.php";

$bejelentkezve = isset($_SESSION["user_id"]);
$userId = $bejelentkezve ? (int)$_SESSION["user_id"] : 0;
$tervek = $bejelentkezve ? getTervek($conn, $userId) : [];
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/fooldal.css">
    <link rel="icon" type="image/x-icon" href="../img/gymlog-white.png">
    <script src="../js/index.js" defer></script>
    <title>Edzéstervek</title>
</head>
<body class="fooldal-body">
    <?php include "nav.php"; ?>

    <main class="main-shell">
        <div class="outer-box">
            <section class="posts-box">
                <h1>Mentett edzéstervek</h1>

                <?php if (!$bejelentkezve): ?>
                    <p class="posts-placeholder">
                        Jelentkezz be az edzéstervek megtekintéséhez és mentéséhez.
                        <br><a href="login-html.php" class="auth-link-inline">Bejelentkezés</a>
                    </p>
                <?php elseif (empty($tervek)): ?>
                    <p class="posts-placeholder">
                        Még nincs elmentett edzésterved. Hozz létre egyet az „Új edzés” fülön, majd kattints az „Edzés mentése” gombra.
                    </p>
                <?php else: ?>
                    <?php foreach ($tervek as $terv): ?>
                        <article class="terv-kartya">
                            <h2><?php echo htmlspecialchars($terv["nev"]); ?></h2>
                            <p class="terv-datum">
                                Létrehozva: <?php echo htmlspecialchars($terv["letrehozva"]); ?>
                            </p>
                            <?php
                                $sorok = json_decode($terv["tartalom"], true) ?: [];
                            ?>
                            <?php if (!empty($sorok)): ?>
                                <ul class="terv-gyakorlatok">
                                    <?php foreach ($sorok as $sor): ?>
                                        <li>
                                            <?php 
                                                echo htmlspecialchars($sor["nev"] ?? "");
                                                echo htmlspecialchars(formatGyakorlatReszletek($sor));
                                            ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p>Nincsenek gyakorlatok mentve ehhez a tervhez.</p>
                            <?php endif; ?>
                            <a href="ujedzes.php?terv_id=<?php echo (int)$terv["id"]; ?>" class="terv-inditas-gomb">Terv indítása</a>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>

            <aside class="friends-box">
                <h2>Megjegyzés</h2>
                <p class="friends-info">
                    Itt látod felsorolva az összes elmentett edzéstervedet.
                    Új tervet az „Új edzés” menüpont alatt tudsz készíteni.
                </p>
            </aside>
        </div>
    </main>
</body>
</html>

