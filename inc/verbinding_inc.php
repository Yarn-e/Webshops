<?php
//verbinding met de MySql-database
$host = "mysql093.webhosting.be";
$user = "ID131354_webshop";
$password = "G4NZ9a9K";
$database = "ID131354_webshop";
$link = mysqli_connect($host, $user, $password, $database);
if (!$link) {
    trigger_error("Fout bij verbinden met database: " . mysqli_connect_error());
}
$link->set_charset("utf8");
