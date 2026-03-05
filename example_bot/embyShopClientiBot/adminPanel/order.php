<?php
include_once 'inc/header.php';
include_once 'config/db.php';
include_once 'config/secretKey.php';
include_once '../embyFunction/embyFunction.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['action'])) {
    $orderId = (int) $_POST['order_id'];
    $action = $db->real_escape_string($_POST['action']);

    if ($action == 'accept') {
        $stmt = $db->prepare("SELECT * FROM $tableOrders WHERE ID = ? AND status = 1");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        if ($result->num_rows > 0) {
            $order = $result->fetch_assoc();
            $product_user_id = $order['user_id'];
            $mail = $order['mail'];
            $product_duration = $order['product_duration'];
            $type = $order['type'];

            if(trim($order['product_name']) === 'emby') $host = "\n🌐 <b>Host:</b> <a href=\"https://em3np.apollocloud.me/\">em3np.apollocloud.me</a>\n🚪 <b>Porta:</b> <i>Lasciare vuoto</i>";
            else if(trim($order['product_name']) === 'plex') $host = '';

            if ($type == 1) { // rinnovo
                if(trim($order['product_name']) === 'emby') $table = $tableClientsEmby;
                else if(trim($order['product_name']) === 'plex') $table = $tableClientsPlex;

                $stmt = $db->prepare("SELECT * FROM $table WHERE user_id = ? AND mail = ?");
                $stmt->bind_param("is", $product_user_id, $mail);
                $stmt->execute();
                $clientResult = $stmt->get_result();
                $stmt->close();

                if ($clientResult->num_rows > 0) {
                    $client = $clientResult->fetch_assoc();
                    $expiration = date('Y-m-d', strtotime($client['expiration'] . " +$product_duration months"));

                    $stmtUpdate = $db->prepare("UPDATE $table SET expiration = ? WHERE user_id = ? AND mail = ?");
                    $stmtUpdate->bind_param("sis", $expiration, $product_user_id, $mail);
                    if (!$stmtUpdate->execute()) {
                        $toastMsg = 'Errore durante l\'aggiornamento della scadenza!';
                        $toastColor = '#e53935';
                    }
                    $stmtUpdate->close();

                    $stmtup = $db->prepare("UPDATE $tableOrders SET status = '3' WHERE ID = ?");
                    $stmtup->bind_param("i", $orderId);
                    $stmtup->execute();
                    $stmtup->close();

                    $curl = curl_init();
                    curl_setopt_array($curl, [
                        CURLOPT_URL => "https://api.telegram.org/bot$botToken/sendMessage",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_POST => true,
                        CURLOPT_POSTFIELDS => [
                            "chat_id" => $product_user_id,
                            "text" => "🎁 <b>Ordine #$orderId!</b> 🎁\n\n👤 <b>Utenza:</b> <code>$mail</code>\n📅 <b>Nuova Scadenza:</b> <code>$expiration</code>$host\n\n🎉 Grazie per aver rinnovato, buon divertimento! 😃",
                            "parse_mode" => "HTML"
                        ]
                    ]);
                    curl_exec($curl);
                    curl_close($curl);


                    $toastMsg = 'Account rinnovato con successo!';
                    $toastColor = '#43a047';
                } else {
                    $toastMsg = 'Cliente non trovato per il rinnovo!';
                    $toastColor = '#e53935';
                    return;
                }
            } else {
                $expiration = date('Y-m-d', strtotime("+$product_duration months"));

                if(trim($order['product_name']) === 'emby'){
                    $password = create_password();
                    $response = create_user($mail, $password);
                    if ($response) {
                        $stmtadd = $db->prepare("INSERT INTO $tableClientsEmby (user_id, embyID, mail, expiration) VALUES (?, ?, ?, ?)");
                        $stmtadd->bind_param("isss", $product_user_id, $response, $mail, $expiration);
                        if (!$stmtadd->execute()) {
                            $toastMsg = 'Errore durante l\'aggiornamento nel db!';
                            $toastColor = '#e53935';
                        }
                        $stmtadd->close();

                        $stmtup = $db->prepare("UPDATE $tableOrders SET status = '3' WHERE ID = ?");
                        $stmtup->bind_param("i", $orderId);
                        $stmtup->execute();
                        $stmtup->close();

                        $curl = curl_init();
                        curl_setopt_array($curl, [
                            CURLOPT_URL => "https://api.telegram.org/bot$botToken/sendMessage",
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_POST => true,
                            CURLOPT_POSTFIELDS => [
                                "chat_id" => $product_user_id,
                                "text" => "🎁 <b>Ordine #$orderId!</b> 🎁\n\n👤 <b>Utenza:</b> <code>$mail</code>\n🔑 <b>Password:</b> <code>$password</code>\n📅 <b>Scadenza:</b> <code>$expiration</code>\n🌐 <b>Host:</b> <a href=\"https://em3np.apollocloud.me/\">em3np.apollocloud.me</a>\n🚪 <b>Porta:</b> <i>Lasciare vuoto</i>\n\n🎉 Accedi subito e buon divertimento! 😃",
                                "parse_mode" => "HTML"
                            ]
                        ]);
                        curl_exec($curl);
                        curl_close($curl);

                        $toastMsg = 'Account creato con successo!';
                        $toastColor = '#43a047';
                    } else {
                        $stmt = $db->prepare("UPDATE $tableOrders SET status = '20' WHERE ID = ?");
                        $stmt->bind_param("i", $orderId);
                        $stmt->execute();
                        $stmt->close();
                        $toastMsg = 'Errore nella creazione dell\'account emby!';
                        $toastColor = '#e53935';
                    }
                }else if(trim($order['product_name']) === 'plex'){
                        $stmtadd = $db->prepare("INSERT INTO $tableClientsPlex (user_id, mail, expiration) VALUES (?, ?, ?)");
                        $stmtadd->bind_param("iss", $product_user_id, $mail, $expiration);
                        if (!$stmtadd->execute()) {
                            $toastMsg = 'Errore durante l\'aggiornamento nel db!';
                            $toastColor = '#e53935';
                        }
                        $stmtadd->close();

                        $stmtup = $db->prepare("UPDATE $tableOrders SET status = '3' WHERE ID = ?");
                        $stmtup->bind_param("i", $orderId);
                        $stmtup->execute();
                        $stmtup->close();

                        $curl = curl_init();
                        curl_setopt_array($curl, [
                            CURLOPT_URL => "https://api.telegram.org/bot$botToken/sendMessage",
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_POST => true,
                            CURLOPT_POSTFIELDS => [
                                "chat_id" => $product_user_id,
                                "text" => "🎁 <b>Ordine #$orderId!</b> 🎁\n\n👤 <b>Utenza:</b> <code>$mail</code>\n📅 <b>Scadenza:</b> <code>$expiration</code>$host\n\n🎉 Accedi subito e buon divertimento! 😃",
                                "parse_mode" => "HTML"
                            ]
                        ]);
                        curl_exec($curl);
                        curl_close($curl);

                        $toastMsg = 'Account creato con successo!';
                        $toastColor = '#43a047';
                   
                }
            }
        } else {
            $toastMsg = 'Ordine non trovato o già elaborato!';
            $toastColor = '#e53935';
        }

    } else if ($action == 'reject') {
        $stmt = $db->prepare("SELECT * FROM $tableOrders WHERE ID = ? AND (status = 1 OR status = 0 OR status = 20)");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {
            $product_user_id = $result->fetch_assoc()['user_id'];
            $stmt = $db->prepare("UPDATE $tableOrders SET status = '10' WHERE ID = ?");
            $stmt->bind_param("i", $orderId);
            $stmt->execute();
            $stmt->close();

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => "https://api.telegram.org/bot$botToken/sendMessage",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => [
                    "chat_id" => $product_user_id,
                    "text" => "❌ <b>Ordine #$orderId annullato!</b> ❌\n\n<i>Il tuo ordine è stato rifiutato o annullato.\nPer maggiori informazioni contatta il supporto.</i>",
                    "parse_mode" => "HTML"
                ]
            ]);
            curl_exec($curl);
            curl_close($curl);

            $toastMsg = 'Ordine rifiutato con successo!';
            $toastColor = '#43a047';
        } else {
            $toastMsg = 'Ordine non trovato o già elaborato!';
            $toastColor = '#e53935';
        }
    }
}

