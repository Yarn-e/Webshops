<?php
//Sessie starten
include('../dashboard/assets/inc/session.php');


include("inc/verbinding_inc.php");

$eindbedrag = 0;
$winkels = array();
if (!isset($_GET['WID'])) {
    header("Location:index.php");
}

$wid = trim($link->real_escape_string($_GET['WID']));

$sqlWinkelNaam = "SELECT Winkelnaam FROM Winkels WHERE IDWinkel = '" . $wid . "'";
$resWN = $link->query($sqlWinkelNaam);
$recWN = mysqli_fetch_assoc($resWN);

if (empty($recWN)) {
    header("Location: index.php");
}


if (!isset($_SESSION['logged_in']) && $_SESSION['logged_in'] !== true) {
    header("Location:index.php");
}

$weekterug = date("Y") . "-" . date("m") . "-" . date("d") - 7;
$sqlExp = "SELECT TijdVanAankoop FROM Bestelling WHERE Koper = '" . $_SESSION['userID'] . "' AND StatusBestelling = '0' AND Winkel = '" . $wid . "'";
$Exp = $link->query($sqlExp);
if ($Exp->num_rows == 1) {
    $ArExp = $Exp->fetch_assoc();
    if ($weekterug > $ArExp['TijdVanAankoop']) {
        $sqlUD = "UPDATE Bestelling SET StatusBestelling = '1' WHERE IDBestelling = (SELECT IDBestelling FROM Bestelling WHERE Koper = '" . $_SESSION['userID'] . "' AND StatusBestelling = '0' AND Winkel = '" . $wid . "')";
        if (!$res = $link->query($sqlUD)) {
            $_SESSION['AlertMand'] = '<script>$.notify({message: "<strong>FOUT: </strong>' . $link->error . '"}, {type: "alertCustom bg-danger"});</script>';
        } else {
            $_SESSION['AlertMand'] = '<script>$.notify({message: "<strong>SUCCES: </strong>Uw winkelmand is verlopen!"}, {type: "alertCustom bg-success"});</script>';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['Bestel'])) {
    $idbest = trim($link->real_escape_string($_POST["Bestel"]));

    $sqlSelIDDB = "SELECT IDDetail, Productnummer FROM BestellingDetail WHERE IDBestelling = (SELECT IDBestelling FROM Bestelling WHERE Koper = '" . $_SESSION['userID'] . "' AND StatusBestelling = '0' AND Winkel = '" . $wid . "')";
    $res = $link->query($sqlSelIDDB);

    $bool = true;
    $bestellingitems = array();
    while ($row = $res->fetch_assoc()) {
        $bestellingitems['IDDetail'] = $row;
    }
    foreach ($bestellingitems as $row) {
        $sqlSelPr = "SELECT Prijs FROM Producten WHERE IDProduct = " . $row['Productnummer'];
        $res2 = $link->query($sqlSelPr);
        $row2 = $res2->fetch_assoc();
        $sqlUD2 = "UPDATE BestellingDetail SET Eenheidsprijs = '" . $row2['Prijs'] . "' WHERE IDDetail = '" . $row['IDDetail'] . "'";
        if (!$res = $link->query($sqlUD2)) {
            $_SESSION['AlertMand'] = '<script>$.notify({message: "<strong>FOUT: </strong>' . $link->error . '"}, {type: "alertCustom bg-danger"});</script>';
            $bool = false;
        }
    }


    if ($bool) {
        $sqlUD = "UPDATE Bestelling SET StatusBestelling = '2' WHERE IDBestelling = '$idbest'";
        if (!$res = $link->query($sqlUD)) {
            $_SESSION['AlertMand'] = '<script>$.notify({message: "<strong>FOUT: </strong>' . $link->error . '"}, {type: "alertCustom bg-danger"});</script>';
        } else {
            include('mds_sendMsg.php');
            $_SESSION['AlertMand'] = '<script>$.notify({message: "<strong>SUCCES: </strong>Uw bestelling is verzonden"}, {type: "alertCustom bg-success"});</script>';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['leegmaken'])) {
    $idbest = trim($link->real_escape_string($_POST["leegmaken"]));

    $sqlUD = "DELETE FROM Bestelling WHERE IDBestelling = '$idbest'";
    if (!$res = $link->query($sqlUD)) {
        $_SESSION['AlertMand'] = '<script>$.notify({message: "<strong>FOUT: </strong>' . $link->error . '"}, {type: "alertCustom bg-danger"});</script>';
    } else {
        $_SESSION['AlertMand'] = '<script>$.notify({message: "<strong>SUCCES: </strong>Uw winkelmand is succesvol leeggemaakt"}, {type: "alertCustom bg-success"});</script>';
    }
}

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['iddt'])) {
    $iddt = trim($link->real_escape_string($_POST["iddt"]));
    $aantal = trim($link->real_escape_string($_POST["aantal"]));
    $maxBestel = trim($link->real_escape_string($_POST["maxaant"]));


    if ($aantal >= 1 && $aantal <= $maxBestel) {
        $sqlUD = "UPDATE BestellingDetail SET Aantal = '$aantal' WHERE IDDetail = '$iddt'";
        if (!$res = $link->query($sqlUD)) {
            $_SESSION['AlertMand'] = '<script>$.notify({message: "<strong>FOUT: </strong>' . $link->error . '"}, {type: "alertCustom bg-danger"});</script>';
        } else {
            $_SESSION['AlertMand'] = '<script>$.notify({message: "<strong>SUCCES: </strong>Uw product is succesvol aangepast"}, {type: "alertCustom bg-success"});</script>';
        }
    } else {
        $_SESSION['AlertMand'] = '<script>$.notify({message: "<strong>FOUT:: </strong>Gelieve een geldig aantal te kiezen"}, {type: "alertCustom bg-danger"});</script>';
    }
}


