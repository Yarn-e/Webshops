<?php
//Sessie starten.
include('../../dashboard/assets/inc/session.php');

//Functies laden en kijken of de persoon ingelogd is en de juiste permissie heeft om de pagina te zien.
require_once('../mybasket/bootstrap.php');
if (!isset($_SESSION['logged_in']) && !(password_verify(($_SERVER['HTTP_USER_AGENT'] . "GO-AO_Webshops" . $_SERVER['REMOTE_ADDR']), $_SESSION['fingerprint']))) {
    header('Location:../registreer.php');
    exit;
}
if ((!isset($_SESSION['admin']) || !$_SESSION['admin'])) {
    header('Location:../registreer.php');
    exit;
}

//Standaardgegevens zetten.
$data['admin'] = true;
$data['page'] = 'Database';
$data['pagetitle'] = 'Handelingen database';


//Wanneer men op de knop voor gebruikers te verwijderen heeft gedrukt gaat de applicatie de gebruikers verwijderen die de laatste 2 jaar geen bestelling hebben gemaakt.
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['Gebruiker'])) {
    if (model_database_delete_gebruikers()) {
        $_SESSION['alert'] = "$.notify({message: 'Alle gebruikers verwijderd!'},{type: 'alert bg-success'});";
    }
}

//Wanneer men op de knop voor producten te activeren heeft gedrukt gaat de applicatie alle producten op actief zetten.
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ProductAc'])) {
    if (model_database_activate_products()) {
        $_SESSION['alert'] = "$.notify({message: 'Alle producten geactiveerd!'},{type: 'alert bg-success'});";
    }
}

//Wanneer men op de knop voor producten te deactiveren heeft gedrukt gaat de applicatie alle producten op niet actief zetten.
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ProductDe'])) {
    if (model_database_deactivate_products()) {
        $_SESSION['alert'] = "$.notify({message: 'Alle producten gedactiveerd!'},{type: 'alert bg-success'});";
    }
}

//Wanneer men op de knop voor winkels te activeren heeft gedrukt gaat de applicatie alle winkels op actief zetten.
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['WinkelAc'])) {
    if (model_database_activate_winkels()) {
        $_SESSION['alert'] = "$.notify({message: 'Alle winkels geactiveerd!'},{type: 'alert bg-success'});";
    }
}

//Wanneer men op de knop voor winkels te deactiveren heeft gedrukt gaat de applicatie alle winkels op niet actief zetten.
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['WinkelDe'])) {
    if (model_database_deactivate_winkels()) {
        $_SESSION['alert'] = "$.notify({message: 'Alle winkels gedactiveerd!'},{type: 'alert bg-success'});";
    }
}

//Alerts laten tonen.
if (isset($_SESSION['alert'])) {
    $data['alert'] = $_SESSION['alert'];
    unset($_SESSION['alert']);
}

//Pagina laden.
echo $twig->render('pages/dashboard_database.html.twig', $data);