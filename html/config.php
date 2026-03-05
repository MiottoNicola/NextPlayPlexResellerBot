<?php
session_start();

$admins = [131638922, 178441112, 318975968];

if(!isset($_SESSION["admin_telegram_id"])){
    header('Location: /plexpanelnextplay/html/index.php');
    exit;
}

if(!in_array($_SESSION['admin_telegram_id'], $admins)){
    header('Location: /plexpanelnextplay/html/error.html?type=unauthorized2');
    exit;
}

$db = mysqli_connect("localhost", "enigmaelaboration", "", "my_enigmaelaboration");
$tableIscritti      = "plexpanelnextplay_iscritti";
$tableClienti       = "plexpanelnextplay_clienti";
$tableRichieste     = 'plexpanelnextplay_richieste';
$tableCoupon        = 'plexpanelnextplay_coupon';
$tableCouponUsati   = 'plexpanelnextplay_couponUtilizzati';