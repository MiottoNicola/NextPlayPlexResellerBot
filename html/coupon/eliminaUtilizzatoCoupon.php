<?php
    include "../config.php";

    $id = (int) $_GET['id'];
    $stm = $db->prepare("SELECT * FROM $tableCouponUsati WHERE ID = ?");
    $stm->bind_param("i", $id);
    $stm->execute();
    $result = $stm->get_result()->fetch_assoc();
    $stm->close();

    $user_id = $result['user_id'];
    $date = $result['date'];
?>

<html>
    <body>
        <?php include "../inc/header.php"; ?>

        <div class="w3-card-4 w3-light-grey w3-padding-large w3-margin-top w3-round-large" style="max-width: 600px; margin: auto;">
            <h1 class="w3-center">Elimina Coupon Utilizzato</h1>
            <?php
                if(isset($_GET['success'])){
                    echo '<div class="w3-panel w3-red w3-round w3-margin-top">
                            <p>'.$_GET['success'].'</p>
                        </div>';
                }
                if(isset($_GET['error'])){
                    echo '<div class="w3-panel w3-red w3-round w3-margin-top">
                            <p>'.$_GET['error'].'</p>
                        </div>';
                }
            ?>
            <form action="elimina2.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                <div class="w3-row w3-margin-bottom">
                    <div class="w3-col s4">
                        <label for="code" class="form-label">UserID</label>
                    </div>
                    <div class="w3-col s8">
                        <input type="number" class="w3-input" id="user_id" name="user_id" value="<?php echo $user_id; ?>" disabled>
                    </div>
                </div>
                <div class="w3-row w3-margin-bottom">
                    <div class="w3-col s4">
                        <label for="type" class="form-label">Date</label>
                    </div>
                    <div class="w3-col s8">
                        <input type="text" class="w3-input" id="date" name="date" value="<?php echo $date; ?>" min="0" max="1" disabled>
                    </div>
                </div>
                <button type="submit" class="w3-button w3-red w3-round">Elimina</button>
            </form>
        </div>
        <?php include "../inc/footer.php"; ?>
    </body>
</html>