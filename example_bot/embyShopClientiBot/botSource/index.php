<?php

include_once 'config.php';
include_once '../embyFunction/embyFunction.php';

$stmt = $db->prepare("SELECT action FROM $tableUsers WHERE user_id = ? AND action IS NOT NULL");
$stmt->bind_param("i", $user_idSafe);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $action = explode(" ", $row['action']);
    $stmt->close();

    if ($action[0] === "coupon") {
        $coupon_name = $textSafe;
        $cart_id = filter_var(trim($action[1]), FILTER_VALIDATE_INT);
        if ($cart_id === false) {
            $stmtUpdateUser = $db->prepare("UPDATE $tableUsers SET action = NULL WHERE user_id = ?");
            $stmtUpdateUser->bind_param("i", $user_idSafe);
            $stmtUpdateUser->execute();
            $stmtUpdateUser->close();

            $bot->deleteMessage($user_idSafe, $message_idSafe - 1);
            $tastiera = '[{"text":"🏠 Menu principale 🏠","callback_data":"menu "}]';
            $bot->sendMessage($user_idSafe, "✖️ Carrello non trovato o già annullato.", $tastiera);
            return;
        }

        $stmtCart = $db->prepare("SELECT * FROM $tableCarts WHERE ID = ?");
        $stmtCart->bind_param("i", $cart_id);
        $stmtCart->execute();
        $resultCart = $stmtCart->get_result();
        if (!$resultCart || $resultCart->num_rows === 0) {
            $stmtUpdateUser = $db->prepare("UPDATE $tableUsers SET action = NULL WHERE user_id = ?");
            $stmtUpdateUser->bind_param("i", $user_idSafe);
            $stmtUpdateUser->execute();
            $stmtUpdateUser->close();

            $bot->deleteMessage($user_idSafe, $message_idSafe - 1);
            $tastiera = '[{"text":"🏠 Menu principale 🏠","callback_data":"menu "}]';
            $bot->sendMessage($user_idSafe, "✖️ Ordine non trovato o già annullato.", $tastiera);
            return;
        }
        $rowCart = $resultCart->fetch_assoc();
        $stmtCart->close();

        $stmtProduct = $db->prepare("SELECT * FROM $tableProducts WHERE ID = ?");
        $stmtProduct->bind_param("i", $rowCart['product_id']);
        $stmtProduct->execute();
        $resultProduct = $stmtProduct->get_result();
        if (!$resultProduct || $resultProduct->num_rows === 0) {
            $stmtDeleteCart = $db->prepare("DELETE FROM $tableCarts WHERE ID = ?");
            $stmtDeleteCart->bind_param("i", $cart_id);
            $stmtDeleteCart->execute();
            $stmtDeleteCart->close();

            $bot->deleteMessage($user_idSafe, $message_idSafe - 1);
            $tastiera = '[{"text":"🏠 Menu principale 🏠","callback_data":"menu "}]';
            $bot->sendMessage($user_idSafe, "✖️ Prodotto non trovato, il carrello è vuoto. Torna al menu principale.", $tastiera);
            return;
        }
        $stmtProduct->close();

        $stmtCoupon = $db->prepare("SELECT * FROM $tableCoupons WHERE name = ?");
        $stmtCoupon->bind_param("s", $coupon_name);
        $stmtCoupon->execute();
        $resultCoupon = $stmtCoupon->get_result();
        if (!$resultCoupon || $resultCoupon->num_rows === 0) {
            $bot->deleteMessage($user_idSafe, $message_idSafe - 1);
            $tastiera = '[{"text":"⏭️ Salta","callback_data":"skip_coupon ' . $cart_id . '"}], [{"text":"❌ Annulla Ordine","callback_data":"annulla_carrello ' . $cart_id . '"}]';
            $bot->sendMessage($user_idSafe, "❌ <b>Coupon non trovato.</b>\n\n<i>Reinseriscilo ora oppure premi ⏭️ Salta per continuare senza coupon.</i>", $tastiera);
            return;
        }
        $rowCoupon = $resultCoupon->fetch_assoc();
        $stmtCoupon->close();

        $stmtUsedCoupon = $db->prepare("SELECT 1 FROM $tableUsedCoupons WHERE coupon_id = ? AND user_id = ? LIMIT 1");
        $stmtUsedCoupon->bind_param("ii", $rowCoupon['ID'], $user_idSafe);
        $stmtUsedCoupon->execute();
        $rowUsedCoupon = $stmtUsedCoupon->get_result();
        if ($rowUsedCoupon && $rowUsedCoupon->num_rows > 0) {
            $bot->deleteMessage($user_idSafe, $message_idSafe - 1);
            $tastiera = '[{"text":"⏭️ Salta","callback_data":"skip_coupon ' . $cart_id . '"}], [{"text":"❌ Annulla Ordine","callback_data":"annulla_carrello ' . $cart_id . '"}]';
            $bot->sendMessage($user_idSafe, "❌ <b>Coupon già utilizzatto.</b>\n\n<i>Inseriscine uno diverso ora oppure premi ⏭️ Salta per continuare senza coupon.</i>", $tastiera);
            return;
        }
        $stmtUsedCoupon->close();

        $type = (int) $rowCart['type'];
        $coupon_name = $db->real_escape_string($rowCoupon['name']);
        $coupon_amount = (int) $rowCoupon['amount'];

        $stmtUpdateUser = $db->prepare("UPDATE $tableUsers SET action = NULL WHERE user_id = ?");
        $stmtUpdateUser->bind_param("i", $user_idSafe);
        $stmtUpdateUser->execute();
        $stmtUpdateUser->close();

        $stmtUpdateCart = $db->prepare("UPDATE $tableCarts SET coupon_id = ? WHERE ID = ?");
        $stmtUpdateCart->bind_param("ii", $rowCoupon['ID'], $cart_id);
        $stmtUpdateCart->execute();
        $stmtUpdateCart->close();

        if ($type === 1)
            $tastiera = '[{"text":"➡️ Continua","callback_data":"riepilogo_ordine ' . $cart_id . '"}], [{"text":"❌ Annulla Ordine","callback_data":"annulla_carrello ' . $cart_id . '"}]';
        else
            $tastiera = '[{"text":"➡️ Continua","callback_data":"user_ordine ' . $cart_id . '"}], [{"text":"❌ Annulla Ordine","callback_data":"annulla_carrello ' . $cart_id . '"}]';
        $bot->sendMessage($user_idSafe, "🎟️ <b>Coupon trovato!</b>\n\n 🔖 <b>Nome:</b> " . htmlspecialchars($coupon_name) . "\n💸 <b>Sconto:</b> " . htmlspecialchars((string) $coupon_amount) . "%\n\n<i>Premi ➡️ Continua per proseguire con la creazione dell'ordine.</i>", $tastiera);
        return;
    }

    if ($action[0] === "user_ordine") {
        $mail = $textSafe;
        $cart_id = filter_var(trim($action[1]), FILTER_VALIDATE_INT);

        if ($cart_id === false) {
            $stmtUpdateUser = $db->prepare("UPDATE $tableUsers SET action = NULL WHERE user_id = ?");
            $stmtUpdateUser->bind_param("i", $user_idSafe);
            $stmtUpdateUser->execute();
            $stmtUpdateUser->close();

            $bot->deleteMessage($user_idSafe, $message_idSafe - 1);
            $tastiera = '[{"text":"🏠 Menu principale 🏠","callback_data":"menu "}]';
            $bot->sendMessage($user_idSafe, "✖️ Carrello non trovato o già annullato.", $tastiera);
            return;
        }

        if (strlen($mail) < 5) {
            $bot->deleteMessage($user_idSafe, $message_idSafe - 1);
            $tastiera = '[{"text":"❌ Annulla Ordine","callback_data":"annulla_carrello ' . $cart_id . '"}]';
            $bot->sendMessage($user_idSafe, "❌ <b>Utenza non valida.</b> ❌\n\n<i>Inserisci una utenza più lunga di 5 caratteri per continuare.</i>", $tastiera);
            return;
        }

        $stmtCart = $db->prepare("SELECT * FROM $tableCarts WHERE ID = ?");
        $stmtCart->bind_param("i", $cart_id);
        $stmtCart->execute();
        $resultCart = $stmtCart->get_result();
        if (!$resultCart || $resultCart->num_rows === 0) {
            $stmtUpdateUser = $db->prepare("UPDATE $tableUsers SET action = NULL WHERE user_id = ?");
            $stmtUpdateUser->bind_param("i", $user_idSafe);
            $stmtUpdateUser->execute();
            $stmtUpdateUser->close();

            $bot->deleteMessage($user_idSafe, $message_idSafe - 1);
            $tastiera = '[{"text":"🏠 Menu principale 🏠","callback_data":"menu "}]';
            $bot->sendMessage($user_idSafe, "✖️ Ordine non trovato o già annullato. Torna al menu principale.", $tastiera);
            return;
        }
        $rowCart = $resultCart->fetch_assoc();
        $stmtCart->close();

        $stmtProduct = $db->prepare("SELECT * FROM $tableProducts WHERE ID = ?");
        $stmtProduct->bind_param("i", $rowCart['product_id']);
        $stmtProduct->execute();
        $resultProduct = $stmtProduct->get_result();
        if (!$resultProduct || $resultProduct->num_rows === 0) {
            $stmtDeleteCart = $db->prepare("DELETE FROM $tableCarts WHERE ID = ?");
            $stmtDeleteCart->bind_param("i", $cart_id);
            $stmtDeleteCart->execute();
            $stmtDeleteCart->close();

            $bot->deleteMessage($user_idSafe, $message_idSafe - 1);
            $tastiera = '[{"text":"🏠 Menu principale 🏠","callback_data":"menu "}]';
            $bot->sendMessage($user_idSafe, "✖️ Prodotto non trovato, il carrello è vuoto. Torna al menu principale.", $tastiera);
            return;
        }
        $rowProduct = $resultProduct->fetch_assoc();
        $stmtProduct->close();

        $product_name = trim($rowProduct['name']);
        $exist = false;

        if ($product_name === 'emby') {
            $tableClientiReseller       = 'embypanel_clienti';
            $tableRichiesteReseller     = 'embypanel_richieste';

            $stmtCheckAlready = $db->prepare("SELECT 1 FROM $tableClientsEmby WHERE mail = ? LIMIT 1");
            $stmtCheckAlready->bind_param("s", $mail);
            $stmtCheckAlready->execute();
            $resultCheck = $stmtCheckAlready->get_result();
            if ($resultCheck && $resultCheck->num_rows > 0)
                $exist = true;
            $stmtCheckAlready->close();

            $stmtCheckAlreadyReseller = $db->prepare("SELECT 1 FROM $tableClientiReseller WHERE email = ? LIMIT 1");
            $stmtCheckAlreadyReseller->bind_param("s", $mail);
            $stmtCheckAlreadyReseller->execute();
            $resultCheck = $stmtCheckAlreadyReseller->get_result();
            if ($resultCheck && $resultCheck->num_rows > 0)
                $exist = true;
            $stmtCheckAlreadyReseller->close();

            $stmtCheckAlreadyReseller = $db->prepare("SELECT 1 FROM $tableRichiesteReseller WHERE email = ? LIMIT 1");
            $stmtCheckAlreadyReseller->bind_param("s", $mail);
            $stmtCheckAlreadyReseller->execute();
            $resultCheck = $stmtCheckAlreadyReseller->get_result();
            if ($resultCheck && $resultCheck->num_rows > 0)
                $exist = true;
            $stmtCheckAlreadyReseller->close();
        } else if ($product_name === 'plex') {
            $tableClientiReseller       = "plexpanelnextplay_clienti";
            $tableRichiesteReseller     = "plexpanelnextplay_richieste";

            if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                $bot->deleteMessage($user_idSafe, $message_idSafe - 1);
                $tastiera = '[{"text":"❌ Annulla Ordine","callback_data":"annulla_carrello ' . $cart_id . '"}]';
                $bot->sendMessage($user_idSafe, "❌ <b>Utenza non valida</b> ❌\n\n<i>Inserisci un'email valida per continuare.</i>", $tastiera);
                return;
            }
            $stmtCheckAlready = $db->prepare("SELECT 1 FROM $tableClientsPlex WHERE mail = ? LIMIT 1");
            $stmtCheckAlready->bind_param("s", $mail);
            $stmtCheckAlready->execute();
            $resultCheck = $stmtCheckAlready->get_result();
            if ($resultCheck && $resultCheck->num_rows > 0)
                $exist = true;
            $stmtCheckAlready->close();

            $stmtCheckAlreadyReseller = $db->prepare("SELECT 1 FROM $tableClientiReseller WHERE email = ? LIMIT 1");
            $stmtCheckAlreadyReseller->bind_param("s", $mail);
            $stmtCheckAlreadyReseller->execute();
            $resultCheck = $stmtCheckAlreadyReseller->get_result();
            if ($resultCheck && $resultCheck->num_rows > 0)
                $exist = true;
            $stmtCheckAlreadyReseller->close();

            $stmtCheckAlreadyReseller = $db->prepare("SELECT 1 FROM $tableRichiesteReseller WHERE email = ? LIMIT 1");
            $stmtCheckAlreadyReseller->bind_param("s", $mail);
            $stmtCheckAlreadyReseller->execute();
            $resultCheck = $stmtCheckAlreadyReseller->get_result();
            if ($resultCheck && $resultCheck->num_rows > 0)
                $exist = true;
            $stmtCheckAlreadyReseller->close();
        }

        if (!$exist) {
            $stmtCheckAlreadyExists = $db->prepare("SELECT 1 FROM $tableCarts JOIN $tableProducts ON $tableCarts.product_id = $tableProducts.ID WHERE $tableCarts.mail = ? AND $tableProducts.name = ? LIMIT 1");
            $stmtCheckAlreadyExists->bind_param("ss", $mail, $product_name);
            $stmtCheckAlreadyExists->execute();
            $resultCheck = $stmtCheckAlreadyExists->get_result();
            if ($resultCheck && $resultCheck->num_rows > 0)
                $exist = true;
            $stmtCheckAlreadyExists->close();
        }

        if (!$exist) {
            $stmtCheckAlreadyExists = $db->prepare("SELECT 1 FROM $tableOrders WHERE mail = ? AND product_name = ? LIMIT 1");
            $stmtCheckAlreadyExists->bind_param("ss", $mail, $product_name);
            $stmtCheckAlreadyExists->execute();
            $resultCheck = $stmtCheckAlreadyExists->get_result();
            if ($resultCheck && $resultCheck->num_rows > 0)
                $exist = true;
            $stmtCheckAlreadyExists->close();
        }

        if ($exist) {
            $bot->deleteMessage($user_idSafe, $message_idSafe - 1);
            $tastiera = '[{"text":"❌ Annulla Ordine","callback_data":"annulla_carrello ' . $cart_id . '"}]';
            $bot->sendMessage($user_idSafe, "❌ <b>Utenza già registrata</b> ❌\n\n<i>Inserisci un nome utente diverso per continuare.</i>", $tastiera);
            return;
        }

        $stmtUpdateCart = $db->prepare("UPDATE $tableCarts SET mail = ? WHERE ID = ?");
        $stmtUpdateCart->bind_param("si", $mail, $cart_id);
        $stmtUpdateCart->execute();
        $stmtUpdateCart->close();

        $tastiera = '[{"text":"➡️ Continua","callback_data":"riepilogo_ordine ' . $cart_id . '"}], [{"text":"❌ Annulla Ordine","callback_data":"annulla_carrello ' . $cart_id . '"}]';
        $bot->sendMessage($user_idSafe, "👤 <b>Utenza account</b> 👤\n\n🔑 <b>Utenza:</b> " . htmlspecialchars($mail) . "\n\n<i>Premi ➡️ Continua per proseguire con la creazione dell'ordine.</i>", $tastiera);
        return;
    }

    if ($action[0] === 'info_account') {
        $mail = $textSafe;

        $rowEmby = null;
        $stmtClientEmby = $db->prepare("SELECT * FROM $tableClientsEmby WHERE mail = ? AND user_id = ? LIMIT 1");
        $stmtClientEmby->bind_param("si", $mail, $user_idSafe);
        $stmtClientEmby->execute();
        $resultEmby = $stmtClientEmby->get_result();
        if ($resultEmby && $resultEmby->num_rows > 0) {
            $foundEmby = 1;
            $rowEmby = $resultEmby->fetch_assoc();
        }
        $stmtClientEmby->close();

        $rowPlex = null;
        $stmtClientiPlex = $db->prepare("SELECT * FROM $tableClientsPlex WHERE mail = ? AND user_id = ? LIMIT 1");
        $stmtClientiPlex->bind_param("si", $mail, $user_idSafe);
        $stmtClientiPlex->execute();
        $resultPlex = $stmtClientiPlex->get_result();
        if ($resultPlex && $resultPlex->num_rows > 0) {
            $foundPlex = 1;
            $rowPlex = $resultPlex->fetch_assoc();
        }
        $stmtClientiPlex->close();

        if ((!isset($foundEmby) || $foundEmby != 1) && (!isset($foundPlex) || $foundPlex != 1)) {
            $bot->deleteMessage($user_idSafe, $message_idSafe - 1);
            $tastiera = '[{"text":"❌ Annulla ❌","callback_data":"annulla "}]';
            $bot->sendMessage($user_idSafe, "❌ <b>Account non trovato</b> ❌\n\n<i>Inserisci un nome utente valido per continuare.</i>", $tastiera);
            return;
        }

        $stmtUpdateUser = $db->prepare("UPDATE $tableUsers SET action = NULL WHERE user_id = ?");
        $stmtUpdateUser->bind_param("i", $user_idSafe);
        $stmtUpdateUser->execute();
        $stmtUpdateUser->close();

        $tastiera = '';
        if ($foundEmby === 1)
            $tastiera .= '[{"text":"Emby","callback_data":"info_account emby ' . $rowEmby['ID'] . ' "}],';
        if ($foundPlex === 1)
            $tastiera .= '[{"text":"Plex","callback_data":"info_account plex ' . $rowPlex['ID'] . ' "}],';
        $tastiera .= '[{"text":"🔙 Torna indietro","callback_data":"menu "}]';

        $bot->deleteMessage($user_idSafe, $message_idSafe - 1);
        $bot->sendMessage($user_idSafe, "👤 <b>Info Account</b> 👤\n\n🔑 <b>Utenza:</b> " . $mail . "\n\n<i>Seleziona quale account visualizzare.</i>", $tastiera);
        return;
    }

    if ($action[0] === 'info_ordine') {
        $order_id = filter_var(trim($textSafe), FILTER_VALIDATE_INT);
        if ($order_id === false) {
            $bot->deleteMessage($user_idSafe, $message_idSafe - 1);
            $tastiera = '[{"text":"❌ Annulla Ordine","callback_data":"annulla "}]';
            $bot->sendMessage($user_idSafe, "❌ <b>Ordine trovato</b> ❌\n\n<i>Inserisci un numero ordine valido per continuare.</i>", $tastiera);
            return;
        }

        $stmtOrder = $db->prepare("SELECT * FROM $tableOrders WHERE ID = ? AND user_id = ? AND status NOT IN (0)");
        $stmtOrder->bind_param("ii", $order_id, $user_idSafe);
        $stmtOrder->execute();
        $resultOrder = $stmtOrder->get_result();
        if (!$resultOrder || $resultOrder->num_rows === 0) {
            $bot->deleteMessage($user_idSafe, $message_idSafe - 1);
            $tastiera = '[{"text":"❌ Annulla ❌","callback_data":"annulla "}]';
            $bot->sendMessage($user_idSafe, "❌ <b>Ordine non trovato</b> ❌\n\n<i>Inserisci un numero ordine valido per continuare.</i>", $tastiera);
            return;
        }
        $rowOrder = $resultOrder->fetch_assoc();
        $stmtOrder->close();

        $stmtUpdateUser = $db->prepare("UPDATE $tableUsers SET action = NULL WHERE user_id = ?");
        $stmtUpdateUser->bind_param("i", $user_idSafe);
        $stmtUpdateUser->execute();
        $stmtUpdateUser->close();

        $bot->deleteMessage($user_idSafe, $message_idSafe - 1);
        $coupon = '';
        if ($rowOrder['coupon_name']) {
            $coupon = "🎟️ <b>Coupon:</b> <i>" . htmlspecialchars($rowOrder['coupon_name']) . "(<i>" . htmlspecialchars($rowOrder['coupon_amount']) . "</i>)</i>\n\n";
        }
        switch($rowOrder['status']){
            case 0:
                $stato = "In creazione";
                break;
            case 1:
                $stato = "In attesa di verifica";
                break;
            case 2:
                $stato = "In lavorazione";
                break;
            case 3:
                $stato = "Completato";
                break;
            case 4:
                $stato = "Annullato";
                break;
            default:
                $stato = "Sconosciuto";
                break;
        }
        $tastiera = '[{"text":"🔙 Torna indietro","callback_data":"menu "}]';
        $bot->sendMessage(
            $user_idSafe,
            "📝 <b>Info Ordine</b> 📝\n\n"
            . "🔢 <b>Numero ordine:</b> <i>#" . htmlspecialchars($rowOrder['ID']) . "</i>\n"
            . "⏳ <b>Data Ordine:</b> <i>" . $rowOrder['date'] . "</i>\n"
            . "📊 <b>Stato:</b> <i>" . $stato . "</i>\n\n"
            . "📦 <b>Prodotto:</b> <i>" . htmlspecialchars($rowOrder['product_name']) . "</i>\n"
            . "⏰ <b>Durata:</b> <i>" . htmlspecialchars($rowOrder['product_duration']) . " mesi</i>\n"
            . "👤 <b>Utenza:</b> <i>" . htmlspecialchars($rowOrder['mail']) . "</i>\n\n"
            . $coupon
            . "💰 <b>Totale:</b> <i>" . number_format((float) $rowOrder['total'], 2, ',', '') . "€</i>\n",
            $tastiera
        );
    }

}