function statusButton($stato)
{
    switch ($stato) {
        case '0':
            echo '<span class="w3-tag w3-grey">In creazione</span>';
            break;
        case '1':
            echo '<span class="w3-tag w3-grey">In attesa di verifica</span>';
            break;
        case '2':
            echo '<span class="w3-tag w3-yellow">In lavorazione</span>';
            break;
        case '3':
            echo '<span class="w3-tag w3-green">Completato</span>';
            break;
        case '10':
            echo '<span class="w3-tag w3-red">Annullato</span>';
            break;
        case '20':
            echo '<span class="w3-tag w3-orange">Errore</span>';
            break;
        default:
            echo '<span class="w3-tag w3-blue">Sconosciuto</span>';
            break;
    }
}

function typeOrder($type)
{
    switch ($type) {
        case '0':
            return 'Nuovo account';
        case '1':
            return 'Rinnovo account';
        default:
            return 'Sconosciuto';
    }
}
?>

<?php if (isset($_GET['order_id'])): ?>
    <h2 class="w3-text-teal" style="margin:40px 0 4px 0; text-align:center;">Gestione Ordine nr.
        <?php echo htmlspecialchars($_GET['order_id']); ?>
    </h2>
    <p style="text-align:center; color:#555; margin-bottom:28px; margin-top:0; font-size:1.08em;">Visualizzazione e modifica
        dettagli singolo ordine.</p>
    <?php
    $stmt = $db->prepare("SELECT * FROM $tableOrders WHERE ID = ?");
    $stmt->bind_param("i", $_GET['order_id']);
    $stmt->execute();
    $order = $stmt->get_result();
    $stmt->close();
    if ($order->num_rows > 0) {
        $order = $order->fetch_assoc();
        $stmtUser = $db->prepare("SELECT * FROM $tableUsers WHERE user_id = ?");
        $stmtUser->bind_param("i", $order['user_id']);
        $stmtUser->execute();
        $user = $stmtUser->get_result()->fetch_assoc();
        $stmtUser->close();
        ?>
        <div class="w3-row-padding" style="max-width:1200px;margin:0 auto 40px auto;">
            <div class="w3-third">
                <div class="w3-card w3-padding w3-white" style="margin-bottom:24px;">
                    <h4 class="w3-text-teal"><i class="fa fa-user"></i> Informazioni Utente</h4>
                    <?php if ($user): ?>
                        <b>UserId:</b> <?php echo htmlspecialchars($user['user_id']); ?><br>
                        <b>Username:</b> <?php echo htmlspecialchars($user['username']); ?><br>
                        <b>Ruolo:</b> <?php if ($user['isAdmin']): ?>Admin<?php else: ?>Utente<?php endif; ?><br>
                        <b>Rivenditore:</b> <?php if ($user['isReseller']): ?>Rivenditore<?php else: ?>Utente<?php endif; ?><br>
                    <?php else: ?>
                        <span class="w3-text-red">Utente non trovato</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="w3-third">
                <div class="w3-card w3-padding w3-white" style="margin-bottom:24px;">
                    <h4 class="w3-text-teal"><i class="fa fa-shopping-cart"></i> Informazioni Ordine</h4>
                    <b>ID Ordine:</b> <?php echo htmlspecialchars($order['ID']); ?><br>
                    <b>Data:</b> <?php echo htmlspecialchars($order['date']); ?><br>
                    <b>Tipo Ordine:</b> <?php echo typeOrder($order['type']); ?><br>
                    <b>Stato:</b> <?php statusButton($order['status']); ?><br>
                    <b>Totale:</b> <?php echo htmlspecialchars($order['total']); ?> €<br>
                    <hr>
                    <b>Prodotti:</b>
                    <?php
                    echo htmlspecialchars($order['product_name']) . '<i> (' . htmlspecialchars($order['product_duration']) . ' mesi)</i>';
                    ?>
                    <br><b>Nome Account:</b>
                    <?php
                    if ($order['mail'])
                        echo htmlspecialchars($order['mail']);
                    else
                        echo '<span class="w3-text-grey">Nessun nome account</span>';
                    ?>
                    <br><b>Coupon:</b>
                    <?php
                    if ($order['coupon_name']) {
                        echo htmlspecialchars($order['coupon_name']) . ' (' . htmlspecialchars($order['coupon_amount']) . '%)';
                    } else {
                        echo '<span class="w3-text-grey">Nessun coupon</span>';
                    }
                    ?>
                </div>
            </div>
            <div class="w3-third">
                <div class="w3-card w3-padding w3-white" style="margin-bottom:24px;">
                    <h4 class="w3-text-teal"><i class="fa fa-cogs"></i> Azioni</h4>
                    <form method="post" action="#">
                        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['ID']); ?>">
                        <?php $disabled1 = ($order['status'] == '1') ? '' : 'disabled'; ?>
                        <?php $disabled2 = ($order['status'] == '1' || $order['status'] == '20' || $order['status'] == '0') ? '' : 'disabled'; ?>
                        <button type="submit" name="action" value="accept" class="w3-button w3-green w3-margin-bottom w3-block"
                            <?php echo $disabled1; ?>>
                            <i class="material-icons" style="vertical-align:middle;font-size:1.1em;">check_circle</i> Accetta
                            Ordine
                        </button>
                        <button type="submit" name="action" value="reject" class="w3-button w3-red w3-block" <?php echo $disabled2; ?>>
                            <i class="material-icons" style="vertical-align:middle;font-size:1.1em;">cancel</i> Rifiuta Ordine
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php } else {
        echo "<p style='text-align:center; color:#f00;'>Ordine non trovato.</p>";
    }
