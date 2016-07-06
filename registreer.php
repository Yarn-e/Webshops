<?php
// enkel via HTTPS
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "") {
    $redirect = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: $redirect");
}  

session_start();

include('inc/verbinding_inc.php');
include('functions/login.php');

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location:index.php");
}

$ingevuldveld = [];
$regVelden = [
    'voornaam',
    'achternaam',
    'email',
    'gebdatum',
    'ww1',
    'ww2'
];

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['LogRes'] == 0) {
        // "functions/login.php"
        login_all($_POST['email'], $_POST['pwd']);
    }
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['LogRes'] == 1) {
        if (isset($_POST['voornaam'], $_POST['achternaam'], $_POST['email'], $_POST['geslacht'], $_POST['gebdatum'], $_POST['ww1'], $_POST['ww2'])) {
            $email = trim($_POST['email']);
            $voornaam = trim($_POST['voornaam']);
            $achternaam = trim($_POST['achternaam']);
            $geslacht = trim($_POST['geslacht']);
            $gebdatum = trim($_POST['gebdatum']);
            $pwd = trim($_POST['ww1']);
            $pwd2 = trim($_POST['ww2']);
            $sql = "SELECT Email FROM Users WHERE Email = '" . $email . "'";
            $res = $link->query($sql);
            $row = $res->fetch_assoc();
            if ($row['Email'] != $email) {
                if ($pwd == $pwd2) {
                    $lengte = strlen($pwd);
                    if ($lengte > 5) {
                        $year = date("Y") - 12;
                        $month = date("m");
                        $day = date("d");
                        $y12back = strtotime($year . "-" . $month . "-" . $day);
                        
                        $date = str_replace('/', '-', $gebdatum);
                        $date = strtotime($date);
                        
                        if ($date < $y12back && $date ) {
                            $passHashed = login_hashen($pwd);
                            $date = date("Y-m-d", $date);
                            $sql = "INSERT INTO Users (Voornaam, Achternaam, Email, Geslacht, Geboortedatum, Wachtwoord, Permissie, Stamboeknummer) VALUES ('$voornaam', '$achternaam', '$email', '$geslacht', '$date', '$passHashed', 1, 0)";
                            $link->query($sql);
                            $_SESSION['AlertReg'] = '<script>$.notify({message:"<strong>Registreren: </strong>Account succesvol aangemaakt"}, {type: "alert bg-success"});</script>';
                        } else {
                            $_SESSION['RegAlert'] = '<strong>Fout bij registreren: </strong>Geen geldige datum';
                            foreach ($regVelden as $veld) {
                                if (empty($_POST[$veld])) {
                                    $_SESSION['RegAlert'] = "<strong>Fout bij registreren: </strong>Gelieve alle velden in te vullen";
                                } else {
                                    $Regingevuldveld[$veld] = $_POST[$veld];
                                }
                            } 
                        }
                    } else {
                        $_SESSION['RegAlert'] = '<strong>Fout bij registreren: </strong>Wachtwoorden moeten minstens 6 karakters lang zijn.';
                        foreach ($regVelden as $veld) {
                        if (empty($_POST[$veld])) {
                            $_SESSION['RegAlert'] = "<strong>Fout bij registreren: </strong>Gelieve alle velden in te vullen";
                        } else {
                            $Regingevuldveld[$veld] = $_POST[$veld];
                        }
                    }
                    }
                } else {
                    $_SESSION['RegAlert'] = '<strong>Fout bij registreren: </strong>Wachtwoorden komen niet overeen';
                    foreach ($regVelden as $veld) {
                        if (empty($_POST[$veld])) {
                            $_SESSION['RegAlert'] = "<strong>Fout bij registreren: </strong>Gelieve alle velden in te vullen";
                        } else {
                            $Regingevuldveld[$veld] = $_POST[$veld];
                        }
                    }
                }
            } else {
                $_SESSION['RegAlert'] = "<strong>Fout bij registreren: </strong>$email is al in gebruik";
                foreach ($regVelden as $veld) {
                    if (empty($_POST[$veld])) {
                        $_SESSION['RegAlert'] = "<strong>Fout bij registreren: </strong>Gelieve alle velden in te vullen";
                    } else {
                        $Regingevuldveld[$veld] = $_POST[$veld];
                    }
                }
            }    
        } else {
            foreach ($regVelden as $veld) {
                if (empty($_POST[$veld])) {
                    $_SESSION['RegAlert'] = "<strong>Fout bij registreren: </strong>Gelieve alle velden in te vullen";
                } else {
                    $Regingevuldveld[$veld] = $_POST[$veld];
                }
            }
        }
    }

