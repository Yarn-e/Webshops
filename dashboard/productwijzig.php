<?php
//Sessie starten
include('../../dashboard/assets/inc/session.php');

//Functies laden en kijken of de persoon ingelogd is en de juiste permissie heeft om de pagina te zien.
require_once('../mybasket/bootstrap.php');
if (!isset($_SESSION['logged_in']) && !(password_verify(($_SERVER['HTTP_USER_AGENT'] . "GO-AO_Webshops" . $_SERVER['REMOTE_ADDR']), $_SESSION['fingerprint']))) {
    header('Location:../registreer.php');
    exit;
}
if ((!isset($_SESSION['admin']) || !$_SESSION['admin']) && (!isset($_SESSION['winkelbeheerder']) || !$_SESSION['winkelbeheerder'])) {
    header('Location:../registreer.php');
    exit;
}

//Standaardgegevens zetten.
$data['winkelbeheerder'] = false;
$data['supervisor'] = false;
$data['admin'] = false;
$data['page'] = '<a href="product.php">Product</a></li> <li>Product wijzigen';
$data['pagetitle'] = 'Producten wijzigen';


//Kijken wat de persoon zijn permissie is en de data die de persoon mag zien ophalen.
if (isset($_SESSION['winkelbeheerder']) && $_SESSION['winkelbeheerder']) {
    $data['Winkels'] = model_winkels_select_naam_by_Beheer($_SESSION['userID']);
    $data['winkelbeheerder'] = true;
} elseif (isset($_SESSION['supervisor']) && $_SESSION['supervisor']) {
    $data['Winkels'] = model_winkels_select_naam_by_Supervisor($_SESSION['userID']);
    $data['supervisor'] = true;
} else {
    $data['Winkels'] = model_winkels_select_naam();
    $data['admin'] = true;
}


//Data van de BTW staat vast in de applicatie, als je de BTW tarieven veranderen moet je ze hier veranderen.
$data['BTW'] = array(
    '0' => array(
        'value' => '0',
        'label' => '0%'
    ),
    '6' => array(
        'value' => '6',
        'label' => '6%'
    ),
    '12' => array(
        'value' => '12',
        'label' => '12%'
    ),
    '21' => array(
        'value' => '21',
        'label' => '21%'
    )
);

//Als er een IDProduct is dan gaan we alle gegevens van het product ophalen en in de formuliervelden zetten.
if (!empty($_GET['ID'])) {
    $product = array();
    $data['product'] = model_products_by_IDProduct($_GET['ID']);
    $data['product']['Prijs'] = str_replace(".", ",", $data['product']['Prijs']);
    $product['Foto'] = model_products_foto_by_IDProduct($_GET['ID']);
    $_SESSION['PRODID'] = $_GET['ID'];
}

//Als er een fout was dan wordt de data opgeslaan want de pagina wordt herladen dus moet de data ook opnieuw moeten geladen worden.
if (!empty($_SESSION['Postdata'])) {
    $data['product'] = $_SESSION['Postdata'];
    unset($_SESSION['Postdata']);
}

