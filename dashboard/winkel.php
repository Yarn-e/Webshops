<?php
//Sessie starten
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
$data = array();
$data['winkelbeheerder'] = false;
$data['supervisor'] = false;
$data['admin'] = false;
$data['showactivation'] = false;
$data['pagetitle'] = 'Winkels beheren';
$data['beheerders'] = model_users_beheerder_select();
$data['supervisors'] = model_users_supervisor_select();
$data['winkel'] = false;
$data['page'] = 'Winkel';
$data['request_uri'] = $_SERVER['PHP_SELF'];
$data['alert'] = "";

//Kijken wat de persoon zijn permissie is en de data die de persoon mag zien ophalen.
if (isset($_SESSION['winkelbeheerder']) && $_SESSION['winkelbeheerder']) {
    $data['winkels'] = model_winkels_by_Beheerder($_SESSION['userID']);
    $data['winkelbeheerder'] = true;
} elseif (isset($_SESSION['supervisor']) && $_SESSION['supervisor']) {
    $data['pagetitle'] = 'Winkels raadplegen';
    $data['winkels'] = model_winkels_by_Supervisor($_SESSION['userID']);
    $data['supervisor'] = true;
} else {
    $data['winkels'] = model_winkels_all();
    $data['admin'] = true;

}

$winkel = false;

//Als er een IDWinkel is dan gaan we alle gegevens van de winkel ophalen en in de formuliervelden zetten.
if (!empty($_GET['IDWinkel'])) {
    $winkel = model_winkels_by_IDWinkel($_GET['IDWinkel']);
    $data['showactivation'] = true;
}

//Wanneer men op de knop voor winkel verwijderen drukt dan gaat de applicatie de winkel verwijderen als er geen bestellingen meer aan hangen.
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verwijder'])) {
    $winkel = model_winkels_by_IDWinkel($_POST['verwijder']);
    $result = model_winkels_delete_by_IDWinkel($_POST['verwijder'], $winkel['Banner']);

    if ($result) {
        $_SESSION['alert'] = "$.notify({message: '<strong>" . $winkel['Winkelnaam'] . "</strong> succesvol verwijderd'},{type: 'alert bg-success'});";
        header('Location:winkel.php');
        exit();
    } else {
        $_SESSION['alert'] = "$.notify({message: '<strong>" . $winkel['Winkelnaam'] . "</strong> niet verwijderd<br>Kijk of er geen opengaande bestellingen zijn aan deze winkel!'},{type: 'alert bg-danger'});";
        header('Location:winkel.php?IDWinkel=' . $_POST['verwijder']);
        exit();
    }
}

//Wanneer men op de knop opslaan drukt gaan we alle data opslaan.
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['Activatie'])) {
    $db = app_db();

    if (!$winkel) {
        $winkel = array();
    }

    $winkel['Winkelnaam'] = $_POST['Winkelnaam'];
    $winkel['Activatie'] = $_POST['Activatie'];
    
    //Kijken of er wel een winkelbeheerder is gekozen.
    if (!empty(trim($_POST['Winkelbeheerder']))) {
        $winkel['Winkelbeheerder'] = $_POST['Winkelbeheerder'];
    } else {
        $_SESSION['alert'] = "$.notify({message: 'Gelieve een winkelbeheerder te selecteren.'},{type: 'alert bg-danger'});";
        header('Location:winkel.php?IDWinkel=' . $_GET['IDWinkel']);
        exit();
    }

    //Kijken of er wel een winkelsupervisor is gekozen.
    if (!empty(trim($_POST['Supervisor']))) {
        $winkel['Supervisor'] = $_POST['Supervisor'];
    } else {
        $_SESSION['alert'] = "$.notify({message: 'Gelieve een Supervisor te selecteren.'},{type: 'alert bg-danger'});";
        header('Location:winkel.php?IDWinkel=' . $_GET['IDWinkel']);
        exit();
    }

    //Foto toevoegen.
    $banner = model_winkels_upload_img($winkel['Winkelnaam']);
    if (isset($banner) && $banner) {
        $winkel['Banner'] = $banner;
    } elseif (!empty($_POST['currbanner']) && $_POST['currbanner'] == $winkel['Banner']) {
    } else {
        $winkel['Banner'] = "";
    }

    //Vorige foto weghalen.
    if (!empty($_POST['currbanner']) && $_POST['currbanner'] !== $winkel['Banner']) {
        $Banner = '../mybasket/files/images/' . $_POST['currbanner'];
        unlink($Banner);
    }

    //Winkel opslaan.
    $actie = model_winkels_save($winkel);
    
    //Kijken of er wel iets is gebeurd.
    if (!$actie) {
        $_SESSION['alert'] = "$.notify({message: 'Je hebt niets veranderd!'},{type: 'alert bg-danger'});";
        header('Location:winkel.php?IDWinkel=' . $_GET['IDWinkel']);
        unset($_SESSION['WID']);
        exit();
    }
    
    //Alles is ok.
    $_SESSION['alert'] = "$.notify({message: '<strong>" . $_POST['Winkelnaam'] . "</strong> succesvol " . $actie . ".'},{type: 'alert bg-success'});";
    header('Location:winkel.php?IDWinkel=' . $_GET['IDWinkel']);
    unset($_SESSION['WID']);
    exit();
}
$data['winkel'] = $winkel;

//Alerts laten tonen.
if (isset($_SESSION['alert'])) {
    $data['alert'] = $_SESSION['alert'];
    unset($_SESSION['alert']);
}

//Pagina laden.
echo $twig->render('pages/dashboard_winkel.html.twig', $data);
