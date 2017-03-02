<?php
/**
 * Created by PhpStorm.
 * User: daniq
 * Date: 19-1-2017
 * Time: 12:02
 */
$cvwl = false;
include 'includes/connectdb.php';
include 'includes/INSTA_API.php';
user::logout();
mysqli_close($mysqli);
header("Location: index.php");
exit;
?>