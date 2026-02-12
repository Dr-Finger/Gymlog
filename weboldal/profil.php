<?php
session_start();
require "db.php";
require "functions.php";

$bejelentkezve = isset($_SESSION["user_id"]);
$userId = $bejelentkezve ? (int)$_SESSION["user_id"] : 0;

$megtekintettId = isset($_GET["user_id"]) ? (int)$_GET["user_id"] : 0;
$sajatProfil = ($megtekintettId <= 0 || $megtekintettId === $userId);
$profilUser = null;

if ($sajatProfil && $bejelentkezve) {
    $profilUser = getFelhasznaloById($conn, $userId);
} elseif ($megtekintettId > 0) {
    $profilUser = getFelhasznaloById($conn, $megtekintettId);
}

$baratsagAllapot = null;
$baratok = [];
if ($profilUser && $bejelentkezve) {
    if ($sajatProfil) {
        $baratok = getBaratok($conn, $userId);
    } else {
        $baratsagAllapot = getBaratsagAllapot($conn, $userId, $megtekintettId);
    }
}

$edzesDb = $profilUser ? getProfilStat($conn, $profilUser["id"], "edzes") : 0;
$baratDb = $profilUser ? getProfilStat($conn, $profilUser["id"], "barat") : 0;
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil</title>
    <link rel="stylesheet" href="../css/fooldal.css">
    <link rel="stylesheet" href="../css/profiltartalom.css">
    <link rel="stylesheet" href="../css/profil.css">
    <link rel="icon" type="image/x-icon" href="../img/gymlog-white.png">
    <script src="../js/index.js" defer></script>
    <?php if (!$sajatProfil && $bejelentkezve): ?>
    <script src="../js/profil.js" defer></script>
    <?php endif; ?>
</head>
<body class="fooldal-body">
    <?php include "nav.php"; ?>

<?php if (!$bejelentkezve): ?>
<main class="profil-main">
    <div class="profil-shell">
        <section class="profil-left">
            <div class="profil-card profil-basic">
                <h1>Profil</h1>
                <p class="vendeg-uzenet">Jelentkezz be a profilok megtekintéséhez.</p>
                <a href="login-html.php" class="gomb">Bejelentkezés</a>
            </div>
        </section>
    </div>
</main>
<?php elseif (!$profilUser): ?>
<main class="profil-main">
    <div class="profil-shell">
        <div class="profil-card profil-basic">
            <p>Felhasználó nem található.</p>
            <a href="kozosseg.php" class="gomb">Vissza a közösséghez</a>
        </div>
    </div>
</main>
<?php else: ?>
<main class="profil-main">
    <div class="profil-shell">
        <section class="profil-left">
            <div class="profil-card profil-basic">
                <h1><?php echo htmlspecialchars($profilUser["nev"]); ?></h1>
                <p><span class="label">Név:</span> <span class="value"><?php echo htmlspecialchars($profilUser["nev"]); ?></span></p>
                <p><span class="label">E‑mail:</span> <span class="value"><?php echo htmlspecialchars($profilUser["email"]); ?></span></p>
                <?php if ($sajatProfil): ?>
                    <p><span class="label">Profilod</span></p>
                <?php else: ?>
                    <p><a href="kozosseg.php" class="vissza-kozosseg">← Vissza a közösséghez</a></p>
                    <p id="baratAllapotUzenet"></p>
                    <?php if ($baratsagAllapot === null): ?>
                        <button type="button" id="baratJonelolGomb" class="barat-jonelol-gomb" data-user-id="<?php echo (int)$profilUser["id"]; ?>">Barátnak jelölés</button>
                    <?php elseif ($baratsagAllapot["status"] === "pending"): ?>
                        <p class="barat-status"><?php echo (int)$baratsagAllapot["kero_id"] === $userId ? "Baráti kérés küldve." : "Fogadd el a Közösség oldalon."; ?></p>
                    <?php else: ?>
                        <p class="barat-status">Már barátok vagytok.</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="profil-card profil-stats">
                <h2>Statisztikák</h2>
                <div class="profil-stat-grid">
                    <div class="profil-stat-box">
                        <div class="number"><?php echo $edzesDb; ?></div>
                        <div class="label">edzés</div>
                    </div>
                    <div class="profil-stat-box">
                        <div class="number">-</div>
                        <div class="label">óra</div>
                    </div>
                    <div class="profil-stat-box">
                        <div class="number"><?php echo $baratDb; ?></div>
                        <div class="label">barát</div>
                    </div>
                </div>
            </div>
        </section>

        <aside class="profil-right">
            <div class="profil-card profil-friends">
                <h2>Barátok</h2>
                <?php if ($sajatProfil): ?>
                    <ul class="friends-list">
                        <?php foreach ($baratok as $b): ?>
                            <li><a href="profil.php?user_id=<?php echo (int)$b["id"]; ?>"><?php echo htmlspecialchars($b["nev"]); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if (empty($baratok)): ?>
                        <p class="friends-hint">Itt jelennek meg a barátaid. Jelöld barátnak másokat a Közösség oldalon.</p>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="friends-hint">A barátok listája csak a saját profilban látható.</p>
                <?php endif; ?>
            </div>
        </aside>
    </div>
</main>
<?php endif; ?>
</body>
</html>
