<?php
include "../config.php";

if(isset($_POST['email']) && isset($_POST['scadenza']) && isset($_POST['scadenzaPass']) && isset($_POST['reseller'])){
    $email = $_POST['email'];
    $scadenza = $_POST['scadenza'];
    $scadenzaPass = $_POST['scadenzaPass'];
    $reseller = (int) $_POST['reseller'];

    $scadenza = date("d/m/Y", strtotime($scadenza));
    $scadenzaPass = date("d/m/Y", strtotime($scadenzaPass));

    $stm = $db->prepare("INSERT INTO $tableClienti (email, scadenza, scadenzaPass, reseller) VALUES (?, ?, ?, ?)");
    $stm->bind_param("sssi", $email, $scadenza, $scadenzaPass, $reseller);
    $stm->execute();
    $stm->close();

    header("Location: clienti.php?success=Cliente ".$email." creato con successo!");
    die;
}else{
    header("Location: addCCliente.php?error=Campi non compilati nel form di creazione!");
    die;
}


?>