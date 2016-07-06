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


//Als er geen ID is gezet wordt je teruggestuurd.
if (empty(trim($_GET['ID'])) || !is_numeric($_GET['ID']) || $_GET['ID'] < 0 || $_GET['ID'] != round($_GET['ID'], 0)) {
    header('Location: user.php');
    exit;
}

//Als je geen admin bent dan wordt je teruggestuurd
if (!isset($_SESSION['admin']) && !$_SESSION['admin']) {
    header('Location: user.php');
    exit;
}

//Standaardgegevens zetten.
$data['admin'] = true;
$data['Smartschool'] = false;
$data['pagetitle'] = 'Gebruikers beheren';
$data['Permissies'] = model_users_select_permissies();
$data['winkels'] = model_winkels_select_naam();
$data['page'] = '<a href="user.php">Gebruiker</a></li> <li>Gebruiker wijzigen';
$data['request_uri'] = $_SERVER['PHP_SELF'];

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

//Als er een IDUser is dan gaan we alle gegevens van de gebruiker ophalen en in de formuliervelden zetten.
if (!empty($_GET['ID'])) {
    $user = array();
    $data['user'] = model_users_by_IDUser($_GET['ID']);
    $_SESSION['USERID'] = $_GET['ID'];
    if ($data['user']['Stamboeknummer'] == 0) {
        $birthdate = $data['user']['Geboortedatum'];
        $birthdate = date("d/m/Y", strtotime($birthdate));
        $data['user']['Geboortedatum'] = $birthdate;
    } else {
        $data['Smartschool'] = true;
    }

}

//Als er een fout was dan wordt de data opgeslaan want de pagina wordt herladen dus moet de data ook opnieuw moeten geladen worden.
if (!empty($_SESSION['Postdata'])) {
    $data['user'] = $_SESSION['Postdata'];
    unset($_SESSION['Postdata']);
}


//Wanneer men op de knop opslaan drukt gaan we alle data opslaan.
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['Voornaam'])) {
    $db = app_db();
    if (!isset($user)) {
        $user = array();
    }

    //Als de gebruiker niet van smartschool is dan gaan we de data opslaan.
    if ($_POST['Smartschool'] == 0) {
        //Check voor 12 jaar.
        $year12 = date("Y") - 12;
        $year100 = date("Y") - 100;
        $month = date("m");
        $day = date("d");
        $y12back = strtotime($year12 . "-" . $month . "-" . $day);
        $y100back = strtotime($year100 . "-" . $month . "-" . $day);

        $date = str_replace('/', '-', $_POST['Geboortedatum']);
        $date = strtotime($date);
        if (!$date || $date > $y12back || $date < $y100back) {
            $_SESSION['alert'] = "$.notify({message: 'Gelieve een geldige geboortedatum in te vullen!<br>Niet ouder dan 100 jaar en niet jonger dan 12 jaar'},{type: 'alert bg-danger'});";
            header('Location:userwijzig.php?ID=' . $_SESSION['USERID']);

            $_SESSION['Postdata'] = array(
                'IDUser' => $_SESSION['USERID'],
                'Voornaam' => $_POST['Voornaam'],
                'Achternaam' => $_POST['Achternaam'],
                'Email' => $_POST['Email'],
                'Geslacht' => $_POST['Geslacht'],
                'Permissie' => $_POST['Permissie']
            );
            unset($_SESSION['USERID']);
            exit();
        }

        //Als er geen ID is gezet dan gaan we naar de mail kijken.
        if (!isset($_SESSION['USERID'])) {
            if (!model_users_mailcheck($_POST['Email'], $_SESSION['USERID'])) {
                $_SESSION['alert'] = "$.notify({message: 'De mail wordt al gebruikt door een andere gebruiker'},{type: 'alert bg-danger'});";
                header('Location:userwijzig.php?ID=' . $_SESSION['USERID']);

                $_SESSION['Postdata'] = array(
                    'IDUser' => $_SESSION['USERID'],
                    'Voornaam' => $_POST['Voornaam'],
                    'Achternaam' => $_POST['Achternaam'],
                    'Geslacht' => $_POST['Geslacht'],
                    'Geboortedatum' => $_POST['Geboortedatum'],
                    'Permissie' => $_POST['Permissie']
                );
                unset($_SESSION['USERID']);
                exit();
            }
        }


        $user['Email'] = $_POST['Email'];
        $user['Geslacht'] = $_POST['Geslacht'];
        $user['Geboortedatum'] = date("Y-m-d", $date);
    } else {
        //Smartschoolgebruikers moeten geen mail hebben
        $user['Email'] = "";
        $user['Geslacht'] = "";
        $user['Geboortedatum'] = "";
    }

    $user['Voornaam'] = str_replace(range(0, 9), '', $_POST['Voornaam']);
    $user['Achternaam'] = str_replace(range(0, 9), '', $_POST['Achternaam']);


    $user['Permissie'] = $_POST['Permissie'];
    //ID meegeven
    if (!empty($_SESSION['USERID'])) {
        $user['IDUser'] = $_SESSION['USERID'];
    }

    //SQL uitvoeren.
    $actie = model_users_save_bestaand($user);

    //Als er een fout is de fout tonen.
    if (!$actie) {
        $_SESSION['alert'] = "$.notify({message: 'Je hebt niets veranderd! <br> Voornaam/naam mogen geen cijfers bevatten!<br> Het kan zijn dat de mail al gebruikt wordt door een andere gebruiker!'},{type: 'alert bg-danger'});";
        header('Location:userwijzig.php?ID=' . $_SESSION['USERID']);
        unset($_SESSION['USERID']);
        exit();
    }

    //Alles is ok.
    $_SESSION['alert'] = "$.notify({message: '<strong>" . $_POST['Voornaam'] . " " . $_POST['Achternaam'] . "</strong> succesvol " . $actie . ".'},{type: 'alert bg-success'});";
    header('Location:user.php');
    exit();
}

//Alerts laten tonen.
if (isset($_SESSION['alert'])) {
    $data['alert'] = $_SESSION['alert'];
    unset($_SESSION['alert']);
}

//Pagina laden.
echo $twig->render('pages/dashboard_userwijzig.html.twig', $data);