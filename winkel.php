<?php
// enkel via HTTPS
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "") {
    $redirect = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: $redirect");
}  

//Sessie starten
include('../dashboard/assets/inc/session.php');

include("inc/verbinding_inc.php");
include("functions/pr-fotoupload.php");
$modify = false;


if (!isset($_GET['WID'])) {
    header("Location:index.php");
}

// Alles uit db halen.
$wid = trim($link->real_escape_string($_GET['WID']));

if (!is_numeric($wid)) {
    Header("Location: index.php");
}

$sql1 = "SELECT * FROM Winkels WHERE IDWinkel = '" . $wid . "'";
$res1 = $link->query($sql1);
$rec1 = mysqli_fetch_assoc($res1);

if (empty($rec1)) {
    Header("Location: index.php");
}

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true) {
    if (isset($_SESSION['permission']) && $_SESSION['permission'] > 1) {
        // Userinfo uit db halen.

        $wid = trim($link->real_escape_string($_GET['WID']));
        $sql2 = "SELECT IDUser FROM UsersWinkels WHERE IDWinkel = '" . $wid . "' AND IDUser = '" . $_SESSION['userID'] . "'";
        $res2 = $link->query($sql2);
        if ($res2->num_rows > 0) {
            $modify = true;
        }
    } elseif (isset($_SESSION['admin']) && $_SESSION['admin'] == true) {
        $modify = true;
    } elseif (isset($_SESSION['mini-admin']) && $_SESSION['mini-admin'] == true) {
        $sql = "SELECT IDWinkel FROM Winkels WHERE Winkelbeheerder = " . $_SESSION['userID'] . " AND IDWinkel = '" . $wid . "'";
        $res = $link->query($sql);
        if ($res->num_rows > 0) {
            $modify = true;
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['logout'])) {
    session_destroy();
    //Sessie starten
    include('../dashboard/assets/inc/session.php');
    header("Location:index.php");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['zoek'])) {
    $zoek = trim($link->real_escape_string($_POST["zoek"]));
    header("Location:" . $_SERVER['PHP_SELF'] . "?WID=" . $_GET['WID'] . "&zoek=" . $zoek) ;
}

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['statusPr'])) {
    $prodid = trim($link->real_escape_string($_POST["prodID"]));

    if ($_POST['statusPr'] == "Deactiveren") {
        $sql = "UPDATE Producten SET StatusProduct = 1
                WHERE IDProduct = $prodid
                ";
        if (!$res = $link->query($sql)) {
            $_SESSION['AlertWinkel'] = '<script>$.notify({message: "<strong>FOUT: </strong>' . $link->error . '"}, {type: "alertCustom bg-danger"});</script>';
        } else {
            $_SESSION['AlertWinkel'] = '<script>$.notify({message: "<strong>SUCCES: </strong>Uw product is succesvol gedeactiveerd"}, {type: "alertCustom bg-success"});</script>';
        }
    } elseif ($_POST['statusPr'] == "Activeren") {
        $sql = "UPDATE Producten SET StatusProduct = 0
                WHERE IDProduct = $prodid
                ";
        if (!$res = $link->query($sql)) {
            $_SESSION['AlertWinkel'] = '<script>$.notify({message: "<strong>FOUT: </strong>' . $link->error . '"}, {type: "alertCustom bg-danger"});</script>';
        } else {
            $_SESSION['AlertWinkel'] = '<script>$.notify({message: "<strong>SUCCES: </strong>Uw product is succesvol geactiveerd"}, {type: "alertCustom bg-success"});</script>';
        }
    }
    
    $_POST['practie'] = false;
}

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['prodID']) && $_POST['practie'] == 'Wijzigen') {
    $naam = trim($link->real_escape_string($_POST['Naam']));
    $prijs = trim($link->real_escape_string($_POST["Prijs"]));
    $uitleg = trim($link->real_escape_string($_POST["Uitleg"]));
    $max = trim($link->real_escape_string($_POST["Max"]));
    $prodid = trim($link->real_escape_string($_POST["prodID"]));
    $BTW = trim($link->real_escape_string($_POST["BTW"]));
    
    if (isset($_FILES['prFoto'])) {
        $prodFoto = model_product_upload_img($_GET['WID'], $naam);
        $lengte = strlen($naam);
        if ($prijs > 0 && $prijs <= 250) {
            if ($lengte <= 18) {
                if ($max > 0 && $max <= 100) {
                    $prijs = str_replace(",", ".", $prijs);
                    if ($prodFoto !== false) {
                        $sql = "SELECT Foto FROM Producten WHERE IDProduct = '$prodid'";
                        $res = $link->query($sql);
                        $rec = $res->fetch_assoc();
                        $lastpic = $rec['Foto'];
                        $sql = "UPDATE Producten SET
                            Productnaam = '$naam',
                            Foto = '$prodFoto',
                            Prijs = '$prijs',
                            Uitleg = '$uitleg',
                            BTW = '$BTW',
                            Maxaantal = '$max'
                            WHERE IDProduct = '$prodid'
                            ";
                    } else {
                        $sql = "UPDATE Producten SET
                            Productnaam = '$naam',
                            Prijs = '$prijs',
                            Uitleg = '$uitleg',
                            BTW = '$BTW',
                            Maxaantal = '$max'
                            WHERE IDProduct = '$prodid'
                            ";
                    }
                    if (!$res = $link->query($sql)) {
                        $_SESSION['AlertWinkel'] = '<script>$.notify({message: ""<strong>FOUT: </strong>"' . $link->error . '"}, {type: "alertCustom bg-danger"});</script>';
                    } elseif ($link->affected_rows > 0) {
                        if (isset($lastpic)) {
                            unlink('ProductImage/' . $lastpic);
                        }
                        $_SESSION['AlertWinkel'] = '<script>$.notify({message: "<strong>SUCCES: </strong>Uw product ' . $naam . ' is succesvol gewijzigd"}, {type: "alertCustom bg-success"});</script>';
                    } else {
                        $_SESSION['AlertWinkel'] = '<script>$.notify({message: "<strong>FOUT: </strong>U hebt niets veranderd"}, {type: "alertCustom bg-danger"});</script>';
                    }
                } else {
                    $_SESSION['AlertWinkel'] = '<script>$.notify({message: "<strong>FOUT: </strong>Een product moet minstens 1x kunnen worden gekocht en maximum 100x"}, {type: "alertCustom bg-danger"});</script>';
                }
            } else {
                $_SESSION['AlertWinkel'] = '<script>$.notify({message: "<strong>FOUT: </strong>Een productnaam mag maximum 18 characters lang zijn"}, {type: "alertCustom bg-danger"});</script>';
            }
        } else {
            $_SESSION['AlertWinkel'] = '<script>$.notify({message: "<strong>FOUT: </strong>Gelieve een geldige prijs in te geven"}, {type: "alertCustom bg-danger"});</script>';
        }
    } else {
        $_SESSION['AlertWinkel'] = '<script>$.notify({message: "<strong>FOUT: </strong>Gelieve een geldige foto te selecteren"}, {type: "alertCustom bg-danger"});</script>';
    }
}

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['prodID']) && $_POST['practie'] == 'Toevoegen') {
    $naam = trim($link->real_escape_string($_POST['Naam']));
    $prijs = trim($link->real_escape_string($_POST["Prijs"]));
    $uitleg = trim($link->real_escape_string($_POST["Uitleg"]));
    $max = trim($link->real_escape_string($_POST["Max"]));
    $prodid = trim($link->real_escape_string($_POST["prodID"]));
    $BTW = trim($link->real_escape_string($_POST["BTW"]));

    if (isset($_FILES['prFoto'])) {
        $prodFoto = model_product_upload_img($_GET['WID'], $naam);
        if ($prodFoto != false) {
            $lengte = strlen($naam);
            if ($prijs > 0 && $prijs <= 250) {
                if ($lengte <= 18) {
                    if ($max > 0 && $max <= 100) {
                        $prijs = str_replace(",", ".", $prijs);
                        $sql = "INSERT INTO Producten(
                            Productnaam,
                            Foto,
                            Prijs,
                            Uitleg,
                            BTW,
                            Maxaantal,
                            Winkel)
                        VALUES(
                            '$naam',
                            '$prodFoto',
                            '$prijs',
                            '$uitleg',
                            '$BTW',
                            '$max',
                            '" . $_GET['WID'] . "')";
                        if (!$res = $link->query($sql)) {
                            $_SESSION['AlertWinkel'] = '<script>$.notify({message: "<strong>FOUT: </strong>' . $link->error . '"}, {type: "alertCustom bg-danger"});</script>';
                        } else {
                            $_SESSION['AlertWinkel'] = '<script>$.notify({message: "<strong>SUCCES: </strong>Uw product is succesvol toegevoegd"}, {type: "alertCustom bg-success"});</script>';
                        }
                    } else {
                        $_SESSION['AlertWinkel'] = '<script>$.notify({message: "<strong>FOUT: </strong>Een product moet minstens 1x kunnen worden gekocht en maximum 100x"}, {type: "alertCustom bg-danger"});</script>';
                    }
                } else {
                    $_SESSION['AlertWinkel'] = '<script>$.notify({message: "<strong>FOUT: </strong>Een productnaam mag maximum 18 characters lang zijn"}, {type: "alertCustom bg-danger"});</script>';
                }
            } else {
                $_SESSION['AlertWinkel'] = '<script>$.notify({message: "<strong>FOUT: </strong>Gelieve een geldige prijs in te geven"}, {type: "alertCustom bg-danger"});</script>';
            }
        } else {
            $_SESSION['AlertWinkel'] = '<script>$.notify({message: "<strong>FOUT: </strong>Gelieve een geldige foto te selecteren"}, {type: "alertCustom bg-danger"});</script>';
        }
    } else {
        $_SESSION['AlertWinkel'] = '<script>$.notify({message: "<strong>FOUT: </strong>Gelieve een foto te selecteren"}, {type: "alertCustom bg-danger"});</script>';
    }
}

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['kind']) && $_POST['kind'] == "ProdBestellen") {
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        $aantal = trim($link->real_escape_string($_POST["aantal"]));
        $IDProd = trim($link->real_escape_string($_POST["product"]));
        $prodPrijs = trim($link->real_escape_string($_POST["prijs"]));
        $maxBestel = trim($link->real_escape_string($_POST["maxbestel"]));
        if ($aantal >= 1 && $aantal <= $maxBestel) {
            $sqlSel = "SELECT IDBestelling FROM Bestelling WHERE Koper = '" . $_SESSION['userID'] . "' AND StatusBestelling = 0 AND Winkel = '" . $wid . "'";
            $resBes = $link->query($sqlSel);
            if ($resBes->num_rows == 1) {
                $IDB = mysqli_fetch_assoc($resBes);
                $bsPrijs = str_replace(",", ".", $prodPrijs);
                
                $sqlSelD = "SELECT IDDetail, Aantal FROM BestellingDetail WHERE IDBestelling = '" . $IDB['IDBestelling'] . "' AND Productnummer = '" . $IDProd . "'";
                $resBesD = $link->query($sqlSelD);
                if ($resBesD->num_rows == 1) {
                    $Qr = mysqli_fetch_assoc($resBesD);
                    $CurAant = $Qr['Aantal'];
                    $tot = $CurAant + $aantal;
                    if ($tot <= $maxBestel) {
                        $sqlUDD = "UPDATE BestellingDetail SET Aantal = '".$tot."' WHERE IDDetail = '". $Qr['IDDetail'] ."'";

                        if (!$resUDD = $link->query($sqlUDD)) {
                            $_SESSION['AlertWinkel'] = '<script>$.notify({message: "<strong>FOUT: </strong>' . $link->error . '"}, {type: "alertCustom bg-danger"});</script>';
                        } else {
                            $_SESSION['AlertWinkel'] = '<script>$.notify({message: "<strong>SUCCES: </strong>Uw product is succesvol toegevoegd"}, {type: "alertCustom bg-success"});</script>';
                        }
                    } else {
                        $_SESSION['AlertWinkel'] = '<script>$.notify({message: "<strong>FOUT: </strong>Dit product mag maar '. $maxBestel .' keer verkocht worden per keer <br>(Je hebt er al '. $CurAant .' in je winkelmand.)"}, {type: "alertCustom bg-danger"});</script>';
                    }
                } else {
                    $sqlIProd = "INSERT INTO BestellingDetail(
                                    IDBestelling,
                                    Productnummer,
                                    Aantal,
                                    Eenheidsprijs
                                    )
                                VALUES(
                                    '" . $IDB['IDBestelling'] . "',
                                    '" . $IDProd . "',
                                    '" . $aantal . "',
                                    '" . $bsPrijs . "')";
                    $vndaag = date("Y") . "-" . date("m") . "-" . date("d");
                    $sqlInsBest = "UPDATE Bestelling
                                    SET TijdVanAankoop = '$vndaag'
                                    WHERE IDBestelling = '".$IDB['IDBestelling'] ."'";
                    $resInsBes = $link->query($sqlInsBest);

                    if (!$res = $link->query($sqlIProd)) {
                        $_SESSION['AlertWinkel'] = '<script>$.notify({message: "<strong>FOUT: </strong>' . $link->error . '"}, {type: "alertCustom bg-danger"});</script>';
                    } else {
                        $_SESSION['AlertWinkel'] = '<script>$.notify({message: "<strong>SUCCES: </strong>Uw product is succesvol toegevoegd"}, {type: "alertCustom bg-success"});</script>';
                    }
                }
            } elseif ($resBes->num_rows == 0) {
                $vndaag = date("Y") . "-" . date("m") . "-" . date("d");
                $sqlInsBest = "INSERT INTO Bestelling(
                                Koper,
                                TijdVanAankoop,
                                StatusBestelling,
                                Winkel
                                )
                            VALUES(
                                '" . $_SESSION['userID'] . "',
                                '" . $vndaag . "',
                                '0',
                                '" . $wid . "')";
                $resInsBes = $link->query($sqlInsBest);
                $sqlSelID = "SELECT IDBestelling FROM Bestelling WHERE Koper = '" . $_SESSION['userID'] . "' AND StatusBestelling = 0 AND Winkel = '" . $wid . "'";
                $resSelID = $link->query($sqlSelID);
                $IDB = mysqli_fetch_assoc($resSelID);
                $bsPrijs = str_replace(",", ".", $prodPrijs);
                $sqlIProd = "INSERT INTO BestellingDetail(
                                IDBestelling,
                                Productnummer,
                                Aantal,
                                Eenheidsprijs
                                )
                            VALUES(
                                '" . $IDB['IDBestelling'] . "',
                                '" . $IDProd . "',
                                '" . $aantal . "',
                                '" . $bsPrijs . "')";
                if (!$res = $link->query($sqlIProd)) {
                    $_SESSION['AlertWinkel'] = '<script>$.notify({message: "<strong>FOUT: </strong>' . $link->error . '"}, {type: "alertCustom bg-danger"});</script>';
                } else {
                    $_SESSION['AlertWinkel'] = '<script>$.notify({message: "<strong>SUCCES: </strong>Uw product is succesvol toegevoegd"}, {type: "alertCustom bg-success"});</script>';
                }
            } else {
                $_SESSION['AlertWinkel'] = '<script>$.notify({message: "<strong>FOUT: </strong>Intern probleem"}, {type: "alertCustom bg-danger"});</script>';
            }
        } else {
            $_SESSION['AlertWinkel'] = '<script>$.notify({message: "<strong>FOUT: </strong>Gelieve een geldig aantal te bestellen dat kleiner is dan ' . $maxBestel . '"}, {type: "alertCustom bg-danger"});</script>';
        }
    } else {
        $_SESSION['AlertWinkel'] = '<script>$.notify({message: "<strong>FOUT: </strong>Je moet ingelogd zijn voor u een product kan bestellen"}, {type: "alertCustom bg-danger"});</script>';
    }
}

