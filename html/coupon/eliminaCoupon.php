<?php
    include "../config.php";

    $id = (int) $_GET['id'];
    $stm = $db->prepare("SELECT * FROM $tableCoupon WHERE ID = ?");
    $stm->bind_param("i", $id);
    $stm->execute();
    $result = $stm->get_result()->fetch_assoc();
    $stm->close();

    $code = $result['code'];
    $type = $result['type'];
    $value = $result['value'];
?>

<html>
    <body>
        <?php include "../inc/header.php"; ?>

        <div class="w3-card-4 w3-light-grey w3-padding-large w3-margin-top w3-round-large" style="max-width: 600px; margin: auto;">
            <h1 class="w3-center">Elimina Coupon</h1>
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
            <form action="elimina.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <input type="hidden" name="type" value="<?php echo $type; ?>">

                <div class="w3-row w3-margin-bottom">
                    <div class="w3-col s4">
                        <label for="code" class="form-label">Code</label>
                    </div>
                    <div class="w3-col s8">
                        <input type="text" class="w3-input" id="code" name="code" value="<?php echo $code; ?>" disabled>
                    </div>
                </div>
                <div class="w3-row w3-margin-bottom">
                    <div class="w3-col s4">
                        <label for="type" class="form-label">Type<br /><span class="w3-tiny">0 -> credito<br /> 1 -> mensilità</span></label>
                    </div>
                    <div class="w3-col s8">
                        <input type="number" class="w3-input" id="type" name="type" value="<?php echo $type; ?>" min="0" max="1" disabled>
                    </div>
                </div>
                <div class="w3-row w3-margin-bottom">
                    <div class="w3-col s4">
                        <label for="value" class="form-label">Value<br /><span class="w3-tiny">Inserire la quantità: <br /> - credito: valore >=0 <br /> - mensilità: 1, 3, 6</span></label>
                    </div>
                    <div class="w3-col s8">
                        <input type="number" class="w3-input" id="value" name="value" value="<?php echo $value; ?>" min="0" disabled>
                    </div>
                </div>
                <button type="submit" class="w3-button w3-red w3-round">Elimina</button>
            </form>
        </div>
        <?php include "../inc/footer.php"; ?>
    </body>
</html>