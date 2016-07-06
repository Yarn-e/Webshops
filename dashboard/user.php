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

//Als er een UserID is gezet onmiddelijk die weghalen want we hebben die niet nodig.
if (isset($_SESSION['USERID'])) {
    unset($_SESSION['USERID']);
}

//Standaardgegevens zetten.
$data['admin'] = false;
$data['winkelbeheerder'] = false;
$data['supervisor'] = false;
$data['showselect'] = true;
$data['request_uri'] = $_SERVER['PHP_SELF'];
$data['selectempty'] = 'user.php';

//Kijken wat de persoon zijn permissie is en de data die de persoon mag zien ophalen.
if (isset($_SESSION['admin']) && $_SESSION['admin']) {
    $data['winkels'] = model_winkels_select_naam();
    $data['users'] = model_users_all();
    $data['pagetitle'] = 'Gebruikers beheren';
    $data['page'] = 'Gebruiker';
    $data['admin'] = true;
} elseif (isset($_SESSION['supervisor']) && $_SESSION['supervisor']) {
    $winkels = model_winkels_by_Supervisor($_SESSION['userID']);
    $data['winkels'] = model_winkels_select_naam_by_Supervisor($_SESSION['userID']);
    $data['users'] = model_users_klanten(array_keys($winkels));

    $data['pagetitle'] = 'Klanten raadplegen';
    $data['page'] = 'Klant';
    $data['supervisor'] = true;
} else {
    $winkels = model_winkels_by_Beheerder($_SESSION['userID']);
    $data['winkels'] = model_winkels_select_naam_by_Beheer($_SESSION['userID']);
    $data['users'] = model_users_klanten(array_keys($winkels));

    $data['pagetitle'] = 'Klanten beheren';
    $data['page'] = 'Klant';
    $data['winkelbeheerder'] = true;
}

//Kijken of de ID van de winkel bestaat, indien die bestaat gaan we alleen de gebruikers tonen die een bestelling hebben van die winkel.
if (isset($_GET['ID'])) {
    $data['currentID'] = "user.php?ID=" . $_GET['ID'];
    $data['users'] = model_users_klanten($_GET['ID']);
}

//Van de ID's links maken zodat we die in de select kunnen steken.
foreach ($data['winkels'] as &$winkels) {
    $winkels['value'] = "user.php?ID=" . $winkels['value'];
}

//Als er maar 1 winkel is dan moet je geen select tonen.
if (count($data['winkels']) == 1) {
    $data['showselect'] = false;
}

//Alle gebruikers hun geboortedatums omzetten naar een normaal formaat.
foreach ($data['users'] as &$users) {
    $birthdate = $users['Geboortedatum'];
    $birthdate = date("d/m/Y", strtotime($birthdate));
    $users['Geboortedatum'] = $birthdate;
}

$user = false;

//Genders van mensen zitten vast in de applicatie.
$data['Gender'] = array(
    0 => array(
        'value' => '0',
        'label' => 'Man'
    ),
    1 => array(
        'value' => '1',
        'label' => 'Vrouw'
    )
);

//Kijken of een IDuser is gezet.
if (!empty($_GET['IDUser'])) {
    $user = array();
    $user['IDUser'] = $_GET['IDUser'];
}

//Permissies ophalen.
$data['Permissies'] = model_users_select_permissies();


//Wanneer men op de knop voor product verwijderen  drukt dan gaat de applicatie de gebruiker verwijderen.
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verwijder'])) {
    $user = model_users_by_IDUser($_POST['verwijder']);
    model_users_delete_by_IDUser($_POST['verwijder']);
    $_SESSION['alert'] = "$.notify({message: '<strong>" . $user['Voornaam'] . " " . $user['Achternaam'] . "</strong> succesvol verwijderd'},{type: 'alert bg-success'});";
    header('Location:user.php');
    exit();
}

//Alerts tonen.
if (isset($_SESSION['alert'])) {
    $data['alert'] = $_SESSION['alert'];
    unset($_SESSION['alert']);
}

//Pagina laden.
echo $twig->render('pages/dashboard_user.html.twig', $data);