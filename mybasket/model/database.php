<?php

/**
 * Verwijderd alle gebruikers die de laatste 2 jaar geen bestelling hebben gedaan.
 *
 * @return bool
 */
function model_database_delete_gebruikers()
{
    $db = app_db();
    $sql = "SELECT IDBestelling FROM Bestelling WHERE (Koper, TijdVanAankoop) IN (
    SELECT Koper, max(TijdVanAankoop) AS date FROM Bestelling GROUP BY Koper
)";

    $res = $db->query($sql);
    $data = array();
    while ($row = $res->fetch_assoc()) {
        $data[$row['IDBestelling']] = $row;
    }


    $date = date('Y-m-d', strtotime('-2 year'));
    $bestellingen = implode(', ', array_keys($data));
    $sql = "DELETE Users FROM Users INNER JOIN Bestelling ON (IDUser = Koper) WHERE TijdvanAankoop < '" . $date ."' AND IDBestelling IN (" . $bestellingen . ") AND Permissie = 1";

    $res = $db->query($sql);
    if (!$res) {
        return false;
    }
    return true;
}

/**
 * Zet alle producten op actief.
 *
 * @return bool
 */
function model_database_activate_products()
{
    $db = app_db();
    $sql = "UPDATE Producten SET StatusProduct = 0";
    $res = $db->query($sql);
    if (!$res) {
        return false;
    }
    return true;
}

/**
 * Zet alle winkels op actief.
 *
 * @return bool
 */
function model_database_activate_winkels()
{
    $db = app_db();
    $sql = "UPDATE Winkels SET Activatie = 0";
    $res = $db->query($sql);
    if (!$res) {
        return false;
    }
    return true;
}

/**
 * Zet alle producten op deactief.
 *
 * @return bool
 */
function model_database_deactivate_products()
{
    $db = app_db();
    $sql = "UPDATE Producten SET StatusProduct = 1";
    $res = $db->query($sql);
    if (!$res) {
        return false;
    }
    return true;
}

/**
 * Zet alle winkels op deactief.
 *
 * @return bool
 */
function model_database_deactivate_winkels()
{
    $db = app_db();
    $sql = "UPDATE Winkels SET Activatie = 2";
    $res = $db->query($sql);
    if (!$res) {
        return false;
    }
    return true;
}