<?php
session_start();
$admins = [131638922, 178441112, 318975968];

if(isset($_SESSION["admin_telegram_id"])){
    header('Location: admin.php');
    exit;
}

if(isset($_GET['hash'])){
    $check_hash = isset($_GET['hash']) ? $_GET['hash'] : '';
    unset($_GET['hash']);

    ksort($_GET);

    $check_string = [];
    foreach ($_GET as $k => $v) {
        $check_string[] = "$k=$v";
    }
    $check_string = implode("\n", $check_string);

    $botToken = '7288803688:AAH7v28f0A6HgThoDci6YHzfm4_35Bqjtlw';
    $secretKey = hash('sha256', $botToken, true);
    $hash = hash_hmac('sha256', $check_string, $secretKey);

    if ($hash === $check_hash) {
        $user_id  = $_GET['id'];
        $username = isset($_GET['username']) ? $_GET['username'] : null;
        $fistname = isset($_GET['first_name']) ? $_GET['first_name'] : null;
        $lastname = isset($_GET['last_name']) ? $_GET['last_name'] : null;

        if (in_array( $user_id, $admins)) {
            $_SESSION['admin_telegram_id']  = $user_id;
            $_SESSION['admin_username']     = $username;
            $_SESSION['admin_first_name']   = $fistname;
            $_SESSION['admin_last_name']    = $lastname;

            header('Location: admin.php');
            exit;
        } else {
            header('Location: error.php?type=Autorizzazione negata!');
        }
    } else {
        header('Location: error.php?type=Codice di autenticazione non valido!');
    }
}
