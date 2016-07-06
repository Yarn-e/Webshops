<?php

/**
 * Laad alle producten uit de DB
 *
 * @return array
 *     Array van productdetails.
 */
function model_products_all()
{
    $db = app_db();
    $sql = 'SELECT IDProduct, Productnaam, Foto, Prijs, Uitleg, BTW, Maxaantal, Winkel, Winkelnaam, StatusProduct
            FROM Producten, Winkels
            WHERE Winkel = IDWinkel';
    $res = $db->query($sql);

    $producten = array();
    while ($row = $res->fetch_assoc()) {
        $producten[$row['IDProduct']] = $row;
    }

    return $producten;
}


/**
 * Geef 1 product via zijn winkel.
 *
 * @param int $IDWinkel
 *
 * @return array
 *     Array van de productdetails.
 */
function model_products_by_winkel($IDWinkel)
{
    $db = app_db();
    $IDWinkel = $db->real_escape_string($IDWinkel);

    $sql = "SELECT IDProduct, Productnaam, Foto, Prijs, Uitleg, BTW, Maxaantal, Winkel, Winkelnaam, StatusProduct
            FROM Producten, Winkels
            WHERE Winkel = IDWinkel AND Winkel = $IDWinkel";
    $res = $db->query($sql);
    if (!$res) {
        return false;
    }

    $producten = array();
    while ($row = $res->fetch_assoc()) {
        $producten[$row['IDProduct']] = $row;
    }

    return $producten;
}

/**
 * Geef 1 product via zijn ID
 *
 * @return array
 *     Array van de productdetails.
 */
function model_products_by_IDProduct($IDProduct)
{
    $db = app_db();
    $IDProduct = $db->real_escape_string($IDProduct);

    $sql = "SELECT *
            FROM Producten
            WHERE IDProduct = '$IDProduct'";
    $res = $db->query($sql);

    if (!$res) {
        return false;
    }

    return $res->fetch_assoc();
}

/**
 * Geef producten via de winkelbeheerder
 *
 * @return array
 *     Array van productdetails.
 */
function model_products_by_Beheerder($beheerder)
{
    $db = app_db();
    $beheerder = $db->real_escape_string($beheerder);

    $sql = 'SELECT IDProduct, Productnaam, Foto, Prijs, Uitleg, BTW, Maxaantal, Winkel, Winkelnaam, StatusProduct
            FROM Producten, Winkels
            WHERE Winkel = IDWinkel AND Winkelbeheerder =' . $beheerder;
    $res = $db->query($sql);

    $producten = array();
    while ($row = $res->fetch_assoc()) {
        $producten[$row['IDProduct']] = $row;
    }

    return $producten;
}

/**
 * Geef producten via de Supervisor
 *
 * @return array
 *     Array van productdetails.
 */
function model_products_by_Supervisor($supervisor)
{
    $db = app_db();
    $supervisor = $db->real_escape_string($supervisor);

    $sql = 'SELECT IDProduct, Productnaam, Foto, Prijs, Uitleg, BTW, Maxaantal, Winkel, Winkelnaam, StatusProduct
            FROM Producten, Winkels
            WHERE Winkel = IDWinkel AND Supervisor =' . $supervisor;
    $res = $db->query($sql);

    $producten = array();
    while ($row = $res->fetch_assoc()) {
        $producten[$row['IDProduct']] = $row;
    }

    return $producten;
}



/**
 * Geef de naam van de foto terug via IDProduct
 *
 * @param integer $IDProduct
 *
 * @return string $foto
 */
function model_products_foto_by_IDProduct($IDProduct)
{
    $db = app_db();
    $sql = "SELECT Foto
            FROM Producten
            WHERE IDProduct = '" . $IDProduct . "'";
    $res = $db->query($sql);
    $row = $res->fetch_assoc();

    return (string)$row['Foto'];
}


/**
 * Geef het aantal producten weer van een winkelbeheerder zijn winkels.
 *
 * @return integer
 *     Het aantal producten dat er zijn.
 */
function model_products_count_by_beheer($beheerder)
{
    $db = app_db();
    $sql = 'SELECT COUNT(IDProduct) AS Product
            FROM Producten, Winkels
            WHERE Winkel = IDWinkel AND Winkelbeheerder =' . $beheerder;
    $res = $db->query($sql);
    $row = $res->fetch_assoc();

    return (int)$row['Product'];
}

/**
 * Geef het aantal producten weer van een winkelsupervisor zijn winkels.
 *
 * @return integer
 *     Het aantal producten dat er zijn.
 */
function model_products_count_by_supervisor($supervisor)
{
    $db = app_db();
    $sql = 'SELECT COUNT(IDProduct) AS Product
            FROM Producten, Winkels
            WHERE Winkel = IDWinkel AND Supervisor =' . $supervisor;
    $res = $db->query($sql);
    $row = $res->fetch_assoc();

    return (int)$row['Product'];
}

