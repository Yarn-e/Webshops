<?php
////MYBA
include("inc/verbinding_inc.php");
include("functions/func_wwset.php");
include("functions/login.php");

$alert = false;
$show = "invalidKey";

if (isset($_GET['a']) && isset($_GET['email']) && isset($_GET['u'])) {
    $a = $_GET['a'];
    $email = $_GET['email'];
    $u = $_GET['u'];
}

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['pwd1']) && isset($_POST['pwd2'])) {
    $pwd1 = trim($link->real_escape_string($_POST['pwd1']));
    $pwd2 = trim($link->real_escape_string($_POST['pwd2']));
    $uID = trim($link->real_escape_string($_POST['uID']));
    //ww is omgegeven voor nieuwe gebruiker
    
    if ($pwd1 == $pwd2) {
        $lengte = strlen($pwd1);
        if ($lengte > 5) {
            $passHashed = login_hashen($pwd1);
            $sql = "UPDATE Users SET Wachtwoord = '$passHashed' WHERE IDUser = '$uID'";
            if (!$res = $link->query($sql)) {
                $show = 'setForm';
                $alert = "<strong>FOUT: </strong>" . $link->error . ". Gelieve contact met ons te nemen.";
            } elseif ($link->affected_rows > 0) {
                $show = "setSucces";
            } else {
                $show = 'setForm';
                $alert = "<strong>FOUT: </strong>Wachtwoord zelfde als orgineel.";
            }
        } else {
            $show = 'setForm';
            $alert = "<strong>FOUT: </strong>Je wachtwoord moet minstens 6 karakters lang zijn!";
        }
    } else {
        $show = 'setForm';
        $alert = "<strong>FOUT: </strong>De 2 wachtwoorden komen niet overeen!";
    }

} elseif ($_SERVER['REQUEST_METHOD'] == "GET" && isset($_GET['a']) && $_GET['a'] == "recover" && isset($_GET['email']) && $_GET['email'] != "" && isset($_GET['u']) && $_GET['u'] != "") {
    $result = checkEmailKey(urldecode($_GET['email']), urldecode(base64_decode($_GET['u'])));
    
    $sql = "SELECT expDate  FROM `UserRecovery` WHERE `recKey` = '".$_GET['email']."' AND `IDUser` = '".urldecode(base64_decode($_GET['u']))."'";
    $res = $link->query($sql);
    $rec = mysqli_fetch_assoc($res);
    $expFormat = mktime(date("H"), date("i"), date("s"), date("m")  , date("d"), date("Y"));
    $dateToday = date("Y-m-d H:i:s",$expFormat);
    
    if ($dateToday < $rec['expDate']) {
        if ($result['status']) {
            $show = 'setForm';
            $securityUser = $result['userID'];
        } else {
            $show = 'invalidKey';
        }
    } else {
        $show = 'invalidKey';
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

        <title>Recovery - MyBasket</title>

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


        <script src="https://code.jquery.com/jquery-2.2.0.min.js"></script>
        <script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
        <link href="https://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css" rel="stylesheet">
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
                    <div class="col-md-12">
                        <div class="panel-heading">
                            <h1 style="text-align:center;">Wachtwoord vergeten</h1>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-2"></div>
                    <div class="col-md-8">
                        <form class="form-emailzoek" action="<?php echo $_SERVER['PHP_SELF']; ?>?a=<?php echo urlencode($a); ?>&email=<?php echo urlencode($email); ?>&u=<?php echo urlencode($u); ?>" method="post">
                            <div class="form-group">
                                <?php 
                                switch ($show) {
                                    case "invalidKey":
                                ?>
                                <div class="alert alert-danger" role="alert">
                                    <strong>Niet geldige sleutel</strong>
                                    <p>De opgegeven sleutel is niet meer geldig of je bent op deze pagina gekomen zonder doorverwijzing.<br>
                                        sleutels verlopen 1 dag na aanvraag. <br><br>
                                    klik <a href="wwreset.php">hier</a> om een nieuwe sleutel aan te maken.</p>
                                </div>
                                <?php
                                        break;
                                    case "setForm":
                                    if($alert != false) { 
                                        echo '<div class="alert alert-danger" role="alert">'. $alert .'</div>';
                                    }
                                ?>
                                <label for="pwd1">Wachtwoord:</label>
                                <input type="password" class="form-control" id="pwd1" name="pwd1" required>
                                <br>
                                <label for="pwd1">Wachtwoord herhalen:</label>
                                <input type="password" class="form-control" id="pwd2" name="pwd2" required>
                            </div>
                            <div class="text-center">
                                <input type="hidden" name="uID" value="<?php echo urldecode(base64_decode($_GET['u'])); ?>">
                                <button type="submit" class="btn btn-primary">Wijzigen</button>
                            </div>
                            <?php
                                    break;
                                case "setSucces":
                            ?>
                            <div class="alert alert-success" role="alert">
                                <strong>Wachtwoord succesvol aangepast</strong>
                                <p>Je wachtwoord is aangepast.<br>
                                klik <a href="wwreset.php">hier</a> om naar de startpagina te gaan of klik <a href="registreer.php">hier</a> om in te loggen.</p>
                            </div>
                            <?php
                                    break;
                                }
                            ?>
                        </form>
                    </div>
                    <div class="col-md-2"></div>
                </div>

            </div>
        </div>   

        <footer class="navbar-fixed-bottom">
            <div class="container">
                <p>Copyright &copy; GO-AO Webshops</p>
            </div>
        </footer>        
                
    </body>