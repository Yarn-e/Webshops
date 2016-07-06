<?php
// enkel via HTTPS
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "") {
    $redirect = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: $redirect");
}  

//Sessie starten
include('../dashboard/assets/inc/session.php');
include('inc/verbinding_inc.php');
include('functions/login.php');

if (!isset($_SESSION['logged_in']) && $_SESSION['logged_in'] !== true) {
    header("Location:index.php");
}

if (isset($_SESSION['stamnr']) && $_SESSION['stamnr'] !== 0) {
    header("Location:index.php");
}

$gegevens = login_gegevens_by_id($_SESSION['userID']);

$gegevens['Geboortedatum'] = strtotime($gegevens['Geboortedatum']);

$gegevens['Geboortedatum'] = date('d/m/Y', $gegevens['Geboortedatum']);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['LogRes'] == 0) {
    $oudpass = $link->real_escape_string($_POST['oudww']);
    $newpass = $link->real_escape_string($_POST['pwd']);
    $newpass2 = $link->real_escape_string($_POST['pwd2']);
    $IDuser = $link->real_escape_string($_SESSION['userID']);
    if (login_checkPassword($IDuser, $oudpass)) {
        if ($newpass == $newpass2) {
            $lengte = strlen($newpass);
            if ($lengte > 5) {
                if ($oudpass !== $newpass) {
                    $newpass = login_hashen($newpass);
                    $sql = "UPDATE Users SET Wachtwoord = '$newpass' WHERE IDUser = '$IDuser'";
                    $res = $link->query($sql);
                    if (!$res) {
                        $_SESSION['Alertacc'] = '<script>$.notify({message:"<strong>Fout bij Wachtwoord wijzigen: </strong>' . $link->error . '"}, {type: "alert bg-danger"});</script>';
                    } else {
                        $_SESSION['Alertacc'] = '<script>$.notify({message:"<strong>Paswoord gewijzigd</strong>"}, {type: "alert bg-success"});</script>';
                    }
                } else {
                    $_SESSION['Alertacc'] = '<script>$.notify({message:"<strong>Fout bij Wachtwoord wijzigen: </strong>Wachtwoord mag niet hetzelfde zijn als het vorige!"}, {type: "alert bg-danger"});</script>';
                }
            } else {
                $_SESSION['Alertacc'] = '<script>$.notify({message:"<strong>Fout bij Wachtwoord wijzigen: </strong>Het nieuwe paswoord moet minstens 6 tekens lang zijn!"}, {type: "alert bg-danger"});</script>';
            }
        } else {
            ;
            $_SESSION['Alertacc'] = '<script>$.notify({message:"<strong>Fout bij Wachtwoord wijzigen: </strong>De nieuwe paswoorden komen niet overeen!"}, {type: "alert bg-danger"});</script>';
        }
    } else {
        $_SESSION['Alertacc'] = '<script>$.notify({message:"<strong>Fout bij Wachtwoord wijzigen: </strong>Oud paswoord komt niet overeen!"}, {type: "alert bg-danger"});</script>';
    }


}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['LogRes'] == 1) {
    $data['Voornaam'] = $link->real_escape_string($_POST['voornaam']);
    $data['Achternaam'] = $link->real_escape_string($_POST['achternaam']);
    $data['Email'] = $link->real_escape_string($_POST['email']);
    $data['Geslacht'] = $link->real_escape_string($_POST['geslacht']);
    $data['Geboortedatum'] = $link->real_escape_string($_POST['gebdatum']);

    $date = str_replace('/', '-', $data['Geboortedatum']);
    $date = strtotime($date);
    $year12 = date("Y") - 12;
    $year100 = date("Y") - 100;
    $month = date("m");
    $day = date("d");
    $y12back = strtotime($year12 . "-" . $month . "-" . $day);
    $y100back = strtotime($year100 . "-" . $month . "-" . $day);

    if ($date && $date < $y12back && $date > $y100back) {
        if (login_mailcheck($data['Email'], $_SESSION['userID'])) {
            $data['Geboortedatum'] = date('Y-m-d', $date);
            if (login_update_user($data, $_SESSION['userID'])) {
                $_SESSION['Alertacc'] = '<script>$.notify({message:"<strong>SUCCES: </strong>Gegevens gewijzigd"}, {type: "alert bg-success"});</script>';
            } else {
                $_SESSION['Alertacc'] = '<script>$.notify({message:"<strong>FOUT: </strong>kon gegevens niet wijzigen"}, {type: "alert bg-danger"});</script>';
            }
        } else {
            $_SESSION['Alertacc'] = '<script>$.notify({message:"<strong>Fout bij Account wijzigen: </strong>De mail wordt al gebruikt door een andere gebruiker!"}, {type: "alert bg-danger"});</script>';
        }
    } else {
        $_SESSION['Alertacc'] = '<script>$.notify({message:"<strong>Fout bij Account wijzigen: </strong>Je leeftijd moet tussen 12 jaar en 100 jaar zijn!"}, {type: "alert bg-danger"});</script>';
    }


}