/**
 * Geef het aantal producten weer.
 *
 * @return integer
 *     Het aantal producten dat er zijn.
 */
function model_products_count()
{
    $db = app_db();
    $sql = 'SELECT COUNT(IDProduct) AS Product FROM Producten';
    $res = $db->query($sql);
    $row = $res->fetch_assoc();

    return (int)$row['Product'];
}

/**
 * Een product opslaan in de database.
 *
 * @param array $product
 * 
 * @return string
 */
function model_products_save(array $product)
{
    if (isset($product['IDProduct'])) {
        return model_products_save_bestaand($product);
    }
    return model_products_save_new($product);
}

/**
 * Een product opslaan in de database.
 *
 * @param array $product
 *
 * @return string
 *  Geeft de actie terug.
 */
function model_products_save_bestaand(array $product)
{
    $db = app_db();
    $sql = 'UPDATE Producten SET
            Productnaam = "%s",
            Foto = "%s",
            Prijs = %f,
            Uitleg = "%s",
            BTW = %d,
            Maxaantal = %d,
            Winkel = %d,
            StatusProduct = %d
            WHERE IDProduct = %d
    ';
    $res = $db->query(sprintf($sql,
        $db->real_escape_string($product['Productnaam']),
        $db->real_escape_string($product['Foto']),
        $db->real_escape_string($product['Prijs']),
        $db->real_escape_string($product['Uitleg']),
        $db->real_escape_string($product['BTW']),
        $db->real_escape_string($product['Maxaantal']),
        $db->real_escape_string($product['Winkel']),
        $db->real_escape_string($product['Status']),
        $db->real_escape_string($product['IDProduct'])
    ));

    if (!$res) {
        return false;
    } elseif($db->affected_rows == 0) {
        return false;
    }
    return 'gewijzigd';
}

/**
 * Een gebruiker opslaan in de database.
 *
 * @param array $product
 *
 * @return string
 *  Geeft de actie terug.
 */
function model_products_save_new(array $product)
{
    $db = app_db();
    $sql = 'INSERT INTO
        Producten(
            Productnaam,
            Foto,
            Prijs,
            Uitleg,
            BTW,
            Maxaantal,
            Winkel,
            StatusProduct
        )
        VALUES("%s", "%s", %f, "%s", %d, %d, %d, %d)
    ';
    $res = $db->query(sprintf($sql,
        $db->real_escape_string($product['Productnaam']),
        $db->real_escape_string($product['Foto']),
        $db->real_escape_string($product['Prijs']),
        $db->real_escape_string($product['Uitleg']),
        $db->real_escape_string($product['BTW']),
        $db->real_escape_string($product['Maxaantal']),
        $db->real_escape_string($product['Winkel']),
        $db->real_escape_string($product['Status'])
    ));
    if (!$res) {
        return false;
    }
    return 'toegevoegd';
}

/**
 * Product verijwderen via IDProduct.
 *
 * @param type $IDProduct
 * @param type $Foto
 *
 * @return boolean
 */
function model_products_delete_by_IDProduct($IDProduct, $Foto)
{
    $db = app_db();
    $IDProduct = $db->real_escape_string($IDProduct);
    $sql = "DELETE FROM Producten WHERE IDProduct = '$IDProduct'";
    $res = $db->query($sql);

    if (!$res) {
        return false;
    }

    $Foto = '../ProductImage/' . $Foto;
    if (!unlink($Foto)) {
        return false;
    }


    return true;
}

/**
 * Foto van een product uploaden.
 *
 * @param string $winkel
 *   Winkelnaam
 * @param integer $aantalfotos
 *   Aantal foto's
 *
 * @return bool|string
 *   Als het juist is geef de naam van het bestand terug.
 *   Indien fout, false terug geven.
 */
function model_products_upload_img($winkel, $prodname)
{
    $random = rand(0,10000);
    $newname = $winkel . '_' . $prodname . '_' . $random;
    $target_file = '../ProductImage/'  . $_FILES["Foto"]["name"];
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $target_file = '../ProductImage/' . $newname . '.' . $imageFileType;
    $newname = $newname . '.' . $imageFileType;
    // Check if image file is a actual image or fake image
    if (isset($_POST["submit"])) {
        $check = getimagesize($_FILES["Foto"]["tmp_name"]);
        if ($check == false) {
            return false;
        }
    }

    // Check if file already exists
    if (file_exists($target_file)) {
        return $_FILES["Foto"]["name"];
    }
    // Check file size
    if ($_FILES["Foto"]["size"] > 1000000) {
        return false;
    }
    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        return false;
    }

    if (move_uploaded_file($_FILES["Foto"]["tmp_name"], $target_file)) {
        return $newname;
    } else {
        return false;
    }
}