<?php
include_once 'inc/header.php';
include_once 'inc/modal-style.php';
include_once 'config/db.php';
include_once '../embyFunction/embyFunction.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] == 'editAccount' && isset($_POST['id'])) {
        $id = (int) $_POST['id'];
        $user_id = (int) $_POST['user_id'];
        $email = $db->real_escape_string($_POST['mail']);
        $expiration = date('Y-m-d', strtotime($_POST['expiration']));
        $stmt = $db->prepare("UPDATE $tableClientsPlex SET expiration = ? WHERE ID = ?");
        $stmt->bind_param("si", $expiration, $id);
        if ($stmt->execute()) {
            $toastMsg = 'Utente modificato con successo!';
            $toastColor = '#43a047';
        } else {
            $toastMsg = 'Errore nella modifica dell\'utente!';
            $toastColor = '#e53935';
        }
        $stmt->close();
    } else if ($_POST['action'] == 'deleteClient' && isset($_POST['id'])) {
        $id = (int) $_POST['id'];

        $stmt = $db->prepare("DELETE FROM $tableClientsPlex WHERE ID = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $toastMsg = 'Utente eliminato con successo!';
            $toastColor = '#43a047';
        } else {
            $toastMsg = 'Errore nella eliminazione dell\'utente!';
            $toastColor = '#e53935';
        }
        $stmt->close();
    }
}
?>

<h2 class="w3-text-teal" style="margin:40px 0 4px 0; text-align:center;">Gestione Account Plex</h2>
<?php
$resCount = $db->query("SELECT COUNT(*) as total FROM $tableClientsPlex");
$rowCount = $resCount ? $resCount->fetch_assoc() : ['total' => 0];
?>
<div style="max-width:1100px;margin:0 auto 8px auto;display:flex;align-items:center;justify-content:flex-start;">
    <span style="margin-left: 8px; font-size:1.08em;">
        <span class="material-icons" style="vertical-align:middle;font-size:1.1em;">smart_toy</span>
        <i> Totale account: <?php echo $rowCount['total']; ?></i>
    </span>
</div>
<table class="w3-table-all w3-hoverable w3-centered"
    style="max-width:1100px;margin:0 auto 48px auto;border-radius:12px;overflow:hidden;font-size:1.08em;">
    <thead>
        <tr class="w3-teal">
            <th>UserID</th>
            <th>Email</th>
            <th>Data di scadenza</th>
            <th>Azioni</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $stmt = $db->prepare("SELECT * FROM $tableClientsPlex ORDER BY ID ASC");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0):
            while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['mail']); ?></td>
                    <td><?php echo invertiData($row['expiration']); ?></td>

                    <td>
                        <a href="#"
                            onclick="openAccountModal('<?php echo $row['ID']; ?>','<?php echo $row['user_id'] ?>','<?php echo $row['mail']; ?>','<?php echo $row['expiration']; ?>');return false;"
                            class="w3-button w3-blue w3-round">
                            <i class="material-icons" style="vertical-align:middle;font-size:1.1em;">edit</i> Modifica
                        </a>
                        <form method="post" action="#" style="display:inline;">
                            <input type="hidden" name="action" value="deleteClient">
                            <input type="hidden" name="id" value="<?php echo $row['ID']; ?>">
                            <button type="submit" class="w3-button w3-red w3-round" style="margin-left:2px;">
                                <i class="material-icons" style="vertical-align:middle;font-size:1.1em;">delete</i> Elimina
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; else: ?>
            <tr>
                <td colspan="6">Nessun account plex trovato.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<div id="editAccountModal" class="w3-modal">
    <div class="w3-modal-content w3-animate-top w3-card-4" style="max-width:440px;">
        <header class="w3-container">
            <span onclick="closeAccountModal()" class="w3-button w3-display-topright">&times;</span>
            <h3 style="margin:0; font-size:1.35em; letter-spacing:0.5px;">Modifica Account Plex</h3>
        </header>
        <form class="w3-container" method="post" action="#">
            <input type="hidden" name="action" value="editAccount">
            <input type="hidden" name="id" id="editId" value="">
            <div class="w3-section">
                <label class="w3-text-grey" style="font-weight:500;">UserID</label>
                <input class="w3-input w3-border w3-margin-bottom" type="text" name="user_id" id="editUserId" readonly
                    style="background:#f5f5f5;">
            </div>
            <div class="w3-section">
                <label class="w3-text-grey" style="font-weight:500;">Email</label>
                <input class="w3-input w3-border w3-margin-bottom" type="text" name="email" id="editEmail" readonly
                    style="background:#f5f5f5;">
            </div>
            <div class="w3-section">
                <label class="w3-text-grey" style="font-weight:500;">Data Scadenza</label>
                <input class="w3-input w3-border w3-margin-bottom" type="date" name="expiration" id="editExpiration"
                    style="background:#f5f5f5;">
            </div>
            <div class="w3-bar w3-margin-top">
                <button type="submit" class="w3-bar-item w3-button w3-blue w3-round-large w3-large"
                    style="width:48%;margin-right:4%;">Salva</button>
                <button type="button" onclick="closeAccountModal()"
                    class="w3-bar-item w3-button w3-grey w3-round-large w3-large" style="width:48%;">Annulla</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAccountModal(id, user_id, email, expiration) {
        document.getElementById('editId').value = id;
        document.getElementById('editUserId').value = user_id;
        document.getElementById('editEmail').value = email;
        document.getElementById('editExpiration').value = expiration;
        document.getElementById('editAccountModal').style.display = 'block';
    }
    function closeAccountModal() {
        document.getElementById('editAccountModal').style.display = 'none';
    }
</script>

<?php include_once 'inc/footer.php'; ?>