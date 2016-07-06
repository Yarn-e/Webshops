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

//Als er een ProductID is gezet onmiddelijk die weghalen want we hebben die niet nodig.
if (isset($_SESSION['PRODID'])) {
    unset($_SESSION['PRODID']);
}

//Standaardgegevens zetten.
$data['winkelbeheerder'] = false;
$data['supervisor'] = false;
$data['admin'] = false;
$data['showselect'] = true;
$data['selectempty'] = 'product.php';
$data['page'] = 'Product';
$data['pagetitle'] = 'Producten beheren';

//Kijken wat de persoon zijn permissie is en de data die de persoon mag zien ophalen.
if (isset($_SESSION['winkelbeheerder']) && $_SESSION['winkelbeheerder']) {
    $data['products'] = model_products_by_Beheerder($_SESSION['userID']);
    $data['winkels'] = model_winkels_select_naam_by_Beheer($_SESSION['userID']);
    $data['Winkels'] = model_winkels_select_naam_by_Beheer($_SESSION['userID']);
    $data['winkelbeheerder'] = true;
} elseif (isset($_SESSION['supervisor']) && $_SESSION['supervisor']) {
    $data['pagetitle'] = 'Producten raadplegen';
    $data['products'] = model_products_by_Supervisor($_SESSION['userID']);
    $data['winkels'] = model_winkels_select_naam_by_Supervisor($_SESSION['userID']);
    $data['Winkels'] = model_winkels_select_naam_by_Supervisor($_SESSION['userID']);
    $data['supervisor'] = true;
} else {
    $data['products'] = model_products_all();
    $data['winkels'] = model_winkels_select_naam();
    $data['Winkels'] = model_winkels_select_naam();
    $data['admin'] = true;
}

//Kijken of de ID van de winkel bestaat, indien die bestaat gaan we alleen de producten tonen van die winkel.
if (isset($_GET['ID'])) {
    $data['currentID'] = "product.php?ID=" . $_GET['ID'];
    $data['products'] = model_products_by_winkel($_GET['ID']);
}

//Van de ID's links maken zodat we die in de select kunnen steken.
foreach ($data['winkels'] as &$winkels) {
    $winkels['value'] = "product.php?ID=" . $winkels['value'];
}

//Als er maar 1 winkel is dan moet je geen select tonen.
if (count($data['winkels']) == 1) {
    $data['showselect'] = false;
}


$product = false;

//Wanneer men op de knop voor product verwijderen drukt dan gaat de applicatie het product verwijderen als er geen bestellingen meer aan hangen.
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verwijder'])) {
    $product = model_products_by_IDProduct($_POST['verwijder']);
    $result = model_products_delete_by_IDProduct($_POST['verwijder'], $product['Foto']);

    if($result) {
        $_SESSION['alert'] = "$.notify({message: '<strong>" . $product['Productnaam'] . "</strong> succesvol verwijderd'},{type: 'alert bg-success'});";
        header('Location:product.php');
        exit();
    } else {
        $_SESSION['alert'] = "$.notify({message: '<strong>" . $product['Productnaam'] . "</strong> niet verwijderd<br>Kijk of er geen opengaande bestellingen zijn aan dit product!'},{type: 'alert bg-danger'});";
        header('Location:product.php?Wijzig=OK');
        exit();
    }
}

//Wanneer men op de knop voor producten te kopiëren drukt dan gaat de applicatie het product kopiëren naar de gekozen winkel.
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['copy'])) {
    $path = '../ProductImage/';
    $winkelnaam = model_winkels_by_IDWinkel($_POST['winkel']);
    $path = substr($_POST['foto'], -3);
    $random = rand(0,10000);
    $newname = $winkelnaam['IDWinkel'] . '_' . $_POST['prodname'] . '_' . $random . '.' . $path;
    copy('../ProductImage/' . $_POST['foto'], '../ProductImage/' . $newname);
    $newprod = array(
        'Productnaam' => $_POST['prodname'],
        'Foto' => $newname,
        'Prijs' => $_POST['prijs'],
        'Uitleg' => $_POST['uitleg'],
        'BTW' => $_POST['btw'],
        'Maxaantal' => $_POST['maxaantal'],
        'Winkel' => $_POST['winkel'],
        'Status' => $_POST['status']
    );

    $result = model_products_save($newprod);
    if($result) {
        $_SESSION['alert'] = "$.notify({message: '<strong>" . $product['Productnaam'] . "</strong> Product gekopieerd.'},{type: 'alert bg-success'});";
        header('Location:product.php');
        exit();
    } else {
        $_SESSION['alert'] = "$.notify({message: '<strong>" . $product['Productnaam'] . "</strong> Product niet gekopieerd!'},{type: 'alert bg-danger'});";
        header('Location:product.php?Wijzig=OK');
        exit();
    }
}

//Alerts laten tonen.
if (isset($_SESSION['alert'])) {
    $data['alert'] = $_SESSION['alert'];
    unset($_SESSION['alert']);
}

//Pagina laden.
echo $twig->render('pages/dashboard_product.html.twig', $data);