?>
<!DOCTYPE html>
<html lang="nl-be">

    <head>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
        
        <title>Aanmelden/Registreren - MyBasket</title>
        
        <script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>        
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
        
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
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="index.php">GO-AO Webshops</a>

                </div>
            </div>
            <!-- /.container -->
        </nav>

        <!-- Page Content -->
        <div class="container">
            <div class="panel panel-default">
                <div class="row">
                    <div class="col-md-5" style="margin-left: 25px;">
                        <div class="panel-heading">
                            <h1 style="text-align:center;">Aanmelden</h1>
                        </div>
                        <?php 
                            if(isset($_SESSION['LogAlert'])) { 
                                echo '<div class="alert alert-danger" role="alert">'.$_SESSION['LogAlert'].'</div>';
                                unset($_SESSION['LogAlert']);
                            }
                        ?>
                        <div class="panel">
                            <form class="form-signin" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                                <div class="form-group">
                                    <label for="email">Email:</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($ingevuldveld['email']) ? $ingevuldveld['email'] : ""; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="pwd">Wachtwoord:</label>
                                    <input type="password" class="form-control" id="pwd" name="pwd" required>
                                </div>
                                <div class="form-group">
                                    <a href="wwreset.php">Wachtwoord vergeten</a><br>
                                </div>
                                <input type="hidden" name="LogRes" value="0">
                                <button type="submit" class="btn btn-default">Doorgaan</button>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-1">&nbsp;</div>
                    <div class="col-md-5" style="margin-left: 15px;">
                        <div class="panel-heading">
                            <h1 style="text-align:center;">Account aanmaken</h1>
                        </div>
                        <?php 
                            if(isset($_SESSION['RegAlert'])) { 
                                echo '<div class="alert alert-danger" role="alert">'.$_SESSION['RegAlert'].'</div>';
                                unset($_SESSION['RegAlert']);
                            }
                        ?>
                        <div class="panel">
                            <form class="form-regis" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                                <div class="form-group">
                                    <label for="email">Voornaam:</label>
                                    <input type="text" class="form-control" id="voornaam" name="voornaam" value="<?php echo isset($Regingevuldveld['voornaam']) ? $Regingevuldveld['voornaam'] : ""; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="pwd">Achternaam:</label>
                                    <input type="text" class="form-control" id="achternaam" name="achternaam" value="<?php echo isset($Regingevuldveld['achternaam']) ? $Regingevuldveld['achternaam'] : ""; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="pwd">Email:</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($Regingevuldveld['email']) ? $Regingevuldveld['email'] : ""; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Geslacht:</label><br>
                                    <input type="radio" name="geslacht" value="0" checked> <label for="man" style="font-weight: normal;">Man</label>
                                    <input type="radio" name="geslacht" value="1" style="margin-left: 15px;"> <label for="vrouw" style="font-weight: normal;">Vrouw</label>
                                </div>
                                <div class="form-group">
                                    <label for="birthdate">Geboortedatum:</label>
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="gebdatum" value="<?php echo isset($Regingevuldveld['gebdatum']) ? $Regingevuldveld['gebdatum'] : ""; ?>" placeholder="22/12/2000" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="pwd">Wachtwoord:</label>
                                    <input type="password" class="form-control" id="pwd" name="ww1" required>
                                </div>
                                <div class="form-group">
                                    <label for="pwd2">Wachtwoord herhalen:</label>
                                    <input type="password" class="form-control" id="pwd2" name="ww2" required>
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
                    <p>Copyright &copy; GO-AO Webshops</p>
                </div>
            </footer>
            
            <script src="js/bootstrap-notify.js"></script>
            <?php 
            if (isset($_SESSION['AlertReg'])) {
                echo $_SESSION['AlertReg'];
                unset($_SESSION['AlertReg']);
            }
            ?>
        </body>