if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['verwijder'])) {
    $idpr = trim($link->real_escape_string($_POST["verwijder"]));
    $sql = "SELECT Foto FROM Producten WHERE IDProduct = '$idpr'";
    $res2 = $link->query($sql);
    $fotoNaam = mysqli_fetch_assoc($res2);
    $foto = $fotoNaam['Foto'];

    $sqlDel = "DELETE FROM Producten WHERE IDProduct = '$idpr'";
    if (!$res = $link->query($sqlDel)) {
        $_SESSION['AlertWinkel'] = '<script>$.notify({message: "<strong>FOUT: </strong>' . $link->error . '"}, {type: "alertCustom bg-danger"});</script>';
    } else {
        unlink("ProductImage/" . $foto);
        $_SESSION['AlertWinkel'] = '<script>$.notify({message: "<strong>SUCCES: </strong>Uw product is succesvol verwijderd"}, {type: "alertCustom bg-success"});</script>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title><?php echo $rec1['Winkelnaam']; ?> - MyBasket</title>

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
            <button type="button" class="navbar-toggle" data-toggle="collapse"
                    data-target="#bs-example-navbar-collapse-1">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="index.php">GO-AO Webshops</a>

        </div>
        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse " id="bs-example-navbar-collapse-1">
            <div class="nav navbar-form navbar-left">
                <div class="input-group">
                    <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>?WID=<?php echo $_GET['WID']; ?>">
                        <input type="text" name="zoek" class="form-control">
                        <span class="input-group-btn">
                            <button class="btn btn-default" type="submit" name="zoekProd">
                                Zoeken
                            </button>
                        </span>
                    </form>
                </div>
            </div>
            <ul class="nav navbar-nav navbar-right">
                <?php
                if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
                    //Needed vars.
                    $aantalMand = 0;

                    //SQL for ID's.
                    $sqlForProduct = 'SELECT IDProduct FROM Producten WHERE Winkel = ' . $wid;
                    $result = $link->query($sqlForProduct);

                    if ($result->num_rows != 0) {
                        while ($row = $result->fetch_assoc()) {
                            $productFromWinkel[] = $row;
                        }

                        //Loop for getting all Bestellingen.
                        foreach ($productFromWinkel as $prodID) {
                            $sqlForMand = 'SELECT Aantal as AantalBest FROM BestellingDetail WHERE IDBestelling = (SELECT IDBestelling FROM Bestelling WHERE Koper = ' . $_SESSION['userID'] . ' AND StatusBestelling = 0 AND Winkel = ' . $wid . ') AND Productnummer = ' . $prodID['IDProduct'];
                            $resultMand = $link->query($sqlForMand);
                            while ($row = $resultMand->fetch_assoc()) {
                                $aantalMand = $aantalMand + $row['AantalBest'];
                            }
                        }
                    } else {
                        $aantalMand = 0;
                    }
                    ?>
                    <li class="header-user">
                        Ingelogd als: <b><?php echo $_SESSION['user'] ?></b>
                    </li>
                    <li>
                        <a href="winkelmand.php?WID=<?php echo $_GET['WID']; ?>"><span
                                class="glyphicon glyphicon-shopping-cart header-cart"><span
                                    class="header-cart-text">(<?php echo $aantalMand ?>)</span></span></a>
                    </li>
                    <?php
                }
                if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && (isset($_SESSION['admin']) && $_SESSION['admin'] === true) || (isset($_SESSION['mini-admin']) && $_SESSION['mini-admin'] === true) || (isset($_SESSION['supervisor']) && $_SESSION['supervisor'] === true)) {
                    ?>
                    <li>
                        <a href="dashboard/index.php">Dashboard</a>
                    </li>
                    <?php
                } elseif (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
                    if (isset($_SESSION['stamnr']) && $_SESSION['stamnr'] === 0) {
                    ?>
                    <li>
                        <a href="mijnaccount.php">Mijn account</a>
                    </li>
                    <?php
                    }
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
                        <a href="registreer.php">Inloggen/Registreren</a>
                    </li>
                    <?php
                }
                ?>
            </ul>
        </div>
        <!-- /.navbar-collapse -->
    </div>
    <!-- /.container -->
