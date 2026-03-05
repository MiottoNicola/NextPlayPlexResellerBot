<?php
session_start();
if(isset($_SESSION["admin_telegram_id"])){
    header('Location: admin.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="UTF-8">
        <title>Plex Admin Panel - Arzilla</title>
        <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    </head>
    <body class="w3-container w3-center w3-padding-64">
        <h1 class="w3-jumbo">Benvenuto nell'Area Admin</h1>
        <p class="w3-large">Clicca sul pulsante qui sotto per accedere con il tuo account Telegram.</p>

        <!-- Telegram Login Widget -->
        <script async src="https://telegram.org/js/telegram-widget.js?15"
                data-telegram-login="Plexpanelnextplay_bot"
                data-size="large"
                data-userpic="false"
                data-request-access="write"
                data-auth-url="telegram_auth.php">
        </script>

        <?php include "inc/footer.php"; ?>

    </body>
</html>