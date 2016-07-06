<?php

/**
 * Laad alle winkels van de DB.
 *
 * @return array
 *     Array van winkeldetails.
 */
function model_winkels_all()
{
    $db = app_db();
    $sql = 'SELECT * FROM Winkels';
    $res = $db->query($sql);

    $winkels = array();
    while ($row = $res->fetch_assoc()) {
        $winkels[$row['IDWinkel']] = $row;
    }

    return $winkels;
}

/**
 * Laad winkels op basis van de Winkelbeheerder.
 *
 * @param int $Beheerder
 *
 * @return array
 *     Winkel details.
 */
function model_winkels_by_Beheerder($Beheerder)
{
    $db = app_db();
    $Beheerder = $db->real_escape_string($Beheerder);
    $sql = "SELECT * FROM Winkels WHERE Winkelbeheerder = '$Beheerder'";
    $res = $db->query($sql);

    if (!$res) {
        return false;
    }
    $winkels = array();
    while ($row = $res->fetch_assoc()) {
        $winkels[$row['IDWinkel']] = $row;
    }

    return $winkels;
}

/**
 * Laad winkels op basis van de Supervisor.
 *
 * @param int $supervisor
 *
 * @return array
 *     Winkel details.
 */
function model_winkels_by_Supervisor($supervisor)
{
    $db = app_db();
    $supervisor = $db->real_escape_string($supervisor);
    $sql = "SELECT * FROM Winkels WHERE Supervisor = '$supervisor'";
    $res = $db->query($sql);

    if (!$res) {
        return false;
    }
    $winkels = array();
    while ($row = $res->fetch_assoc()) {
        $winkels[$row['IDWinkel']] = $row;
    }

    return $winkels;
}


/**
 * Geeft alle winkelnamen
 *
 * @return array
 *      Array van alle winkelnamen
 */
function model_winkels_select_naam()
{
    $db = app_db();
    $sql = 'SELECT IDWinkel, Winkelnaam FROM Winkels';
    $res = $db->query($sql);

    $winkels = array();
    while ($winkel = $res->fetch_assoc()) {
        $winkels[$winkel['IDWinkel']] = array(
            'value' => $winkel['IDWinkel'],
            'label' => $winkel['Winkelnaam']
        );
    }

    return $winkels;
}

/**
 * Geeft alle winkelnamen via de Winkelbeheerder.
 *
 * @return array
 *      Array van alle winkelnamen
 */
function model_winkels_select_naam_by_Beheer($beheer)
{
    $db = app_db();
    $beheer = $db->real_escape_string($beheer);

    $sql = 'SELECT IDWinkel, Winkelnaam FROM Winkels WHERE Winkelbeheerder =' . $beheer;
    $res = $db->query($sql);

    $winkels = array();
    while ($winkel = $res->fetch_assoc()) {
        $winkels[$winkel['IDWinkel']] = array(
            'value' => $winkel['IDWinkel'],
            'label' => $winkel['Winkelnaam']
        );
    }

    return $winkels;
}

/**
 * Geeft alle winkelnamen via de Supervisor.
 *
 * @return array
 *      Array van alle winkelnamen
 */
function model_winkels_select_naam_by_Supervisor($supervisor)
{
    $db = app_db();
    $supervisor = $db->real_escape_string($supervisor);

    $sql = 'SELECT IDWinkel, Winkelnaam FROM Winkels WHERE Supervisor =' . $supervisor;
    $res = $db->query($sql);

    $winkels = array();
    while ($winkel = $res->fetch_assoc()) {
        $winkels[$winkel['IDWinkel']] = array(
            'value' => $winkel['IDWinkel'],
            'label' => $winkel['Winkelnaam']
        );
    }

    return $winkels;
}

/**
 *  Geef de winkel waar de bediende aan hangt
 *
 * @param int $bediende
 *
 * @return array $winkel
 */
function model_winkels_by_bediende($bediende) {
    $db = app_db();
    $bediende = $db->real_escape_string($bediende);

    $sql = 'SELECT W.IDWinkel as IDWinkel, W.Winkelnaam as Winkelnaam FROM Winkels as W INNER JOIN UsersWinkels as UW ON (W.IDWinkel = UW.IDWinkel) WHERE UW.IDUser = ' . $bediende;
    $res = $db->query($sql);

    if (!$res) {
        return false;
    }

    $winkels = array();
    while ($row = $res->fetch_assoc()) {
        $winkels = $row;
    }

    return $winkels;
}
/**
 * Geef het aantal winkels weer.
 *
 * @return integer
 *     Het aantal winkels dat er zijn.
 */
function model_winkels_count()
{
    $db = app_db();
    $sql = 'SELECT COUNT(IDWinkel) AS Winkel FROM Winkels';
    $res = $db->query($sql);
    $row = $res->fetch_assoc();

    return (int)$row['Winkel'];
}

/**
 * Geef het aantal winkels weer van een Mini-Admin.
 *
 * @return integer
 *     Het aantal winkels dat er zijn.
 */
function model_winkels_count_by_beheer($beheer)
{
    $db = app_db();
    $sql = 'SELECT COUNT(IDWinkel) AS Winkel FROM Winkels WHERE Winkelbeheerder =' . $beheer;
    $res = $db->query($sql);
    $row = $res->fetch_assoc();

    return (int)$row['Winkel'];
}

