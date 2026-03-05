<?
include "../config.php";

if(isset($_POST['id'])){
    $id = (int) $_POST['id'];

    $stm = $db->prepare("DELETE FROM $tableCouponUsati WHERE ID = ?");
    $stm->bind_param("i", $id);
    $stm->execute();
    $stm->close();

    header("Location: utilizzatoCoupon.php?success=Coupon utilizzato da ".$_POST['user_id']." eliminato con successo!");
}else{
    header("Location: elimina2.php?error=Campi non compilati nel form di cancellazione!");
}