<?php
session_start();
include("inc/PHPMailer/PHPMailerAutoload.php");

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] === false) { 
    header("Location:index.php");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['naam']) && isset($_POST['bericht'])) {
    if (isset($_SESSION['stamnr']) && $_SESSION['stamnr'] === 0 && isset($_POST['email'])) {
        $naam = trim($_POST['naam']);
        $email = trim($_POST['email']);
        $bericht = trim($_POST['bericht']);

        $message = "<html><body>";
        $message .= $bericht;
        $message .= "<br>From: $naam - $email</body></html>";
        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = "leandercolpaert@go-ao.eu";
        $mail->Password = '';
        $mail->Username = "leandercolpaert@go-ao.eu";
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->setFrom($email, 'Contact opnemen - GO-AO Webshops');
        $mail->addAddress('leandercolpaert@go-ao.eu', 'GO-AO Webshops Administrator');
        $mail->isHTML(true);
        $mail->Subject = "$naam zoekt contact op";
        $mail->Body    = $message;
        $mail->AltBody = strip_tags($message);

        if ($mail->send()) {} else {
            echo 'mislukt';
        }
    } elseif (isset($_SESSION['stamnr']) && $_SESSION['stamnr'] !== 0) {
        
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

        <title>Contact - MyBasket</title>

        <!-- Bootstrap Core CSS -->
        <link href="css/bootstrap.css" rel="stylesheet">

        <!-- Custom CSS -->
        <link href="css/shop-homepage.css" rel="stylesheet">

        <!-- Sticky-footer CSS -->
        <link href="css/sticky-footer.css" rel="stylesheet">

        <!-- Own CSS -->
        <link href="css/style.css" rel="stylesheet">

        <!-- Alert Animation CSS -->
        <link href="css/animation.css" rel="stylesheet"


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
                    } elseif (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
                        ?>
                        <li>
                            <a href="mijnaccount.php">Mijn account</a>
                        </li>
                        <?php
                    }
                    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
                        ?>
                        <li>
                            <a href="index.php?logout=true">Uitloggen</a>
                        </li>
                        <?php
                    } else {
                        ?>
                        <li>
                            <a href="registreer.php">Inloggen / Registreren</a>
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
                            <h1 style="text-align:center;">Contact</h1>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2"></div>
                    <div class="col-md-8">
                        <form class="form-emailzoek" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                            <h4 class="text-center"><?php echo $_SESSION['user']; if (isset($_SESSION['stamnr']) && $_SESSION['stamnr'] === 0) { echo " - " . $_SESSION['mail']; } ?></h4>
                            <input type="hidden" name="naam" value="<?php echo $_SESSION['user'] ?>">
                            <input type="hidden" name="email" value="<?php echo $_SESSION['mail'] ?>">
                            <div class="form-group">
                                <label for="comment">Bericht:</label>
                                <textarea class="form-control" rows="10" id="comment" name="bericht"></textarea>
                            </div>
                            <button class="btn btn-lg btn-primary center-block">Verzenden</button>
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