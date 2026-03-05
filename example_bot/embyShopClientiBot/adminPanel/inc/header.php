<?php
include_once 'toast.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: errorPage/403.html');
    exit();
}
?>
<html>

<head>
    <meta charset="UTF-8">
    <title>NextPlay Admin Panel</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <meta charset="utf-8">
</head>

<body>
    <style>
        .custom-navbar {
            background: #009688;
            color: #fff;
            box-shadow: 0 2px 8px #00968833;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            height: 60px;
            position: relative;
        }

        .custom-navbar .logo {
            font-size: 1.3em;
            font-weight: bold;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .custom-navbar .nav-links {
            display: flex;
            gap: 28px;
            flex: 1;
            justify-content: center;
            align-items: center;
        }

        .custom-navbar .nav-link {
            color: #fff;
            text-decoration: none;
            font-size: 1.08em;
            font-weight: 500;
            padding: 8px 0;
            border-bottom: 2px solid transparent;
            transition: border 0.2s, color 0.2s;
        }

        .custom-navbar .nav-link:hover,
        .custom-navbar .nav-link.active {
            border-bottom: 2px solid #fff;
            color: #e0f2f1;
        }

        .custom-navbar .menu-toggle {
            display: none;
            align-items: center;
            cursor: pointer;
            margin-left: 16px;
        }

        .custom-navbar .logout-btn {
            background: #e53935;
            color: #fff;
            border: none;
            border-radius: 24px;
            padding: 7px 22px;
            font-size: 1em;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 8px #e5393533;
            margin-left: 18px;
        }

        .custom-navbar .logout-btn:hover {
            background: #ff5252;
        }
    </style>
    <div class="custom-navbar">
    <a href="index.php" class="logo" style="text-decoration:none;">NextPlay Admin Panel</a>
        <div class="nav-links">
            <a href="user.php" class="nav-link">Utenti Bot</a>
            <a href="client_emby.php" class="nav-link">Account Emby</a>
            <a href="client_plex.php" class="nav-link">Account Plex</a>
            <a href="product.php" class="nav-link">Prodotti</a>
            <a href="coupon.php" class="nav-link">Coupon</a>
            <a href="order.php" class="nav-link">Ordini</a>
        </div>
    <a href="logout.php" class="logout-btn" style="text-decoration:none;"><span class="material-icons" style="vertical-align:middle; margin-right:6px;">logout</span>Logout</a>
    </div>