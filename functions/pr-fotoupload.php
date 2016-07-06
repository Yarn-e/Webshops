<?php
/**
 * Foto vh product uploaden.
 *
 *
 * @param int $winkel
 *   ID van de winkel
 * @param string $prodname
 *   Productnaam
 * @param int $aantalfotos
 *   Aantal producten in die winkel
 *
 *
 * @return bool|string
 *   Als het juist is geef de naam van het bestand terug.
 *   Indien fout, false terug geven.
 */
function model_product_upload_img($winkel, $prodname) {
    $random = rand(0,10000);
    $newname = $winkel . '_' . $prodname . '_' . $random;
    $target_file = 'ProductImage/'  . $_FILES["prFoto"]["name"];
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $target_file = 'ProductImage/' . $newname . '.' . $imageFileType;
    $newname = $newname . '.' . $imageFileType;


    // Check if image file is a actual image or fake image
    if (isset($_POST["submit"])) {
        $check = getimagesize($_FILES["prFoto"]["tmp_name"]);
        if ($check == false) { 
            $_SESSION['fotoFout'] = '<script>$.notify({title: "<strong>FOUT: </strong>"}, {message: "gelieve een andere foto te selecteren"}, {type: "danger"});</script>';
            return false;
        }
    }
 
    // Check if file already exists
    if (file_exists($target_file)) { 
        $_SESSION['fotoFout'] = '<script>$.notify({title: "<strong>FOUT: </strong>"}, {message: "hernoem uw foto"}, {type: "danger"});</script>';
        return false;
    }
    // Check file size
    if ($_FILES["prFoto"]["size"] > 1500000) {
        
        $_SESSION['fotoFout'] = '<script>$.notify({title: "<strong>FOUT: </strong>"}, {message: "bestand te groot"}, {type: "danger"});</script>';
        return false;
    }
    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
        $_SESSION['fotoFout'] = '<script>$.notify({title: "<strong>FOUT: </strong>"}, {message: "gelieve een van volgende formaten te gebruiker: JPG, JPEG, PNG, GIF"}, {type: "danger"});</script>';
        return false;
    }
    
    if (move_uploaded_file($_FILES["prFoto"]["tmp_name"], $target_file)) {
        return $newname;
    } else {
        $_SESSION['fotoFout'] = '<script>$.notify({title: "<strong>FOUT: </strong>"}, {message: "gelieve een andere foto te selecteren"}, {type: "danger"});</script>';
        return false;
    }
}


/**
 * Kijkt hoeveel foto's er zijn van een bepaalde winkel
 * @param $winkels
 * @return bool/int
 *
 */
function model_products_count_img($winkel) {
    global $link;
    $winkel = $link->real_escape_string($winkel);
    $sql = "SELECT COUNT(Foto) as FotoAantal FROM Producten WHERE Foto LIKE '$winkel%'";
    $res = $link->query($sql);

    if (!$res) {
        return 1;
    } else {
        $row = $res->fetch_assoc();
        return $row['FotoAantal'] + 1;
    }
}