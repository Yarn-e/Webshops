<?php
//Sessie starten.
include('../../dashboard/assets/inc/session.php');


//Functies laden en kijken of de persoon ingelogd is en de juiste permissie heeft om de pagina te zien.
require_once('../mybasket/bootstrap.php');
if (!isset($_SESSION['logged_in']) && !(password_verify(($_SERVER['HTTP_USER_AGENT'] . "GO-AO_Webshops" . $_SERVER['REMOTE_ADDR']), $_SESSION['fingerprint']))) {
    header('Location:../registreer.php');
    exit;
}
if ((!isset($_SESSION['admin']) || !$_SESSION['admin']) && (!isset($_SESSION['winkelbeheerder']) || !$_SESSION['winkelbeheerder']) && (!isset($_SESSION['supervisor']) || !$_SESSION['supervisor'])) {
    header('Location:../registreer.php');
    exit;
}


//Standaardgegevens zetten.
$data['winkelbeheerder'] = false;
$data['supervisor'] = false;
$data['admin'] = false;
$data['bediendes'] = model_users_sms();
$data['page'] = 'Bediende';

//Kijken wat de persoon zijn permissie is en de data die de persoon mag zien ophalen.
if (isset($_SESSION['admin']) && $_SESSION['admin']) {
    $data['Winkels'] = model_winkels_select_naam();
    $data['pagetitle'] = 'Bedienden beheren';
    $data['admin'] = true;
} elseif (isset($_SESSION['supervisor']) && $_SESSION['supervisor']) {
    $data['Winkels'] = model_winkels_select_naam_by_Supervisor($_SESSION['userID']);
    $data['pagetitle'] = 'Bedienden raadplegen';
    $data['supervisor'] = true;
} else {
    $data['Winkels'] = model_winkels_select_naam_by_Beheer($_SESSION['userID']);

    $data['pagetitle'] = 'Bedienden beheren';
    $data['winkelbeheerder'] = true;
}

$errors = array();
//Kijken of de persoon een bediende is. als hij er geen is wordt dat opgeslaan in de array $errors.
foreach ($data['bediendes'] as &$bediende) {
    $ID = $bediende['IDUser'];
    if ($gegevens = model_winkels_by_bediende($ID)) {
        foreach ($data['Winkels'] as $winkel) {
            if ($gegevens['IDWinkel'] == $winkel['value']) {
                $bediende['IDWinkel'] = $gegevens['IDWinkel'];
                $bediende['Winkelnaam'] = $gegevens['Winkelnaam'];
                $errors[$ID] = array(
                    'Fout' => false,
                    'ID' => $ID
                );
                break;
            } else {
                $errors[$ID] = array(
                    'Fout' => true,
                    'ID' => $ID
                );
            }
        }

    }
}
//Als de persoon geen bediende is dan gaan we de id weghalen.
foreach ($errors as $error) {
    if ($error['Fout']) {
        unset($data['bediendes'][$error['ID']]);
    }
}


//Wanneer men op de knop voor bedienden aan te maken heeft gedrukt gaat de applicatie de nieuwe bediende vasthangen aan een winkel.
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['IDUser'])) {
    $user = $_POST['IDUser'];
    $fullname = $_POST['usernaam'];
    $winkel = $_POST['winkel'];
    if (model_users_add_bediende($user, $winkel)) {
        if(model_users_set_bediende($user)) {
            $_SESSION['alert'] = "$.notify({message: '<strong>" . $fullname . "</strong> is nu een bediende!'},{type: 'alert bg-success'});";
        }
    }
    header('Location:bediende.php');
    exit();
}


//Wanneer men op de knop voor bedienden te verwijderen heeft gedrukt gaat de applicatie de bediende weghalen van de winkel en zijn permissie terug op klant zetten.
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verwijder'])) {
    $user = $_POST['verwijder'];
    $fullname = $_POST['Gebruiker'];
    $winkel = $_POST['IDWinkel'];
    if (model_users_remove_bediende($user, $winkel)) {

        if (model_users_set_klant($user)) {
            $_SESSION['alert'] = "$.notify({message: '<strong>" . $fullname . "</strong> is nu geen bediende meer!'},{type: 'alert bg-success'});";
        }
    } else {
        $_SESSION['alert'] = "$.notify({message: '<strong>" . $fullname . "</strong> FOUTTT!'},{type: 'alert bg-success'});";
    }
    header('Location:bediende.php');
    exit();
}

//Alerts laten tonen.
if (isset($_SESSION['alert'])) {
    $data['alert'] = $_SESSION['alert'];
    unset($_SESSION['alert']);
}

//Pagina laden.
echo $twig->render('pages/dashboard_bediendes.html.twig', $data);