?>

<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Mijn Account - GO-AO Webshops</title>

    <!-- Bootstrap Core CSS -->
    <link href="css/bootstrap.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="css/shop-homepage.css" rel="stylesheet">

    <!-- Sticky-footer CSS -->
    <link href="css/sticky-footer.css" rel="stylesheet">

    <!-- Own CSS -->
    <link href="css/style.css" rel="stylesheet">

    <!-- Alert Animation CSS -->
    <link href="css/animation.css" rel="stylesheet">

</head>

<body class="background-blue">
<!-- Navigation -->
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse"
                    data-target="#bs-example-navbar-collapse-1">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="index.php">GO-AO Webshops</a>
        </div>
        <ul class="nav navbar-nav navbar-right">
            <?php
            if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
                ?>
                <li class="header-user">
                    Ingelogd als: <b><?php echo $_SESSION['user'] ?></b>
                </li>
                <?php
            }
            if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
                ?>
                <li>
                    <a href="dashboard/index.php">Dashboard</a>
                </li>
                <?php
            }
            if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
                ?>
                <li>
                    <a href="index.php?logout=true">Uitloggen</a>
                </li>
                <?php
            }
            ?>
        </ul>
    </div>
    <!-- /.container -->
</nav>

<!-- Page Content -->
<div class="container">
    <div class="panel panel-default">
        <div class="row">
            <div class="col-md-12">
                <div class="panel-heading">
                    <h1 style="text-align:center;">Mijn Account</h1>
                    <hr>
                </div>
            </div>
            <div class="col-md-5" style="margin-left: 25px;">
                <div class="panel-heading">
                    <h1 style="text-align:center;">Wachtwoord wijzigen</h1>
                </div>
                <div class="panel">
                    <form class="form-signin" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                        <div class="form-group">
                            <label for="oudww">Oud wachtwoord:</label>
                            <input type="password" class="form-control" id="oudww" name="oudww" required>
                        </div>
                        <br>
                        <div class="form-group">
                            <label for="pwd">Wachtwoord:</label>
                            <input type="password" class="form-control" id="pwd" name="pwd" required>
                        </div>
                        <div class="form-group">
                            <label for="pwd2">Wachtwoord herhalen:</label>
                            <input type="password" class="form-control" id="pwd2" name="pwd2" required>
                        </div>
                        <input type="hidden" name="LogRes" value="0">
                        <button type="submit" class="btn btn-default">Wachtwoord wijzigen</button>
                    </form>
                </div>
            </div>
            <div class="col-md-1">&nbsp;</div>
            <div class="col-md-5" style="margin-left: 15px;">
                <div class="panel-heading">
                    <h1 style="text-align:center;">Account informatie</h1>
                </div>
                <div class="panel">
                    <form class="form-regis" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                        <div class="form-group">
                            <label for="email">Voornaam:</label>
                            <input type="text" class="form-control" id="voornaam" name="voornaam"
                                   value="<?php echo $gegevens['Voornaam']; ?>"
                                   required>
                        </div>
                        <div class="form-group">
                            <label for="pwd">Achternaam:</label>
                            <input type="text" class="form-control" id="achternaam" name="achternaam"
                                   value="<?php echo $gegevens['Achternaam']; ?>"
                                   required>
                        </div>
                        <div class="form-group">
                            <label for="pwd">Email:</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?php echo $gegevens['Email']; ?>"
                                   required>
                        </div>
                        <div class="form-group">
                            <label>Geslacht:</label><br>
                            <input type="radio" name="geslacht" value="0"
                                   <?php if ($gegevens['Geslacht'] == 0): ?>checked<?php endif; ?>> <label for="man"
                                                                                                           style="font-weight: normal;">Man</label>
                            <input type="radio" name="geslacht" value="1" style="margin-left: 15px;"
                                   <?php if ($gegevens['Geslacht'] == 1): ?>checked<?php endif; ?>> <label for="vrouw"
                                                                                                           style="font-weight: normal;">Vrouw</label>
                        </div>
                        <div class="form-group">
                            <label for="birthdate">Geboortedatum:</label>
                            <div class="form-group">
                                <input type="text" class="form-control" name="gebdatum"
                                       value="<?php echo $gegevens['Geboortedatum']; ?>"
                                       placeholder="22/12/2014" required>
                            </div>
                        </div>
                        <input type="hidden" name="LogRes" value="1">
                        <button type="submit" class="btn btn-default">Doorgaan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="navbar-fixed-bottom">
    <div class="container">
        <p>Copyright &copy; GO-AO Webshops - <a href="contact.php" class="contact-link">Contact</a></p>
    </div>
</footer>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/bootstrap-notify.js"></script>
<?php
if (isset($_SESSION['Alertacc'])) {
    echo $_SESSION['Alertacc'];
    unset($_SESSION['Alertacc']);
}
?>

</body>
         