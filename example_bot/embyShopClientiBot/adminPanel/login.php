<?php
include_once 'config/secretKey.php';
include_once 'config/db.php';

if (isset($_GET['login']) && isset($_GET['user_id']) && isset($_GET['first_name']) && isset($_GET['last_name']) && isset($_GET['username'])) {
    if (hash_equals($_GET['login'], hash_hmac('sha256', $_GET['user_id'] . $_GET['first_name'] . $_GET['last_name'] . $_GET['username'], $hashSecretKey))) {
        $user_id = filter_var($_GET['user_id'], FILTER_VALIDATE_INT);
        if ($user_id) {
            $stmt = $db->prepare("SELECT isAdmin FROM $tableUsers WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $isAdmin = $stmt->get_result()->fetch_assoc()['isAdmin'];
            $stmt->close();

            if ($isAdmin) {
                session_start();
                $_SESSION['user_id'] = $_GET['user_id'];
                $_SESSION['first_name'] = $_GET['first_name'];
                $_SESSION['last_name'] = $_GET['last_name'];
                $_SESSION['username'] = $_GET['username'];
                $_SESSION['creationDate'] = date('Y-m-d H:i:s');
                header('Location: index.php');
                exit();
            } else {
                session_abort();
                header('Location: errorPage/403.html');
                exit();
            }
        } else {
            session_abort();
            header('Location: errorPage/403.html');
            exit();
        }
    } else {
        session_abort();
        header('Location: errorPage/403.html');
        exit();
    }
} else {
    session_abort();
    header('Location: errorPage/403.html');
    exit();
}
