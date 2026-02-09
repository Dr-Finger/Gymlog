<?php
// Helper függvények

function getTervAdatok($conn, $tervId, $userId) {
    $check = $conn->query("SHOW TABLES LIKE 'edzesterv_mentes'");
    if (!$check || $check->num_rows === 0) {
        return null;
    }
    
    $stmt = $conn->prepare("SELECT nev, tartalom FROM edzesterv_mentes WHERE id = ? AND felhasznaloId = ?");
    if (!$stmt) {
        return null;
    }
    
    $stmt->bind_param("ii", $tervId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return [
            "nev" => $row["nev"],
            "tartalom" => json_decode($row["tartalom"], true) ?: []
        ];
    }
    
    return null;
}

function getTervek($conn, $userId) {
    $tervek = [];
    $check = $conn->query("SHOW TABLES LIKE 'edzesterv_mentes'");
    
    if ($check && $check->num_rows > 0) {
        $stmt = $conn->prepare("SELECT id, nev, tartalom, letrehozva FROM edzesterv_mentes WHERE felhasznaloId = ? ORDER BY letrehozva DESC");
        if ($stmt) {
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $tervek[] = $row;
            }
        }
    }
    
    return $tervek;
}

function formatGyakorlatReszletek($sor) {
    $set = isset($sor["set"]) ? (int)$sor["set"] : 0;
    $rep = isset($sor["rep"]) ? (int)$sor["rep"] : 0;
    $suly = isset($sor["suly"]) ? (int)$sor["suly"] : 0;
    
    $reszletek = [];
    if ($set > 0)  $reszletek[] = $set . "x";
    if ($rep > 0)  $reszletek[] = $rep . " ismétlés";
    if ($suly > 0) $reszletek[] = $suly . " kg";
    
    return !empty($reszletek) ? " – " . implode(", ", $reszletek) : "";
}
?>