?>
<?php else: ?>
    <?php if (isset($_GET['user_id'])): ?>
        <?php
        $userId = $_GET['user_id'];
        $stmt = $db->prepare("SELECT username FROM $tableUsers WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        ?>
        <h2 class="w3-text-teal" style="margin:40px 0 4px 0; text-align:center;">Gestione Ordini di
            <?php echo htmlspecialchars($order['username']); ?>
        </h2>
        <?php
        $resCount = $db->query("SELECT COUNT(*) as total FROM $tableOrders WHERE user_id = $userId");
        $rowCount = $resCount ? $resCount->fetch_assoc() : ['total' => 0];

        ?>
        <div style="max-width:1100px;margin:0 auto 8px auto;display:flex;align-items:center;justify-content:flex-start;">
            <span style="margin-left: 8px; font-size:1.08em;">
                <span class="material-icons" style="vertical-align:middle;font-size:1.1em;">shopping_cart</span>
                <i> Totale Ordini: <?php echo $rowCount['total']; ?></i>
            </span>
        </div>
    <?php else: ?>
        <h2 class="w3-text-teal" style="margin:40px 0 4px 0; text-align:center;">Gestione Ordini</h2>
        <?php
        $resCount = $db->query("SELECT COUNT(*) as total FROM $tableOrders");
        $rowCount = $resCount ? $resCount->fetch_assoc() : ['total' => 0];

        ?>
        <div style="max-width:1100px;margin:0 auto 8px auto;display:flex;align-items:center;justify-content:flex-start;">
            <span style="margin-left: 8px; font-size:1.08em;">
                <span class="material-icons" style="vertical-align:middle;font-size:1.1em;">shopping_cart</span>
                <i> Totale Ordini: <?php echo $rowCount['total']; ?></i>
            </span>
        </div>
    <?php endif; ?>
    <table class="w3-table-all w3-hoverable w3-centered"
        style="max-width:1100px;margin:0 auto 48px auto;border-radius:12px;overflow:hidden;font-size:1.08em;">
        <thead>
            <tr class="w3-teal">
                <th>Nr. Ordine</th>
                <th>Tipo ordine</th>
                <th>Utente</th>
                <th>Prodotto</th>
                <th>Stato</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (isset($_GET['user_id'])) {
                $userId = $_GET['user_id'];
                $stmt = $db->prepare("SELECT * FROM $tableOrders WHERE user_id = ? ORDER BY status ASC");
                $stmt->bind_param("i", $userId);
            } else {
                $stmt = $db->prepare("SELECT * FROM $tableOrders ORDER BY status ASC");
            }
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0):
                while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['ID']); ?></td>
                        <td>
                            <?php
                            if ($row['type'] == 0) {
                                echo '<span class="w3-tag w3-green">Nuovo</span>';
                            } elseif ($row['type'] == 1) {
                                echo '<span class="w3-tag w3-blue">Rinnovo</span>';
                            } else {
                                echo '<span class="w3-tag w3-grey">Sconosciuto</span>';
                            }
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                        <td>
                            <?php
                            echo htmlspecialchars($row['product_name']) . ' (' . htmlspecialchars($row['product_duration']) . ' mesi)';
                            ?>
                        </td>
                        <td><?php statusButton($row['status']); ?></td>
                        <td>
                            <a href="order.php?order_id=<?php echo $row['ID']; ?>" class="w3-button w3-blue w3-round">
                                <i class="material-icons" style="vertical-align:middle;font-size:1.1em;">visibility</i> Visualizza
                            </a>
                        </td>
                    </tr>
                <?php endwhile; else: ?>
                <tr>
                    <td colspan="6">Nessun ordine trovato.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
<?php endif; ?>


<?php include_once 'inc/footer.php'; ?>