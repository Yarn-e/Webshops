<?php
session_start();
session_destroy();
//Sessie starten
require('../dashboard/assets/inc/session.php');

//Belangrijke bestanden.
require('inc/verbinding_inc.php');
require('functions/login.php');


if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['stamnr']) && isset($_GET['smartschool'])) {
    $_SESSION['logged_in'] = FALSE;
    if ($_GET['smartschool'] == "VG9lZ2FuZyB0b3Qgd2Vic2hvcCB2aWEgU21hcnRzY2hvb2w=") {
        include('../dashboard/assets/inc/smartschoolcheck.php');
        if (infoLKR($_GET['stamnr'])) {
            $_SESSION['fingerprint'] = password_hash(($_SERVER['HTTP_USER_AGENT'] . "GO-AO_Webshops" . $_SERVER['REMOTE_ADDR']), PASSWORD_DEFAULT);
            $_SESSION['logged_in'] = TRUE;
            $_SESSION['user'] = $_SESSION['ss_voornaam'] . " " . $_SESSION['ss_naam'];
            $_SESSION['stamnr'] = $_SESSION['ss_nr'];

            if ($result = login_check_sms($_SESSION['stamnr'])) {
                $_SESSION['userID'] = $result['IDUser'];
                if ($result['Permissie'] == 5) {
                    $_SESSION['supervisor'] = true;
                    header('Location:/dashboard/index.php');
                } elseif ($result['Permissie'] == 4) {
                    $_SESSION['admin'] = true;
                    header('Location:/dashboard/index.php');
                } elseif ($result['Permissie'] == 3) {
                    $_SESSION['winkelbeheerder'] = true;
                    header('Location:/dashboard/index.php');
                } else {
                    $_SESSION['permission'] = $result['Permissie'];
                    $_SESSION['alert'] = '$.notify({title: "<strong>Aanmelden: </strong>",message:"Successvol ingelogd},{type: "alert bg-success"});';
                }
            } else {
                if (login_add_sms($_SESSION['ss_voornaam'], $_SESSION['ss_naam'], $_SESSION['ss_nr'])) {
                    $_SESSION['fingerprint'] = password_hash(($_SERVER['HTTP_USER_AGENT'] . "GO-AO_Webshops" . $_SERVER['REMOTE_ADDR']), PASSWORD_DEFAULT);
                    $_SESSION['logged_in'] = TRUE;
                    $_SESSION['user'] = $_SESSION['ss_voornaam'] . " " . $_SESSION['ss_naam'];
                    $_SESSION['stamnr'] = $_SESSION['ss_nr'];

                    if ($result = login_check_sms($_SESSION['stamnr'])) {
                        $_SESSION['userID'] = $result['IDUser'];
                        if ($result['Permissie'] == 5) {
                            $_SESSION['supervisor'] = true;
                            header('Location:/dashboard/index.php');
                        } elseif ($result['Permissie'] == 4) {
                            $_SESSION['admin'] = true;
                            header('Location:/dashboard/index.php');
                        } elseif ($result['Permissie'] == 3) {
                            $_SESSION['winkelbeheerder'] = true;
                            header('Location:/dashboard/index.php');
                        } else {
                            $_SESSION['permission'] = $result['Permissie'];
                            $_SESSION['alert'] = '$.notify({title: "<strong>Aanmelden: </strong>",message:"Successvol ingelogd},{type: "alert bg-success"});';
                        }
                    }
                }

            }
            header("Location: index.php");
            exit();
        } else {
            header("Location: registreer.php");
        }
    }
}
