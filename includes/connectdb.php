<?php
/*$host="localhost";
$username="asd";
$password="YALCRi3XflFBpto4";
$db_name="pco";*/

$host="localhost";
$username="root";
$password="Sierra_123";
$db_name="thewall";


$mysqli = mysqli_connect($host, $username, $password) or die(mysqli_connect_error());
mysqli_select_db($mysqli, $db_name) or die(mysqli_connect_error());
?>