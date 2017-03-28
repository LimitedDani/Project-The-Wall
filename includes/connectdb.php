<?php
/*$host="localhost";
$username="asd";
$password="YALCRi3XflFBpto4";
$db_name="pco";*/

$host="localhost";
$username="daniquedejong";
$password="sJpNDZbJWNWJxws8";
$db_name="daniquedejong";


$mysqli = mysqli_connect($host, $username, $password) or die(mysqli_connect_error());
mysqli_select_db($mysqli, $db_name) or die(mysqli_connect_error());
?>