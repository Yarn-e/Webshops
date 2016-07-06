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
$data['admin'] = false;
$data['winkelbeheerder'] = false;
$data['supervisor'] = false;
$data['stamnr'] = false;
$data['page'] = 'Home';
$data['pagetitle'] = 'Welkom: <b>' . $_SESSION['user'] . '</b>';

//Kijken of er een stamnummer is.
if (isset($_SESSION['stamnr'])) {
    $data['stamnr'] = true;
}

//Kijken wat de persoon zijn permissie is en de data die de persoon mag zien ophalen.
if (isset($_SESSION['winkelbeheerder']) && $_SESSION['winkelbeheerder']) {
    $data['winkelbeheerder'] = true;

    $winkels = model_winkels_by_Beheerder($_SESSION['userID']);
    $data['winkels'] = model_winkels_count_by_beheer($_SESSION['userID']);
    $data['users'] = model_users_count_Klant(array_keys($winkels));
    $data['products'] = model_products_count_by_beheer($_SESSION['userID']);
} elseif (isset($_SESSION['supervisor']) && $_SESSION['supervisor']) {
    $data['supervisor'] = true;

    $winkels = model_winkels_by_Supervisor($_SESSION['userID']);
    $data['users'] = model_users_count_Klant(array_keys($winkels));
    $data['products'] = model_products_count_by_supervisor($_SESSION['userID']);
    $data['winkels'] = model_winkels_count_by_supervisor($_SESSION['userID']);
} else {
    $data['admin'] = true;

    $data['winkels'] = model_winkels_count();
    $data['users'] = model_users_count();
    $data['products'] = model_products_count();
}

//Alerts laten tonen.
if (isset($_SESSION['alert'])) {
    $data['alert'] = $_SESSION['alert'];
    unset($_SESSION['alert']);
}

//Pagina laden.
echo $twig->render('pages/dashboard_home.html.twig', $data);