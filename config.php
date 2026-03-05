<?php
require_once '../bot_sourceCode/bot.php';

$admin1 = (int) 318975968;
$admin2 = (int) 178441112;
$dev    = (int) 131638922;

$bot = new Bot("7288803688:AAH7v28f0A6HgThoDci6YHzfm4_35Bqjtlw");

$db = mysqli_connect("localhost", "enigmaelaboration", "", "my_enigmaelaboration");
$tableIscritti      = "plexpanelnextplay_iscritti";
$tableClienti       = "plexpanelnextplay_clienti";
$tableRichieste     = 'plexpanelnextplay_richieste';
$tableCoupon        = 'plexpanelnextplay_coupon';
$tableCouponUsati   = 'plexpanelnextplay_couponUtilizzati';

if($db->connect_error) die;
$db->set_charset('utf8mb4');

if($bot->callback_query_id){
    $callback_query_idSafe      = (int) $bot->callback_query_id;
    $callback_dataSafe          = $db->real_escape_string($bot->callback_data);
    $callback_chat_idSafe       = (int) $bot->callback_chat_id;
    $callback_user_idSafe       = (int) $bot->callback_user_id;
    $callback_message_idSafe    = (int) $bot->callback_message_id;
    $callback_nomeSafe          = $db->real_escape_string( $bot->callback_nome);
    $callback_cognomeSafe       = $db->real_escape_string( $bot->callback_cognome);
    $usernameSafe               = $db->real_escape_string( $bot->callback_username);
    $textSafe                   = $db->real_escape_string( $bot->callback_text);
}else{
    $chat_idSafe            = (int) $bot->chat_id;
    $user_idSafe            = (int) $bot->from_id;
    $message_idSafe         = (int) $bot->message_id;
    $nomeSafe               = $db->real_escape_string( $bot->from_first_name);
    $cognomeSafe            = $db->real_escape_string( $bot->from_last_name);
    $usernameSafe           = $db->real_escape_string( $bot->chat_username);
    $textSafe               = $db->real_escape_string( $bot->text);
}

if($usernameSafe == '' || is_null($usernameSafe)){
    $bot->sendMessage($user_idSafe, "👮 <b>Per utilizzare il bot è necessario un username personale</b>\n\n<i>Per impostare un username vai nelle IMPOSTAZIONI di Telegram, quindi premi su USERNAME e scegline uno. Poi salva.</i>");
    $bot->sendMessage($callback_user_idSafe, "👮 <b>Per utilizzare il bot è necessario un username personale</b>\n\n<i>Per impostare un username vai nelle IMPOSTAZIONI di Telegram, quindi premi su USERNAME e scegline uno. Poi salva.</i>");
    die;
}

if($user_idSafe !== 0){
    $stmt = $db->prepare("SELECT username FROM $tableIscritti WHERE user_id = ?");
    $stmt->bind_param('i', $user_idSafe);
    $stmt->execute();
    $res = $stmt->get_result();
    

    if($res && $res->num_rows === 0){
        $stmtInsert = $db->prepare("INSERT INTO $tableIscritti (user_id, username) VALUES (?, ?)");
        $stmtInsert->bind_param('is', $user_idSafe, $usernameSafe);
        $stmtInsert->execute();
        $stmtInsert->close();    
    }else{ 
        $row = $res->fetch_assoc();
        if ($row['username'] !== $usernameSafe) {
            $stmtUpdate = $db->prepare("UPDATE $tableIscritti SET username = ? WHERE user_id = ?");
            $stmtUpdate->bind_param('si', $usernameSafe, $user_idSafe);
            $stmtUpdate->execute();
            $stmtUpdate->close();

            $bot->sendMessage($user_idSafe, "👮 <b>Rilevata modifica alle informazioni del tuo account Telegram.</b>\n\n<i>Aggiornamento database effettuato.</i>");
        }
    }
    $stmt->close(); 
}

if($callback_query_idSafe){
    $stmt = $db->prepare("SELECT username FROM $tableIscritti WHERE user_id = ?");
    $stmt->bind_param("i", $callback_query_idSafe);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if($row['username']!= $usernameSafe){
        $stmtUpdate = $db->prepare("UPDATE $tableIscritti SET username = ? WHERE user_id = ?");
        $stmtUpdate->bind_param('si', $usernameSafe, $callback_query_idSafe);
        $stmtUpdate->execute();
        $stmtUpdate->close();
    }
}