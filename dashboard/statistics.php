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
$data['winkelbeheerder'] = false;
$data['supervisor'] = false;
$data['admin'] = false;
$data['showselect'] = true;
$data['notop3'] = false;
$data['selectempty'] = 'statistics.php';
$data['page'] = 'Statistieken';
$data['pagetitle'] = 'Statistieken';

//Kijken wat de persoon zijn permissie is en de data die de persoon mag zien ophalen.
if (isset($_SESSION['winkelbeheerder']) && $_SESSION['winkelbeheerder']) {
    $data['winkelbeheerder'] = true;
    $winkels = model_winkels_by_Beheerder($_SESSION['userID']);
    $data['Winkels'] = model_winkels_select_naam_by_Beheer($_SESSION['userID']);

    $data['stats'] = model_bestellingen_count_month_by_winkels(array_keys($winkels));
    $data['top3'] = model_bestellingen_top3_by_winkels(array_keys($winkels));
} elseif (isset($_SESSION['supervisor']) && $_SESSION['supervisor']) {
    $data['supervisor'] = true;
    $winkels = model_winkels_by_Supervisor($_SESSION['userID']);
    $data['Winkels'] = model_winkels_select_naam_by_Supervisor($_SESSION['userID']);

    $data['stats'] = model_bestellingen_count_month_by_winkels(array_keys($winkels));
    $data['top3'] = model_bestellingen_top3_by_winkels(array_keys($winkels));
} else {
    $data['admin'] = true;
    $data['stats'] = model_bestellingen_count_month();
    $data['Winkels'] = model_winkels_select_naam();
}

//Kijken of de ID van de winkel bestaat, indien die bestaat gaan we de statistieken tonen van die winkel.
if (isset($_GET['ID'])) {
    $data['currentID'] = "statistics.php?ID=" . $_GET['ID'];
    $data['stats'] = model_bestellingen_count_month_by_winkels($_GET['ID']);
    $data['top3'] = model_bestellingen_top3_by_winkels($_GET['ID']);
}

//Van de ID's links maken zodat we die in de select kunnen steken.
foreach ($data['Winkels'] as &$winkels) {
    $winkels['value'] = "statistics.php?ID=" . $winkels['value'];
}

//Als er maar 1 winkel is dan moet je geen select tonen.
if (count($data['Winkels']) == 1) {
    $data['showselect'] = false;
}

//Als er geen top3 producten zijn toon ze dan niet.
if (empty($data['top3'])) {
    $data['notop3'] = true;
}

//Alerts laten tonen.
if (isset($_SESSION['alert'])) {
    $data['alert'] = $_SESSION['alert'];
    unset($_SESSION['alert']);
}

//Pagina laden.
echo $twig->render('pages/dashboard_statistics.html.twig', $data);