if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['verwijder'])) {
    $iddt = trim($link->real_escape_string($_POST["verwijder"]));

    $sqlDel = "DELETE FROM BestellingDetail WHERE IDDetail = '$iddt'";
    if (!$res = $link->query($sqlDel)) {
        $_SESSION['AlertMand'] = '<script>$.notify({message: "<strong>FOUT: </strong>' . $link->error . '"}, {type: "alertCustom bg-danger"});</script>';
    } else {
        $_SESSION['AlertMand'] = '<script>$.notify({message: "<strong>SUCCES: </strong>Uw product is succesvol verwijderd uit de winkelmand"}, {type: "alertCustom bg-success"});</script>';
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

    <title>Winkelmand - MyBasket</title>

    <!-- Bootstrap Core CSS -->
    <link href="css/bootstrap.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="css/shop-homepage.css" rel="stylesheet">

    <!-- Sticky-footer CSS -->
    <link href="css/sticky-footer.css" rel="stylesheet">

    <!-- Own CSS -->
    <link href="css/style.css" rel="stylesheet">
    <link href="css/frontstyle.css" rel="stylesheet">

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
            <li>
                <a href="winkel.php?WID=<?php echo $wid; ?>"><span class="glyphicon glyphicon-arrow-left"></span> Terug
                    naar winkel</a>
            </li>
            <!--<li>
                <a href="#">Voorgaande bestellingen</a>
            </li>-->
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
                    <h1 style="text-align:center;">Mijn Winkelmand - <?php echo $recWN['Winkelnaam']; ?></h1>
                </div>
            </div>
        </div>
    </div>
    <div class="winkelmand-container">
        <?php
        //SQL for ID's.
        $detailFromProduct = array();
        $productFromWinkel = array();
        $detailFromProduct[0] = "";
        $sqlForProduct = 'SELECT IDProduct FROM Producten WHERE Winkel = ' . $wid;
        $result = $link->query($sqlForProduct);

        while ($row = $result->fetch_assoc()) {
            $productFromWinkel[] = $row['IDProduct'];
        }

        foreach ($productFromWinkel as $prodID) {
            $sqlCount = "SELECT count(Productnummer) as AantalProd FROM BestellingDetail WHERE IDBestelling = (SELECT IDBestelling FROM Bestelling WHERE Koper = '" . $_SESSION['userID'] . "' AND StatusBestelling = 0 AND Winkel = '" . $wid . "') AND Productnummer = '" . $prodID . "'";
            $sqlCount = $link->query($sqlCount);
            $sqlCount = $sqlCount->fetch_assoc();

            $sqlCount2 = "SELECT IDDetail FROM BestellingDetail WHERE IDBestelling = (SELECT IDBestelling FROM Bestelling WHERE Koper = '" . $_SESSION['userID'] . "' AND StatusBestelling = '0' AND Winkel = '" . $wid . "') AND Productnummer = '" . $prodID . "'";
            $sqlCount2 = $link->query($sqlCount2);

            unset($detailFromProduct);
            $detailFromProduct = array();
            $detailFromProduct[0] = "";
            while ($row = $sqlCount2->fetch_assoc()) {
                $detailFromProduct[] = $row['IDDetail'];
            }

            for ($i = 1; $i <= $sqlCount['AantalProd']; $i++) {
                $sqlForMand = 'SELECT * FROM BestellingDetail WHERE IDBestelling = (SELECT IDBestelling FROM Bestelling WHERE Koper = ' . $_SESSION['userID'] . ' AND StatusBestelling = 0  AND Winkel = ' . $wid . ') AND Productnummer = ' . $prodID . ' AND IDDetail = ' . $detailFromProduct[$i];
                $resultMand = $link->query($sqlForMand);
                $resultMand = mysqli_fetch_assoc($resultMand);

                if ($resultMand !== NULL) {
                    $sqlProd = "SELECT * FROM Producten WHERE IDProduct = " . $resultMand['Productnummer'];
                    $res2 = $link->query($sqlProd);
                    $rec2 = mysqli_fetch_assoc($res2);
                    ?>
                    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?WID=<?php echo $wid; ?>">
                        <div class="row">
                            <div class="col-md-12 winkel thumbnail winkelmand-product-cover" style="cursor:default;">
                                <div class="pull-left winkelmand-product-image"
                                     style="background-image: url('ProductImage/<?php echo $rec2['Foto']; ?>');"></div>
                                <div class="pull-left" style="width:40%;">
                                    <h1 class="winkelmand-product-h1"
                                        style="color: black; font-size: 24px; margin-top: 4px;"><?php echo $rec2['Productnaam']; ?></h1>
                                    <h4 style="color:#333;"><label>Aantal: <input type="number" class="form"
                                                                                  name="aantal" min="1"
                                                                                  style="width: 50px;"
                                                                                  max="<?php echo $rec2['Maxaantal'] ?>"
                                                                                  value="<?php echo $resultMand['Aantal']; ?>"></label>
                                    </h4>
                                    <h4 style="color:#333; margin-top:-8px;">
                                        Totaal: &euro; <?php $total = $resultMand['Aantal'] * $rec2['Prijs'];
                                        $total2 = $total;
                                        $eindbedrag = $eindbedrag + $total2;
                                        $totalEnd = number_format($total2, 2, ',', ' ');
                                        echo $totalEnd; ?>
                                    </h4>
                                </div>
                                <div class="pull-right" style="width: 12%; margin: 0 auto;">
                                    <button type="submit" class="btn btn-default"
                                            style="margin-top: 14px; ;margin-bottom: 2px; width: 96px;">Wijzigen
                                    </button>
                                    <br>
                                    <a href="#" data-toggle="modal"
                                       data-iddetail="<?php echo $resultMand['IDDetail']; ?>"
                                       data-productnaam="<?php echo $rec2['Productnaam']; ?>"
                                       data-target="#modalDelete">
                                        <button class="btn btn-danger"
                                                style="margin-top: 2px; ;margin-bottom: 7px; width: 96px; color: white;">
                                            Verwijderen
                                        </button>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="prijs" value="<?php echo $rec2['Prijs']; ?>">
                        <input type="hidden" name="iddt" value="<?php echo $resultMand['IDDetail']; ?>">
                        <input type="hidden" name="maxaant" value="<?php echo $rec2['Maxaantal']; ?>">
                    </form>
                    <?php
                }
            }
        }
        ?>

    </div>
    <br>

    <div class="pull-right"
         style="width:300px; height: 100px; background-color: #fff; padding: 10px 10px 10px 10px; border-radius: 5px;">
        <h4 class="pull-right">Totaal prijs: <strong>&euro; <?php $eindbedrag;
                echo '';
                $eindbedrag = number_format($eindbedrag, 2, ',', ' ');
                echo $eindbedrag; ?></strong></h4><br><br>
        <a href="#" data-toggle="modal" data-idbest="<?php echo $resultMand['IDBestelling']; ?>"
           data-target="#modalBestel">
            <button class="btn btn-primary pull-right" style="margin-left:10px;" name="Bestel">Bestellen</button>
        </a>
        <a href="#" data-toggle="modal" data-idbest="<?php echo $resultMand['IDBestelling']; ?>"
           data-target="#modalLeeg">
            <button class="btn btn-default pull-right">Leeg winkelmand</button>
        </a>
    </div>

</div>
<!-- Modal -->
<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>?WID=<?php echo $_GET['WID']; ?>">
    <div id="modalDelete" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Item verwijderen</h4>
                </div>
                <div class="modal-body">
                    <p>Weet u zeker dat u het product '<span class="naam"></span>' wilt
                        verwijderen uit uw winkelmand?</p>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="verwijder" class="prID">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        Annuleren
                    </button>
                    <button type="submit" class="btn btn-primary">Verwijderen</button>
                    </a>
                </div>
            </div>

        </div>
    </div>
</form>

<!-- Modal Legen -->
<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>?WID=<?php echo $_GET['WID']; ?>">
    <div id="modalLeeg" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Winkelmand leegmaken</h4>
                </div>
                <div class="modal-body">
                    <p>Weet u zeker dat u alles uit uw winkelmand wil verwijderen?</p>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="leegmaken" class="prID">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        Annuleren
                    </button>
                    <button type="submit" class="btn btn-primary">Verwijderen</button>
                    </a>
                </div>
            </div>

        </div>
    </div>
</form>

<!-- Modal Bestellen -->
<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>?WID=<?php echo $_GET['WID']; ?>">
    <div id="modalBestel" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Winkelmand bestellen</h4>
                </div>
                <div class="modal-body">
                    <p>Weet u zeker dat u alles in uw winkelmand wil bestellen?</p>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="Bestel" class="prID">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        Annuleren
                    </button>
                    <button type="submit" class="btn btn-primary">Bestellen</button>
                    </a>
                </div>
            </div>

        </div>
    </div>
</form>

<footer class="navbar-fixed-bottom">
    <div class="container">
        <p>Copyright &copy; GO-AO Webshops - <a href="contact.php" class="contact-link">Contact</a></p>
    </div>
</footer>
<!-- jQuery -->
<script src="js/jquery.min.js"></script>

<!-- Bootstrap Core JavaScript -->
<script src="js/bootstrap.min.js"></script>

<script>
    $('#modalDelete').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var id = button.data('iddetail');// Extract info from data-* attributes
        var naam = button.data('productnaam');// Extract info from data-* attributes
        // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
        // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
        var modal = $(this);
        modal.find('.prID').val(id);
        modal.find('.naam').text(naam);
    });

    $('#modalLeeg').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var id = button.data('idbest');// Extract info from data-* attributes
        // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
        // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
        var modal = $(this);
        modal.find('.prID').val(id);
    });

    $('#modalBestel').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var id = button.data('idbest');// Extract info from data-* attributes
        // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
        // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
        var modal = $(this);
        modal.find('.prID').val(id);
    });
</script>
</body>
<script src="js/bootstrap-notify.js"></script>
<?php
if (isset($_SESSION['AlertMand'])) {
    echo $_SESSION['AlertMand'];
    unset($_SESSION['AlertMand']);
}
?>
</html>