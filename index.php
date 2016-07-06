<?php
// enkel via HTTPS
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "") {
    $redirect = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: $redirect");
}  

//Applicatie laden.
require_once('mybasket/bootstrap.php');

//Sessie starten
include('../dashboard/assets/inc/session.php');

$data = array();
$data['winkels'] = model_winkels_by_all_actief();
$data['logged_in'] = false;
// check of je de pagina mag zien
if(isset($_SESSION['logged_in']) && (password_verify(($_SERVER['HTTP_USER_AGENT'] . "GO-AO_Webshops" . $_SERVER['REMOTE_ADDR']), $_SESSION['fingerprint'])))
{ 
	$data['logged_in'] = true;
    $data['user'] = $_SESSION['user'];
}
if (isset($_SESSION['admin']) || isset($_SESSION['winkelbeheerder']) || isset($_SESSION['supervisor'])) {
    $data['winkels'] = model_winkels_all();
}

$data['stamnr'] = false;
if (isset($_SESSION['stamnr'])) {
    $data['stamnr'] = true;
}


if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['logout'])) {
    session_destroy();
    session_start();
    header("Location:index.php");
}


if ((isset($_SESSION['admin']) && $_SESSION['admin']) || (isset($_SESSION['winkelbeheerder']) && $_SESSION['winkelbeheerder']) || (isset($_SESSION['supervisor']) && $_SESSION['supervisor'])) {
    $data['admin'] = true;
}

if (isset($_SESSION['alert'])) {
    $data['alert'] = $_SESSION['alert'];
    unset($_SESSION['alert']);
}

echo $twig->render('pages/winkel.html.twig', $data);
