<?php
//verbinding met de MySql-database
$host = "";
$user = "";
$password = "";
$database = "";
$link = mysqli_connect($host, $user, $password, $database);
if (!$link) {
    trigger_error("Fout bij verbinden met database: " . mysqli_connect_error());
}
$link->set_charset("utf8");
