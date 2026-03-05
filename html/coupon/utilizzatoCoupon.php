<?php
    include "../config.php";
?>

<html>
    <body>
        <?php include "../inc/header.php"; ?>

        <div class="w3-padding w3-margin-top">
            <h1 class="w3-center">Coupon Utilizzati</h1>
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
                    <th>Code</th>
                    <th>UserID</th>
                    <th>Username</th>
                    <th>date</th>
                    <th>Azioni</th>
                </tr>
                <?php
                    $stm = $db->prepare("SELECT * FROM $tableCoupon WHERE ID = ?");
                    $stm->bind_param("s", $_GET['id']);
                    $stm->execute();
                    $res = $stm->get_result();
                    $code = $res->fetch_assoc()['code'];
                    $stm->close();

                    $stm = $db->prepare("SELECT * FROM $tableCouponUsati WHERE coupon_id = ? ORDER BY ID");
                    $stm->bind_param("s", $_GET['id']);
                    $stm->execute();
                    $res = $stm->get_result();
                    if ($res->num_rows > 0) {
                        while($row = $res->fetch_assoc()){
                            if($row['type'] == 0) $row['type'] = 'credito';
                            else $row['type'] = 'mensilità';
                            echo '<tr>
                                    <td>'.$code.'</td>
                                    <td>'.$row['user_id'].'</td>';
                                    
                                    $stm = $db->prepare("SELECT * FROM $tableIscritti WHERE user_id = ?");
                                    $stm->bind_param("i", $row['user_id']);
                                    $stm->execute();
                                    $res2 = $stm->get_result();
                                    $username = $res2->fetch_assoc()['username'];
                                    $stm->close();

                            echo '  <td>'.$username.'</td>
                                    <td>'.$row['date'].'</td>
                                    <td>
                                        <a href="eliminaUtilizzatoCoupon.php?id='.$row['ID'].'" class="w3-button w3-red w3-round">Elimina</a>
                                    </td>
                                </tr>';
                        }
                    } else {
                        echo '<tr>
                                <td colspan="6" class="w3-center">Nessun utente trovato</td>
                            </tr>';
                    }
                ?>                
            </table>
        </div>
        <?php include "../inc/footer.php"; ?>
    </body>
</html>