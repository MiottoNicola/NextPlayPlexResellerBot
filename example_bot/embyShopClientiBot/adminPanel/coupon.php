<?php
include_once 'inc/header.php';
include_once 'inc/modal-style.php';
include_once 'config/db.php';

// Gestione aggiunta coupon
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'addCoupon') {
        $name = $db->real_escape_string($_POST['name']);
        $amount = (int) $_POST['amount'];
        $stmt = $db->prepare("INSERT INTO $tableCoupons (name, amount) VALUES (?, ?)");
        $stmt->bind_param("si", $name, $amount);
        if ($stmt->execute()) {
            $toastMsg = 'Coupon aggiunto con successo!';
            $toastColor = '#43a047';
        } else {
            $toastMsg = 'Errore nell\'aggiunta del coupon!';
            $toastColor = '#e53935';
        }
        $stmt->close();
    } else if (isset($_POST['action']) && $_POST['action'] === 'editCoupon' && isset($_POST['id'])) {
        $id = (int) $_POST['id'];
        $name = $db->real_escape_string($_POST['name']);
        $amount = (int) $_POST['amount'];
        $stmt = $db->prepare("UPDATE $tableCoupons SET name = ?, amount = ? WHERE ID = ?");
        $stmt->bind_param("sii", $name, $amount, $id);
        if ($stmt->execute()) {
            $toastMsg = 'Coupon modificato con successo!';
            $toastColor = '#43a047';
        } else {
            $toastMsg = 'Errore nella modifica del coupon!';
            $toastColor = '#e53935';
        }
        $stmt->close();
    } else if (isset($_POST['action']) && $_POST['action'] === 'deleteCoupon' && isset($_POST['id'])) {
        $couponId = (int) $_POST['id'];
        $stmt = $db->prepare("DELETE FROM $tableCoupons WHERE ID = ?");
        $stmt->bind_param("i", $couponId);
        if ($stmt->execute()) {
            $stmtDeleteusedCoupon = $db->prepare("DELETE FROM $tableUsedCoupons WHERE coupon_id = ?");
            $stmtDeleteusedCoupon->bind_param("i", $couponId);
            $stmtDeleteusedCoupon->execute();
            $stmtDeleteusedCoupon->close();

            $toastMsg = 'Coupon eliminato con successo!';
            $toastColor = '#43a047';
        } else {
            $toastMsg = 'Errore nell\'eliminazione del coupon!';
            $toastColor = '#e53935';
        }
        $stmt->close();
    }
}
?>
<div style="max-width:1100px; margin:40px auto 4px auto; position:relative;">
    <div style="text-align:center; margin-bottom:8px;">
        <h2 class="w3-text-teal" style="margin:0; display:inline-block;">Gestione Coupon</h2>
    </div>
    <?php
    $resCount = $db->query("SELECT COUNT(*) as total FROM $tableCoupons");
    $rowCount = $resCount ? $resCount->fetch_assoc() : ['total' => 0];
    ?>
    <div
        style="max-width:1100px;margin:0 auto 18px auto;display:flex;align-items:center;justify-content:space-between;">
        <span style="font-size:1.08em;">
            <span class="material-icons" style="vertical-align:middle;font-size:1.1em;">card_giftcard</span>
            <i>Totale Coupon: <?php echo $rowCount['total']; ?></i>
        </span>
        <button class="w3-button w3-green w3-round" style="padding:6px 18px; font-size:1em;"
            onclick="document.getElementById('modal-add-coupon').style.display='block'">
            <i class="material-icons" style="vertical-align:middle; font-size:1.1em;">add</i> <span
                style="font-size:0.98em;">Aggiungi</span>
        </button>
    </div>
