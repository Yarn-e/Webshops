<?php

/**
 * Kijkt of gebruiker bestaat en logt in.
 * 
 * @param string $email, string $pwd
 * 
 * @return array
 *   indien er een fout is bij het ww moet het emailadres onthouden worden.
 */
function login_all($email, $pwd) {
    global $link;
    global $_SESSION;
    global $ingevuldveld;
    
    $email = trim($email);
    $pass = trim($pwd);
    $sql = "SELECT IDUser, Wachtwoord, Permissie, Voornaam, Achternaam FROM Users WHERE Email = '$email'";
    if (!$res = $link->query($sql)) {
        echo 'Error';
    } else {
        $numRows = $res->num_rows;
        if ($numRows == 0) {
            $_SESSION['LogAlert'] = '<strong>FOUT bij aanmelden: </strong>Gelieve een geldig e-mailadres te selecteren.';
        } else {
            $row = $res->fetch_assoc();
            if (password_verify($pass, $row['Wachtwoord'])) {
                $_SESSION['fingerprint'] = password_hash(($_SERVER['HTTP_USER_AGENT'] . "GO-AO_Webshops" . $_SERVER['REMOTE_ADDR']), PASSWORD_DEFAULT);
                $_SESSION['logged_in'] = true;
                $_SESSION['userID'] = $row['IDUser'];
                $_SESSION['user'] = $row['Voornaam'] . " " . $row['Achternaam'];
                $_SESSION['mail'] = $email;
                if ($row['Permissie'] == 5) {
                    $_SESSION['supervisor'] = true;
                    header('Location:dashboard/index.php');
                } elseif ($row['Permissie'] == 4) {
                    $_SESSION['admin'] = true;
                    header('Location:dashboard/index.php');
                } elseif ($row['Permissie'] == 3) {
                    $_SESSION['winkelbeheerder'] = true;
                    header('Location:dashboard/index.php');
                } else {
                    $_SESSION['permission'] = $row['Permissie'];
                    $_SESSION['alert'] = '$.notify({title: "<strong>Aanmelden: </strong>",message:"Successvol ingelogd},{type: "alert bg-success"});';
                    header('Location:index.php');
                }
				
            } else {
                $_SESSION['LogAlert'] = '<strong>FOUT bij aanmelden: </strong>Wachtwoord is niet correct.';
                $ingevuldveld['email'] = $email;
            }
        }
    }
}

/**
 * Hashed een passwoord.
 * 
 * @param string $pwd
 * 
 * @return string $pwdHashed
 *   Stuurt het gehashed paswoord terug
 */
function login_hashen($pwd) {
    $pwdHashed = password_hash($pwd, PASSWORD_DEFAULT);
    return $pwdHashed;
}

/**
 * Checkt of het passwoord klopt met die van de database
 * 
 * @param int $userid, string $pwdHerh
 * 
 * @return boolean
 *   indien de wachtwoorden identiek zijn true anders false.
 */
function login_checkPassword($userid, $pwd) {
    global $link;

    $sql = "SELECT Wachtwoord FROM Users WHERE IDUser = '$userid'";

    if (!$res = $link->query($sql)) {
        return false;
    }
    if ($res->num_rows == 0) {
        return false;
    }

    $row = $res->fetch_assoc();
    if (!password_verify($pwd, $row['Wachtwoord'])) {
        return false;
    }
    return true;
}

/**
 * Haalt alle gegevens op van de gebruiker via zijn ID
 * @param $id
 *
 * @return array $gegevens
 *   Alle usergegevens
 */
function login_gegevens_by_ID($id) {
    global $link;

    $id = $link->real_escape_string($id);

    $sql = "SELECT Voornaam, Achternaam, Email, Geslacht, Geboortedatum FROM Users WHERE IDUser = '$id' LIMIT 1";

    if (!$res = $link->query($sql)) {
        return false;
    }

    $row = $res->fetch_assoc();
    return $row;
}


/**
 * Kijken of een mail niet wordt gebruikt door een gebruiker
 *
 * @param string $mail, int $userid
 * @return boolean
 */
function login_mailcheck($mail, $userid) {
    global $link;
    $mail = $link->real_escape_string($mail);
    $userid = $link->real_escape_string($userid);

    $sql = "SELECT Email FROM Users WHERE Email = '$mail' AND IDUser != $userid";
    $res = $link->query($sql);

    if (!$res) {
        return true;
    } elseif ($res->num_rows == 0) {
        return true;
    } else {
        return false;
    }

}

/**
 * Update de gebruiker met nieuwe gegevens.
 * @param array $data
 *
 * @return bool
 */
function login_update_user(array $data, $userid) {
    global $link;
    $sql = "UPDATE Users SET
                            Voornaam = '".$data['Voornaam']."',
                            Achternaam = '".$data['Achternaam']."',
                            Email = '".$data['Email']."',
                            Geslacht = '".$data['Geslacht']."' 
                            WHERE IDUser = '".$userid."'";
    $res = $link->query($sql);
    if (!$res) {
        return false;
    } else {
        return true;
    }
    
}

/**
 * Kijken of de gebruiker van SMS al bestaat.
 *
 * @param int $SMSnummer
 *
 * @return bool/int
 *  False: Gebruiker bestaat niet
 *  True: ID van de gebruiker meegeven
 *
 */
function login_check_sms($SMSnummer) {
    global $link;
    $SMSnummer = $link->real_escape_string($SMSnummer);

    $sql = "SELECT IDUser, Permissie FROM Users WHERE Stamboeknummer = '$SMSnummer' LIMIT 1";

    $res = $link->query($sql);

    if (!$res) {
        return false;
    } elseif($res->num_rows == 0) {
        return false;
    } else {
        $rec = $res->fetch_assoc();
        return $rec;
    }
}

/**
 * SMS gebruiker toevoegen.
 *
 * @param string $voornaam, string $naam, int $stamnr
 *
 * @return bool
 */
function login_add_sms($voornaam, $naam, $stamnr) {
    global $link;

    $voornaam = $link->real_escape_string($voornaam);
    $naam = $link->real_escape_string($naam);
    $stamnr = $link->real_escape_string($stamnr);

    $sql = 'INSERT INTO
        Users(
            Voornaam,
            Achternaam,
            Permissie,
            Stamboeknummer
        )
        VALUES("%s", "%s", %d, %d)
    ';

    $res = $link->query(sprintf($sql,
        $link->real_escape_string($voornaam),
        $link->real_escape_string($naam),
        $link->real_escape_string(1),
        $link->real_escape_string($stamnr)
    ));

    if (!$res) {
        return false;
    }

    return true;

}

