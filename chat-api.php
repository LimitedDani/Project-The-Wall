<?php
/**
 * Created by PhpStorm.
 * User: daniq
 * Date: 16-3-2017
 * Time: 16:19
 */
ob_start();
session_start();
include 'includes/connectdb.php';
include 'includes/CHAT_API.php';
include 'includes/INSTA_API.php';
header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: X-Requested-With, Content-Type, Secret');
header('Access-Control-Max-Age: 120');
if(isset($_GET['loadchats'])) {
    chats::loadChats($mysqli, $_SESSION['UUID']);
}
if(isset($_GET['chat'])) {
    $chatid = trim($_GET['chat']);
    $chatid = strip_tags($chatid);
    $chatid = mysqli_real_escape_string($mysqli, $chatid);
    chats::loadChatReload($mysqli, $chatid);
}
if(isset($_GET['sendchat'])) {
    $chatid = trim($_GET['sendchat']);
    $chatid = strip_tags($chatid);
    $chatid = mysqli_real_escape_string($mysqli, $chatid);

    $message = trim($_GET['message']);
    $message = strip_tags($message);
    $message = mysqli_real_escape_string($mysqli, $message);

    $user = $_SESSION['UUID'];
    chats::sendMessage($mysqli, $chatid, $message, $user);
}
if(isset($_GET['chatcount'])) {
    echo chats::countNotReadedMessages($mysqli, $_SESSION['UUID']);
}
exit;