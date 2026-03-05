<?php
require_once 'config.php';

/*** START ACTION ***/
$stmAction = $db->prepare("SELECT action FROM $tableIscritti WHERE user_id = ?");
$stmAction->bind_param('i', $user_idSafe);
$stmAction->execute();
$azioni = $stmAction->get_result()->fetch_assoc()['action'];
$stmAction->close();

if ($azioni !== NULL) {
    $a = explode(" ", $azioni);

    // INFORMAZIONI ACCOUNT
    if ($a[0] == 'info_account') {
        $email = $textSafe;

        $stmtClienti = $db->prepare("SELECT * FROM $tableClienti WHERE email = ?");
        $stmtClienti->bind_param("s", $email);
        $stmtClienti->execute();
        $resultClienti = $stmtClienti->get_result();
        if (!$resultClienti || $resultClienti->num_rows === 0) {
            $bot->deleteMessage($user_idSafe, $message_idSafe - 1);
            $tastiera = '[{"text":"🏚 menu principale 🏚","callback_data":"menu_principale "}]';
            $bot->sendMessage($user_idSafe, "👮 <b>Account non trovato</b>\n\n<i>Controlla l'email inserita e riprova.</i>", $tastiera, 'inline');
            return;
        }
        $rowClienti = $resultClienti->fetch_assoc();
        $stmtClienti->close();

        $stm = $db->prepare("UPDATE $tableIscritti SET action = NULL WHERE user_id = ?");
        $stm->bind_param("i", $user_idSafe);
        $stm->execute();
        $stm->close();

        $scadenza = $rowClienti['scadenza'];
        $scadenzaPass = $rowClienti['scadenzaPass'];
        if ($scadenza != NULL)
            $scadenza = "\n<b>⏳Scadenza</b>: <i>$scadenza</i>";
        if ($scadenzaPass != NULL)
            $scadenzaPass = "\n<b>⏰Scadenza Pass</b>: <i>$scadenzaPass</i>";

        $bot->deleteMessage($user_idSafe, $message_idSafe - 1);
        $tastiera = '[{"text":"🏚 menu principale 🏚","callback_data":"menu_principale "}]';
        $bot->sendMessage($user_idSafe, "ℹ️ <b>informazioni Account</b>\n\n📧<b>Account</b>: $email $scadenza $scadenzaPass", $tastiera, 'inline');
        return;
    }

    // AGGIUNGI ACCOUNT
    if ($a[0] == 'account_add' && $a[1] == '-1') {
        $email = filter_var($textSafe, FILTER_VALIDATE_EMAIL);
        if(!$email || strlen($email) < 5){
            $bot->deleteMessage($user_idSafe, $message_idSafe - 1);
            $tastiera = '[{"text":"🏚 menu principale 🏚","callback_data":"menu_principale "}]';
            $bot->sendMessage($user_idSafe, "👮 <b>Email non valida</b>\n\n<i>Inserisci una email valida.</i>", $tastiera, 'inline');
            return;
        }

        $exist = 0;
        $stmtClienti = $db->prepare("SELECT * FROM $tableClienti WHERE email = ?");
        $stmtClienti->bind_param("s", $email);
        $stmtClienti->execute();
        $resultClienti = $stmtClienti->get_result();
        if ($resultClienti && $resultClienti->num_rows > 0)
            $exist = 1;
        $stmtClienti->close();

        $stmtRichieste = $db->prepare("SELECT * FROM $tableRichieste WHERE email = ?");
        $stmtRichieste->bind_param("s", $email);
        $stmtRichieste->execute();
        $resultRichieste = $stmtRichieste->get_result();
        if ($resultRichieste && $resultRichieste->num_rows > 0)
            $exist = 1;
        $stmtRichieste->close();

        $tableOrdersClients = 'nextPlayShopClientiBot_orders';
        $tablePlexClients = 'nextPlayShopClientiBot_clientsPlex';

        $stmtOrdersClients = $db->prepare("SELECT * FROM $tableOrdersClients WHERE mail = ? AND product_name = 'plex'");
        $stmtOrdersClients->bind_param("s", $email);
        $stmtOrdersClients->execute();
        $resultOrdersClients = $stmtOrdersClients->get_result();
        if ($resultOrdersClients && $resultOrdersClients->num_rows > 0)
            $exist = 1;
        $stmtOrdersClients->close();

        $stmtPlexClients = $db->prepare("SELECT * FROM $tablePlexClients WHERE mail = ?");
        $stmtPlexClients->bind_param("s", $email);
        $stmtPlexClients->execute();
        $resultPlexClients = $stmtPlexClients->get_result();
        if ($resultPlexClients && $resultPlexClients->num_rows > 0)
            $exist = 1;
        $stmtPlexClients->close();

        if ($exist === 1) {
            $bot->deleteMessage($user_idSafe, $message_idSafe - 1);
            $tastiera = '[{"text":"✖️ annulla ✖️","callback_data":"annulla "}]';
            $bot->sendMessage($user_idSafe, "👮 <b>Email già registrata</b>\n\n<i>Inserisci una email differente</i>", $tastiera, 'inline');
            return;
        }

        $stmInsert = $db->prepare("INSERT INTO $tableRichieste (type, email) VALUES ('new', ?)");
        $stmInsert->bind_param("s", $email);
        $stmInsert->execute();
        $IDRichiesta = $stmInsert->insert_id;
        $stmInsert->close();

        $stm = $db->prepare("UPDATE $tableIscritti SET action = CONCAT('account_add ', ?) WHERE user_id = ?");
        $stm->bind_param("ii", $IDRichiesta, $user_idSafe);
        $stm->execute();
        $stm->close();

        $bot->deleteMessage($user_idSafe, $message_idSafe - 1);
        $tastiera = '[{"text":"3 mesi","callback_data":"account add 3mesi "}, {"text":"6 mesi","callback_data":"account add 6mesi "}], [{"text":"12 mesi","callback_data":"account add 12mesi "}], [{"text":"✖️ annulla ✖️","callback_data":"annulla "}]';
        $bot->sendMessage($user_idSafe, "➕ <b>Aggiungi account</b>\n\n<i>Inserisci durata normale dell'account</i>", $tastiera, 'inline');
        return;
    }

    // RINNOVA ACCOUNT
    if ($a[0] == 'account_update' and $a[1] == '-1') {
        $email = $textSafe;

        $stm1 = $db->prepare("SELECT * FROM $tableClienti WHERE email = ?");
        $stm1->bind_param("s", $email);
        $stm1->execute();
        $res1 = $stm1->get_result();
        $stm1->close();

        $stm2 = $db->prepare("SELECT * FROM $tableRichieste WHERE email = ?");
        $stm2->bind_param("s", $email);
        $stm2->execute();
        $res2 = $stm2->get_result();
        $stm2->close();

        if ($res1->num_rows > 0 && $res2->num_rows == 0) {
            $stm = $db->prepare("SELECT reseller FROM $tableClienti WHERE email = ?");
            $stm->bind_param("s", $email);
            $stm->execute();
            $res = $stm->get_result();
            $stm->close();
            $reseller = $res->fetch_assoc()['reseller'];
            if ($reseller != $user_idSafe) {
                $bot->deleteMessage($user_idSafe, $message_idSafe - 1);
                $tastiera = '[{"text":"✖️ annulla ✖️","callback_data":"annulla "}]';
                $bot->sendMessage($user_idSafe, "👮 <b>Email non valida</b>\n\n<i>Inserisci una email differente</i>", $tastiera, 'inline');
                die;
            }

            $stm = $db->prepare("INSERT INTO $tableRichieste (type, email) VALUES ('update', ?)");
            $stm->bind_param("s", $email);
            $stm->execute();
            $stm->close();

            $stm = $db->prepare("SELECT ID FROM $tableRichieste WHERE email = ? AND type = 'update'");
            $stm->bind_param("s", $email);
            $stm->execute();
            $res = $stm->get_result();
            $stm->close();
            $ID = (int) $res->fetch_assoc()['ID'];

            $stm = $db->prepare("UPDATE $tableIscritti SET action = CONCAT('account_update ', ?) WHERE user_id = ?");
            $stm->bind_param("ii", $ID, $user_idSafe);
            $stm->execute();
            $stm->close();

            $bot->deleteMessage($user_idSafe, $message_idSafe - 1);
            $tastiera = '[{"text":"1 mese","callback_data":"account update 1mese"}, {"text":"3 mesi","callback_data":"account update 3mesi "}], [{"text":"6 mesi","callback_data":"account update 6mesi "}], [{"text":"✖️ annulla ✖️","callback_data":"annulla "}]';
            $bot->sendMessage($user_idSafe, "🔄 <b>Rinnova account</b>\n\n<i>Inserisci durata normale dell'account</i>", $tastiera, 'inline');
        } else {
            $bot->deleteMessage($user_idSafe, $message_idSafe - 1);
            $tastiera = '[{"text":"✖️ annulla ✖️","callback_data":"annulla "}]';
            $bot->sendMessage($user_idSafe, "👮 <b>Email non valida</b>\n\n<i>Inserisci una email differente</i>", $tastiera, 'inline');
        }
        die;
    }

    // COUPON
    if (trim($a[0]) == 'coupon') {
        $coupon = trim($textSafe);

        $stm = $db->prepare("SELECT * FROM $tableCoupon WHERE code = ?");
        $stm->bind_param("s", $coupon);
        $stm->execute();
        $res = $stm->get_result();
        $res2 = $res->fetch_assoc();
        $ID = (int) $res2['ID'];
        $type = $res2['type'];
        $value = (int) $res2['value'];
        $stm->close();

        if ($res->num_rows > 0) {
            $stm = $db->prepare("SELECT * FROM $tableCouponUsati WHERE coupon_id = ? AND user_id = ?");
            $stm->bind_param("ii", $ID, $user_idSafe);
            $stm->execute();
            $resUsati = $stm->get_result();
            $stm->close();

            if ($resUsati->num_rows == 0) {
                $stm = $db->prepare("INSERT INTO $tableCouponUsati (coupon_id, user_id) VALUES (?, ?)");
                $stm->bind_param("ii", $ID, $user_idSafe);
                $stm->execute();
                $stm->close();

                $stm = $db->prepare("UPDATE $tableIscritti SET coin = coin + ?, action = NULL WHERE user_id = ?");
                $stm->bind_param("ii", $value, $user_idSafe);
                $stm->execute();
                $stm->close();

                $tipologia = "Crediti";
                $valore = $value;

                $bot->deleteMessage($user_idSafe, $message_idSafe - 1);
                $tastiera = '[{"text":"🏚 menu principale 🏚","callback_data":"menu_principale "}]';
                $bot->sendMessage($user_idSafe, "✅ <b>Coupon utilizzato con successo</b>\n\n<b>💳Coupon:</b> $coupon\n<b>💡Tipologia:</b>$tipologia\n<b>🎁Valore:</b> $valore coin", $tastiera, 'inline');

            } else {
                $bot->deleteMessage($user_idSafe, $message_idSafe - 1);
                $tastiera = '[{"text":"✖️ annulla ✖️","callback_data":"annulla "}]';
                $bot->sendMessage($user_idSafe, "👮 <b>Coupon già utilizzato</b>\n\n<i>Inserisci una coupon differente</i>", $tastiera, 'inline');
            }
        } else {
            $bot->deleteMessage($user_idSafe, $message_idSafe - 1);
            $tastiera = '[{"text":"✖️ annulla ✖️","callback_data":"annulla "}]';
            $bot->sendMessage($user_idSafe, "👮 <b>Coupon non valida</b>\n\n<i>Inserisci una coupon differente</i>", $tastiera, 'inline');
        }
    }

    // LOGIN RESELLER
    if (trim($a[0]) == 'login') {
        $bot->deleteMessage($user_idSafe, $message_idSafe - 1);

        $stmt = $db->prepare("SELECT hash FROM $tableIscritti WHERE user_id = ?");
        $stmt->bind_param("i", $user_idSafe);
        $stmt->execute();
        $hash = $stmt->get_result()->fetch_assoc()['hash'];
        $stmt->close();

        if ($hash == trim($textSafe) || $hash == $textSafe) {
            $stmtHash = $db->prepare("UPDATE $tableIscritti SET action = NULL, type = 1 WHERE user_id = ?");
            $stmtHash->bind_param("i", $user_idSafe);
            $stmtHash->execute();
            $stmtHash->close();
            $tastiera = '[{"text":"🔐 Entra 🔐","callback_data":"menu_principale"}]';
            $bot->sendMessage($user_idSafe, "✅ <b>Accesso Reseller effettuato con successo</b>", $tastiera, 'inline');
        } else {
            $tastiera = '[{"text":"✖️ annulla ✖️","callback_data":"annulla "}]';
            $bot->sendMessage($user_idSafe, "👮 <b>Codice di accesso non valido</b>\n\n<i>Per accedere come reseller è necessario inserire il codice di accesso personale.</i>", $tastiera, 'inline');
        }
        die;
    }
}
/*** END ACTION ***/

