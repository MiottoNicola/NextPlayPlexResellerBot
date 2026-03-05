<?
    include "config.php";
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Plex Admin Panel - Arzilla</title>
        <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
        <style>
            .w3-navbar a {
                text-decoration: none;
                color: inherit;
            }
            .w3-navbar a:hover {
                text-decoration: none;
                color: inherit;
            }
        </style>
    </head>
    <body>
        <div class="w3-navbar w3-light-grey w3-padding">
        <a href="admin.php"><h1 class="w3-bar-item w3-title w3-center"><b>Plex Admin Panel</b></h1></a>
            <a href="logout.php" class="w3-bar-item w3-button w3-right">Logout</a>
        </div>
        <br/>
        <div class="w3-center">
            <h2>Benvenuto, il tuo ID è<?php echo $_SESSION['admin_telegram_id']; ?>!</h2>
        </div>
        <div class="w3-center w3-padding-64">
            <a href="clienti/clienti.php" class="w3-button w3-red w3-round w3-xlarge">Clienti</a>
            <a href="reseller/reseller.php" class="w3-button w3-blue w3-round w3-xlarge">Reseller</a>
            <a href="coupon/coupon.php" class="w3-button w3-yellow w3-round w3-xlarge">Coupon</a>
        </div>

        <?php include "inc/footer.php"; ?>
    </body>
</html>