/**
 * Geef het aantal winkels weer van een Supervisor.
 *
 * @return integer
 *     Het aantal winkels dat er zijn.
 */
function model_winkels_count_by_supervisor($supervisor)
{
    $db = app_db();
    $sql = 'SELECT COUNT(IDWinkel) AS Winkel FROM Winkels WHERE Supervisor =' . $supervisor;
    $res = $db->query($sql);
    $row = $res->fetch_assoc();

    return (int)$row['Winkel'];
}


/**
 * Laad 1 winkel op basis van de ID.
 *
 * @param int $IDwinkel
 *
 * @return array
 *     Winkel details.
 */
function model_winkels_by_IDWinkel($IDwinkel)
{
    $db = app_db();
    $IDwinkel = $db->real_escape_string($IDwinkel);
    $sql = "SELECT * FROM Winkels WHERE IDWinkel = '$IDwinkel'";
    $res = $db->query($sql);

    if (!$res) {
        return false;
    }

    return $res->fetch_assoc();
}



/**
 * Een winkel opslaan in de database.
 *
 * @param array $winkel
 */
function model_winkels_save(array $winkel)
{
    if (isset($winkel['IDWinkel'])) {
        return model_winkels_save_bestaand($winkel);
    }
    return model_winkels_save_new($winkel);
}

/**
 * Een winkel opslaan in de database.
 *
 * @param array $winkel
 *
 * @return string
 *  Geeft de actie terug.
 */
function model_winkels_save_new(array $winkel)
{
    $db = app_db();
    $sql = 'INSERT INTO 
        Winkels(
            Winkelnaam,
            Activatie,
            Banner,
            Winkelbeheerder,
            Supervisor
        )
        VALUES("%s", %d, "%s", %d, "%s")
    ';
    $res = $db->query(sprintf($sql,
        $db->real_escape_string($winkel['Winkelnaam']),
        $db->real_escape_string($winkel['Activatie']),
        $db->real_escape_string($winkel['Banner']),
        $db->real_escape_string($winkel['Winkelbeheerder']),
        $db->real_escape_string($winkel['Supervisor'])
    ));

    if (!$res) {
        return false;
    }
    return 'toegevoegd';
}

/**
 * Een winkel opslaan in de database.
 *
 * @param array $winkel
 *
 * @return string
 *  Geeft de actie terug.
 */
function model_winkels_save_bestaand(array $winkel)
{
    $db = app_db();
    $sql = 'UPDATE Winkels SET
            Winkelnaam = "%s",
            Activatie = %d,
            Banner = "%s",
            Winkelbeheerder = %d,
            Supervisor = "%s"
            WHERE IDWinkel = %d
    ';
    $res = $db->query(sprintf($sql,
        $db->real_escape_string($winkel['Winkelnaam']),
        $db->real_escape_string($winkel['Activatie']),
        $db->real_escape_string($winkel['Banner']),
        $db->real_escape_string($winkel['Winkelbeheerder']),
        $db->real_escape_string($winkel['Supervisor']),
        $db->real_escape_string($winkel['IDWinkel'])

    ));


    if (!$res) {
        return false;
    } elseif($db->affected_rows == 0) {
        return false;
    }
    return 'gewijzigd';
}


/**
 * Winkels tonen die actief zijn.
 * @return array
 */
function model_winkels_by_all_actief()
{
    $db = app_db();
    $sql = 'SELECT * FROM Winkels WHERE Activatie = 0';
    $res = $db->query($sql);

    $winkels = array();
    while ($row = $res->fetch_assoc()) {
        $winkels[$row['IDWinkel']] = $row;
    }

    return $winkels;
}

/**
 * Winkel verijwderen via IDWinkel.
 *
 * @param type $IDWinkel
 * @param type $Banner
 *
 * @return boolean
 */
function model_winkels_delete_by_IDWinkel($IDWinkel, $Banner)
{
    $db = app_db();
    $IDWinkel = $db->real_escape_string($IDWinkel);
    $sql = "DELETE FROM Winkels WHERE IDWinkel = '$IDWinkel'";
    $res = $db->query($sql);

    if(!$res) {
        return false;
    }

    $Banner = DIR_APP_IMG . '/' . $Banner;
    if (!unlink($Banner)) {
        return false;
    }

    return true;
}

/**
 * Banner van de winkel uploaden.
 *
 * @param string $winkelnaam
 *
 * @return bool|string
 *   Als het juist is geef de naam van het bestand terug.
 *   Indien fout, false terug geven.
 */
function model_winkels_upload_img($winkelnaam)
{
    $random = rand(0,10000);
    $target_file = DIR_APP_IMG . '/' . basename($_FILES["banner"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $newname = $winkelnaam . '_' . $random . '.' . $imageFileType;
    $target_file = DIR_APP_IMG . '/' . $newname;
    
    // Check if image file is a actual image or fake image
    if (isset($_POST["submit"])) {
        $check = getimagesize($_FILES["banner"]["tmp_name"]);
        if ($check == false) {
            return false;
        }
    }

    if (empty($imageFileType)) {
        return false;
    }

    // Check if file already exists
    if (file_exists($target_file)) {
        return $newname;
    }
    // Check file size
    if ($_FILES["banner"]["size"] > 1000000) {
        return false;
    }
    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
        return false;
    }

    if (move_uploaded_file($_FILES["banner"]["tmp_name"], $target_file)) {
        return $newname;
    } else {
        return false;
    }
}