/*** START COMANDI ***/
// COMANDO /start
if (stripos($textSafe, "/start") === 0) {
    $stm = $db->prepare("SELECT type FROM $tableIscritti WHERE user_id = ?");
    $stm->bind_param('i', $user_idSafe);
    $stm->execute();
    $res = $stm->get_result()->fetch_assoc();
    $stm->close();

    if ($res['type'] == 1) { //reseller
        $tastiera = '[{"text":"🤖 Account 🤖","callback_data":"account prepre "}], [{"text":"🎁 Coupon 🎁","callback_data":"coupon "}], [{"text":"ℹ️ Info Account ℹ️","callback_data":"account info "}], [{"text":"👤 Account Personale👤","callback_data":"info "}]';
        $bot->sendMessage($user_idSafe, "<b>Benvenuto $nomeSafe</b> nel pannello Plex di NextPlay", $tastiera, 'inline');
    } else {
        $tastiera = '[{"text":"🔐 Accesso Reseller 🔐","callback_data":"login reseller "}], [{"text":"ℹ️ Info Account ℹ️","callback_data":"account info "}]';
        $bot->sendMessage($user_idSafe, "<b>Benvenuto $nomeSafe</b> nel pannello Plex di NextPlay.\\n\n<i>Per richiedere l'accesso contatta @NextPlayJellyFin_bot.</i>", $tastiera, 'inline');
    }
    die;
}

