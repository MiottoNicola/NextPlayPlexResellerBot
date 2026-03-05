<?

include "../config.php";

if(isset($_POST['type']) && isset($_POST['coin']) && isset($_POST['id'])){
    $type = (int) $_POST['type'];
    $id = (int) $_POST['id'];
    $coin = (int) $_POST['coin'];

    $stm = $db->prepare("UPDATE $tableIscritti SET type = ?, coin = ? WHERE ID = ?");
    $stm->bind_param("iii", $type, $coin, $id);
    $stm->execute();
    $stm->close();

    header("Location: reseller.php?success=Cliente ".$email." modificato con successo!");
}else{
    header("Location: error.php?error=Campi non compilati nel form di modifica!");
}