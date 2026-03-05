<?
include "../config.php";

if(isset($_POST['id'])){
    $id = (int) $_POST['id'];

    $stm = $db->prepare("DELETE FROM $tableCouponUsati WHERE coupon_id = ?");
    $stm->bind_param("i", $id);
    $stm->execute();
    $stm->close();

    $stm = $db->prepare("DELETE FROM $tableCoupon WHERE ID = ?");
    $stm->bind_param("i", $id);
    $stm->execute();
    $stm->close();

    header("Location: coupon.php?success=Cliente ".$code." eliminato con successo!");
}else{
    header("Location: eliminaCoupon.php?error=Campi non compilati nel form di cancellazione!");
}