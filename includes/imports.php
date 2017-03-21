<?php
/**
 * Created by PhpStorm.
 * User: daniq
 * Date: 17-1-2017
 * Time: 22:28
 */
if(isset($_REQUEST['warning'])) {
    $warning= $_REQUEST['warning'];
}
if(isset($_REQUEST['info'])) {
    $info= $_REQUEST['info'];
}
if(isset($_REQUEST['danger'])) {
    $danger= $_REQUEST['danger'];
}
?>
<?php include_once("includes/analyticstracking.php") ?>
<!-- Material Design fonts -->
<link rel="stylesheet" type="text/css" href="https//fonts.googleapis.com/css?family=Roboto:300,400,500,700">
<link rel="stylesheet" type="text/css" href="assets/fonts/materialicons/Material-Icons.css">

<!-- Bootstrap -->
<link href="assets/css/bootstrap.min.css" rel="stylesheet">
<link href="includes/css/core.css" rel="stylesheet">
<link href="includes/css/animate.min.css" rel="stylesheet">
<link href="includes/css/customnav.css" rel="stylesheet">

<!-- Bootstrap Material Design -->
<link href="assets/material/css/bootstrap-material-design.min.css" rel="stylesheet">
<link href="assets/material/css/ripples.min.css" rel="stylesheet">

<!-- Javascript -->
<script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
<script src="assets/material/js/material.js"></script>
<!-- Favivon -->
<link rel="icon" type="image/png" href="resources/icon.png" sizes="16x16">