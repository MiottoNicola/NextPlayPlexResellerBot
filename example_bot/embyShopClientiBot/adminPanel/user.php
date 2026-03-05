<?php
include_once 'inc/header.php';
include_once 'inc/modal-style.php';
include_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] == 'editUser' && isset($_POST['id'])) {
    $id = (int) $_POST['id'];
    $username = $db->real_escape_string($_POST['username']);
    $isAdmin = (int) $_POST['isAdmin'];
    $stmt = $db->prepare("UPDATE $tableUsers SET username = ?, isAdmin = ? WHERE ID = ?");
    $stmt->bind_param("sii", $username, $isAdmin, $id);
    if ($stmt->execute()) {
        $toastMsg = 'Utente modificato con successo!';
        $toastColor = '#43a047';
    } else {
        $toastMsg = 'Errore nella modifica dell\'utente!';
        $toastColor = '#e53935';
    }
    $stmt->close();
}
?>

<h2 class="w3-text-teal" style="margin:40px 0 4px 0; text-align:center;">Gestione Utenti</h2>
<?php
$resCount = $db->query("SELECT COUNT(*) as total FROM $tableUsers");
$rowCount = $resCount ? $resCount->fetch_assoc() : ['total' => 0];
?>
<div style="max-width:1100px;margin:0 auto 8px auto;display:flex;align-items:center;justify-content:flex-start;">
    <span style="margin-left: 8px; font-size:1.08em;"><span class="material-icons" style="vertical-align:middle;font-size:1.1em;">group</span><i> Totale utenti: <?php echo $rowCount['total']; ?></i></span>
</div>
<table class="w3-table-all w3-hoverable w3-centered"
    style="max-width:1100px;margin:0 auto 48px auto;border-radius:12px;overflow:hidden;font-size:1.08em;">
    <thead>
        <tr class="w3-teal">
            <th>UserID</th>
            <th>Username</th>
            <th>Ruolo</th>
            <th>Data Registrazione</th>
            <th>Azioni</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $stmt = $db->prepare("SELECT * FROM $tableUsers ORDER BY id ASC");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0):
            while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td>
                        <?php
                        if ($row['isAdmin'])
                            echo '<i>Admin</i>';
                        else
                            echo 'Utente';
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['registrationDate']); ?></td>

                    <td>
                        <a href="#"
                            onclick="openEditModal('<?php echo $row['ID']; ?>','<?php echo htmlspecialchars($row['username'], ENT_QUOTES); ?>','<?php echo $row['isAdmin']; ?>','<?php echo $row['user_id']; ?>');return false;"
                            class="w3-button w3-blue w3-round">
                            <i class="material-icons" style="vertical-align:middle;font-size:1.1em;">edit</i> Modifica
                        </a>
                        <a href="order.php?user_id=<?php echo $row['user_id']; ?>" class="w3-button w3-teal w3-round">
                            <i class="material-icons" style="vertical-align:middle;font-size:1.1em;">list</i> Ordini
                        </a>
                    </td>
                </tr>
            <?php endwhile; else: ?>
            <tr>
                <td colspan="6">Nessun utente trovato.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<div id="editUserModal" class="w3-modal">
    <div class="w3-modal-content w3-animate-top w3-card-4" style="max-width:440px;">
        <header class="w3-container">
            <span onclick="closeEditModal()" class="w3-button w3-display-topright">&times;</span>
            <h3 style="margin:0; font-size:1.35em; letter-spacing:0.5px;">Modifica Utente</h3>
        </header>
        <form class="w3-container" method="post" action="#">
            <input type="hidden" name="action" value="editUser">
            <input type="hidden" name="id" id="editId" value="">
            <div class="w3-section">
                <label class="w3-text-grey" style="font-weight:500;">UserID</label>
                <input class="w3-input w3-border w3-margin-bottom" type="text" name="user_id" id="editUserId" readonly
                    style="background:#f5f5f5;">
            </div>
            <div class="w3-section">
                <label class="w3-text-grey" style="font-weight:500;">Username</label>
                <input class="w3-input w3-border w3-margin-bottom" type="text" name="username" id="editUsername"
                    readonly style="background:#f5f5f5;">
            </div>
            <div class="w3-section" style="margin-bottom:18px;">
                <label class="w3-text-grey" style="font-weight:500;">Ruolo</label><br>
                <select class="w3-select w3-border" name="isAdmin" id="editIsAdminSelect" style="margin-top:6px;">
                    <option value="0">Utente</option>
                    <option value="1">Admin</option>
                </select>
            </div>
            <div class="w3-bar w3-margin-top">
                <button type="submit" class="w3-bar-item w3-button w3-blue w3-round-large w3-large"
                    style="width:48%;margin-right:4%;">Salva</button>
                <button type="button" onclick="closeEditModal()"
                    class="w3-bar-item w3-button w3-grey w3-round-large w3-large" style="width:48%;">Annulla</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(id, username, isAdmin, user_id) {
        document.getElementById('editId').value = id;
        document.getElementById('editUserId').value = user_id;
        document.getElementById('editUsername').value = username;
        document.getElementById('editIsAdminSelect').value = isAdmin == 1 ? '1' : '0';
        document.getElementById('editUserModal').style.display = 'block';
    }
    function closeEditModal() {
        document.getElementById('editUserModal').style.display = 'none';
    }
</script>

<?php include_once 'inc/footer.php'; ?>