<?
include "../config.php";

if(isset($_POST['id'])){
    $id = (int) $_POST['id'];

    $stm = $db->prepare("DELETE FROM $tableClienti WHERE ID = ?");
    $stm->bind_param("i", $id);
    $stm->execute();
    $stm->close();

    header("Location: clienti.php?success=Cliente ".$email." eliminato con successo!");
}else{
    header("Location: error.php?error=Campi non compilati nel form di cancellazione!");
}