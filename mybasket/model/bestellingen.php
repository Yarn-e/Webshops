<?php

/**
 * Laad alle bestellingen uit de DB
 *
 * @return array $bestellingen
 */
function model_bestellingen_all()
{
    $db = app_db();
    $sql = 'SELECT B.IDBestelling as IDBestelling, D.IDDetail AS IDDetail, U.Voornaam AS Voornaam, U.Achternaam AS Achternaam, P.Productnaam AS Productnaam, D.Aantal AS Aantal, D.Eenheidsprijs AS Eenheidsprijs, W.Winkelnaam AS Winkelnaam, B.StatusBestelling as Status
            FROM BestellingDetail AS D, Producten AS P, Users AS U, Bestelling AS B, Winkels AS W
            WHERE D.IDBestelling = B.IDBestelling AND B.Koper = U.IDUser AND D.Productnummer = P.IDProduct AND P.Winkel = W.IDWinkel AND B.StatusBestelling > 0';
    $res = $db->query($sql);

    $bestellingen = array();
    while ($row = $res->fetch_assoc()) {
        $bestellingen[$row['IDDetail']] = $row;
    }

    return $bestellingen;
}

/**
 * Laad alle bestellingen uit de DB van specifieke winkel
 *
 * @param array $winkels
 *
 * @return array $bestellingen
 */
function model_bestellingen_by_winkel($winkels)
{
    $bestellingen = array();

    if (!is_array($winkels)) {
        $winkels = array($winkels);
    }

    $db = app_db();
    $sql = array();
    // What.
    $sql[] = 'SELECT B.IDBestelling as IDBestelling, D.IDDetail AS IDDetail, U.Voornaam AS Voornaam, U.Achternaam AS Achternaam, P.Productnaam AS Productnaam, D.Aantal AS Aantal, D.Eenheidsprijs AS Eenheidsprijs, W.Winkelnaam as Winkelnaam, B.StatusBestelling as Status';

    // From where.
    $sql[] = 'FROM';
    $sql[] = 'BestellingDetail AS D';
    $sql[] = 'INNER JOIN Bestelling AS B ON (D.IDBestelling = B.IDBestelling)';
    $sql[] = 'INNER JOIN Producten AS P ON (D.Productnummer = P.IDProduct)';
    $sql[] = 'INNER JOIN Users AS U ON (B.Koper = U.IDUser)';
    $sql[] = 'INNER JOIN Winkels AS W ON (P.Winkel = W.IDWinkel)';

    // Criteria.
    $sql[] = 'WHERE';
    $winkelsIn = implode(', ', $winkels);
    $sql[] = 'W.IDWinkel IN (' . $winkelsIn . ')';
    $sql[] = 'AND B.StatusBestelling NOT IN (0,1)';
    $sql[] = 'AND U.Permissie = 1';

    $res = $db->query(implode(' ', $sql));
    if (!$res) {
        return $bestellingen;
    }

    while ($row = $res->fetch_assoc()) {
        $bestellingen[$row['IDDetail']] = $row;
    }
    return $bestellingen;
}


/**
 * Telt alle bestellingen op van de laatste maand.
 *
 * @return array $date
 *  Array van datums als key
 */
function model_bestellingen_count_month()
{
    $start = new DateTime();
    $start->sub(new DateInterval('P31D'));

    $data = array();
    for ($d = 0; $d <= 31; $d++) {
        $data[$start->format('d-m-Y')] = 0;
        $start->add(new DateInterval('P1D'));
    }
    $db = app_db();
    $sql = array();

    //SELECT
    $sql[] = "SELECT TijdVanAankoop AS Datum, count(1) AS Totaal";

    //FROM
    $sql[] = 'FROM Bestelling';

    //WHERE
    $sql[] = 'WHERE TijdVanAankoop >= ' . $start->format('Y-m-d') . ' AND StatusBestelling IN (2,3)';

    //GROUP
    $sql[] = 'GROUP BY TijdVanAankoop';

    //ORDER
    $sql[] = 'ORDER BY TijdVanAankoop ASC';


    $res = $db->query(implode(' ', $sql));
    if (!$res) {
        return $nothing = array();
    }

    // FOREACH
    while ($row = $res->fetch_assoc()) {
        $row['Datum'] = strtotime($row['Datum']);
        $row['Datum'] = date('d-m-Y', $row['Datum']);
        $data[$row['Datum']] = $row['Totaal'];
    }

    return $data;
}

