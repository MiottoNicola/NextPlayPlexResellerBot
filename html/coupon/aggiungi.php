<?
include "../config.php";

if(isset($_POST['code']) && isset($_POST['type']) && isset($_POST['value'])){
    $type = (int) $_POST['type'];
    $code = $_POST['code'];
    $value = (int) $_POST['value'];

    if($type == 1 && ($value != 1 || $value != 3 || $value != 6)){
        header("Location: creaCoupon.php?error=Il valore del coupon mensile deve essere 1, 3 o 6!");
        exit;
    }

    $stm = $db->prepare("SELECT * FROM $tableCoupon ");
    $stm->execute();
    $res = $stm->get_result();
    while($row = $res->fetch_assoc()){
        if($row['code'] == $code && $row['ID'] != $id){
            header("Location: creaCoupon.php?&error=Coupon ".$code." già esistente!");
            exit;
        }
    }
    $stm->close();

    $stm = $db->prepare("INSERT INTO $tableCoupon (type, code, value) VALUES (?, ?, ?)");
    $stm->bind_param("isi", $type, $code, $value);
    $stm->execute();
    $stm->close();

    header("Location: coupon.php?success=Coupon ".$code." creato con successo!");
}else{
    header("Location: creaCoupon.php?id=$id&error=Campi non compilati nel form di modifica!".$_POST['code'].", ".$_POST['type'].", ".$_POST['value'].", ".$_POST['id']."");
}