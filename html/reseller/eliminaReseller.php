<?php
    include "../config.php";

    $id = (int) $_GET['id'];
    $stm = $db->prepare("SELECT * FROM $tableIscritti WHERE ID = ?");
    $stm->bind_param("i", $id);
    $stm->execute();
    $result = $stm->get_result()->fetch_assoc();
    $stm->close();

    $user_id = $result['user_id'];
    $username = $result['username'];
    $type = $result['type'];
    $coin = $result['coin'];
?>

<html>
    <body>
        <?php include "../inc/header.php"; ?>

        <div class="w3-card-4 w3-light-grey w3-padding-large w3-margin-top w3-round-large" style="max-width: 600px; margin: auto;">
            <h1 class="w3-center">Elimina Reseller</h1>
            <form action="elimina.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <div class="w3-row w3-margin-bottom">
                    <div class="w3-col s4">
                        <label for="user_id" class="form-label">UserID</label>
                    </div>
                    <div class="w3-col s8">
                        <input type="text" class="w3-input" id="user_id" name="user_id" value="<?php echo $user_id; ?>" disabled>
                    </div>
                </div>
                <div class="w3-row w3-margin-bottom">
                    <div class="w3-col s4">
                        <label for="username" class="form-label">Username</label>
                    </div>
                    <div class="w3-col s8">
                        <input type="text" class="w3-input" id="username" name="username" value="<?php echo $username; ?>" disabled>
                    </div>
                </div>
                <div class="w3-row w3-margin-bottom">
                    <div class="w3-col s4">
                        <label for="type" class="form-label">Tipologia <br /><span class="w3-tiny">(0 -> utente, 1 -> reseller)</span></label>
                    </div>
                    <select class="w3-select w3-col s8" id="type" name="type">
                        <option value="reseller" <?php if($type == 1) echo 'selected'; ?>>Reseller</option>
                        <option value="admin" <?php if($type == 0) echo 'selected'; ?>>Utente</option>
                    </select>
                </div>
                <div class="w3-row w3-margin-bottom">
                    <div class="w3-col s4">
                        <label for="coin" class="form-label">Coin</label>
                    </div>
                    <div class="w3-col s8">
                        <input type="number" class="w3-input" id="coin" name="coin" value="<?php echo $coin; ?>" min="0" disabled>
                    </div>
                </div>
                <button type="submit" class="w3-button w3-red w3-round">Elimina</button>
            </form>
        </div>
        <?php include "../inc/footer.php"; ?>
    </body>
</html>