/**
 * Telt alle bestellingen op van de laatste maand van bepaalde winkels.
 *
 * @return array $date
 *  Array van datums als key
 */
function model_bestellingen_count_month_by_winkels($winkels)
{

    if (!is_array($winkels)) {
        $winkels = array($winkels);
    }

    $start = new DateTime();
    $start->sub(new DateInterval('P31D'));

    $data = array();
    for ($d = 0; $d <= 31; $d++) {
        $data[$start->format('d-m-Y')] = 0;
        $start->add(new DateInterval('P1D'));
    }
    $db = app_db();
    $sql = array();

    //SELECT
    $sql[] = "SELECT TijdVanAankoop AS Datum, count(1) AS Totaal";

    //FROM
    $sql[] = 'FROM Bestelling';

    //WHERE
    $winkelsIn = implode(', ', $winkels);
    $sql[] = 'WHERE TijdVanAankoop >= ' . $start->format('Y-m-d') . ' AND StatusBestelling IN (2,3) AND Winkel IN (' . $winkelsIn . ')';

    //GROUP
    $sql[] = 'GROUP BY TijdVanAankoop';

    //ORDER
    $sql[] = 'ORDER BY TijdVanAankoop ASC';


    $res = $db->query(implode(' ', $sql));
    if (!$res) {
        return $nothing = array();
    }

    // FOREACH
    while ($row = $res->fetch_assoc()) {
        $row['Datum'] = strtotime($row['Datum']);
        $row['Datum'] = date('d-m-Y', $row['Datum']);
        $data[$row['Datum']] = $row['Totaal'];
    }

    return $data;
}

/**
 * Zet de status van de bestelling op afgehandeld.
 *
 * @param $IDBestelling
 *
 * @return bool
 */
function model_bestellingen_afhandel($IDBestelling) {
    $db = app_db();
    $IDBestelling = $db->real_escape_string($IDBestelling);
    $sql = "UPDATE Bestelling SET
            StatusBestelling = '3'
            WHERE IDBestelling = '$IDBestelling'
    ";

    $res = $db->query($sql);
    if (!$res) {
        return false;
    } elseif($db->affected_rows == 0) {
        return false;
    }
    return true;
}

/**
 * Zet de status van de bestelling op geannuleerd.
 *
 * @param $IDBestelling
 *
 * @return bool
 */
function model_bestellingen_annuleer($IDBestelling) {
    $db = app_db();
    $IDBestelling = $db->real_escape_string($IDBestelling);
    $sql = "UPDATE Bestelling SET
            StatusBestelling = '4'
            WHERE IDBestelling = '$IDBestelling'
    ";

    $res = $db->query($sql);
    if (!$res) {
        return false;
    } elseif($db->affected_rows == 0) {
        return false;
    }
    return true;
}


/**
 * Haalt de 3 best verkopende producten van de laatste maand op.
 * 
 * @param $winkels
 * @return array
 */
function model_bestellingen_top3_by_winkels($winkels) {

    if (!is_array($winkels)) {
        $winkels = array($winkels);
    }
    $db = app_db();
    $sql = array();

    $start = new DateTime();
    $start->sub(new DateInterval('P31D'));
    //SELECT
    $sql[] = "SELECT P.Productnaam as Product, COUNT(D.Productnummer) AS Populair, D.Aantal AS Aantal, D.Eenheidsprijs AS Prijs";

    //FROM
    $sql[] = 'FROM';
    $sql[] = 'BestellingDetail AS D';
    $sql[] = 'INNER JOIN Bestelling AS B ON (D.IDBestelling = B.IDBestelling)';
    $sql[] = 'INNER JOIN Producten AS P ON (D.Productnummer = P.IDProduct)';

    //WHERE
    $winkelsIn = implode(', ', $winkels);
    $sql[] = 'WHERE B.TijdVanAankoop >= ' . $start->format('Y-m-d') . ' AND B.StatusBestelling IN (2,3) AND B.Winkel IN (' . $winkelsIn . ')';

    //GROUP
    $sql[] = 'GROUP BY Productnummer';

    //ORDER
    $sql[] = 'ORDER BY Populair DESC';

    //LIMIT
    $sql[] = 'LIMIT 3';

    $res = $db->query(implode(' ', $sql));
    if (!$res) {
        return $nothing = array();
    }

    // FOREACH
    $i = 1;
    $data = array();
    while ($row = $res->fetch_assoc()) {
        $data[$i] = $row;
        ++$i;
    }

    return $data;

}