<?php
session_start();
require "db.php";
require "functions.php";

$bejelentkezve = isset($_SESSION["user_id"]);
$userId = $bejelentkezve ? (int)$_SESSION["user_id"] : 0;
$keres = trim($_GET["keres"] ?? "");
$felhasznalok = [];
$baratiKerelmek = [];

if ($bejelentkezve) {
    ensureBaratsagTable($conn);
    $felhasznalok = getFelhasznalok($conn, $userId, $keres);
    $baratiKerelmek = getBaratiKerelmek($conn, $userId);
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/fooldal.css">
    <link rel="stylesheet" href="../css/kozosseg.css">
    <link rel="icon" type="image/x-icon" href="../img/gymlog-white.png">
    <script src="../js/index.js" defer></script>
    <script src="../js/kozosseg.js" defer></script>
    <title>Közösség</title>
</head>
<body class="fooldal-body">
    <?php include "nav.php"; ?>

    <main class="main-shell">
        <div class="outer-box">
            <section class="posts-box kozosseg-fo">
                <h1>Közösség</h1>

                <?php if (!$bejelentkezve): ?>
                    <p class="posts-placeholder">Jelentkezz be a közösség megtekintéséhez.</p>
                <?php else: ?>
                    <?php if (!empty($baratiKerelmek)): ?>
                        <div class="kerelmek-box">
                            <h2>Baráti kérelmek</h2>
                            <?php foreach ($baratiKerelmek as $k): ?>
                                <div class="kerelm-sor" data-id="<?php echo (int)$k["id"]; ?>">
                                    <span><?php echo htmlspecialchars($k["kero_nev"]); ?> barátnak jelölt</span>
                                    <button type="button" class="elfogad-gomb" data-id="<?php echo (int)$k["id"]; ?>">Elfogad</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="kereses-box">
                        <form method="get" action="kozosseg.php" class="kereses-form">
                            <input type="text" name="keres" value="<?php echo htmlspecialchars($keres); ?>" placeholder="Keresés név vagy email alapján...">
                            <button type="submit">Keresés</button>
                        </form>
                    </div>

                    <h2>Felhasználók</h2>
                    <?php if (empty($felhasznalok)): ?>
                        <p class="posts-placeholder"><?php echo $keres ? "Nincs találat." : "Nincs más felhasználó."; ?></p>
                    <?php else: ?>
                        <ul class="user-lista">
                            <?php foreach ($felhasznalok as $u): 
                                $allapot = getBaratsagAllapot($conn, $userId, (int)$u["id"]);
                            ?>
                                <li>
                                    <a href="profil.php?user_id=<?php echo (int)$u["id"]; ?>" class="user-link">
                                        <span class="user-nev"><?php echo htmlspecialchars($u["nev"]); ?></span>
                                        <span class="user-email"><?php echo htmlspecialchars($u["email"]); ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                <?php endif; ?>
            </section>
            <aside class="friends-box">
                <h2>Közösség</h2>
                <p class="friends-info">
                    Keress emberekre név vagy email alapján, kattints a profiljukra, és jelöld őket barátnak.
                    A baráti kérelmeket itt fogadod el.
                </p>
            </aside>
        </div>
    </main>
</body>
</html>
