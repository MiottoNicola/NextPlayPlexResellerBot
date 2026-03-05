<?php
    include "../config.php";

    $stm = $db->prepare("SELECT * FROM $tableIscritti WHERE type > 0");
    $stm->execute();
    $numIscritti = $stm->get_result()->num_rows;
    $stm->close();
?>

<html>
    <body>
        <?php include "../inc/header.php"; ?>

        <div class="w3-padding w3-margin-top">
            <h1 class="w3-center">Coupon</h1>
            <a href="creaCoupon.php?id='.$row['ID'].'" class="w3-button w3-green w3-round w3-right">Crea Coupon</a>
            <br /> <br />
            <?php
                if(isset($_GET['success'])){
                    echo '<div class="w3-panel w3-green w3-round w3-margin-top">
                            <p>'.$_GET['success'].'</p>
                        </div>';
                }
                if(isset($_GET['error'])){
                    echo '<div class="w3-panel w3-red w3-round w3-margin-top">
                            <p>'.$_GET['error'].'</p>
                        </div>';
                }
            ?>
            <table class="w3-table w3-striped w3-hoverable w3-white">
                <tr>
                    <th>ID</th>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Value</th>
                    <th>Utilizzo</th>
                    <th>Azioni</th>
                </tr>
                <?php
                    $stm = $db->prepare("SELECT * FROM $tableCoupon ORDER BY ID");
                    $stm->execute();
                    $res = $stm->get_result();
                    if ($res->num_rows > 0) {
                        while($row = $res->fetch_assoc()){
                            if($row['type'] == 0) $row['type'] = 'credito';
                            else $row['type'] = 'mensilità';
                            echo '<tr>
                                    <td>'.$row['ID'].'</td>
                                    <td>'.$row['code'].'</td>
                                    <td>'.$row['type'].'</td>
                                    <td>'.$row['value'].'</td>';

                                    $stm = $db->prepare("SELECT * FROM $tableCouponUsati WHERE coupon_id = ?");
                                    $stm->bind_param("i", $row['ID']);
                                    $stm->execute();
                                    $numUsati = $stm->get_result()->num_rows;
                                    $stm->close();


                                    echo '  <td>'.$numUsati.' su '.$numIscritti.'</td>
                                    <td>
                                        <a href="modificaCoupon.php?id='.$row['ID'].'" class="w3-button w3-blue w3-round">Modifica</a>
                                        <a href="utilizzatoCoupon.php?id='.$row['ID'].'" class="w3-button w3-blue w3-round">Utilizzatori</a>
                                        <a href="eliminaCoupon.php?id='.$row['ID'].'" class="w3-button w3-red w3-round">Elimina</a>
                                    </td>
                                </tr>';
                        }
                    } else {
                        echo '<tr>
                                <td colspan="6" class="w3-center">Nessun coupon trovato</td>
                            </tr>';
                    }
                ?>                
            </table>
        </div>
        <?php include "../inc/footer.php"; ?>
    </body>
</html>