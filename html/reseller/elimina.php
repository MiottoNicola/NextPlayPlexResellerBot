<?
include "../config.php";

if(isset($_POST['id'])){
    $id = (int) $_POST['id'];

    $stm = $db->prepare("UPDATE $tableIscritti SET type = 0 WHERE ID = ?");
    $stm->bind_param("i", $id);
    $stm->execute();
    $stm->close();

    header("Location: reseller.php?success=Cliente ".$email." eliminato con successo!");
}else{
    header("Location: eliminaCoupon.php?error=Campi non compilati nel form di cancellazione!");
}