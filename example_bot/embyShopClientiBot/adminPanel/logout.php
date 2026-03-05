<?php
include_once 'inc/header.php';
session_start();
session_destroy();
?>
<div style="max-width:500px; margin:80px auto 0 auto; text-align:center;">
    <div class="w3-card w3-padding-large w3-white" style="border-radius:18px;">
        <span class="material-icons" style="font-size:3.2em; color:#43a047;">logout</span>
        <h2 class="w3-text-teal" style="margin-top:12px;">Logout effettuato</h2>
        <p style="color:#555; font-size:1.15em;">Hai effettuato il logout con successo.<br>Puoi chiudere la finestra o
            <a href='#' class='w3-text-teal' style='text-decoration:underline;'>tornare al bot</a>.</p>
    </div>
</div>
<?php include_once 'inc/footer.php'; ?>