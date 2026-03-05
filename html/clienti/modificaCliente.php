<?php
include "../config.php";

$id = (int) $_GET['id'];
$stm = $db->prepare("SELECT * FROM $tableClienti WHERE ID = ?");
$stm->bind_param("i", $id);
$stm->execute();
$result = $stm->get_result()->fetch_assoc();
$stm->close();

$email = $result['email'];
$scadenza = $result['scadenza'];
$scadenzaPass = $result['scadenzaPass'];
$reseller = $result['reseller'];
?>

<?php include "../inc/header.php"; ?>

<div class="w3-card-4 w3-light-grey w3-padding-large w3-margin-top w3-round-large"
    style="max-width: 600px; margin: auto;">
    <h1 class="w3-center">Modifica Cliente</h1>
    <?
    if (isset($_GET['error'])) {
        echo '<div class="w3-panel w3-red w3-round w3-margin-top">
                            <p>Errore nella modifica del cliente!</p>
                        </div>';
    }
    ?>
    <form action="modifica.php" method="POST">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <div class="w3-row w3-margin-bottom">
            <div class="w3-col s4">
                <label for="email" class="form-label">Email</label>
            </div>
            <div class="w3-col s8">
                <input type="text" class="w3-input" id="email" name="email" value="<?php echo $email; ?>" required>
            </div>
        </div>
        <div class="w3-row w3-margin-bottom">
            <div class="w3-col s4">
                <label for="scadenza" class="form-label">Scadenza</label>
            </div>
            <div class="w3-col s8">
                <input type="text" class="w3-input" id="scadenza" name="scadenza" value="<?php echo $scadenza; ?>"
                    required>
            </div>
        </div>
        <div class="w3-row w3-margin-bottom">
            <div class="w3-col s4">
                <label for="scadenzaPass" class="form-label">Scadenza Pass</label>
            </div>
            <div class="w3-col s8">
                <input type="text" class="w3-input" id="scadenzaPass" name="scadenzaPass"
                    value="<?php echo $scadenzaPass; ?>">
            </div>
        </div>
        <div class="w3-row w3-margin-bottom">
            <div class="w3-col s4">
                <label for="reseller" class="form-label">Reseller</label>
            </div>
            <div class="w3-col s8">
                <select class="w3-select" id="reseller" name="reseller">
                    <option value="">Seleziona Reseller</option>
                    <?php
                    $stmt = $db->prepare("SELECT * FROM $tableIscritti WHERE type = 1 ORDER BY username ASC");
                    $stmt->execute();
                    $resellerResult = $stmt->get_result();
                    $stmt->close();
                    if ($resellerResult->num_rows > 0) {
                        while ($row = $resellerResult->fetch_assoc()) {
                            if ($row['user_id'] == $reseller) {
                                echo '<option value="' . $row['user_id'] . '" selected>' . $row['username'] . '</option>';
                            } else {
                                echo '<option value="' . $row['user_id'] . '">' . $row['username'] . '</option>';
                            }
                        }
                    }
                    ?>
                </select>
            </div>
        </div>
        <button type="submit" class="w3-button w3-blue w3-round">Modifica</button>
    </form>
</div>
<?php include "../inc/footer.php"; ?>