<?php
    include "../config.php";
?>

<html>
    <body>
        <?php include "../inc/header.php"; ?>

        <div class="w3-card-4 w3-light-grey w3-padding-large w3-margin-top w3-round-large" style="max-width: 600px; margin: auto;">
            <h1 class="w3-center">Aggiungi Coupon</h1>
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
            <form action="aggiungi.php" method="POST">
                <input type="hidden" name="type" value="0">
                <div class="w3-row w3-margin-bottom">
                    <div class="w3-col s4">
                        <label for="code" class="form-label">Code</label>
                    </div>
                    <div class="w3-col s8">
                        <input type="text" class="w3-input" id="code" name="code" required>
                    </div>
                </div>
                <div class="w3-row w3-margin-bottom">
                    <div class="w3-col s4">
                        <label for="type" class="form-label">Type<br /><span class="w3-tiny">0 -> credito<br /> 1 -> mensilità</span></label>
                    </div>
                    <div class="w3-col s8">
                        <input type="number" class="w3-input" id="type" name="type" value="0" min="0" max="1" disabled>
                    </div>
                </div>
                <div class="w3-row w3-margin-bottom">
                    <div class="w3-col s4">
                        <label for="value" class="form-label">Value<br /><span class="w3-tiny">Inserire la quantità: <br /> - credito: valore >=0 <br /> - mensilità: 1, 3, 6</span></label>
                    </div>
                    <div class="w3-col s8">
                        <input type="number" class="w3-input" id="value" name="value" min="0" required>
                    </div>
                </div>
                <button type="submit" class="w3-button w3-blue w3-round">Aggiungi</button>
            </form>
        </div>
        <?php include "../inc/footer.php"; ?>
    </body>
</html>