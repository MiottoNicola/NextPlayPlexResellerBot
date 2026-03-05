<?php
include_once 'inc/header.php';
include_once 'inc/modal-style.php';
include_once 'config/db.php';

// Gestione aggiunta prodotto
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'addProduct') {
        $name = $db->real_escape_string($_POST['name']);
        $description = $_POST['description'] == '' ? null : $db->real_escape_string($_POST['description']);
        $duration = $db->real_escape_string((int) $_POST['duration']);
        $price = number_format((float) str_replace([',', ' '], ['.', ''], $_POST['price']), 2, '.', '');

        $stmt = $db->prepare("INSERT INTO $tableProducts (name, description, duration, price) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $name, $description, $duration, $price);
        if ($stmt->execute()) {
            $toastMsg = 'Prodotto aggiunto con successo!';
            $toastColor = '#43a047';
        } else {
            $toastMsg = 'Errore nell\'aggiunta del prodotto!';
            $toastColor = '#e53935';
        }
        $stmt->close();
    } else if (isset($_POST['action']) && $_POST['action'] === 'editProduct' && isset($_POST['id'])) {
        $id = (int) $_POST['id'];
        $name = $db->real_escape_string($_POST['name']);
        $description = $_POST['description'] == '' ? null : $db->real_escape_string($_POST['description']);
        $duration = $db->real_escape_string((int) $_POST['duration']);
        $price = number_format((float) str_replace([',', ' '], ['.', ''], $_POST['price']), 2, '.', '');

        $stmt = $db->prepare("UPDATE $tableProducts SET name = ?, description = ?, duration = ?, price = ? WHERE ID = ?");
        $stmt->bind_param("ssisi", $name, $description, $duration, $price, $id);
        if ($stmt->execute()) {
            $toastMsg = 'Prodotto modificato con successo!';
            $toastColor = '#43a047';
        } else {
            $toastMsg = 'Errore nella modifica del prodotto!';
            $toastColor = '#e53935';
        }
        $stmt->close();
    }else if(isset($_POST['action']) && $_POST['action'] === 'deleteProduct' && isset($_POST['id'])) {
        $productId = (int) $_POST['id'];
        $stmt = $db->prepare("DELETE FROM $tableProducts WHERE ID = ?");
        $stmt->bind_param("i", $productId);
        if ($stmt->execute()) {
            $toastMsg = 'Prodotto eliminato con successo!';
            $toastColor = '#43a047';
        } else {
            $toastMsg = 'Errore nell\'eliminazione del prodotto!';
            $toastColor = '#e53935';
        }
        $stmt->close();
    }
}
?>
<div style="max-width:1100px; margin:40px auto 4px auto; position:relative;">
    <div style="text-align:center; margin-bottom:8px;">
        <h2 class="w3-text-teal" style="margin:0; display:inline-block;">Gestione Prodotti</h2>
    </div>
    <?php
    $resCount = $db->query("SELECT COUNT(*) as total FROM $tableProducts");
    $rowCount = $resCount ? $resCount->fetch_assoc() : ['total' => 0];
    ?>
    <div style="max-width:1100px;margin:0 auto 18px auto;display:flex;align-items:center;justify-content:space-between;">
        <span style="font-size:1.08em;">
            <span class="material-icons" style="vertical-align:middle;font-size:1.1em;">inventory_2</span>
            <i>Totale prodotti: <?php echo $rowCount['total']; ?></i>
        </span>
        <button class="w3-button w3-green w3-round" style="padding:6px 18px; font-size:1em;"
            onclick="document.getElementById('modal-add-product').style.display='block'">
            <i class="material-icons" style="vertical-align:middle; font-size:1.1em;">add</i>
            <span style="font-size:0.98em;">Aggiungi</span>
        </button>
    </div>
</div>
<table class="w3-table-all w3-hoverable w3-centered"
    style="max-width:1100px;margin:0 auto 48px auto;border-radius:12px;overflow:hidden;font-size:1.08em;">
    <thead>
        <tr class="w3-teal">
            <th>ID</th>
            <th>Nome Prodotto</th>
            <th>Descrizione</th>
            <th>Durata</th>
            <th>Prezzo</th>
            <th>Azioni</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $stmt = $db->prepare("SELECT * FROM $tableProducts ORDER BY ID ASC");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0):
            while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['ID']); ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars(stripslashes($row['description'])); ?></td>
                    <td><?php echo htmlspecialchars($row['duration']); ?> mesi</td>
                    <td><?php echo number_format((float) $row['price'], 2, ',', ''); ?> €</td>

                    <td>
                        <a href="#"
                            onclick="openEditProductModal('<?php echo $row['ID']; ?>','<?php echo htmlspecialchars($row['name'], ENT_QUOTES); ?>','<?php echo htmlspecialchars($row['description'], ENT_QUOTES); ?>','<?php echo $row['duration']; ?>','<?php echo $row['price']; ?>');return false;"
                            class="w3-button w3-blue w3-round">
                            <i class="material-icons" style="vertical-align:middle;font-size:1.1em;">edit</i> Modifica
                        </a>
                        <form method="post" action="#" style="display:inline;">
                            <input type="hidden" name="action" value="deleteProduct">
                            <input type="hidden" name="id" value="<?php echo $row['ID']; ?>">
                            <button type="submit" class="w3-button w3-red w3-round" style="margin-left:2px;">
                                <i class="material-icons" style="vertical-align:middle;font-size:1.1em;">delete</i> Elimina
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; else: ?>
            <tr>
                <td colspan="6">Nessun prodotto trovato</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
