<?php
session_start();

// Distrugge tutti i dati di sessione
session_unset();
session_destroy();

// Ora reindirizza alla pagina di login
header('Location: index.php');
exit;