// COMANDO /admin
if (stripos($textSafe, '/admin') === 0 && ($user_idSafe == $admin1 || $user_idSafe == $admin2 || $user_idSafe == $dev)) {
    $tastiera = '[{"text":"🌐 Redirect 🌐","url":"http://enigmaelaboration.altervista.org/plexpanelnextplay/html/"}]';
    $bot->sendMessage($user_idSafe, "<b>Pannello admin</b>", $tastiera, 'inline');
    die;
}
/*** END COMANDI ***/


/*** START CALLBACK ***/
if ($callback_query_idSafe) {
    $q = explode(" ", $callback_dataSafe);

    // MENU LOGIN RESELLER
    if ($q[0] == 'login') {
        if ($q[1] == 'reseller') {
            $r = rand(10000000, 99999999);
            $stm = $db->prepare("SELECT hash FROM $tableIscritti WHERE user_id = ?");
            $stm->bind_param("i", $callback_user_idSafe);
            $stm->execute();
            $hash = $stm->get_result()->fetch_assoc()['hash'];
            $stm->close();
            if ($hash === NULL) {
                $stmHash = $db->prepare("UPDATE $tableIscritti SET hash = ? WHERE user_id = ?");
                $stmHash->bind_param("ii", $r, $callback_user_idSafe);
                $stmHash->execute();
                $stmHash->close();
                $hash = $r;
            }
            $stmUpdate = $db->prepare("UPDATE $tableIscritti SET action = 'login' WHERE user_id = ?");
            $stmUpdate->bind_param("i", $callback_user_idSafe);
            $stmUpdate->execute();
            $stm->close();

            $bot->sendMessage($admin1, "🔐 <b>Accesso Reseller richiesto</b>\n\nℹ️<b>Utente</b>: <a href='tg://user?id=$callback_user_idSafe'>$callback_nomeSafe</a>\n🧑‍💻<b>Codice:</b> <code>$hash</code>\n\n<i>Tale codice è personale e rimarrà invariato sino all'accesso da parte dell'utente.</i>");
            $bot->sendMessage($admin2, "🔐 <b>Accesso Reseller richiesto</b>\n\nℹ️<b>Utente</b>: <a href='tg://user?id=$callback_user_idSafe'>$callback_nomeSafe</a>\n🧑‍💻<b>Codice:</b> <code>$hash</code>\n\n<i>Tale codice è personale e rimarrà invariato sino all'accesso da parte dell'utente.</i>");
            $tastiera = '[{"text":"✖️ annulla ✖️","callback_data":"annulla "}]';
            $bot->editMessageText($callback_user_idSafe, $callback_message_idSafe, "🔐 <b>Accesso Reseller</b>\n\n<i>Inserisci il tuo codice di accesso personale</i>", $tastiera, 'inline');
        }
        die;
    }

    // MENU ACCOUNT
    if ($q[0] == 'account') {
        // MENU PRE
        if ($q[1] == 'prepre') {
            $tastiera = '[{"text":"continua ➡️","callback_data":"account pre "}], [{"text":"🏚 Menu Principale 🏚","callback_data":"menu_principale "}]';
            $bot->editMessageText($callback_user_idSafe, $callback_message_idSafe, "‼️ <b>Importante</b> ‼️\n\n<i>Se hai già acquistato un piano pass o remote watch pass presso plex.tv, ti invitiamo a contattare il supporto </i><b>prima</b><i> di effettuare l'acquisto</i>.\n\n🤖 <b>ChatBot:</b> @NextPlayJellyFin_bot", $tastiera, 'inline');
        }

        if ($q[1] == 'pre') {
            $tastiera = '[{"text":"➕ Aggiungi","callback_data":"account add "}, {"text":"Rinnova 🔄","callback_data":"account update "}], [{"text":"🔙 Torna Indietro","callback_data":"account prepre "}]';
            $bot->editMessageText($callback_user_idSafe, $callback_message_idSafe, "<b>🤖 Account 🤖</b>\n\n<i>Seleziona l'azione che vuoi eseguire</i>", $tastiera, 'inline');
        }

        // MENU ACCOUNT INFO
        if ($q[1] == 'info') {
            $stm = $db->prepare("UPDATE $tableIscritti SET action = 'info_account' WHERE user_id = ?");
            $stm->bind_param("i", $callback_user_idSafe);
            $stm->execute();
            $stm->close();
            $tastiera = '[{"text":"✖️ annulla ✖️","callback_data":"annulla "}]';
            $bot->editMessageText($callback_user_idSafe, $callback_message_idSafe, "ℹ️ <b>informazioni Account</b>\n\n<i>Inserisci email/username dell'account</i>", $tastiera, 'inline');
            die;
        }

        // MENU ACCOUNT ADD
        if ($q[1] == 'add') {
            $stm = $db->prepare("SELECT coin, action FROM $tableIscritti WHERE user_id = ?");
            $stm->bind_param("i", $callback_user_idSafe);
            $stm->execute();
            $res = $stm->get_result()->fetch_assoc();
            $stm->close();

            $coin = $res["coin"];
            $action = $res["action"];

            if ($coin <= 0) {
                $tastiera = '[{"text":"🏚 menu principale 🏚","callback_data":"menu_principale "}]';
                $bot->editMessageText($callback_user_idSafe, $callback_message_idSafe, "👮 Generazioni disponibili terminate.", $tastiera, 'inline');
                die;
            }
            if ($q[2] == '1mese' or $q[2] == '3mesi' or $q[2] == '6mesi' or $q[2] == '12mesi') {
                $ID = (int) explode(" ", $action)[1];
                $durata = $db->real_escape_string($q[2]);

                $stmUpdate = $db->prepare("UPDATE $tableRichieste SET durata = ? WHERE ID = ?");
                $stmUpdate->bind_param("si", $durata, $ID);
                $stmUpdate->execute();
                $stmUpdate->close();

                $tastiera = '[{"text":"3 mesi","callback_data":"account add Pass 3mesi "}, {"text":"6 mesi","callback_data":"account add Pass 6mesi "}], [{"text":"12 mesi","callback_data":"account add Pass 12mesi "}], [{"text":"✖️ annulla ✖️","callback_data":"annulla "}]';
                $bot->editMessageText($callback_user_idSafe, $callback_message_idSafe, "➕ <b>Aggiungi account</b>\n\n<i>Inserisci durata dell'account Pass</i>", $tastiera, 'inline');
            } else if ($q[2] == 'Pass' and ($q[3] == 'nullo' or $q[3] == '1mese' or $q[3] == '3mesi' or $q[3] == '6mesi' or $q[3] == '12mesi')) {
                $Pass = $db->real_escape_string($q[3]);

                $stmIscritti = $db->prepare("SELECT action, coin FROM $tableIscritti WHERE user_id = ?");
                $stmIscritti->bind_param("i", $callback_user_idSafe);
                $stmIscritti->execute();
                $res = $stmIscritti->get_result()->fetch_assoc();
                $stmIscritti->close();

                $ID = (int) explode(" ", $res['action'])[1];
                $coin = (int) $res['coin'];

                $stmRichieste = $db->prepare("SELECT * FROM $tableRichieste WHERE ID = ?");
                $stmRichieste->bind_param("i", $ID);
                $stmRichieste->execute();
                $res = $stmRichieste->get_result()->fetch_assoc();
                $stmRichieste->close();

                $email = $db->real_escape_string($res["email"]);
                $durata = $db->real_escape_string($res["durata"]);

                if (strpos($durata, 'mese') !== false) {
                    $duratadb = (int) trim(explode("mese", $durata)[0]);
                } else {
                    $duratadb = (int) trim(explode("mesi", $durata)[0]);
                }

                if ($Pass != 'nullo') {
                    if (strpos($Pass, 'mese') !== false) {
                        $Passdb = (int) trim(explode("mese", $Pass)[0]);
                    } else {
                        $Passdb = (int) trim(explode("mesi", $Pass)[0]);
                    }
                } else
                    $Passdb = $Pass;

                $coindb = 0;
                if ($duratadb == 1)
                    $coindb += 1;
                else if ($duratadb == 3)
                    $coindb += 3;
                else if ($duratadb == 6)
                    $coindb += 5;
                else if ($duratadb == 12)
                    $coindb += 9;

                if ($Passdb == 1)
                    $coindb += 1;
                else if ($Passdb == 3)
                    $coindb += 2;
                else if ($Passdb == 6)
                    $coindb += 4;
                else if ($Passdb == 12)
                    $coindb += 7;

                if ($coindb > $coin) {
                    $stm = $db->prepare("DELETE FROM $tableRichieste WHERE ID = ?");
                    $stm->bind_param("i", $ID);
                    $stm->execute();
                    $stm->close();

                    $stm = $db->prepare("UPDATE $tableIscritti SET action = NULL WHERE user_id = ?");
                    $stm->bind_param("i", $callback_user_idSafe);
                    $stm->execute();
                    $stm->close();


                    $tastiera = '[{"text":"🏚 menu principale 🏚","callback_data":"menu_principale "}]';
                    $bot->editMessageText($callback_user_idSafe, $callback_message_idSafe, "👮<b> Generazioni disponibili terminate.</b>\n\n<b>Coin disponibili:</b> $coin\n<b>Coin richiesti:</b> $coindb\n\n<i>Per continuare diminuisci la durata dell'account o effettua una ricarica.</i>", $tastiera, 'inline');
                    die;
                }

                $stm1 = $db->prepare("INSERT INTO $tableClienti (email, reseller) VALUES (?, ?)");
                $stm1->bind_param("si", $email, $callback_user_idSafe);
                $stm1->execute();
                $stm1->close();

                $stm2 = $db->prepare("UPDATE $tableIscritti SET action = NULL, coin = coin - ? WHERE user_id = ?");
                $stm2->bind_param("ii", $coindb, $callback_user_idSafe);
                $stm2->execute();
                $stm2->close();

                $stm3 = $db->prepare("UPDATE $tableRichieste SET durata = ?, durataPass = ? WHERE ID = ?");
                $stm3->bind_param("ssi", $duratadb, $Passdb, $ID);
                $stm3->execute();
                $stm3->close();

                $r1 = $bot->sendMessage($admin1, "🔔 <b>Nuovo account</b>\n\nℹ️<b>Reseller:</b> $usernameSafe\n📧<b>Account:</b> $email \n<b>⏳Durata:</b> $durata \n⏳<b>DurataPass:</b> $Pass \n💰<b>Coin spesi:</b> $coindb", $tastiera, 'inline');
                $r2 = $bot->sendMessage($admin2, "🔔 <b>Nuovo account</b>\n\nℹ️<b>Reseller:</b> $usernameSafe\n📧<b>Account:</b> $email \n<b>⏳Durata:</b> $durata \n⏳<b>DurataPass:</b> $Pass \n💰<b>Coin spesi:</b> $coindb", $tastiera, 'inline');

                $idMessage1 = (int) $r1['result']['message_id'];
                $idMessage2 = (int) $r2['result']['message_id'];
                $stm4 = $db->prepare("UPDATE $tableRichieste SET message_id1 = ?, message_id2 = ? WHERE email = ? AND type = 'new'");
                $stm4->bind_param("iis", $idMessage1, $idMessage2, $email);
                $stm4->execute();
                $stm4->close();

                $stm5 = $db->prepare("SELECT ID FROM $tableRichieste WHERE email = ? AND type = 'new'");
                $stm5->bind_param("s", $email);
                $stm5->execute();
                $res = $stm5->get_result();
                $stm5->close();

                $ID = (int) $res->fetch_assoc()['ID'];

                $tastiera = '[{"text":"✅ approva ✅","callback_data":"account approva ' . $ID . '"}], [{"text":"✖️ rifiuta ✖️","callback_data":"account rifiuta ' . $ID . ' ' . $coindb . '"}]';
                $bot->editMessageText($admin1, $idMessage1, "🔔 <b>Nuovo account</b>\n\nℹ️<b>Reseller:</b> $usernameSafe\n📧<b>Account:</b> $email \n<b>⏳Durata:</b> $durata \n⏳<b>DurataPass:</b> $Pass \n💰<b>Coin spesi:</b> $coindb", $tastiera, 'inline');
                $bot->editMessageText($admin2, $idMessage2, "🔔 <b>Nuovo account</b>\n\nℹ️<b>Reseller:</b> $usernameSafe\n📧<b>Account:</b> $email \n<b>⏳Durata:</b> $durata \n⏳<b>DurataPass:</b> $Pass \n💰<b>Coin spesi:</b> $coindb", $tastiera, 'inline');

                $tastiera = '[{"text":"🏚 menu principale 🏚","callback_data":"menu_principale "}]';
                $bot->editMessageText($callback_user_idSafe, $callback_message_idSafe, "✅ <b>Account inviato</b> ✅\n\n📧<b>Account</b>: $email\n⏳<b>Scadenza</b>: $durata\n⏰<b>Scadenza Pass</b>: $Pass\n💰<b>Coin spesi:</b> $coindb", $tastiera, 'inline');
            } else {
                $stm = $db->prepare("SELECT coin FROM $tableIscritti WHERE user_id = ?");
                $stm->bind_param("i", $callback_user_idSafe);
                $stm->execute();
                $res = $stm->get_result();
                $stm->close();
                $coin = $res->fetch_assoc()["coin"];

                if ($coin > 0) {
                    $stm = $db->prepare("UPDATE $tableIscritti SET action = 'account_add -1' WHERE user_id = ?");
                    $stm->bind_param("i", $callback_user_idSafe);
                    $stm->execute();
                    $stm->close();

                    $tastiera = '[{"text":"✖️ annulla ✖️","callback_data":"annulla "}]';
                    $bot->editMessageText($callback_user_idSafe, $callback_message_idSafe, "➕ <b>Aggiungi account</b>\n\n<i>Inserisci l'email dell'account</i>", $tastiera, 'inline');
                } else {
                    $tastiera = '[{"text":"🏚 menu principale 🏚","callback_data":"menu_principale "}]';
                    $bot->editMessageText($callback_user_idSafe, $callback_message_idSafe, "👮 Generazioni disponibili terminate.", $tastiera, 'inline');
                }
            }
        }

        // MENU ACCOUNT APPROVA AGGIUNTA
        if ($q[1] == 'approva') {
            $ID = (int) $q[2];

            $stm = $db->prepare("SELECT * FROM $tableRichieste WHERE ID = ?");
            $stm->bind_param("i", $ID);
            $stm->execute();
            $res = $stm->get_result()->fetch_assoc();
            $stm->close();

            $email = $res["email"];
            $durata = (int) $res["durata"];
            $durataPass = (int) $res["durataPass"];
            $message_id1 = (int) $res["message_id1"];
            $message_id2 = (int) $res["message_id2"];

            if (trim($durata) == 1)
                $scadenza = date('d/m/Y', strtotime('+' . $durata . ' month'));
            else
                $scadenza = date('d/m/Y', strtotime('+' . $durata . ' months'));

            if (trim($durataPass) == 1)
                $scadenzaPass = date('d/m/Y', strtotime('+' . $durataPass . ' month'));
            else
                $scadenzaPass = date('d/m/Y', strtotime('+' . $durataPass . ' months'));
            if ($durataPass == 'nullo')
                $scadenzaPass = 'no Pass';

            if ($durataPass == 'nullo') {
                $stm = $db->prepare("UPDATE $tableClienti SET scadenza = ? WHERE email = ?");
                $stm->bind_param("ss", $scadenza, $email);
                $stm->execute();
                $stm->close();
            } else {
                $stm = $db->prepare("UPDATE $tableClienti SET scadenza = ?, scadenzaPass = ?, Pass = 1 WHERE email = ?");
                $stm->bind_param("sss", $scadenza, $scadenzaPass, $email);
                $stm->execute();
                $stm->close();
            }

            $stm = $db->prepare("SELECT reseller FROM $tableClienti WHERE email = ?");
            $stm->bind_param("s", $email);
            $stm->execute();
            $res = $stm->get_result();
            $stm->close();

            $reseller = (int) $res->fetch_assoc()["reseller"];

            $stmReseller = $db->prepare("SELECT username FROM $tableIscritti WHERE user_id = ?");
            $stmReseller->bind_param("i", $reseller);
            $stmReseller->execute();
            $res = $stmReseller->get_result();
            $stm->close();

            $usernameReseller = $res->fetch_assoc()["username"];

            $stm = $db->prepare("DELETE FROM $tableRichieste WHERE ID = ?");
            $stm->bind_param("i", $ID);
            $stm->execute();
            $stm->close();

            $bot->editMessageText($admin1, $message_id1, "✅ <b>Account approvato</b> ✅\n\nℹ️<b>Reseller:</b> $usernameReseller\n📧<b>Account</b>: $email\n⏳<b>Scadenza</b>: $scadenza\n⏳<b>ScadenzaPass</b>: $scadenzaPass");
            $bot->editMessageText($admin2, $message_id2, "✅ <b>Account approvato</b> ✅\n\nℹ️<b>Reseller:</b> $usernameReseller\n📧<b>Account</b>: $email\n⏳<b>Scadenza</b>: $scadenza\n⏳<b>ScadenzaPass</b>: $scadenzaPass");
            $bot->sendMessage($reseller, "✅ <b>Account approvato</b> ✅\n\n📧<b>Account</b>: $email\n⏳<b>Scadenza</b>: $scadenza\n⏳<b>ScadenzaPass</b>: $scadenzaPass");
        }

        // MENU ACCOUNT APPROVA RINNOVO
        if ($q[1] == 'approva_rinnovo') {
            $ID = (int) $q[2];

            $stm = $db->prepare("SELECT * FROM $tableRichieste WHERE ID = ?");
            $stm->bind_param("i", $ID);
            $stm->execute();
            $res = $stm->get_result()->fetch_assoc();
            $stm->close();

            $email = $res["email"];
            $durata = (int) $res["durata"];
            $durataPass = (int) $res["durataPass"];
            $message_id1 = (int) $res["message_id1"];
            $message_id2 = (int) $res["message_id2"];

            $stm = $db->prepare("SELECT * FROM $tableClienti WHERE email = ?");
            $stm->bind_param("s", $email);
            $stm->execute();
            $res = $stm->get_result()->fetch_assoc();
            $stm->close();

            $scadenza = $res["scadenza"];
            $scadenzaPass = $res["scadenzaPass"];
            $reseller = (int) $res["reseller"];

            $date = DateTime::createFromFormat('d/m/Y', $scadenza);
            if ($date) {
                $date->modify('+' . $durata . ' months');
                $scadenza = $date->format('d/m/Y');
            } else {
                $bot->sendMessage($user_idSafe, "👮 <b>Errore</b>\n\n<i>Errore nel calcolo della scadenza normale.</i>");
                die;
            }

            if ($durataPass != 'nullo') {
                if ($scadenzaPass == '' || $scadenzaPass == NULL)
                    $scadenzaPass = date('d/m/Y', strtotime('+' . $durataPass . ' months'));
                else {
                    $date = DateTime::createFromFormat('d/m/Y', $scadenzaPass);
                    if ($date) {
                        $date->modify('+' . $durataPass . ' months');
                        $scadenzaPass = $date->format('d/m/Y');
                    } else {
                        $bot->sendMessage($user_idSafe, "👮 <b>Errore</b>\n\n<i>Errore nel calcolo della scadenza Pass.</i>");
                        die;
                    }
                }
            }

            if ($scadenzaPass == '' || $scadenzaPass == NULL)
                $scadenzaPass = 'no Pass';

            if ($durataPass == 'nullo') {
                $stm = $db->prepare("UPDATE $tableClienti SET scadenza = ? WHERE email = ?");
                $stm->bind_param("ss", $scadenza, $email);
                $stm->execute();
                $stm->close();
            } else {
                $stm = $db->prepare("UPDATE $tableClienti SET scadenza = ?, scadenzaPass = ?, Pass = 1 WHERE email = ?");
                $stm->bind_param("sss", $scadenza, $scadenzaPass, $email);
                $stm->execute();
                $stm->close();
            }

            $stm = $db->prepare("SELECT reseller FROM $tableClienti WHERE email = ?");
            $stm->bind_param("s", $email);
            $stm->execute();
            $res = $stm->get_result();
            $stm->close();

            $reseller = (int) $res->fetch_assoc()["reseller"];

            $stmReseller = $db->prepare("SELECT username FROM $tableIscritti WHERE user_id = ?");
            $stmReseller->bind_param("i", $reseller);
            $stmReseller->execute();
            $res = $stmReseller->get_result();
            $stm->close();

            $usernameReseller = $res->fetch_assoc()["username"];

            $stm = $db->prepare("DELETE FROM $tableRichieste WHERE ID = ?");
            $stm->bind_param("i", $ID);
            $stm->execute();
            $stm->close();

            $bot->editMessageText($admin1, $message_id1, "✅ <b>Rinnovo approvato</b> ✅\n\nℹ️<b>Reseller:</b> $usernameReseller\n📧<b>Account</b>: $email\n⏳<b>Scadenza</b>: $scadenza\n⏳<b>ScadenzaPass</b>: $scadenzaPass");
            $bot->editMessageText($admin2, $message_id2, "✅ <b>Rinnovo approvato</b> ✅\n\nℹ️<b>Reseller:</b> $usernameReseller\n📧<b>Account</b>: $email\n⏳<b>Scadenza</b>: $scadenza\n⏳<b>ScadenzaPass</b>: $scadenzaPass");
            $bot->sendMessage($reseller, "✅ <b>Rinnovo approvato</b> ✅\n\n📧<b>Account</b>: $email\n⏳<b>Scadenza</b>: $scadenza\n⏳<b>ScadenzaPass</b>: $scadenzaPass");
        }

        // MENU ACCOUNT RIFIUTA
        if ($q[1] == 'rifiuta') {
            $ID = (int) $q[2];
            $coindb = (int) $q[3];

            $stm = $db->prepare("SELECT * FROM $tableRichieste WHERE ID = ?");
            $stm->bind_param("i", $ID);
            $stm->execute();
            $res = $stm->get_result()->fetch_assoc();
            $stm->close();

            $email = $res["email"];
            $durata = (int) $res["durata"];
            $durataPass = (int) $res["durataPass"];
            $message_id1 = (int) $res["message_id1"];
            $message_id2 = (int) $res["message_id2"];

            if ($Pass == 'nullomese')
                $Pass = 'no Pass';

            $stm = $db->prepare("SELECT reseller FROM $tableClienti WHERE email = ?");
            $stm->bind_param("s", $email);
            $stm->execute();
            $res = $stm->get_result();
            $stm->close();

            $reseller = (int) $res->fetch_assoc()["reseller"];

            $stmReseller = $db->prepare("SELECT username FROM $tableIscritti WHERE user_id = ?");
            $stmReseller->bind_param("i", $reseller);
            $stmReseller->execute();
            $res = $stmReseller->get_result();
            $stm->close();

            $usernameReseller = $res->fetch_assoc()["username"];

            $stm = $db->prepare("DELETE FROM $tableClienti WHERE email = ?");
            $stm->bind_param("s", $email);
            $stm->execute();
            $stm->close();

            $stm = $db->prepare("UPDATE $tableIscritti SET coin = coin + ? WHERE user_id = ?");
            $stm->bind_param("ii", $coindb, $reseller);
            $stm->execute();
            $stm->close();

            $stm = $db->prepare("DELETE FROM $tableRichieste WHERE ID = ?");
            $stm->bind_param("i", $ID);
            $stm->execute();
            $stm->close();

            $bot->editMessageText($admin1, $message_id1, "❌ <b>Account rifiutato</b> ❌\n\nℹ️<b>Reseller:</b> $usernameReseller\n📧<b>Account</b>: $email\n⏳<b>Durata</b>: $durata\n⏳<b>DurataPass</b>: $Pass\n💰<b>Coin rimborsati:</b> $coindb");
            $bot->editMessageText($admin2, $message_id2, "❌ <b>Account rifiutato</b> ❌\n\nℹ️<b>Reseller:</b> $usernameReseller\n📧<b>Account</b>: $email\n⏳<b>Durata</b>: $durata\n⏳<b>DurataPass</b>: $Pass\n💰<b>Coin rimborsati:</b> $coindb");
            $bot->sendMessage($reseller, "❌ <b>Account rifiutato</b> ❌\n\n📧<b>Account</b>: $email\n⏳<b>Durata</b>: $durata\n⏳<b>DurataPass</b>: $Pass\n💰<b>Coin rimborsati:</b> $coindb");
        }

        // MENU ACCOUNT RIFIUTA RINNOVO
        if ($q[1] == 'rifiuta_rinnovo') {
            $ID = (int) $q[2];
            $coindb = (int) $q[3];

            $stm = $db->prepare("SELECT * FROM $tableRichieste WHERE ID = ?");
            $stm->bind_param("i", $ID);
            $stm->execute();
            $res = $stm->get_result()->fetch_assoc();
            $stm->close();

            $email = $res["email"];
            $message_id1 = (int) $res["message_id1"];
            $message_id2 = (int) $res["message_id2"];

            $stm = $db->prepare("SELECT * FROM $tableClienti WHERE email = ?");
            $stm->bind_param("s", $email);
            $stm->execute();
            $res = $stm->get_result()->fetch_assoc();
            $stm->close();

            $scadenza = $res["scadenza"];
            $scadenzaPass = $res["scadenzaPass"];
            $reseller = (int) $res["reseller"];

            if ($scadenzaPass == NULL)
                $scadenzaPass = 'no Pass';

            $stmReseller = $db->prepare("SELECT username FROM $tableIscritti WHERE user_id = ?");
            $stmReseller->bind_param("i", $reseller);
            $stmReseller->execute();
            $res = $stmReseller->get_result();
            $stm->close();

            $usernameReseller = $res->fetch_assoc()["username"];

            $stm = $db->prepare("UPDATE $tableIscritti SET coin = coin + ? WHERE user_id = ?");
            $stm->bind_param("ii", $coindb, $reseller);
            $stm->execute();
            $stm->close();

            $stm = $db->prepare("DELETE FROM $tableRichieste WHERE ID = ?");
            $stm->bind_param("i", $ID);
            $stm->execute();
            $stm->close();

            $bot->editMessageText($admin1, $message_id1, "❌ <b>Rinnovo rifiutato</b> ❌\n\nℹ️<b>Reseller:</b> $usernameReseller\n📧<b>Account</b>: $email\n⏳<b>Scadenza</b>: $scadenza\n⏳<b>ScadenzaPass</b>: $scadenzaPass\n💰<b>Coin rimborsati:</b> $coindb");
            $bot->editMessageText($admin2, $message_id2, "❌ <b>Rinnovo rifiutato</b> ❌\n\nℹ️<b>Reseller:</b> $usernameReseller\n📧<b>Account</b>: $email\n⏳<b>Scadenza</b>: $scadenza\n⏳<b>ScadebzaPass</b>: $scadenzaPass\n💰<b>Coin rimborsati:</b> $coindb");
            $bot->sendMessage($reseller, "❌ <b>Rinnovo rifiutato</b> ❌\n\n📧<b>Account</b>: $email\n⏳<b>Scadenza</b>: $scadenza\n⏳<b>ScadenzaPass</b>: $scadenzaPass\n💰<b>Coin rimborsati:</b> $coindb");
        }

        // MENU ACCOUNT UPDATE
        if ($q[1] == 'update') {
            $stm = $db->prepare("SELECT coin FROM $tableIscritti WHERE user_id = ?");
            $stm->bind_param("i", $callback_user_idSafe);
            $stm->execute();
            $res = $stm->get_result();
            $stm->close();

            $coin = $res->fetch_assoc()["coin"];
            if ($coin <= 0) {
                $tastiera = '[{"text":"🏚 menu principale 🏚","callback_data":"menu_principale "}]';
                $bot->editMessageText($callback_user_idSafe, $callback_user_idSafe, "👮 Generazioni disponibili terminate.", $tastiera, 'inline');
                die;
            }

            if ($q[2] == '1mese' or $q[2] == '3mesi' or $q[2] == '6mesi') {
                $stm = $db->prepare("SELECT action FROM $tableIscritti WHERE user_id = ?");
                $stm->bind_param("i", $callback_user_idSafe);
                $stm->execute();
                $res = $stm->get_result();
                $stm->close();

                $action = $res->fetch_assoc()["action"];
                $ID = (int) explode(" ", $action)[1];
                $durata = $db->real_escape_string(trim($q[2]));

                $stm = $db->prepare("UPDATE $tableRichieste SET durata = ? WHERE ID = ?");
                $stm->bind_param("si", $durata, $ID);
                $stm->execute();
                $stm->close();

                $tastiera = '[{"text":"no Pass","callback_data":"account update Pass nullo"}],[{"text":"3 mesi","callback_data":"account update Pass 3mesi "}, {"text":"6 mesi","callback_data":"account update Pass 6mesi "}], [{"text":"12 mesi","callback_data":"account update Pass 12mesi "}], [{"text":"✖️ annulla ✖️","callback_data":"annulla "}]';
                $bot->editMessageText($callback_user_idSafe, $callback_message_idSafe, "🔄 <b>Rinnova account</b>\n\n<i>Inserisci durata dell'account Pass</i>", $tastiera, 'inline');
            } else if ($q[2] == 'Pass' and ($q[3] == 'nullo' or $q[3] == '1mese' or $q[3] == '3mesi' or $q[3] == '6mesi' or $q[3] == '12mesi')) {
                $stm = $db->prepare("SELECT action FROM $tableIscritti WHERE user_id = ?");
                $stm->bind_param("i", $callback_user_idSafe);
                $stm->execute();
                $res = $stm->get_result();
                $stm->close();

                $action = $res->fetch_assoc()["action"];
                $ID = (int) explode(" ", $action)[1];

                $stm = $db->prepare("SELECT * FROM $tableRichieste WHERE ID = ?");
                $stm->bind_param("i", $ID);
                $stm->execute();
                $res = $stm->get_result()->fetch_assoc();
                $stm->close();

                $email = $res["email"];
                $durata = $res["durata"];

                if (strpos($durata, 'mese') !== false) {
                    $duratadb = explode("mese", $durata)[0];
                } else {
                    $duratadb = explode("mesi", $durata)[0];
                }

                $Pass = $q[3];
                if ($Pass != 'nullo') {
                    if (strpos($Pass, 'mese') !== false) {
                        $Passdb = explode("mese", $Pass)[0];
                    } else {
                        $Passdb = explode("mesi", $Pass)[0];
                    }
                } else {
                    $stmt = $db->prepare("SELECT * FROM $tableClienti WHERE email = ?");
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $res = $stmt->get_result()->fetch_assoc();
                    $stmt->close();
                    if ($res['scadenzaPass'] != NULL) {
                        $scadenzaPassDate = DateTime::createFromFormat('d/m/Y', $res['scadenzaPass']);
                        $currentDate = new DateTime();

                        if ($scadenzaPassDate < $currentDate) {
                            $bot->answerCallbackquery($bot->callback_query_id, "👮 Il Pass è scaduto, devi obbligatoriamente rinnovarlo.");
                            die;
                        }
                    }

                    $Passdb = $Pass;
                }

                $coindb = 0;
                if ($duratadb == 1)
                    $coindb += 1;
                else if ($duratadb == 3)
                    $coindb += 3;
                else if ($duratadb == 6)
                    $coindb += 5;

                if ($Passdb == 1)
                    $coindb += 1;
                else if ($Passdb == 3)
                    $coindb += 2;
                else if ($Passdb == 6)
                    $coindb += 4;
                else if ($Passdb == 12)
                    $coindb += 7;

                if ($coindb > $coin) {
                    $stm = $db->prepare("DELETE FROM $tableRichieste WHERE ID = ?");
                    $stm->bind_param("i", $ID);
                    $stm->execute();
                    $stm->close();

                    $stm = $db->prepare("UPDATE $tableIscritti SET action = NULL WHERE user_id = ?");
                    $stm->bind_param("i", $callback_user_idSafe);
                    $stm->execute();
                    $stm->close();

                    $tastiera = '[{"text":"🏚 menu principale 🏚","callback_data":"menu_principale "}]';
                    $bot->editMessageText($callback_user_idSafe, $callback_message_idSafe, "👮<b> Generazioni disponibili terminate.</b>\n\n<b>Coin disponibili:</b> $coin\n<b>Coin richiesti:</b> $coindb\n\n<i>Per continuare diminuisci la durata dell'account o effettua una ricarica.</i>", $tastiera, 'inline');
                    die;
                }

                $stm = $db->prepare("UPDATE $tableIscritti SET action = NULL, coin = coin - ? WHERE user_id = ?");
                $stm->bind_param("ii", $coindb, $callback_user_idSafe);
                $stm->execute();
                $stm->close();

                $stm = $db->prepare("UPDATE $tableRichieste SET durata = ?, durataPass = ? WHERE ID = ?");
                $stm->bind_param("ssi", $duratadb, $Passdb, $ID);
                $stm->execute();
                $stm->close();

                $r1 = $bot->sendMessage($admin1, "🔔 <b>Rinnovo account</b>\n\nℹ️<b>Reseller:</b> $usernameSafe\n📧<b>Account:</b> $email \n<b>⏳Durata:</b> $durata \n⏳<b>DurataPass:</b> $Pass \n💰<b>Coin spesi:</b> $coindb", $tastiera, 'inline');
                $r2 = $bot->sendMessage($admin2, "🔔 <b>Rinnovo account</b>\n\nℹ️<b>Reseller:</b> $usernameSafe\n📧<b>Account:</b> $email \n<b>⏳Durata:</b> $durata \n⏳<b>DurataPass:</b> $Pass \n💰<b>Coin spesi:</b> $coindb", $tastiera, 'inline');
                $idMessage1 = (int) $r1['result']['message_id'];
                $idMessage2 = (int) $r2['result']['message_id'];

                $stm = $db->prepare("UPDATE $tableRichieste SET message_id1 = ?, message_id2 = ? WHERE email = ? AND type = 'update'");
                $stm->bind_param("iis", $idMessage1, $idMessage2, $email);
                $stm->execute();
                $stm->close();

                $stm = $db->prepare("SELECT ID FROM $tableRichieste WHERE email = ? AND type = 'update'");
                $stm->bind_param("s", $email);
                $stm->execute();
                $res = $stm->get_result();
                $stm->close();

                $ID = (int) $res->fetch_assoc()['ID'];
                $tastiera = '[{"text":"✅ approva ✅","callback_data":"account approva_rinnovo ' . $ID . ' "}], [{"text":"✖️ rifiuta ✖️","callback_data":"account rifiuta_rinnovo ' . $ID . ' ' . $coindb . '"}]';

                $bot->editMessageText($admin1, $idMessage1, "🔔 <b>Rinnovo account</b>\n\nℹ️<b>Reseller:</b> $usernameSafe\n📧<b>Account:</b> $email \n<b>⏳Durata:</b> $durata \n⏳<b>DurataPass:</b> $Pass \n💰<b>Coin spesi:</b> $coindb", $tastiera, 'inline');
                $bot->editMessageText($admin2, $idMessage2, "🔔 <b>Rinnovo account</b>\n\nℹ️<b>Reseller:</b> $usernameSafe\n📧<b>Account:</b> $email \n<b>⏳Durata:</b> $durata \n⏳<b>DurataPass:</b> $Pass \n💰<b>Coin spesi:</b> $coindb", $tastiera, 'inline');

                $tastiera = '[{"text":"🏚 menu principale 🏚","callback_data":"menu_principale "}]';
                $bot->editMessageText($callback_user_idSafe, $callback_message_idSafe, "✅ <b>Account inviato</b> ✅\n\n📧<b>Account</b>: $email\n⏳<b>Scadenza</b>: $durata\n⏰<b>Scadenza Pass</b>: $Pass \n💰<b>Coin spesi:</b> $coindb", $tastiera, 'inline');
            } else {
                $stm = $db->prepare("UPDATE $tableIscritti SET action = 'account_update -1' WHERE user_id = ?");
                $stm->bind_param("i", $callback_user_idSafe);
                $stm->execute();
                $stm->close();

                $tastiera = '[{"text":"✖️ annulla ✖️","callback_data":"annulla "}]';
                $bot->editMessageText($callback_user_idSafe, $callback_message_idSafe, "🔄 <b>Rinnova account</b>\n\n<i>Inserisci email dell'account</i>", $tastiera, 'inline');
            }
        }
    }

    // MENU COUPON
    if ($q[0] == 'coupon') {
        $stm = $db->prepare("UPDATE $tableIscritti SET action = 'coupon' WHERE user_id = ?");
        $stm->bind_param("i", $callback_user_idSafe);
        $stm->execute();
        $stm->close();

        $tastiera = '[{"text":"✖️ annulla ✖️","callback_data":"annulla "}]';
        $bot->editMessageText($callback_user_idSafe, $callback_message_idSafe, "🎁 <b>Coupon</b> 🎁\n\n<i>Inserisci il coupon</i>", $tastiera, 'inline');
    }

    // MENU INFO
    if ($q[0] == 'info') {
        $stm = $db->prepare("SELECT coin FROM $tableIscritti WHERE user_id = ?");
        $stm->bind_param("i", $callback_user_idSafe);
        $stm->execute();
        $coin = $stm->get_result()->fetch_assoc()['coin'];
        $stm->close();

        $tastiera = '[{"text":"🏚 menu principale 🏚","callback_data":"menu_principale "}]';
        $bot->editMessageText($callback_user_idSafe, $callback_message_idSafe, "ℹ️ <b>Informazioni account</b> ℹ️\n\n👤<b>Nome:</b> $callback_nomeSafe $callback_cognomeSafe\n🆔<b>ID:</b> $callback_user_idSafe\n🌐<b>Username:</b> $usernameSafe\n💰<b>Crediti:</b> $coin", $tastiera, 'inline');
    }

    // MENU PRINCIPALE
    if ($q[0] == 'menu_principale') {
        $stm = $db->prepare("SELECT type FROM $tableIscritti WHERE user_id = ?");
        $stm->bind_param("i", $callback_user_idSafe);
        $stm->execute();
        $res = $stm->get_result()->fetch_assoc();
        $stm->close();

        if ($res['type'] == 1) { //reseller
            $tastiera = '[{"text":"🤖 Account 🤖","callback_data":"account prepre "}], [{"text":"🎁 Coupon 🎁","callback_data":"coupon "}], [{"text":"ℹ️ Info Account ℹ️","callback_data":"account info "}], [{"text":"👤 Account Personale👤","callback_data":"info "}]';
            $bot->editMessageText($callback_user_idSafe, $callback_message_idSafe, "<b>Benvenuto $callback_nomeSafe</b> nel pannello Plex di NextPlay", $tastiera, 'inline');
        } else {
            $tastiera = '[{"text":"🔐 Accesso Reseller 🔐","callback_data":"login reseller "}], [{"text":"ℹ️ Info Account ℹ️","callback_data":"account info "}]';
            $bot->editMessageText($callback_user_idSafe, $callback_message_idSafe, "<b>Benvenuto $callback_nomeSafe</b> nel pannello Plex di NextPlay.\n\n<i>Per richiedere l'accesso contatta @NextPlayJellyFin_bot.</i>", $tastiera, 'inline');
        }
    }

    // MENU ANNULLA
    if ($q[0] == 'annulla') {
        $stmAction = $db->prepare("SELECT action FROM $tableIscritti WHERE user_id = ?");
        $stmAction->bind_param("i", $callback_user_idSafe);
        $stmAction->execute();
        $res = $stmAction->get_result()->fetch_assoc();
        $stmAction->close();

        $ID = (int) trim(explode(" ", $res['action'])[1]);
        if ($ID != -1) {
            $stmRichieste = $db->prepare("DELETE FROM $tableRichieste WHERE ID = ?");
            $stmRichieste->bind_param("i", $ID);
            $stmRichieste->execute();
            $stmRichieste->close();
        }

        $stmUpdate = $db->prepare("UPDATE $tableIscritti SET action = NULL WHERE user_id = ?");
        $stmUpdate->bind_param("i", $callback_user_idSafe);
        $stmUpdate->execute();
        $stmUpdate->close();

        $bot->editMessageText($callback_user_idSafe, $callback_message_idSafe, "👮 <b>Operazione annullata</b> 👮\n\n<i>Digita /start per tornare al menu</i>", $tastiera, 'inline');
    }
}
/*** END CALLBACK ***/