<!-- Modale aggiungi prodotto -->
<div id="modal-add-product" class="w3-modal">
    <div class="w3-modal-content w3-animate-top w3-card-4" style="max-width:420px">
        <header class="w3-container">
            <span onclick="document.getElementById('modal-add-product').style.display='none'"
                class="w3-button w3-display-topright">&times;</span>
            <h4 style="margin:0;">Aggiungi Nuovo Prodotto</h4>
        </header>
        <form method="post" action="#" class="w3-container">
            <input type="hidden" name="action" value="addProduct">
            <label class="w3-text-teal"><b>Nome prodotto</b></label>
            <select name="name" class="w3-input w3-border w3-margin-bottom">
                <option value="emby" selected>Emby</option>
                <option value="plex">Plex</option>
            </select>
            <label class="w3-text-teal"><b>Descrizione</b></label>
            <input class="w3-input w3-border w3-margin-bottom" type="text" name="description" rows="3" placeholder="Inserisci una descrizione del prodotto" required value="L'account di cui non sapevi di aver bisogno è arrivato!">
            <label class="w3-text-teal"><b>Durata (mesi)</b></label>
            <input class="w3-input w3-border w3-margin-bottom" type="number" name="duration" min="1" value="1" required>
            <label class="w3-text-teal"><b>Prezzo (€)</b></label>
            <input class="w3-input w3-border w3-margin-bottom" type="number" name="price" min="0" step="0.10" value="1" required>
            <button type="submit" class="w3-button w3-green w3-block">Aggiungi</button>
        </form>
    </div>
</div>
<!-- Modale modifica prodotto -->
<div id="editProductModal" class="w3-modal">
    <div class="w3-modal-content w3-animate-top w3-card-4" style="max-width:440px;">
        <header class="w3-container">
            <span onclick="document.getElementById('editProductModal').style.display='none'"
                class="w3-button w3-display-topright">&times;</span>
            <h3 style="margin:0; font-size:1.35em; letter-spacing:0.5px;">Modifica Prodotto</h3>
        </header>
    <form class="w3-container" method="post" action="#">
            <input type="hidden" name="action" value="editProduct">
            <input type="hidden" name="id" id="editProductId" value="">
            <label class="w3-text-teal"><b>Nome prodotto</b></label>
            <select name="name" class="w3-input w3-border w3-margin-bottom" required>
                <option value="emby">Emby</option>
                <option value="plex">Plex</option>
            </select>
            <label class="w3-text-teal"><b>Descrizione</b></label>
            <input class="w3-input w3-border w3-margin-bottom" type="text" name="description">
            <label class="w3-text-teal"><b>Durata (mesi)</b></label>
            <input class="w3-input w3-border w3-margin-bottom" type="number" name="duration" min="1" required>
            <label class="w3-text-teal"><b>Prezzo (€)</b></label>
            <input class="w3-input w3-border w3-margin-bottom" type="number" name="price" min="0" step="0.01" required>
            <div class="w3-bar w3-margin-top">
                <button type="submit" class="w3-bar-item w3-button w3-blue w3-round-large w3-large"
                    style="width:48%;margin-right:4%;">Salva</button>
                <button type="button" onclick="closeEditProductModal()"
                    class="w3-bar-item w3-button w3-grey w3-round-large w3-large" style="width:48%;">Annulla</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditProductModal(id, name, description, duration, price) {
        var modal = document.getElementById('editProductModal');
        var idInput = document.getElementById('editProductId');
        var nameSelect = modal.querySelector('select[name="name"]');
        var descInput = modal.querySelector('input[name="description"]');
        var durInput = modal.querySelector('input[name="duration"]');
        var priceInput = modal.querySelector('input[name="price"]');
        if(idInput) idInput.value = id;
        if(nameSelect) nameSelect.value = name;
        if(descInput) descInput.value = description;
        if(durInput) durInput.value = duration;
        if(priceInput) priceInput.value = price;
        modal.style.display = 'block';
    }
    function closeEditProductModal() {
        document.getElementById('editProductModal').style.display = 'none';
    }
</script>

<?php include_once 'inc/footer.php'; ?>