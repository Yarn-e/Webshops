<?php

define('USERS_PERMISSIE_KLANT', 1);
define('USERS_PERMISSIE_BEDIENDE', 2);
define('USERS_PERMISSIE_MINIADMIN', 3);
define('USERS_PERMISSIE_ADMIN', 4);
define('USERS_PERMISSIE_SUPERVISOR', 5);

/**
 * Laad alle users uit de DB
 *
 * @return array
 *     Array van userdetails.
 */
function model_users_all()
{
    $db = app_db();
    $sql = 'SELECT IDUser, Voornaam, Achternaam, Email, Geslacht, Geboortedatum, Permissie, permissieomschrijving, Stamboeknummer
            FROM Users, Permissies
            WHERE Permissie = IDPermissie
            ORDER BY IDPermissie';
    $res = $db->query($sql);

    $users = array();
    while ($row = $res->fetch_assoc()) {
        $users[$row['IDUser']] = $row;
    }
    return $users;
}


/**
 * Laad alle users uit de DB die van sms komen
 *
 * @return array
 *     Array van userdetails.
 */
function model_users_sms()
{
    $db = app_db();
    $sql = 'SELECT IDUser, Voornaam, Achternaam, Stamboeknummer, permissieomschrijving
            FROM Users
            INNER JOIN Permissies ON (Permissie = IDPermissie)
            WHERE Stamboeknummer > 0 AND Permissie <> 4
            ORDER BY Permissie';
    $res = $db->query($sql);

    $users = array();
    while ($row = $res->fetch_assoc()) {
        $users[$row['IDUser']] = $row;
    }
    return $users;
}

/**
 * Laad alle klanten op die een bestelling hebben bij een bepaalde winkel.
 *
 * @param array $winkels
 *
 * @return array
 *     Array van userdetails.
 */
function model_users_klanten($winkels)
{

    if (!is_array($winkels)) {
        $winkels = array($winkels);
    }

    $db = app_db();

    //What.
    $sql[] = 'SELECT U.IDUser AS IDUser, U.Voornaam AS Voornaam, U.Achternaam AS Achternaam, U.Email AS Email, U.Geslacht AS Geslacht, U.Geboortedatum AS Geboortedatum, U.Stamboeknummer as Stamboeknummer, Permissie AS Permissie, P.permissieomschrijving AS permissieomschrijving, W.IDWinkel as IDWinkel, W.Winkelnaam as Winkelnaam';

    // From where.
    $sql[] = 'FROM';
    $sql[] = 'Users AS U';
    $sql[] = 'INNER JOIN Permissies AS P ON (U.Permissie = P.IDPermissie)';
    $sql[] = 'INNER JOIN Bestelling AS B ON (U.IDUser = B.Koper)';
    $sql[] = 'INNER JOIN Winkels AS W ON (B.Winkel = W.IDWinkel)';

    //Criteria.
    $sql[] = 'WHERE';
    $winkelsIn = implode(', ', $winkels);
    $sql[] = 'W.IDWinkel IN (' . $winkelsIn . ')';
    $sql[] = 'AND U.Permissie = 1';

    $res = $db->query(implode(' ', $sql));

    if (!$res) {
        return false;
    }

    $users = array();
    while ($row = $res->fetch_assoc()) {
        $users[$row['IDUser']] = $row;
    }
    return $users;
}

/**
 * Geef 1 gebruiker via zijn ID
 *
 * @return array
 *     Array van de userdetails.
 */
function model_users_by_IDUser($IDUser)
{
    $db = app_db();
    $IDUser = $db->real_escape_string($IDUser);

    $sql = "SELECT *
            FROM Users
            WHERE IDUser = '$IDUser'";
    $res = $db->query($sql);

    if (!$res) {
        return false;
    } elseif ($res->num_rows == 0) {
        return false;
    }

    return $res->fetch_assoc();
}


/**
 * Haalt alle winkelbeheerders op uit de databse.
 *
 * @return array
 *   Array per beheerder met ID en Volledige naam.
 */