if (stripos($textSafe, "/start") === 0) {
    $tastiera = '[{"text":"🛍️ Vetrina 🛍️","callback_data":"vetrina "}], [{"text":"👤 Info Account 👤","callback_data":"info_account "}], [{"text":"📝 Info Ordine 📝","callback_data":"info_ordine "}], [{"text":"❓ FAQ ❓","url":"https://enigmaelaboration.altervista.org/docuWiki/"}]';
    $messaggio = "👋 <b>Benvenuto nel mondo NextPlay!</b>\n"
        . "🛒 <i>Qui puoi acquistare i migliori abbonamenti in modo semplice e veloce.\n\n"
        . "Premi uno dei pulsanti qui sotto per iniziare</i>";
    $bot->sendMessage($user_idSafe, $messaggio, $tastiera);
}
if (stripos($textSafe, '/admin') === 0 && $isAdmin == 1) {
    include_once 'utils/secretKey.php';
    $hashString = hash_hmac('sha256', $user_idSafe . $nomeSafe . $cognomeSafe . $usernameSafe, $hashSecretKey);

    logMessage("$usernameSafe ha aperto il pannello admin", 'ADMIN', 'BOT', $logDirectory);
    $tastiera = '[{"text":"abc","url":"http://enigmaelaboration.altervista.org/embyShopClientiBot/adminPanel/login.php?login=' . $hashString . '&user_id=' . $user_idSafe . '&first_name=' . $nomeSafe . '&last_name=' . $cognomeSafe . '&username=' . $usernameSafe . '"}]';
    $bot->sendMessage($user_idSafe, "Pannello Admin!\n\nNON INOLTRARE QUESTO MESSAGGIO!", $tastiera);
}

