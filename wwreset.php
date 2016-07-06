<?php
////MYBA
session_start();
include("inc/verbinding_inc.php");
include("inc/PHPMailer/PHPMailerAutoload.php");

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location:index.php");
}

$type = "alert";
$alert = false;

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['mail'])) {
    $email = trim($link->real_escape_string($_POST["mail"]));
    $sql = "SELECT IDUser, Voornaam FROM Users WHERE Email = '". $email ."' LIMIT 1";
    $res = $link->query($sql);
    if ($res->num_rows == 1) {
        $rec = mysqli_fetch_assoc($res);
        $type = "alert alert-success";
        $alert = "SUCCES: mail verzonden";
        
        $expFormat = mktime(date("H"), date("i"), date("s"), date("m")  , date("d")+1, date("Y"));
        $expDate = date("Y-m-d H:i:s",$expFormat);
        $tohash = $rec['Voornaam'] . '_' . $email . rand(0,10000) .$expDate;
        $key = password_hash($tohash, PASSWORD_DEFAULT);
        $encUserID = urlencode(base64_encode($rec['IDUser']));
 
        $sql2 = "SELECT IDRec FROM UserRecovery WHERE IDUser = '".$rec['IDUser']."'";
        $res2 = $link->query($sql2);
        if ($res2->num_rows == 1) {
            $sqlIns = "UPDATE UserRecovery SET recKey = '$key', expDate = '$expDate' WHERE IDUser = '". $rec['IDUser'] ."'";
        } elseif ($res2->num_rows == 0) {         
            $sqlIns = "INSERT INTO UserRecovery (IDUser, recKey, expDate) VALUES ('". $rec['IDUser'] ."', '". $key ."', '". $expDate ."')";
        } else {
            $type = "alert alert-danger";
            $alert = "FOUT: Er is een fout verlopen in onze databank, gelieve contact met ons op te nemen.";
            exit;
        }
        
        if (!$resIns = $link->query($sqlIns)) {
            echo '<script>$.notify({title: "<strong>FOUT: </strong>"}, {message: "' . $link->error . '"}, {type: "danger"});</script>';
            echo $link->error;
        } else {
            $passwordLink = "<a href=\"https://www.go-atheneumoudenaarde.be/webshops/wwset.php?a=recover&email=" . $key . "&u=" . $encUserID . "\">https://www.go-atheneumoudenaarde.be/webshops/wwset.php?a=recover&email=" . $key . "&u=" . $encUserID . "</a>";
            $message = "<html><body>Beste ".$rec['Voornaam'].",<br>";
            $message .= "<p>Gelieve volgende link te volgen om je wachtwoord te resetten:<br>";
            $message .= "$passwordLink</p>";
            $message .= "<p>Mocht de link niet werken, gelieve de volledige link in je browser te kopiëren.</p>";
            $message .= "<p>De link zal om veiligheidsredenen na 3 dagen vervallen.</p>";
            $message .= "<p>Als u deze vergeten wachtwoord e-mail niet heeft aangevraagd, is geen verdere actie nodig, uw wachtwoord zal niet worden gereset zolang de link hierboven niet wordt bezocht.</p>";
            $message .= "<p>Alvast bedankt,</p><br>";
            $message .= "<p><a href='https://www.go-atheneumoudenaarde.be/webshops/index.php'>GO-AO Webshops-team</a></p></body></html>";
            $mail = new PHPMailer;
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = "leandercolpaert@go-ao.eu";
            $mail->Password = 'frAyech4';
            $mail->Username = "leandercolpaert@go-ao.eu";
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            $mail->setFrom('leandercolpaert@go-ao.eu', 'Wachtwoord instellen - GO-AO Webshops');
            $mail->addAddress($email, $rec['Voornaam']);
            $mail->isHTML(true);
            $mail->Subject = "Uw aanvraag voor nieuw wachtwoord";
            $mail->Body    = $message;
            $mail->AltBody = strip_tags($message);
            
            if ($mail->send()) {} else {
                $type = "alert alert-danger";
                $alert = "FOUT: Er is iets fout gelopen tijdens het verzenden van je mail:</p><p>' . $mail->ErrorInfo .'</p>";
            }   
            
        }
        
    } else {
        $type = "alert alert-danger";
        $alert = "FOUT: emailadres niet correct";
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

        <title>Wachtwoord vergeten - MyBasket</title>

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
                        <form class="form-emailzoek" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                            <div class="form-group">
                                <?php 
                                    if($alert != false) { 
                                        echo '<div class="'. $type .'" role="alert">'. $alert .'</div>';
                                    }
                                ?>
                                <label for="email">E-mail:</label>
                                <input type="email" class="form-control" id="mail" name="mail" required>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">Verzenden</button>
                                <br><br>
                                <p class="text-info">
                                    Bij het klikken op de verzend knop zal er een email verzonden worden met een link,<br>bij het klikken op deze link wordt u doorverwezen naar een pagina waar u uw wachtwoord kunt aanpassen.
                                </p>
                            </div>
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