function model_users_beheerder_select()
{
    $db = app_db();
    $sql = 'SELECT IDUser, Voornaam, Achternaam FROM Users WHERE Permissie = ' . USERS_PERMISSIE_MINIADMIN;
    $res = $db->query($sql);

    if (!$res) {
        return array();
    }

    $beheerders = array();

    while ($beheerder = $res->fetch_assoc()) {
        $beheerders[$beheerder['IDUser']] = array(
            'value' => $beheerder['IDUser'],
            'label' => $beheerder['Voornaam'] . ' ' . $beheerder['Achternaam'],
        );
    }

    return $beheerders;
}

/**
 * Haalt alle winkelsupervisors op uit de databse.
 *
 * @return array
 *   Array per beheerder met ID en Volledige naam.
 */
function model_users_supervisor_select()
{
    $db = app_db();
    $sql = 'SELECT IDUser, Voornaam, Achternaam FROM Users WHERE Permissie = ' . USERS_PERMISSIE_SUPERVISOR;
    $res = $db->query($sql);

    if (!$res) {
        return array();
    }

    $supervisors = array();

    while ($supervisor = $res->fetch_assoc()) {
        $supervisors[$supervisor['IDUser']] = array(
            'value' => $supervisor['IDUser'],
            'label' => $supervisor['Voornaam'] . ' ' . $supervisor['Achternaam'],
        );
    }

    return $supervisors;
}


/**
 * Geef het aantal users weer.
 *
 * @return integer
 *     Het aantal users dat er zijn.
 */
function model_users_count()
{
    $db = app_db();
    $sql = 'SELECT COUNT(IDUser) AS User FROM Users';
    $res = $db->query($sql);
    $row = $res->fetch_assoc();

    return (int)$row['User'];
}

/**
 * Geef het aantal klanten weer.
 *
 * @return integer
 *     Het aantal users dat er zijn.
 */
function model_users_count_Klant($winkels)
{
    if (!is_array($winkels)) {
        $winkels = array($winkels);
    }

    $db = app_db();

    //What.
    $sql[] = 'SELECT COUNT(DISTINCT U.IDUser) AS User';

    // From where.
    $sql[] = 'FROM';
    $sql[] = 'Users AS U';
    $sql[] = 'INNER JOIN Permissies AS P ON (U.Permissie = P.IDPermissie)';
    $sql[] = 'INNER JOIN Bestelling AS B ON (U.IDUser = B.Koper)';
    $sql[] = 'INNER JOIN Winkels AS W ON (B.Winkel = W.IDWinkel)';

    //Criteria.
    $sql[] = 'WHERE';
    $winkelsIn = implode(', ', $winkels);
    $sql[] = 'W.IDWinkel IN (' . $winkelsIn . ')';
    $sql[] = 'AND U.Permissie = 1';
    
    $res = $db->query(implode(' ', $sql));
    $row = $res->fetch_assoc();

    return (int)$row['User'];
}

/**
 * Geeft alle permissies
 *
 * @return array
 *      Array van alle permissies
 */
function model_users_select_permissies()
{
    $db = app_db();
    $sql = 'SELECT IDPermissie, permissieomschrijving FROM Permissies WHERE IDPermissie <> ' . USERS_PERMISSIE_BEDIENDE;
    $res = $db->query($sql);

    $permissies = array();
    while ($permissie = $res->fetch_assoc()) {
        $permissies[$permissie['IDPermissie']] = array(
            'value' => $permissie['IDPermissie'],
            'label' => $permissie['permissieomschrijving']
        );
    }

    return $permissies;
}

/**
 * Een gebruiker opslaan in de database.
 *
 * @param array $user
 *
 * @return string
 *  Geeft de actie terug.
 */