//Wanneer men op de knop opslaan drukt gaan we alle data opslaan.
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['Prijs'])) {
    $db = app_db();

    if (!isset($product)) {
        $product = array();
    }

    $product['Productnaam'] = $_POST['Productnaam'];
    $newprijs = str_replace(",", ".", $_POST['Prijs']);
    
    //Kijken of de prijs wel een nummer is en tussen 0 en 250 ligt.
    if ($_POST['Prijs'] < 0 || $_POST['Prijs'] > 250 || !is_numeric($newprijs)) {
        $_SESSION['alert'] = "$.notify({message: 'Gelieve een gelidge prijs in te voeren.'},{type: 'alert bg-danger'});";
        header('Location:productwijzig.php?ID=' . $_SESSION['PRODID']);
        $_SESSION['Postdata'] = array(
            'Productnaam' => $_POST['Productnaam'],
            'Foto' => $_POST['currfoto'],
            'Uitleg' => $_POST['Uitleg'],
            'BTW' => $_POST['BTW'],
            'Maxaantal' => $_POST['Maxaantal'],
            'Winkel' => $_POST['Winkel']
        );

        unset($_SESSION['PRODID']);
        exit();
    }

    //Kijken of het maximum aantal tussen de 0 en 100 ligt.
    if ($_POST['Maxaantal'] < 0 || $_POST['Maxaantal'] > 100) {
        $_SESSION['alert'] = "$.notify({message: 'Gelieve een gelidg maximum aantal in te voeren.'},{type: 'alert bg-danger'});";
        header('Location:productwijzig.php?ID=' . $_SESSION['PRODID']);
        $_SESSION['Postdata'] = array(
            'Productnaam' => $_POST['Productnaam'],
            'Foto' => $_POST['currfoto'],
            'Uitleg' => $_POST['Uitleg'],
            'BTW' => $_POST['BTW'],
            'Prijs' => $_POST['Prijs'],
            'Winkel' => $_POST['Winkel']
        );
        unset($_SESSION['PRODID']);
        exit();
    }

    $newprijs = str_replace(",", ".", $_POST['Prijs']);
    $product['Prijs'] = $newprijs;
    $product['Uitleg'] = $_POST['Uitleg'];
    $product['BTW'] = $_POST['BTW'];
    $product['Maxaantal'] = $_POST['Maxaantal'];
    $product['Winkel'] = $_POST['Winkel'];
    $product['Status'] = $_POST['Status'];

    //Als er een ID is dan zetten we ze er ook bij zodat we weten dat we het product moeten wijzigen.
    if (!empty(trim($_SESSION['PRODID'])) && is_numeric($_SESSION['PRODID']) && ($_SESSION['PRODID'] > 0) && ($_SESSION['PRODID'] == round($_SESSION['PRODID'], 0))) {
        $product['IDProduct'] = $_SESSION['PRODID'];
    }

    //Kijken of er wel een winkel is gekozen.
    if (empty($_POST['Winkel'])) {
        $_SESSION['alert'] = "$.notify({message: 'Gelieve een winkel te selecteren.'},{type: 'alert bg-danger'});";
        header('Location:productwijzig.php?ID=' . $product['IDProduct']);
        unset($_SESSION['PRODID']);
        exit();
    }

    //Foto toevoegen.
    $winkelnaam = model_winkels_by_IDWinkel($_POST['Winkel']);
    $foto = model_products_upload_img($_POST['Winkel'], $_POST['Productnaam']);
    if ($foto) {
        $product['Foto'] = $foto;
    } elseif (!empty($_POST['currfoto']) && $_POST['currfoto'] == $product['Foto']) {
    } else {
        $_SESSION['alert'] = "$.notify({message: 'Gelieve een foto toe te voegen.'},{type: 'alert bg-danger'});";
        header('Location:productwijzig.php?ID=' . $product['IDProduct']);
        unset($_SESSION['PRODID']);
        exit();
    }

    //Vorige foto weghalen.
    if (!empty($_POST['currfoto']) && $_POST['currfoto'] !== $product['Foto']) {
        $Foto = '../ProductImage/' . $_POST['currfoto'];
        unlink($Foto);
    }
    
    //Product opslaan.
    $actie = model_products_save($product);

    //Kijken of er wel iets is gebeurd.
    if (!$actie) {
        $_SESSION['alert'] = "$.notify({message: 'Je hebt niets veranderd!'},{type: 'alert bg-danger'});";
        header('Location:productwijzig.php?ID=' . $product['IDProduct']);
        exit();
    }

    $_SESSION['alert'] = "$.notify({message: '<strong>" . $_POST['Productnaam'] . "</strong> succesvol " . $actie . ".'},{type: 'alert bg-success'});";
    header('Location:product.php');
    exit();

}

//Alerts laten tonen.
if (isset($_SESSION['alert'])) {
    $data['alert'] = $_SESSION['alert'];
    unset($_SESSION['alert']);
}

//Pagina laden.
echo $twig->render('pages/dashboard_productwijzig.html.twig', $data);