</nav>
<!-- Page Content -->
<div class="container">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h1><?php echo $rec1['Winkelnaam']; ?>
                <small>Winkel van GO! atheneum Oudenaarde</small>
            </h1>
        </div>
        <div class="panel-body">
            <div class="row">

                <div class="col-md-12">

                    <div class="row">

                        <div class="col-sm-3 col-lg-3 col-md-3" <?php
                        if (isset($modify) && !$modify) {
                            echo 'style="display: none;"';
                        }
                        ?>>
                            <a href="#" class="thumbnail">
                                <img src="image/background-product-white_1.png" alt="..."
                                     data-toggle="modal"
                                     data-practie="Toevoegen"
                                     data-productfoto="ProductImage/noimage.png"
                                     data-target="#modalProduct">
                            </a>
                        </div>

                        <?php
                        if (isset($_GET['WID'])) {
                            if ($modify) {
                                $sts = "";
                            } else {
                                $sts = " AND StatusProduct = 0";
                            }
                            
                            // Alles uit db halen.
                            $wid = trim($link->real_escape_string($_GET['WID']));
                            $sql = "SELECT * FROM Producten WHERE Winkel = '" . $wid . "'$sts";
                            if (isset($_GET['zoek'])) {
                                $sql = "SELECT * FROM Producten WHERE Winkel = '" . $wid . "' AND Productnaam LIKE '" . $_GET['zoek'] ."%'$sts";
                            }
                        }

                        if ($res = $link->query($sql)) {
                            if ($res->num_rows > 0) {
                                // Items neerzetten
                                while ($rec = mysqli_fetch_assoc($res)) {
                                    $prPrijs = str_replace(".", ",", $rec['Prijs']);
                                    // Alle producten laten zien uit een winkel
                                    if ($rec['StatusProduct'] == 1) {
                                        $ribbon = true;
                                    } else {
                                        $ribbon = false;
                                    }
                                    ?>
                                    <form method="post"
                                          action="<?php echo $_SERVER['PHP_SELF'] ?>?WID=<?php echo $_GET['WID']; ?>">
                                        <div class="col-sm-3 col-lg-3 col-md-3">
                                            <div class="thumbnail">
                                                <div style="position:relative;">
                                                    <?php if ($ribbon) { ?><div class="ribbon-wrapper-maroon"data-toggle="modal"
                                                             data-practie="<?php
                                                             if (isset($modify) && $modify) {
                                                                 echo "Wijzigen";
                                                             } else {
                                                                 echo $rec['Productnaam'];
                                                             }
                                                             ?>"
                                                             data-productid="<?php echo $rec['IDProduct'] ?>"
                                                             data-productnaam="<?php echo $rec['Productnaam'] ?>"
                                                             data-productprijs="<?php echo $prPrijs ?>"
                                                             data-productuitleg="<?php echo $rec['Uitleg'] ?>"
                                                             data-productmax="<?php echo $rec['Maxaantal'] ?>"
                                                             data-productactief="<?php if ($ribbon) {echo "Activeren";} else {echo "Deactiveren";} ?>"
                                                             data-productfoto="ProductImage/<?php echo $rec['Foto'] ?>"
                                                            <?php
                                                            if (isset($modify) && $modify) {
                                                                echo 'data-productbtw="' . $rec["BTW"] . '"';
                                                            }
                                                            ?>
                                                             data-target="<?php
                                                             if (isset($modify) && $modify) {
                                                                 echo "#modalProduct";
                                                             } else {
                                                                 echo "#modalKlant";
                                                             }
                                                             ?>"><div class="ribbon-maroon">niet-actief</div></div><?php } ?>
                                                    <a href='#'>
                                                        
                                                        <img src="ProductImage/<?php echo $rec['Foto'] ?>"
                                                             style="max-width: 350px;height: 150px;"
                                                             data-toggle="modal"
                                                             data-practie="<?php
                                                             if (isset($modify) && $modify) {
                                                                 echo "Wijzigen";
                                                             } else {
                                                                 echo $rec['Productnaam'];
                                                             }
                                                             ?>"
                                                             data-productid="<?php echo $rec['IDProduct'] ?>"
                                                             data-productnaam="<?php echo $rec['Productnaam'] ?>"
                                                             data-productprijs="<?php echo $prPrijs ?>"
                                                             data-productuitleg="<?php echo $rec['Uitleg'] ?>"
                                                             data-productmax="<?php echo $rec['Maxaantal'] ?>"
                                                             data-productactief="<?php if ($ribbon) {echo "Activeren";} else {echo "Deactiveren";} ?>"
                                                             data-productfoto="ProductImage/<?php echo $rec['Foto'] ?>"
                                                            <?php
                                                            if (isset($modify) && $modify) {
                                                                echo 'data-productbtw="' . $rec["BTW"] . '"';
                                                            }
                                                            ?>
                                                             data-target="<?php
                                                             if (isset($modify) && $modify) {
                                                                 echo "#modalProduct";
                                                             } else {
                                                                 echo "#modalKlant";
                                                             }
                                                             ?>"
                                                        >
                                                        
                                                        <?php
                                                        if (isset($modify) && $modify) {
                                                            ?>
                                                            <div
                                                                style="position:absolute;top: 0;right: 0; background-color: lightgrey; width: 40px; height: 35px; border-bottom-left-radius: 10px;">
                                                                <?php
                                                                echo '<button class="close" style="position:absolute;top: 5px;right:10px;" type="button"><span aria-hidden="true" data-toggle="modal"
                                                                        data-productid="' . $rec['IDProduct'] . '" 
                                                                        data-productnaam="' . $rec['Productnaam'] . '" 
                                                                        data-target="#modalVerwijder"><span class="glyphicon glyphicon-trash"></span></span>
                                                                        </button>';
                                                                ?>
                                                            </div>
                                                            <?php
                                                        }
                                                        ?>
                                                    </a>
                                                </div>
                                                
                                                <div class="caption" style="cursor:default;">
                                                    
                                                    <h4 class="pull-right">&euro; <?php echo $prPrijs ?></h4>
                                                    <h4>
                                                        <a href='#'
                                                           data-toggle="modal"
                                                           data-practie="<?php
                                                           if (isset($modify) && $modify) {
                                                               echo "Wijzigen";
                                                           } else {
                                                               echo $rec['Productnaam'];
                                                           }
                                                           ?>"
                                                           data-productid="<?php echo $rec['IDProduct'] ?>"
                                                           data-productnaam="<?php echo $rec['Productnaam'] ?>"
                                                           data-productprijs="<?php echo $prPrijs ?>"
                                                           data-productuitleg="<?php echo $rec['Uitleg'] ?>"
                                                           data-productmax="<?php echo $rec['Maxaantal'] ?>"
                                                           data-productactief="<?php if ($ribbon) {echo "Activeren";} else {echo "Deactiveren";} ?>"
                                                           data-productfoto="ProductImage/<?php echo $rec['Foto'] ?>"
                                                            <?php
                                                            if (isset($modify) && $modify) {
                                                                echo 'data-productbtw="' . $rec["BTW"] . '"';
                                                            }
                                                            ?>
                                                           data-target="<?php
                                                           if (isset($modify) && $modify) {
                                                               echo "#modalProduct";
                                                           } else {
                                                               echo "#modalKlant";
                                                           }
                                                           ?>"
                                                        >
                                                            <?php echo $rec['Productnaam'] ?>
                                                        </a>
                                                    </h4>
                                                    <div class="row product-sm">
                                                        <h5 class="col-xs-4">Aantal:</h5>
                                                        <div class="input-group col-xs-8 form-inline">
                                                            <input type="number" class="form-control" name="aantal"
                                                                   min="1" value="1"
                                                                   max="<?php echo $rec['Maxaantal'] ?>">
                                                            <input type="hidden" name="kind" value="ProdBestellen">
                                                            <input type="hidden" name="product"
                                                                   value="<?php echo $rec['IDProduct']; ?>">
                                                            <input type="hidden" name="maxbestel"
                                                                   value="<?php echo $rec['Maxaantal']; ?>">
                                                            <input type="hidden" name="prijs"
                                                                   value="<?php echo $prPrijs; ?>">
                                                                    <span class="input-group-btn">
                                                                        <button class="btn btn-primary" type="submit">
                                                                            Toevoegen
                                                                        </button>
                                                                    </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    <?php
                                }
                            } else {
                                ?><h4 style="padding-left: 10px;">Geen producten beschikbaar</h4> <?php
                            }
                        }
                        ?>

                        <!-- ON ITEM CLICK MODAL FORM -->
                        <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>?WID=<?php echo $_GET['WID']; ?>"
                              enctype="multipart/form-data">
                            <div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="#modalProduct"
                                 id="modalProduct">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span></button>
                                            <h4 class="modal-title" id="modalProduct"><span class="actie"></span></h4>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <div style="position:relative;">
                                                        <img class="center-block Foto" src=""
                                                             style="max-width: 450px; max-height: 450px;">
                                                        <input type="file" name="prFoto" id="prFoto"
                                                               class="btn btn-default"
                                                               style="position:absolute;bottom: 10px;right:50px;">
                                                    </div>
                                                </div>
                                                &nbsp;
                                                <hr>

                                                <div class="row">
                                                    <div class="col-sm-1"></div>
                                                    <div class="col-sm-3"><label>Product: <input type="text"
                                                                                                 class="form-control mdNaam"
                                                                                                 name="Naam"
                                                                                                 required></label></div>
                                                    <div class="col-sm-4"></div>
                                                    <div class="col-sm-3"><label>Prijs: <input type="text"
                                                                                               class="form-control mdPrijs"
                                                                                               name="Prijs"
                                                                                               required></label></div>
                                                    <div class="col-sm-1"></div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-1"></div>
                                                    <div class="col-sm-10"><textarea type="text"
                                                                                     class="form-control mdUitleg"
                                                                                     name="Uitleg" required></textarea>
                                                    </div>
                                                    <div class="col-sm-1"></div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-6"></div>
                                                    <div class="col-sm-3" style="text-align: right;">
                                                        <label>BTW
                                                            <select class="form-control" name="BTW" id="selectBTW">
                                                                <option value="0">0</option>
                                                                <option value="6">6</option>
                                                                <option value="12">12</option>
                                                                <option value="21">21</option>
                                                            </select>
                                                        </label>
                                                    </div>
                                                    <div class="col-sm-2"><label>Max aantal<input type="text"
                                                                                                  class="form-control mdMax"
                                                                                                  name="Max"
                                                                                                  required></label>
                                                    </div>
                                                    <div class="col-sm-1"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <input type="hidden" class="prID" name="prodID">
                                            <input type="hidden" class="prodactie" name="practie">
                                            <button type="submit" id="btnStatus" class="" name="statusPr" style="color:white;"><span class="statusTxt"></span></button>
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Close
                                            </button>
                                            <button type="submit" class="btn btn-primary"><span class="actie"></span>
                                            </button>
                                        </div>
                                    </div><!-- /.modal-content -->
                                </div><!-- /.modal-dialog -->
                            </div><!-- /.modal -->
                        </form>

                        <!-- ON ITEM CLICK MODAL FORM -->
                        <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>?WID=<?php echo $_GET['WID']; ?>">
                            <div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="#modalKlant"
                                 id="modalKlant">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span></button>
                                            <h4 class="modal-title" id="modalProduct"><span class="actie"></span></h4>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <div style="position:relative;"><img class="center-block Foto"
                                                                                         src=""
                                                                                         style="max-width: 450px;">
                                                    </div>
                                                </div>
                                                &nbsp;
                                                <hr>
                                                <div class="row">
                                                    <div class="col-sm-1"></div>
                                                    <div class="col-sm-5"><label>Product: <span class="mdNaam"
                                                                                                style="font-weight: lighter; margin-left: 5px;"></span></label>
                                                    </div>
                                                    <div class="col-sm-2"></div>
                                                    <div class="col-sm-3"><label>Prijs: <span class="mdPrijs"
                                                                                              style="font-weight: lighter; margin-left: 5px;"></span></label>
                                                    </div>
                                                    <div class="col-sm-1"></div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <div class="col-sm-1"></div>
                                                    <div class="col-sm-10"><span class="mdUitleg"></span></textarea>
                                                    </div>
                                                    <div class="col-sm-1"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <input type="hidden" class="prID" name="prodID">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Close
                                            </button>
                                        </div>
                                    </div><!-- /.modal-content -->
                                </div><!-- /.modal-dialog -->
                            </div><!-- /.modal -->
                        </form>

                        <!-- Modal -->
                        <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>?WID=<?php echo $_GET['WID']; ?>">
                            <div id="modalVerwijder" class="modal fade" role="dialog">
                                <div class="modal-dialog">

                                    <!-- Modal content-->
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            <h4 class="modal-title">Product verwijderen</h4>
                                        </div>
                                        <div class="modal-body">
                                            <p>Weet u zeker dat u het product '<span class="naam"></span>' wilt
                                                verwijderen</p>
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
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>
<!-- /.container -->
<!-- Footer -->
<footer class="navbar-fixed-bottom">
    <div class="container">
        <p>Copyright &copy; GO-AO Webshops<?php if (isset($_SESSION['logged_in'])) { ?> - <a href="contact.php" class="contact-link">Contact</a> <?php } ?></p>
    </div>
</footer>

<!-- jQuery -->
<script src="js/jquery.min.js"></script>

<!-- Bootstrap Core JavaScript -->
<script src="js/bootstrap.min.js"></script>

<script>


    $('#modalProduct').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var id = button.data('productid');// Extract info from data-* attributes
        var practie = button.data('practie');// Extract info from data-* attributes
        var naam = button.data('productnaam');// Extract info from data-* attributes
        var prijs = button.data('productprijs');// Extract info from data-* attributes
        var uitleg = button.data('productuitleg');
        var max = button.data('productmax');
        var status = button.data('productactief');
        var BTWaarde = button.data('productbtw');// Extract info from data-* attributes
        var foto = button.data('productfoto');
        var element = document.getElementById('selectBTW');
        // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
        // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
        var modal = $(this);
        modal.find('.modal-title').text('Product ' + practie);
        modal.find('.prID').val(id);
        modal.find('.mdNaam').val(naam);
        modal.find('.mdPrijs').val(prijs);
        modal.find('.mdUitleg').val(uitleg);
        modal.find('.mdMax').val(max);
        modal.find('.actie').text(practie);
        modal.find('.prodactie').val(practie);
        modal.find('.Foto').attr('src', foto);
        
        if (status === "Activeren") {
            document.getElementById("btnStatus").className = "btn pull-left status btn-success";
        } else {
            document.getElementById("btnStatus").className = "btn pull-left status btn-danger";
        }
        
        modal.find('.status').val(status);
        modal.find('.statusTxt').text(status);
        
        if (BTWaarde === 0) {
            element.value = 0;
        } else if (BTWaarde === 6) {
            element.value = 6;
            ;
        } else if (BTWaarde === 12) {
            element.value = 12;
        } else if (BTWaarde === 21) {
            element.value = 21;
        }
    });

    $('#modalKlant').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var id = button.data('productid');// Extract info from data-* attributes
        var practie = button.data('practie');// Extract info from data-* attributes
        var naam = button.data('productnaam');// Extract info from data-* attributes
        var prijs = button.data('productprijs');// Extract info from data-* attributes
        var uitleg = button.data('productuitleg');
        var foto = button.data('productfoto');
        // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
        // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
        var modal = $(this);
        modal.find('.modal-title').text(practie);
        modal.find('.mdNaam').text(naam);
        modal.find('.mdPrijs').text(" €" + prijs);
        modal.find('.mdUitleg').text(uitleg);
        modal.find('.prID').val(id);
        modal.find('.Foto').attr('src', foto);
    });

    $('#modalVerwijder').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var id = button.data('productid');// Extract info from data-* attributes
        var naam = button.data('productnaam');// Extract info from data-* attributes
        // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
        // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
        var modal = $(this);
        modal.find('.prID').val(id);
        modal.find('.naam').text(naam);
    });
</script>

</body>
<script src="js/bootstrap-notify.js"></script>
<?php
if (isset($_SESSION['AlertWinkel'])) {
    echo $_SESSION['AlertWinkel'];
    unset($_SESSION['AlertWinkel']);
}
?>
</html>