</div>
<table class="w3-table-all w3-hoverable w3-centered"
    style="max-width:1100px;margin:0 auto 48px auto;border-radius:12px;overflow:hidden;font-size:1.08em;">
    <thead>
        <tr class="w3-teal">
            <th>ID</th>
            <th>Nome Coupon</th>
            <th>Sconto Applicato</th>
            <th>Azioni</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $stmt = $db->prepare("SELECT * FROM $tableCoupons ORDER BY ID ASC");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0):
            while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['ID']); ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['amount']); ?></td>
                    <td>
                        <a href="#"
                            onclick="openEditCouponModal('<?php echo $row['ID']; ?>','<?php echo htmlspecialchars($row['name'], ENT_QUOTES); ?>','<?php echo $row['amount']; ?>');return false;"
                            class="w3-button w3-blue w3-round">
                            <i class="material-icons" style="vertical-align:middle;font-size:1.1em;">edit</i> Modifica
                        </a>
                        <form method="post" action="#" style="display:inline;">
                            <input type="hidden" name="action" value="deleteCoupon">
                            <input type="hidden" name="id" value="<?php echo $row['ID']; ?>">
                            <button type="submit" class="w3-button w3-red w3-round" style="margin-left:2px;">
                                <i class="material-icons" style="vertical-align:middle;font-size:1.1em;">delete</i> Elimina
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; else: ?>
            <tr>
                <td colspan="6">Nessun Coupon trovato</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
<!-- Modale aggiungi coupon -->
<div id="modal-add-coupon" class="w3-modal">
    <div class="w3-modal-content w3-animate-top w3-card-4" style="max-width:420px">
        <header class="w3-container">
            <span onclick="document.getElementById('modal-add-coupon').style.display='none'"
                class="w3-button w3-display-topright">&times;</span>
            <h4 style="margin:0;">Aggiungi Nuovo Coupon</h4>
        </header>
        <form method="post" action="#" class="w3-container">
            <input type="hidden" name="action" value="addCoupon">
            <label class="w3-text-teal"><b>Nome coupon</b></label>
            <input class="w3-input w3-border w3-margin-bottom" type="text" name="name" required>
            <label class="w3-text-teal"><b>Sconto applicato</b></label>
            <input class="w3-input w3-border w3-margin-bottom" type="number" name="amount" min="0" step="1" required>
            <button type="submit" class="w3-button w3-green w3-block">Aggiungi</button>
        </form>
    </div>
</div>
<!-- Modale modifica coupon -->
<div id="editCouponModal" class="w3-modal">
    <div class="w3-modal-content w3-animate-top w3-card-4" style="max-width:440px;">
        <header class="w3-container">
            <span onclick="document.getElementById('editCouponModal').style.display='none'"
                class="w3-button w3-display-topright">&times;</span>
            <h3 style="margin:0; font-size:1.35em; letter-spacing:0.5px;">Modifica Coupon</h3>
        </header>
        <form class="w3-container" method="post" action="#">
            <input type="hidden" name="action" value="editCoupon">
            <input type="hidden" name="id" id="editCouponId" value="">
            <label class="w3-text-teal"><b>Nome Coupon</b></label>
            <input class="w3-input w3-border w3-margin-bottom" type="text" name="name" required>
            <label class="w3-text-teal"><b>Sconto applicato</b></label>
            <input class="w3-input w3-border w3-margin-bottom" type="number" name="amount" min="0" step="1" required>
            <div class="w3-bar w3-margin-top">
                <button type="submit" class="w3-bar-item w3-button w3-blue w3-round-large w3-large"
                    style="width:48%;margin-right:4%;">Salva</button>
                <button type="button" onclick="closeEditCouponModal()"
                    class="w3-bar-item w3-button w3-grey w3-round-large w3-large" style="width:48%;">Annulla</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditCouponModal(id, name, amount) {
        document.getElementById('editCouponId').value = id;
        document.querySelector('#editCouponModal input[name=\'name\']').value = name;
        document.querySelector('#editCouponModal input[name=\'amount\']').value = amount;
        document.getElementById('editCouponModal').style.display = 'block';
    }
    function closeEditCouponModal() {
        document.getElementById('editCouponModal').style.display = 'none';
    }
</script>

<?php include_once 'inc/footer.php'; ?>