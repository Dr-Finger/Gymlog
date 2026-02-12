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
$edzesDb = 0;
$baratDb = 0;
$edzesek = [];
$edzesNapok = [];
$valasztottHonap = null;
$honapNevek = ["", "január", "február", "március", "április", "május", "június", "július", "augusztus", "szeptember", "október", "november", "december"];

// Honap param: YYYY-MM, max mai hónap
$honapParam = trim($_GET["honap"] ?? "");
if (preg_match('/^\d{4}-\d{2}$/', $honapParam)) {
    $honapTs = strtotime($honapParam . "-01");
    $maHonap = date("Y-m");
    if ($honapTs && date("Y-m", $honapTs) <= $maHonap) {
        $valasztottHonap = date("Y-m", $honapTs);
    }
}
if (!$valasztottHonap) {
    $valasztottHonap = date("Y-m");
}

if ($profilUser) {
    $edzesDb = getProfilStat($conn, $profilUser["id"], "edzes");
    $baratDb = getProfilStat($conn, $profilUser["id"], "barat");
    $edzesek = getProfilEdzesek($conn, $profilUser["id"], 100, $valasztottHonap);
    $edzesNapok = getEdzesNapokHonap($conn, $profilUser["id"], $valasztottHonap);
    if ($sajatProfil && $bejelentkezve) {
        $baratok = getBaratok($conn, $userId);
    } else {
        $baratsagAllapot = $bejelentkezve ? getBaratsagAllapot($conn, $userId, $megtekintettId) : null;
    }
}

$nemSzoveg = ["ferfi" => "Férfi", "no" => "Nő", "mas" => "Egyéb"];

// Lapozó URL-ek
$urlBase = "profil.php";
if ($megtekintettId > 0) $urlBase .= "?user_id=" . $megtekintettId;
$urlSep = ($megtekintettId > 0) ? "&" : "?";
$honapPrevTs = strtotime($valasztottHonap . "-01 -1 month");
$honapNextTs = strtotime($valasztottHonap . "-01 +1 month");
$honapPrev = date("Y-m", $honapPrevTs);
$honapNext = date("Y-m", $honapNextTs);
$maHonap = date("Y-m");
$vanElozo = ($honapPrev <= $maHonap);  // elozo honap mindig van
$vanKovetkezo = ($honapNext <= $maHonap);  // kovetkezo only if not future
$naptarHonapSzoveg = date("Y", strtotime($valasztottHonap . "-01")) . ". " . $honapNevek[(int)date("n", strtotime($valasztottHonap . "-01"))];
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
</head>
<body class="fooldal-body">
    <?php include "nav.php"; ?>

