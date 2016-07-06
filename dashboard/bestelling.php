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
$data['showselect'] = true;
$data['pagetitle'] = 'Bestellingen beheren';
$data['selectempty'] = 'bestelling.php';
$data['page'] = 'Bestelling';


//Kijken wat de persoon zijn permissie is en de data die de persoon mag zien ophalen.
if (isset($_SESSION['winkelbeheerder']) && $_SESSION['winkelbeheerder']) {
    $data['winkelbeheerder'] = true;

    $data['winkels'] = model_winkels_select_naam_by_Beheer($_SESSION['userID']);
    $winkels = model_winkels_by_Beheerder($_SESSION['userID']);
    $data['bestellingen'] = model_bestellingen_by_winkel(array_keys($winkels));

} elseif (isset($_SESSION['supervisor']) && $_SESSION['supervisor']) {
    $data['pagetitle'] = 'Bestellingen raadplegen';
    $data['supervisor'] = true;

    $data['winkels'] = model_winkels_select_naam_by_Supervisor($_SESSION['userID']);
    $winkels = model_winkels_by_Supervisor($_SESSION['userID']);
    $data['bestellingen'] = model_bestellingen_by_winkel(array_keys($winkels));
} else {
    $data['admin'] = true;
    $data['winkels'] = model_winkels_select_naam();
    $data['bestellingen'] = model_bestellingen_all();
}

//Kijken of de ID van de winkel bestaat, indien die bestaat gaan we alleen de bestellingen tonen van die winkel.
if (isset($_GET['ID'])) {
    $data['currentID'] = "bestelling.php?ID=" . $_GET['ID'];
    $data['bestellingen'] = model_bestellingen_by_winkel($_GET['ID']);
}

//Van de ID's links maken zodat we die in de select kunnen steken.
foreach ($data['winkels'] as &$winkels) {
    $winkels['value'] = "bestelling.php?ID=" . $winkels['value'];
}

//Als er maar 1 winkel is dan moet je geen select tonen.
if (count($data['winkels']) == 1) {
    $data['showselect'] = false;
}

//Wanneer men op de knop voor bestellingen te afhandelen drukt dan gaat de applicatie de bestelling op afgehandeld zetten.
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['afhandel'])) {
    $bestelling = $_POST['afhandel'];

    if (model_bestellingen_afhandel($bestelling)) {
        $_SESSION['alert'] = "$.notify({message: 'Je bestelling is afgehandeld!'},{type: 'alert bg-success'});";
    }
    header('Location:bestelling.php');
    exit();
}

//Wanneer men op de knop voor bestellingen te annuleren drukt dan gaat de applicatie de bestelling op geannuleerd zetten.
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verwijder'])) {
    $bestelling = $_POST['verwijder'];

    if (model_bestellingen_annuleer($bestelling)) {
        $_SESSION['alert'] = "$.notify({message: 'Je bestelling is geannuleerd'},{type: 'alert bg-success'});";
    }
    header('Location:bestelling.php');
    exit();
}

//Ervoor zorgen dat de naam tesamen wordt getoont en dat er een totaalprijs wordt opgemaakt.
foreach ($data['bestellingen'] as &$bestellingen) {
    $voornaam = $bestellingen['Voornaam'];
    $achternaam = $bestellingen['Achternaam'];
    $bestellingen['Name'] = $voornaam . ' ' . $achternaam;

    $totaalprijs = $bestellingen['Aantal'] * $bestellingen['Eenheidsprijs'];
    $bestellingen['Totaalprijs'] = $totaalprijs;
}

//Alerts laten tonen.
if (isset($_SESSION['alert'])) {
    $data['alert'] = $_SESSION['alert'];
    unset($_SESSION['alert']);
}

//Pagina laden.
echo $twig->render('pages/dashboard_bestelling.html.twig', $data);