<?
include "../config.php";

if(isset($_POST['code']) && isset($_POST['type']) && isset($_POST['value']) && isset($_POST['id'])){
    $type = (int) $_POST['type'];
    $id = (int) $_POST['id'];
    $code = $_POST['code'];
    $value = (int) $_POST['value'];

    $stm = $db->prepare("SELECT * FROM $tableCoupon");
    $stm->execute();
    $res = $stm->get_result();
    while($row = $res->fetch_assoc()){
        if($row['code'] == $code && $row['ID'] != $id){
            header("Location: modificaCoupon.php?id=$id&error=Coupon ".$code." già esistente!");
            exit;
        }
    }
    $stm->close();

    $stm = $db->prepare("UPDATE $tableCoupon SET type = ?, code = ?, value = ? WHERE ID = ?");
    $stm->bind_param("isii", $type, $code, $value, $id);
    $stm->execute();
    $stm->close();

    header("Location: coupon.php?success=Coupon ".$code." modificato con successo!");
}else{
    header("Location: modificaCoupon.php?id=$id&error=Campi non compilati nel form di modifica!".$_POST['code'].", ".$_POST['type'].", ".$_POST['value'].", ".$_POST['id']."");
}