function model_users_save_bestaand(array $user)
{
    $db = app_db();
    $sql = 'UPDATE Users SET
            Voornaam = "%s",
            Achternaam = "%s",
            Email = "%s",
            Geslacht = %d,
            Geboortedatum = "%s",
            Permissie = %d
            WHERE IDUser = %d
    ';
    $res = $db->query(sprintf($sql,
        $db->real_escape_string($user['Voornaam']),
        $db->real_escape_string($user['Achternaam']),
        $db->real_escape_string($user['Email']),
        $db->real_escape_string($user['Geslacht']),
        $db->real_escape_string($user['Geboortedatum']),
        $db->real_escape_string($user['Permissie']),
        $db->real_escape_string($user['IDUser'])
    ));
    if (!$res) {
        return false;
    } elseif ($db->affected_rows == 0) {
        return false;
    }
    return 'gewijzigd';
}

/**
 * User verijwderen via IDUser.
 *
 * @param integer $IDUser
 * @return boolean
 */
function model_users_delete_by_IDUser($IDUser)
{
    $db = app_db();
    $IDUser = $db->real_escape_string($IDUser);
    $sql = "DELETE FROM Users WHERE IDUser = '$IDUser'";
    $res = $db->query($sql);

    if (!$res) {
        return false;
    }
}


/**
 * Kijken of een mail niet wordt gebruikt door een gebruiker
 *
 * @param string $mail
 * @return boolean
 */
function model_users_mailcheck($mail)
{
    $db = app_db();
    $mail = $db->real_escape_string($mail);
    $sql = "SELECT Email FROM Users WHERE Email = '$mail'";
    $res = $db->query($sql);

    if (!$res) {
        return true;
    } elseif ($res->num_rows == 0) {
        return true;
    } else {
        return false;
    }

}

/**
 * Mail versturen om het wachtwoord in te stellen
 *
 * @param string $email
 * @return boolean
 *  True = gelukt, False = mislukt
 */
function model_users_sendmailPW($email)
{
    $db = app_db();
    $email = $db->real_escape_string($email);
    $sql = "SELECT IDUser, Voornaam FROM Users WHERE Email = '" . $email . "' LIMIT 1";
    $res = $db->query($sql);
    if ($res->num_rows == 1) {
        $rec = mysqli_fetch_assoc($res);
        $expFormat = mktime(date("H"), date("i"), date("s"), date("m"), date("d") + 1, date("Y"));
        $expDate = date("Y-m-d H:i:s", $expFormat);
        $tohash = $rec['Voornaam'] . '_' . $email . rand(0, 10000) . $expDate;
        $key = password_hash($tohash, PASSWORD_DEFAULT);
        $encUserID = urlencode(base64_encode($rec['IDUser']));

        $sql2 = "SELECT IDRec FROM UserRecovery WHERE IDUser = '" . $rec['IDUser'] . "'";
        $res2 = $db->query($sql2);
        if ($res2->num_rows == 1) {
            $sqlIns = "UPDATE UserRecovery SET recKey = '$key', expDate = '$expDate' WHERE IDUser = '" . $rec['IDUser'] . "'";
        } elseif ($res2->num_rows == 0) {
            $sqlIns = "INSERT INTO UserRecovery (IDUser, recKey, expDate) VALUES ('" . $rec['IDUser'] . "', '" . $key . "', '" . $expDate . "')";
        } else {
            var_dump('FOUT BIJ TWEEDE SQL');
            return false;
        }

        if (!$resIns = $db->query($sqlIns)) {
            var_dump('FOUT BIJ DERDE SQL');
            return false;
        } else {
            $passwordLink = "<a href=\"http://gip.go-ao.eu/mybasket/wwset.php?a=recover&email=" . $key . "&u=" . $encUserID . "\">http://gip.go-ao.eu/mybasket/wwset.php?a=recover&email=" . $key . "&u=" . $encUserID . "</a>";
            $message = "<html><body>Beste " . $rec['Voornaam'] . ",<br>";
            $message .= "<p>Gelieve volgende link te volgen om je wachtwoord te resetten:<br>";
            $message .= "$passwordLink</p>";
            $message .= "<p>Mocht de link niet werken, gelieve de volledige link in je browser te kopiëren.</p>";
            $message .= "<p>De link zal om veiligheidsredenen na 3 dagen vervallen.</p>";
            $message .= "<p>Als u deze vergeten wachtwoord e-mail niet heeft aangevraagd, is geen verdere actie nodig, uw wachtwoord zal niet worden gereset zolang de link hierboven niet wordt bezocht.</p>";
            $message .= "<p>Alvast bedankt,</p><br>";
            $message .= "<p><a href='http://gip.go-ao.eu/mybasket/index.php'>MyBasket-team</a></p></body></html>";
            $mail = new PHPMailer;
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = "leandercolpaert@go-ao.eu";
            $mail->Password = 'frAyech4';
            $mail->Username = "leandercolpaert@go-ao.eu";
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            $mail->setFrom('leandercolpaert@go-ao.eu', 'Wachtwoord instellen - MyBasket.com');
            $mail->addAddress($email, $rec['Voornaam']);
            $mail->isHTML(true);
            $mail->Subject = "Uw aanvraag voor nieuw wachtwoord";
            $mail->Body = $message;
            $mail->AltBody = strip_tags($message);

            if ($mail->send()) {
                return true;
            } else {
                var_dump('FOUT BIJ MAIL VERSTUREN');
                return false;
            }
        }
    } else {
        var_dump('FOUT BIJ EERSTE SQL');
        return false;
    }
}