<?php if (!$bejelentkezve): ?>
<main class="profil-main">
    <div class="profil-shell">
        <section class="profil-card profil-basic">
            <h1>Profil</h1>
            <p class="vendeg-uzenet">Jelentkezz be a profilok megtekintéséhez.</p>
            <a href="login-html.php" class="gomb">Bejelentkezés</a>
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
        <section class="profil-fo">
            <div class="profil-card profil-fejezet">
                <div class="fej-sor">
                    <div class="fej-bal">
                        <h1><?php echo htmlspecialchars($profilUser["nev"]); ?></h1>
                        <?php if (!$sajatProfil): ?>
                            <a href="kozosseg.php" class="vissza-kozosseg">← Vissza a közösséghez</a>
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
                    <div class="fej-statok">
                        <div class="stat-chip"><span class="stat-szam"><?php echo $edzesDb; ?></span> edzés</div>
                        <div class="stat-chip"><span class="stat-szam"><?php echo $baratDb; ?></span> barát</div>
                    </div>
                </div>
            </div>

            <?php if ($sajatProfil): ?>
            <div class="profil-tartalom-grid">
            <div class="profil-mellék">
            <div class="profil-card profil-adatok">
                <h2>Személyes adatok <span class="privát-hint">(csak neked látható)</span></h2>
                <div class="profil-adatok-form">
                    <div class="form-sor">
                        <label>Magasság (cm)</label>
                        <input type="number" id="magassagInput" min="50" max="250" placeholder="pl. 175" value="<?php echo $profilUser["magassag"] ? (int)$profilUser["magassag"] : ""; ?>">
                    </div>
                    <div class="form-sor">
                        <label>Testsúly (kg)</label>
                        <input type="number" id="testsulyInput" min="20" max="300" placeholder="pl. 75" value="<?php echo $profilUser["testsuly"] ? (int)$profilUser["testsuly"] : ""; ?>">
                    </div>
                    <div class="form-sor">
                        <label>Nem</label>
                        <select id="nemSelect">
                            <option value="">—</option>
                            <option value="ferfi" <?php echo ($profilUser["nem"] ?? "") === "ferfi" ? "selected" : ""; ?>>Férfi</option>
                            <option value="no" <?php echo ($profilUser["nem"] ?? "") === "no" ? "selected" : ""; ?>>Nő</option>
                            <option value="mas" <?php echo ($profilUser["nem"] ?? "") === "mas" ? "selected" : ""; ?>>Egyéb</option>
                        </select>
                    </div>
                    <button type="button" id="adatokMentes" class="mentes-gomb-kicsi">Mentés</button>
                    <p id="adatokUzenet" class="form-uzenet"></p>
                </div>
            </div>

            <button type="button" id="kaloriaKalkulatorGomb" class="kaloria-kalkulator-gomb">Kalória kalkulátor</button>

            <div class="profil-card profil-naptar">
                <h2>Edzés napjai</h2>
                <div class="naptar-lapozo">
                    <?php if ($vanElozo): ?><a href="<?php echo $urlBase . $urlSep . "honap=" . $honapPrev; ?>" class="naptar-gomb" title="Előző hónap">←</a><?php else: ?><span class="naptar-gomb naptar-gomb-disabled">←</span><?php endif; ?>
                    <p class="naptar-honap"><?php echo htmlspecialchars($naptarHonapSzoveg); ?></p>
                    <?php if ($vanKovetkezo): ?><a href="<?php echo $urlBase . $urlSep . "honap=" . $honapNext; ?>" class="naptar-gomb" title="Következő hónap">→</a><?php else: ?><span class="naptar-gomb naptar-gomb-disabled">→</span><?php endif; ?>
                </div>
                <div class="naptar-grid">
                    <?php
                    $honapStartTs = strtotime($valasztottHonap . "-01");
                    $napokSzama = date("t", $honapStartTs);
                    for ($i = 1; $i <= $napokSzama; $i++):
                        $d = date("Y-m-d", mktime(0,0,0, (int)date("n", $honapStartTs), $i, (int)date("Y", $honapStartTs)));
                        $van = in_array($d, $edzesNapok);
                    ?>
                    <div class="naptar-nap <?php echo $van ? "edzett" : ""; ?>" title="<?php echo $van ? "Edzett ezen a napon" : ""; ?>"><?php echo $i; ?></div>
                    <?php endfor; ?>
                </div>
                <p class="naptar-jelmagy">A kitöltött napok az edzéseket jelölik.</p>
            </div>
            </div>

            <div class="profil-card profil-edzesek profil-edzesek-fo">
                <h2>Edzéseim – <?php echo htmlspecialchars($naptarHonapSzoveg); ?></h2>
                <?php if (empty($edzesek)): ?>
                    <p class="ures-hint">Még nincs befejezett edzésed.</p>
                <?php else: ?>
                    <ul class="edzes-lista">
                        <?php foreach ($edzesek as $e): ?>
                        <li>
                            <a href="edzes_reszletek.php?id=<?php echo (int)$e["id"]; ?>">
                                <span class="edzes-nev"><?php echo htmlspecialchars($e["nev"]); ?></span>
                                <span class="edzes-meta"><?php echo htmlspecialchars($e["datum"]); ?> • <?php echo gmdate("H:i", (int)$e["idotartam"]); ?> • <?php echo (int)$e["osszsuly"]; ?> kg</span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            </div>
            <?php else: ?>
            <div class="profil-tartalom-grid">
            <div class="profil-card profil-edzesek profil-edzesek-fo">
                <h2>Edzései – <?php echo htmlspecialchars($naptarHonapSzoveg); ?></h2>
                <?php if (empty($edzesek)): ?>
                    <p class="ures-hint">Még nincs befejezett edzése.</p>
                <?php else: ?>
                    <ul class="edzes-lista">
                        <?php foreach ($edzesek as $e): ?>
                        <li>
                            <a href="edzes_reszletek.php?id=<?php echo (int)$e["id"]; ?>">
                                <span class="edzes-nev"><?php echo htmlspecialchars($e["nev"]); ?></span>
                                <span class="edzes-meta"><?php echo htmlspecialchars($e["datum"]); ?> • <?php echo gmdate("H:i", (int)$e["idotartam"]); ?> • <?php echo (int)$e["osszsuly"]; ?> kg</span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <div class="profil-mellék">
            <div class="profil-card profil-naptar">
                <h2>Edzés napjai</h2>
                <div class="naptar-lapozo">
                    <?php if ($vanElozo): ?><a href="<?php echo $urlBase . $urlSep . "honap=" . $honapPrev; ?>" class="naptar-gomb" title="Előző hónap">←</a><?php else: ?><span class="naptar-gomb naptar-gomb-disabled">←</span><?php endif; ?>
                    <p class="naptar-honap"><?php echo htmlspecialchars($naptarHonapSzoveg); ?></p>
                    <?php if ($vanKovetkezo): ?><a href="<?php echo $urlBase . $urlSep . "honap=" . $honapNext; ?>" class="naptar-gomb" title="Következő hónap">→</a><?php else: ?><span class="naptar-gomb naptar-gomb-disabled">→</span><?php endif; ?>
                </div>
                <div class="naptar-grid">
                    <?php
                    $honapStartTsMas = strtotime($valasztottHonap . "-01");
                    $napokSzamaMas = date("t", $honapStartTsMas);
                    for ($i = 1; $i <= $napokSzamaMas; $i++):
                        $d = date("Y-m-d", mktime(0,0,0, (int)date("n", $honapStartTsMas), $i, (int)date("Y", $honapStartTsMas)));
                        $van = in_array($d, $edzesNapok);
                    ?>
                    <div class="naptar-nap <?php echo $van ? "edzett" : ""; ?>" title="<?php echo $van ? "Edzett ezen a napon" : ""; ?>"><?php echo $i; ?></div>
                    <?php endfor; ?>
                </div>
                <p class="naptar-jelmagy">A kitöltött napok az edzéseket jelölik.</p>
            </div>
            <?php if (!empty($profilUser["nem"]) && isset($nemSzoveg[$profilUser["nem"]])): ?>
            <div class="profil-card profil-nem-mas">
                <p><span class="label">Nem:</span> <?php echo htmlspecialchars($nemSzoveg[$profilUser["nem"]]); ?></p>
            </div>
            <?php endif; ?>
            </div>
            </div>
            <?php endif; ?>
        </section>

        <aside class="profil-oldal">
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

