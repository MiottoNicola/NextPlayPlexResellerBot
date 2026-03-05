<?php
include "../config.php";

if(isset($_POST['user_id'])){
    $user_id = (int) $_POST['user_id'];

    $stm = $db->prepare("UPDATE $tableIscritti SET type = 1 WHERE user_id = ?");
    $stm->bind_param("i", $user_id);
    $stm->execute();
    $stm->close();

    header("Location: reseller.php?success=Reseller aggiunto con successo!");
}else{
    header("Location: addReseller.php?error=Campi non compilati nel form di aggiunta!");
}

?>