/**
 * Haal de IDWinkel op via de UserID
 *
 * @param int $UserID
 *
 * @return array/bool
 */
function model_users_bediende_winkel_by_ID($UserID)
{
    $db = app_db();
    $UserID = $db->real_escape_string($UserID);
    $sql = "SELECT IDWinkel FROM UsersWinkels WHERE IDUser = '$UserID'";
    $res = $db->query($sql);

    if (!$res) {
        return true;
    }
    $rec = $res->fetch_assoc();
    return $rec['IDWinkel'];
}

/**
 * Voeg een nieuwe bediende aan een winkel
 *
 * @param int $userID, int $winkelID
 *
 * @return bool
 */
function model_users_add_bediende($userID, $winkelID) {
    $db = app_db();
    $userID = $db->real_escape_string($userID);
    $winkelID = $db->real_escape_string($winkelID);

    $sql = 'INSERT INTO
        UsersWinkels(
            IDUser,
            IDWinkel
        )
        VALUES(%d, %d)
    ';

    $res = $db->query(sprintf($sql,
        $db->real_escape_string($userID),
        $db->real_escape_string($winkelID)
    ));

    if (!$res) {
        return false;
    }

    return true;

}

/**
 * gebruiker geen bediende meer maken via IDUser en IDWinkel.
 *
 * @param integer $IDUser, $IDWinkel
 * @return boolean
 */
function model_users_remove_bediende($IDUser, $IDWinkel)
{
    $db = app_db();
    $IDUser = $db->real_escape_string($IDUser);
    $IDWinkel = $db->real_escape_string($IDWinkel);
    $sql = "DELETE FROM UsersWinkels WHERE IDUser = '$IDUser' AND IDWinkel = '$IDWinkel'";
    $res = $db->query($sql);

    if (!$res) {
        return false;
    }
    return true;
}

/**
 * Zet de gebruiker zijn permissie op bediende
 *
 * @param int $userID
 *
 * @return bool
 */
function model_users_set_bediende($userID) {
    $db = app_db();
    $userID = $db->real_escape_string($userID);

    $sql = "UPDATE Users SET
            Permissie = '2'
            WHERE IDUser = %d
    ";

    $res = $db->query(sprintf($sql,
        $db->real_escape_string($userID)
    ));

    if (!$res) {
        return false;
    }

    return true;

}

/**
 * Zet de gebruiker zijn permissie op klant
 *
 * @param int $userID
 *
 * @return bool
 */
function model_users_set_klant($userID) {
    $db = app_db();
    $userID = $db->real_escape_string($userID);

    $sql = "UPDATE Users SET
            Permissie = '1'
            WHERE IDUser = %d
    ";

    $res = $db->query(sprintf($sql,
        $db->real_escape_string($userID)
    ));

    if (!$res) {
        return false;
    }

    return true;

}