<?php if ($sajatProfil): ?>
<div id="kaloriaPopup" class="popup-overlay">
    <div class="popup-kalkulator">
        <button type="button" class="popup-close" aria-label="Bezárás">×</button>
        <h2>Kalória kalkulátor</h2>
        <div class="kalkulator-form">
            <div class="form-sor">
                <label>Életkor</label>
                <input type="number" id="kalkEletkor" min="10" max="120" placeholder="pl. 30">
            </div>
            <div class="form-sor">
                <label>Magasság (cm)</label>
                <input type="number" id="kalkMagassag" min="50" max="250" placeholder="pl. 175" value="<?php echo $profilUser["magassag"] ? (int)$profilUser["magassag"] : ""; ?>">
            </div>
            <div class="form-sor">
                <label>Testsúly (kg)</label>
                <input type="number" id="kalkTomeg" min="20" max="300" placeholder="pl. 75" value="<?php echo $profilUser["testsuly"] ? (int)$profilUser["testsuly"] : ""; ?>">
            </div>
            <div class="form-sor">
                <label>Nem</label>
                <select id="kalkNem">
                    <option value="">—</option>
                    <option value="ferfi" <?php echo ($profilUser["nem"] ?? "") === "ferfi" ? "selected" : ""; ?>>Férfi</option>
                    <option value="no" <?php echo ($profilUser["nem"] ?? "") === "no" ? "selected" : ""; ?>>Nő</option>
                </select>
            </div>
            <div class="form-sor">
                <label>Cél</label>
                <select id="kalkCel">
                    <option value="szintentartas">Súlyszinten tartás</option>
                    <option value="fogyas">Fogyás</option>
                    <option value="tomegnoveles">Tömegnövelés</option>
                </select>
            </div>
            <button type="button" id="kalkSzamit">Számítás</button>
            <p id="kalkEredmeny" class="kalk-eredmeny"></p>
        </div>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<?php if (!$sajatProfil && $bejelentkezve): ?>
<script src="../js/profil.js" defer></script>
<?php endif; ?>
<?php if ($sajatProfil && $bejelentkezve): ?>
<script src="../js/profil_sajat.js" defer></script>
<?php endif; ?>
</body>
</html>
