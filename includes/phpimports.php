<?php
/**
 * Created by PhpStorm.
 * User: daniq
 * Date: 26-1-2017
 * Time: 21:41
 */
ob_start();
session_start();
include 'includes/connectdb.php';
include 'includes/INSTA_API.php';
include 'includes/CHAT_API.php';

if(!$cvwl) {
    if (!isset($_SESSION['user'])) {
        header("Location: index.php?redirect=".urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
    if (!user::hasAccess($mysqli, $_SESSION['UUID'])) {
        header("Location: index.php");
        exit;
    }
    if (!user::isActivated($mysqli, $_SESSION['UUID'])) {
        header("Location: index.php");
        exit;
    }
}
system::addPageVisit($mysqli);
if(system::isMaintenanceModeOn($mysqli)) {
    user::logout();
    header("Location: index.php?warning=Onderhoudsmode is ingeschakeld. Meer info? Volg ons op <a href=\"https://www.facebook.com/ParkCraft-370915049752819/\" class=\"soc-btn fb\">Facebook</a>, <a href=\"https://twitter.com/ParkenCraft\" class=\"soc-btn tw\">Twitter</a> en <a href=\"https://www.youtube.com/ParkCraft\" class=\"soc-btn gp\">YouTube</a>");
    exit;
}
user::setLastExcecution($mysqli);
?>