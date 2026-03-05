<?php
include "../config.php";

?>

<?php include "../inc/header.php"; ?>

<div class="w3-card-4 w3-light-grey w3-padding-large w3-margin-top w3-round-large"
    style="max-width: 600px; margin: auto;">
    <h1 class="w3-center">Aggiungi Reseller</h1>
    <?php
    if (isset($_GET['error'])) {
        echo '<div class="w3-panel w3-red w3-round w3-margin-top">
                    <p>Errore nell\'aggiunta del cliente!</p>
                </div>';
    }

    $utenti = [];
    $stmt = $db->prepare("SELECT * FROM $tableIscritti WHERE type = 0 ORDER BY username ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $utenti[] = $row;
        }
    }
    ?>
    <form action="aggiungi.php" method="POST">
        <div class="w3-row w3-margin-bottom">
            <div class="w3-col s4">
                <label for="user_id" class="form-label">Utente</label>
            </div>
            <div class="w3-col s8">
                <select class="w3-input" id="user_id" name="user_id" required>
                    <option value="">Seleziona utente...</option>
                    <?php foreach ($utenti as $utente): ?>
                        <option value="<?php echo htmlspecialchars( $utente['user_id']); ?>">
                            <?php echo htmlspecialchars($utente['username']) . " (ID: " . htmlspecialchars($utente['user_id']) . ")"; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <button type="submit" class="w3-button w3-green w3-round">Aggiungi</button>
    </form>
</div>


<?php include "../inc/footer.php"; ?>