if (isset($bot->callback_data)) {
    $q = explode(" ", $bot->callback_data);

    if ($q[0] === "vetrina") {
        if ($q[1]) {
            $name = $db->real_escape_string($q[1]);
            $stmt = $db->prepare("SELECT * FROM $tableProducts WHERE name = ? ORDER BY duration ASC");
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                $tastiera = '';
                $c = 0;
                while ($row = $result->fetch_assoc()) {
                    if ($c % 2 == 0) {
                        if ($c == 0) {
                            $tastiera .= '[{"text":"' . htmlspecialchars($row['duration']) . ' mesi","callback_data":"prodotto ' . $row['ID'] . ' "}';
                        } else {
                            $tastiera .= '],[' . '{"text":"' . htmlspecialchars($row['duration']) . ' mesi","callback_data":"prodotto ' . $row['ID'] . ' "}';
                        }
                    } else {
                        $tastiera .= ',{"text":"' . htmlspecialchars($row['duration']) . ' mesi","callback_data":"prodotto ' . $row['ID'] . ' "}';
                    }
                    $c++;
                }
                $tastiera .= '], [{"text":"🔙 Torna indietro","callback_data":"vetrina "}]';
                $bot->editMessageText($callback_user_idSafe, $callback_message_idSafe, "🛍 <b>Vetrina $name</b> 🛍\n\n<i>Seleziona un prodotto per vederne le informazioni.</i>", $tastiera);
            } else {
                $bot->answerCallbackquery($bot->callback_query_id, "❌ Prodotto non trovato!");
            }
        } else {
            $stmt = $db->prepare("SELECT DISTINCT name FROM $tableProducts ORDER BY name ASC");
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                $tastiera = '';
                $c = 0;
                while ($row = $result->fetch_assoc()) {
                    if ($c % 2 == 0) {
                        if ($c == 0) {
                            $tastiera .= '[{"text":"' . htmlspecialchars($row['name']) . '","callback_data":"vetrina ' . $row['name'] . ' "}';
                        } else {
                            $tastiera .= '],[' . '{"text":"' . htmlspecialchars($row['name']) . '","callback_data":"vetrina ' . $row['name'] . ' "}';
                        }
                    } else {
                        $tastiera .= ',{"text":"' . htmlspecialchars($row['name']) . '","callback_data":"vetrina ' . $row['name'] . ' "}';
                    }
                    $c++;
                }
                $tastiera .= '], [{"text":"🔙 Torna indietro","callback_data":"menu "}]';
                $bot->editMessageText($callback_user_idSafe, $callback_message_idSafe, "🛍 <b>Vetrina</b> 🛍\n\n<i>Seleziona il tipo di prodotto a cui sei interessato.</i>", $tastiera);
            } else {
                $bot->answerCallbackquery($bot->callback_query_id, "🛍 Vetrina in allestimento! 🛍");
            }
        }
    }

    if ($q[0] === "prodotto") {
        $product_id = filter_var(trim($q[1]), FILTER_VALIDATE_INT);
        if ($product_id === false) {
            $bot->answerCallbackquery($bot->callback_query_id, "❌ Prodotto non valido!");
            return;
        }
        $stmt = $db->prepare("SELECT * FROM $tableProducts WHERE ID = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($q[2] === "rinnova") {
                $tastiera = '[{"text":"🔄 Rinnova Account 🔄","callback_data":"rinnova ' . $product_id . ' ' . $q[3] . ' "}], [{"text":"🔙 Torna indietro","callback_data":"rinnova_account ' . $row['name'] . ' ' . $q[3] . '"}]';
            } else {
                $tastiera = '[{"text":"🛒 Acquista 🛒","callback_data":"acquista ' . $product_id . '"}], [{"text":"🔙 Torna indietro","callback_data":"vetrina ' . $row['name'] . '"}]';
            }

            $bot->editMessageText(
                $callback_user_idSafe,
                $callback_message_idSafe,
                "🏷️ <b>Dettagli prodotto</b> 🏷️\n\n"
                . "📦 <b>Nome:</b> " . htmlspecialchars($row['name']) . "\n"
                . "⏳ <b>Durata:</b> " . htmlspecialchars($row['duration']) . " mesi\n"
                . "💶 <b>Costo:</b> " . number_format((float) $row['price'], 2, ',', '') . "€\n\n"
                . "📝 <b>Descrizione:</b> " . htmlspecialchars(stripslashes($row['description'])),
                $tastiera
            );
        } else {
            $bot->answerCallbackquery($bot->callback_query_id, "❌ Prodotto non trovato!");
        }
    }

    if ($q[0] === 'acquista') {
        $product_id = filter_var(trim($q[1]), FILTER_VALIDATE_INT);
        if ($product_id === false) {
            $bot->answerCallbackquery($bot->callback_query_id, "❌ ID prodotto non valido!");
            return;
        }

        $stmtCarts = $db->prepare("SELECT * FROM $tableCarts WHERE user_id = ?");
        $stmtCarts->bind_param("i", $callback_user_idSafe);
        $stmtCarts->execute();
        $resultCart = $stmtCarts->get_result();
        if ($resultCart && $resultCart->num_rows > 0) { //TODO: eventuale continuazione/annullamento ordine
            $bot->answerCallbackquery($bot->callback_query_id, "❌ Hai già un carrello attivo!");
            return;
        }
        $stmtCarts->close();

        $stmtProduct = $db->prepare("SELECT name, duration, price FROM $tableProducts WHERE ID = ?");
        $stmtProduct->bind_param("i", $product_id);
        $stmtProduct->execute();
        $result = $stmtProduct->get_result();
        if (!$result || $result->num_rows === 0) { //TODO: eventuale cancellazione ordine, ritorno al menu
            $bot->answerCallbackquery($bot->callback_query_id, "❌ Prodotto non trovato!");
            return;
        }
        $stmtProduct->close();

        $stmtOrder = $db->prepare("INSERT INTO $tableCarts (user_id, product_id) VALUES (?, ?)");
        $stmtOrder->bind_param("ii", $callback_user_idSafe, $product_id);
        $stmtOrder->execute();
        $cart_id = $db->insert_id;
        $stmtOrder->close();

        $stmtUser = $db->prepare("UPDATE $tableUsers SET action = ? WHERE user_id = ?");
        $action = 'coupon ' . $cart_id;
        $stmtUser->bind_param("si", $action, $callback_user_idSafe);
        $stmtUser->execute();
        $stmtUser->close();

        $tastiera = '[{"text":"⏭️ Salta","callback_data":"skip_coupon ' . $cart_id . '"}], [{"text":"❌ Annulla Ordine","callback_data":"annulla_carrello ' . $cart_id . '"}]';
        $bot->editMessageText($callback_user_idSafe, $callback_message_idSafe, "🎟️ <b>Possiedi un coupon sconto?</b>\n\n<i>Inseriscilo ora oppure premi ⏭️ Salta per continuare senza coupon.</i>", $tastiera);
    }

    if ($q[0] === 'skip_coupon') {
        $cart_id = filter_var(trim($q[1]), FILTER_VALIDATE_INT);
        if ($cart_id === false) {
            $bot->answerCallbackquery($bot->callback_query_id, "❌ ID carrello non valido!");
            return;
        }

        $stmtCart = $db->prepare("SELECT * FROM $tableCarts WHERE ID = ?");
        $stmtCart->bind_param("i", $cart_id);
        $stmtCart->execute();
        $resultCart = $stmtCart->get_result();
        if (!$resultCart || $resultCart->num_rows === 0) {
            $bot->answerCallbackquery($bot->callback_query_id, "❌ Carrello non trovato!");
            return;
        }
        $type = $resultCart->fetch_assoc()['type'];
        $stmtCart->close();

        $stmtUpdateUser = $db->prepare("UPDATE $tableUsers SET action = NULL WHERE user_id = ?");
        $stmtUpdateUser->bind_param("i", $callback_user_idSafe);
        $stmtUpdateUser->execute();
        $stmtUpdateUser->close();

        if ($type === 1)
            $tastiera = '[{"text":"➡️ Continua","callback_data":"riepilogo_ordine ' . $cart_id . '"}], [{"text":"❌ Annulla Ordine","callback_data":"annulla_carrello ' . $cart_id . '"}]';
        else
            $tastiera = '[{"text":"➡️ Continua","callback_data":"user_ordine ' . $cart_id . '"}], [{"text":"❌ Annulla Ordine","callback_data":"annulla_carrello ' . $cart_id . '"}]';
        $bot->editMessageText($callback_user_idSafe, $callback_message_idSafe, "⏭️ <b>Coupon saltato</b> ⏭️\n\n<i>Premi ➡️ Continua per proseguire con la creazione dell'ordine.</i>", $tastiera);
    }

    if ($q[0] === 'user_ordine') {
        $cart_id = filter_var(trim($q[1]), FILTER_VALIDATE_INT);
        if ($cart_id === false) {
            $bot->answerCallbackquery($bot->callback_query_id, "❌ ID carrello non valido!");
            return;
        }

        $stmtCart = $db->prepare("SELECT * FROM $tableCarts WHERE ID = ?");
        $stmtCart->bind_param("i", $cart_id);
        $stmtCart->execute();
        $resultCart = $stmtCart->get_result();
        if (!$resultCart || $resultCart->num_rows === 0) { //TODO: eventuale annullamento carrello
            $bot->answerCallbackquery($bot->callback_query_id, "❌ Carrello non trovato!");
            return;
        }
        $stmtUpdateUser = $db->prepare("UPDATE $tableUsers SET action = ? WHERE user_id = ?");
        $action = 'user_ordine ' . $cart_id;
        $stmtUpdateUser->bind_param("si", $action, $callback_user_idSafe);
        $stmtUpdateUser->execute();
        $stmtUpdateUser->close();

        $tastiera = '[{"text":"❌ Annulla Ordine","callback_data":"annulla_carrello ' . $cart_id . '"}]';
        $bot->editMessageText($callback_user_idSafe, $callback_message_idSafe, "👤 <b>Utenza account</b> 👤\n\nInserisci il nome utente che desideri utilizzare per accedere al tuo account.", $tastiera);
    }

    if ($q[0] === 'riepilogo_ordine') {
        $cart_id = filter_var(trim($q[1]), FILTER_VALIDATE_INT);
        if ($cart_id === false) {
            $bot->answerCallbackquery($bot->callback_query_id, "❌ ID carrello non valido!");
            return;
        }

        $stmtUpdateUser = $db->prepare("UPDATE $tableUsers SET action = NULL WHERE user_id = ?");
        $stmtUpdateUser->bind_param("i", $callback_user_idSafe);
        $stmtUpdateUser->execute();
        $stmtUpdateUser->close();

        $stmtCart = $db->prepare("SELECT * FROM $tableCarts WHERE ID = ?");
        $stmtCart->bind_param("i", $cart_id);
        $stmtCart->execute();
        $resultCart = $stmtCart->get_result();
        if (!$resultCart || $resultCart->num_rows === 0) {
            $bot->answerCallbackquery($bot->callback_query_id, "❌ Carrello non trovato!");
            return;
        }
        $rowCart = $resultCart->fetch_assoc();
        $stmtCart->close();

        $stmtProduct = $db->prepare("SELECT * FROM $tableProducts WHERE ID = ?");
        $stmtProduct->bind_param("i", $rowCart['product_id']);
        $stmtProduct->execute();
        $resultProduct = $stmtProduct->get_result();
        if (!$resultProduct || $resultProduct->num_rows === 0) {
            $bot->answerCallbackquery($bot->callback_query_id, "❌ Prodotto non trovato!");
            return;
        }
        $rowProduct = $resultProduct->fetch_assoc();
        $stmtProduct->close();

        $cart_total = (float) $rowProduct['price'];
        if ($rowCart['coupon_id'] != null) {
            $stmtCoupon = $db->prepare("SELECT * FROM $tableCoupons WHERE ID = ?");
            $stmtCoupon->bind_param("i", $rowCart['coupon_id']);
            $stmtCoupon->execute();
            $resultCoupon = $stmtCoupon->get_result();
            if (!$resultCoupon || $resultCoupon->num_rows === 0) {
                $bot->answerCallbackquery($bot->callback_query_id, "❌ Coupon non trovato!");
                return;
            }
            $rowCoupon = $resultCoupon->fetch_assoc();
            $stmtCoupon->close();

            $cart_total = $cart_total - ($cart_total * ($rowCoupon['amount'] / 100));
        }

        $tastiera = '[{"text":"✅ Conferma","callback_data":"conferma_ordine ' . $cart_id . '"}], [{"text":"❌ Annulla Ordine","callback_data":"annulla_carrello ' . $cart_id . '"}]';
        $bot->editMessageText(
            $callback_user_idSafe,
            $callback_message_idSafe,
            "📝 <b>Riepilogo Ordine</b> 📝\n\n"
            . "📦 <b>Prodotto:</b> " . htmlspecialchars($rowProduct['name']) . "\n"
            . "⏳ <b>Durata:</b> " . (int) $rowProduct['duration'] . " mesi\n"
            . "💶 <b>Costo:</b> " . number_format((float) $rowProduct['price'], 2, ',', '') . "€\n"
            . "👤 <b>Utenza:</b> " . htmlspecialchars($rowCart['mail']) . "\n"
            . "🎟️ <b>Coupon:</b> <i>"
            . (($rowCart['coupon_id'] != null)
                ? htmlspecialchars($rowCoupon['name']) . " (-" . htmlspecialchars($rowCoupon['amount']) . "%)"
                : "Nessuno coupon") . "</i>\n"
            . "💰 <b>Totale:</b> " . number_format((float) $cart_total, 2, ',', '') . "€\n\n"
            . "<i>Premi ✅ Conferma per inviare l'ordine.</i>",
            $tastiera
        );

    }

    if ($q[0] === 'conferma_ordine') {
        $cart_id = filter_var(trim($q[1]), FILTER_VALIDATE_INT);
        if ($cart_id === false) {
            $bot->answerCallbackquery($bot->callback_query_id, "❌ Carrello non valido!");
            return;
        }

        $stmtCart = $db->prepare("SELECT * FROM $tableCarts WHERE ID = ?");
        $stmtCart->bind_param("i", $cart_id);
        $stmtCart->execute();
        $resultCart = $stmtCart->get_result();
        if (!$resultCart || $resultCart->num_rows === 0) {
            $bot->answerCallbackquery($bot->callback_query_id, "❌ Carrello non trovato!");
            return;
        }
        $rowCart = $resultCart->fetch_assoc();
        $stmtCart->close();

        $stmtProduct = $db->prepare("SELECT * FROM $tableProducts WHERE ID = ?");
        $stmtProduct->bind_param("i", $rowCart['product_id']);
        $stmtProduct->execute();
        $resultProduct = $stmtProduct->get_result();
        if (!$resultProduct || $resultProduct->num_rows === 0) {
            $bot->answerCallbackquery($bot->callback_query_id, "❌ Prodotto non trovato!");
            return;
        }
        $rowProduct = $resultProduct->fetch_assoc();
        $stmtProduct->close();

        $total = $rowProduct['price'];
        if ($rowCart['coupon_id'] !== null) {
            $stmtCoupon = $db->prepare("SELECT * FROM $tableCoupons WHERE ID = ?");
            $stmtCoupon->bind_param("i", $rowCart['coupon_id']);
            $stmtCoupon->execute();
            $resultCoupon = $stmtCoupon->get_result();
            if (!$resultCoupon || $resultCoupon->num_rows === 0) {
                $bot->answerCallbackquery($bot->callback_query_id, "❌ Coupon non trovato!");
                return;
            }
            $rowCoupon = $resultCoupon->fetch_assoc();
            $stmtCoupon->close();

            $stmtAddUsedCoupon = $db->prepare("INSERT INTO $tableUsedCoupons (user_id, coupon_id) VALUES (?, ?)");
            $stmtAddUsedCoupon->bind_param("ii", $callback_user_idSafe, $rowCoupon['ID']);
            if (!$stmtAddUsedCoupon->execute()) {
                $bot->answerCallbackquery($bot->callback_query_id, "❌ Coupon già utilizzato!");
                return;
            }
            $stmtAddUsedCoupon->close();

            $total = (float) $total - ((float) $total * ((float) $rowCoupon['amount'] / 100));
        }

        $user_id = (int) $callback_user_idSafe;
        $product_name = $db->real_escape_string($rowProduct['name']);
        $product_duration = (int) $rowProduct['duration'];
        $product_cost = (float) $rowProduct['price'];
        if ($rowCart['coupon_id'] !== null) {
            $coupon_name = $db->real_escape_string($rowCoupon['name']);
            $coupon_amount = (int) $rowCoupon['amount'];
        } else {
            $coupon_name = NULL;
            $coupon_amount = NULL;
        }
        $mail = $db->real_escape_string($rowCart['mail']);
        $type = (int) $rowCart['type'];
        $stmtInserOrder = $db->prepare("INSERT INTO $tableOrders (user_id, product_name, product_duration, product_cost, coupon_name, coupon_amount, mail, total, type, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
        $stmtInserOrder->bind_param("isidsisdi", $user_id, $product_name, $product_duration, $product_cost, $coupon_name, $coupon_amount, $mail, $total, $type);
        $stmtInserOrder->execute();
        $order_id = $stmtInserOrder->insert_id;
        $stmtInserOrder->close();

        $stmtDeleteCart = $db->prepare("DELETE FROM $tableCarts WHERE ID = ?");
        $stmtDeleteCart->bind_param("i", $cart_id);
        $stmtDeleteCart->execute();
        $stmtDeleteCart->close();

        $importo = number_format($total, 2, '.', '');
        $descrizione = "Pagamento ordine NextPlay #" . $order_id . " di @" . $usernameSafe;
        $linkPagamento = "https://PayPal.me/peppinostore/$importo";
        $tastiera = '[{"text":"💳 Paga ora","url":"' . $linkPagamento . '"}]';
        $bot->editMessageText(
            $callback_user_idSafe,
            $callback_message_idSafe,
            "💳 <b>Pagamento richiesto</b>\n\n"
            . "Per completare l'ordine è necessario effettuare il pagamento di <b>$importo €</b>.\n"
            . "Nella descrizione del pagamento inserisci:\n\n"
            . "<code>$descrizione</code>\n\n"
            . "<i>L'ordine sarà evaso solo dopo la verifica dell'avvenuto pagamento.</i>",
            $tastiera
        );

        $tastiera = '[{"text":"🏠 Menu principale 🏠","callback_data":"menu "}]';
        $bot->sendMessage($callback_user_idSafe, "✅ Ordine Inviato! Torna al menu principale per continuare.", $tastiera);

        $stmtAdmin = $db->prepare("SELECT user_id FROM $tableUsers WHERE isAdmin = 1");
        $stmtAdmin->execute();
        $resultAdmin = $stmtAdmin->get_result();
        $stmtAdmin->close();
        while ($row = $resultAdmin->fetch_assoc()) {
            $bot->sendMessage($row['user_id'], "🛒 <b>Nuovo ordine ricevuto:</b> #$order_id\n\n<i>Verifica l'avvenuto pagamento e digita /admin per accedere al pannello admin</i>");
        }

        logMessage("$usernameSafe ha inserito un nuovo ordine $order_id", 'OK', 'BOT', $logDirectory);
    }

    if ($q[0] == 'annulla_carrello') {
        $cart_id = filter_var($q[1], FILTER_VALIDATE_INT);
        if ($cart_id === false) {
            $bot->answerCallbackquery($bot->callback_query_id, "❌ ID carrello non valido!");
            return;
        }

        $stmtOrder = $db->prepare("DELETE FROM $tableCarts WHERE ID = ?");
        $stmtOrder->bind_param("i", $cart_id);
        $stmtOrder->execute();
        $stmtOrder->close();

        $stmtUser = $db->prepare("UPDATE $tableUsers SET action = NULL WHERE user_id = ?");
        $stmtUser->bind_param("i", $callback_user_idSafe);
        $stmtUser->execute();
        $stmtUser->close();

        $tastiera = '[{"text":"🏠 Menu principale 🏠","callback_data":"menu "}]';
        $bot->editMessageText(
            $callback_user_idSafe,
            $callback_message_idSafe,
            "✅ Ordine annullato con successo!",
            $tastiera
        );
    }

    if ($q[0] === 'info_account') {
        if (isset($q[1]) && isset($q[2])) {
            $type = $db->real_escape_string($q[1]);
            $account_id = filter_var(trim($q[2]), FILTER_VALIDATE_INT);
            if ($account_id === false) {
                $bot->answerCallbackquery($bot->callback_query_id, "❌ ID account non valido!");
                return;
            }

            if ($type === 'emby') {
                $stmt = $db->prepare("SELECT * FROM $tableClientsEmby WHERE ID = ? AND user_id = ?");
                $stmt->bind_param("ii", $account_id, $callback_user_idSafe);
            } elseif ($type === 'plex') {
                $stmt = $db->prepare("SELECT * FROM $tableClientsPlex WHERE ID = ? AND user_id = ?");
                $stmt->bind_param("ii", $account_id, $callback_user_idSafe);
            } else {
                $bot->answerCallbackquery($bot->callback_query_id, "❌ Tipo di account non valido!");
                return;
            }
            $stmt->execute();
            $result = $stmt->get_result();
            if (!$result || $result->num_rows === 0) {
                $bot->answerCallbackquery($bot->callback_query_id, "❌ Account non trovato!");
                return;
            }
            $row = $result->fetch_assoc();
            $stmt->close();

            if ($type === 'emby')
                $tastiera = '[{"text":"🔄 Rinnova account","callback_data":"rinnova_account emby ' . $row['ID'] . ' "}]';
            elseif ($type === 'plex')
                $tastiera = '[{"text":"🔄 Rinnova account","callback_data":"rinnova_account plex ' . $row['ID'] . ' "}]';
            $tastiera = $tastiera . ', [{"text":"🔙 Torna indietro","callback_data":"info_account "}]';
            $bot->editMessageText($callback_user_idSafe, $callback_message_idSafe, "ℹ️ <b>Informazioni Account</b> ℹ️\n\n👤 <b>Tipo</b>: " . ucfirst($type) . "\n🔑 <b>Utenza</b>: " . htmlspecialchars($row['mail']) . "\n⏰ <b>Data Scadenza</b>: " . htmlspecialchars($row['expiration']) . "\n\n<i>Premi 🔄 Rinnova account per prolungare la durata.</i>", $tastiera);
        } else {
            $stmtUpdateUser = $db->prepare("UPDATE $tableUsers SET action = 'info_account' WHERE user_id = ?");
            $stmtUpdateUser->bind_param("i", $callback_user_idSafe);
            $stmtUpdateUser->execute();
            $stmtUpdateUser->close();

            $tastiera = '[{"text":"❌ Annulla ❌","callback_data":"annulla "}]';
            $bot->editMessageText($callback_user_idSafe, $callback_message_idSafe, "ℹ️ <b>Informazioni Account</b>\n\n<i>Inserisci il nome utente dell'account di cui vuoi ricevere informazioni</i>", $tastiera);
        }
    }

    if ($q[0] === "rinnova_account") {
        $type = $db->real_escape_string(trim($q[1]));
        $account_id = filter_var(trim($q[2]), FILTER_VALIDATE_INT);
        if ($account_id === false) {
            $bot->answerCallbackquery($bot->callback_query_id, "❌ Account non valido!");
            return;
        }

        if ($type === 'emby') {
            $stmt = $db->prepare("SELECT * FROM $tableClientsEmby WHERE ID = ? AND user_id = ?");
            $stmt->bind_param("ii", $account_id, $callback_user_idSafe);
        } elseif ($type === 'plex') {
            $stmt = $db->prepare("SELECT * FROM $tableClientsPlex WHERE ID = ? AND user_id = ?");
            $stmt->bind_param("ii", $account_id, $callback_user_idSafe);
        } else {
            $bot->answerCallbackquery($bot->callback_query_id, "❌ Tipo di account non valido!");
            return;
        }
        $stmt->execute();
        $result = $stmt->get_result();
        if (!$result || $result->num_rows === 0) {
            $bot->answerCallbackquery($bot->callback_query_id, "❌ Account non trovato!");
            return;
        }
        $row = $result->fetch_assoc();
        $stmt->close();

        $stmtProduct = $db->prepare("SELECT * FROM $tableProducts WHERE name = ? ORDER BY duration ASC");
        $stmtProduct->bind_param("s", $type);
        $stmtProduct->execute();
        $resultProduct = $stmtProduct->get_result();
        if (!$resultProduct || $resultProduct->num_rows === 0) {
            $bot->answerCallbackquery($bot->callback_query_id, "🔄 Rinnova Account in allestimento! 🔄");
            return;
        }
        $stmtProduct->close();

        $tastiera = '';
        $c = 0;
        while ($row = $resultProduct->fetch_assoc()) {
            if ($c % 2 == 0) {
                if ($c == 0) {
                    $tastiera .= '[{"text":"' . htmlspecialchars($row['duration']) . ' mesi","callback_data":"prodotto ' . $row['ID'] . ' rinnova ' . $account_id . ' "}';
                } else {
                    $tastiera .= '],[' . '{"text":"' . htmlspecialchars($row['duration']) . ' mesi","callback_data":"prodotto ' . $row['ID'] . ' rinnova ' . $account_id . ' "}';
                }
            } else {
                $tastiera .= ',{"text":"' . htmlspecialchars($row['duration']) . ' mesi","callback_data":"prodotto ' . $row['ID'] . ' rinnova ' . $account_id . ' "}';
            }
            $c++;
        }
        $tastiera .= '], [{"text":"🔙 Torna indietro","callback_data":"info_account ' . $type . ' ' . $account_id . ' "}]';
        $bot->editMessageText($callback_user_idSafe, $callback_message_idSafe, "🔄 <b>Rinnova Account</b> 🔄\n\n<i>Seleziona un prodotto per vederne le informazioni.</i>", $tastiera);
    }

    if ($q[0] === "rinnova") {
        $product_id = filter_var(trim($q[1]), FILTER_VALIDATE_INT);
        if ($product_id === false) {
            $bot->answerCallbackquery($bot->callback_query_id, "❌ ID prodotto non valido!");
            return;
        }

        $account_id = filter_var(trim($q[2]), FILTER_VALIDATE_INT);
        if ($account_id === false) {
            $bot->answerCallbackquery($bot->callback_query_id, "❌ ID account non valido!");
            return;
        }

        $stmtProducts = $db->prepare("SELECT * FROM $tableProducts WHERE ID = ?");
        $stmtProducts->bind_param("i", $product_id);
        $stmtProducts->execute();
        $resultProducts = $stmtProducts->get_result();
        if (!$resultProducts || $resultProducts->num_rows === 0) {
            $bot->answerCallbackquery($bot->callback_query_id, "❌ Prodotto non trovato!");
            return;
        }
        $rowProduct = $resultProducts->fetch_assoc();
        $stmtProducts->close();

        if ($rowProduct['name'] === 'emby') {
            $stmtAccount = $db->prepare("SELECT * FROM $tableClientsEmby WHERE ID = ?");
            $stmtAccount->bind_param("i", $account_id);
        } elseif ($rowProduct['name'] === 'plex') {
            $stmtAccount = $db->prepare("SELECT * FROM $tableClientsPlex WHERE ID = ?");
            $stmtAccount->bind_param("i", $account_id);
        }
        $stmtAccount->execute();
        $resultAccount = $stmtAccount->get_result();
        if (!$resultAccount || $resultAccount->num_rows === 0) {
            $bot->answerCallbackquery($bot->callback_query_id, "❌ Account non trovato!");
            return;
        }
        $rowAccount = $resultAccount->fetch_assoc();
        $stmtAccount->close();

        $stmtInserOrder = $db->prepare("INSERT INTO $tableCarts (user_id, product_id, mail, type) VALUES (?, ?, ?, 1)");
        $stmtInserOrder->bind_param("iis", $callback_user_idSafe, $product_id, $rowAccount['mail']);
        $stmtInserOrder->execute();
        $cart_id = $db->insert_id;
        $stmtInserOrder->close();

        $stmtUpdateUser = $db->prepare("UPDATE $tableUsers SET action = ? WHERE user_id = ?");
        $action = 'rinnova ' . $cart_id;
        $stmtUpdateUser->bind_param("si", $action, $callback_user_idSafe);
        $stmtUpdateUser->execute();
        $stmtUpdateUser->close();

        $tastiera = '[{"text":"⏭️ Salta","callback_data":"skip_coupon ' . $cart_id . '"}], [{"text":"❌ Annulla Ordine","callback_data":"annulla_carrello ' . $cart_id . '"}]';
        $bot->editMessageText($callback_user_idSafe, $callback_message_idSafe, "🎟️ <b>Possiedi un coupon sconto?</b>\n\n<i>Inseriscilo ora oppure premi ⏭️ Salta per continuare senza coupon.</i>", $tastiera);
    }

    if ($q[0] === 'info_ordine') {
        $stmtUpdateUser = $db->prepare("UPDATE $tableUsers SET action = 'info_ordine' WHERE user_id = ?");
        $stmtUpdateUser->bind_param("i", $callback_user_idSafe);
        $stmtUpdateUser->execute();
        $stmtUpdateUser->close();

        $tastiera = '[{"text":"❌ Annulla ❌","callback_data":"annulla "}]';
        $bot->editMessageText(
            $callback_user_idSafe,
            $callback_message_idSafe,
            "ℹ️ <b>Informazioni sull'ordine:</b>\n\n<i>Inserisci il numero dell'ordine per cui desideri informazioni.</i>",
            $tastiera
        );
    }

    if ($q[0] === 'annulla') {
        $stmtUpdateUser = $db->prepare("UPDATE $tableUsers SET action = NULL WHERE user_id = ?");
        $stmtUpdateUser->bind_param("i", $callback_user_idSafe);
        $stmtUpdateUser->execute();
        $stmtUpdateUser->close();

        $tastiera = '[{"text":"🏠 Menu principale 🏠","callback_data":"menu "}]';
        $bot->editMessageText(
            $callback_user_idSafe,
            $callback_message_idSafe,
            "✅ Operazione annullata.",
            $tastiera
        );
    }

    if ($q[0] === 'menu') {
        $tastiera = '[{"text":"🛍️ Vetrina 🛍️","callback_data":"vetrina "}], [{"text":"👤 Info Account 👤","callback_data":"info_account "}], [{"text":"📝 Info Ordine 📝","callback_data":"info_ordine "}], [{"text":"❓ FAQ ❓","url":"https://enigmaelaboration.altervista.org/docuWiki/"}]';
        $bot->editMessageText($callback_user_idSafe, $callback_message_idSafe, "👋 <b>Benvenuto nel mondo NextPlay!</b>\n"
            . "🛒 <i>Qui puoi acquistare i migliori abbonamenti in modo semplice e veloce.\n\n"
            . "Premi uno dei pulsanti qui sotto per iniziare</i>", $tastiera);
    }
}