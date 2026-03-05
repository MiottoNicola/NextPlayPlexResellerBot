<?php
include_once 'utils/botApi.php';
include_once 'utils/log.php';

$bot = new Bot('7992035052:AAGbyj7qqmD5AFJcDYsdOZksAZjX1F6Xgq8');
$admins = [131638922];
$logDirectory = 'logs/';

$db = new mysqli('localhost', 'enigmaelaboration', 's9z63q3UN8Kq', 'my_enigmaelaboration');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}
$db->set_charset('utf8mb4');

$tableUsers             = 'nextPlayShopClientiBot_users';
$tableProducts          = 'nextPlayShopClientiBot_products';
$tableCoupons           = 'nextPlayShopClientiBot_coupons';
$tableUsedCoupons       = 'nextPlayShopClientiBot_usedCoupons';
$tableCarts             = 'nextPlayShopClientiBot_carts';
$tableOrders            = 'nextPlayShopClientiBot_orders';
$tableClientsEmby       = 'nextPlayShopClientiBot_clientsEmby';
$tableClientsPlex       = 'nextPlayShopClientiBot_clientsPlex';

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
    logMessage("$user_idSafe non possiede un username", 'WARNING', 'BOT', $logDirectory);
    die;
}

if($user_idSafe != 0){
    $stmt = $db->prepare("SELECT username, isAdmin FROM $tableUsers WHERE user_id = ?");
    $stmt->bind_param('i', $user_idSafe);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if($res && $res->num_rows === 0){
        $stmtInsert = $db->prepare("INSERT INTO $tableUsers (user_id, username) VALUES (?, ?)");
        $stmtInsert->bind_param('is', $user_idSafe, $usernameSafe);
        $stmtInsert->execute();
        $stmtInsert->close();  
        logMessage("$user_idSafe e' stato inserito nel database", 'OK', 'BOT', $logDirectory); 
        $isAdmin = 0;
        $isReseller = 0;
    }else{ 
        $row = $res->fetch_assoc();
        $usernamedb = $row['username'];
        $isAdmin = $row['isAdmin'];
        if ($usernamedb !== $usernameSafe) {
            $stmtUpdate = $db->prepare("UPDATE $tableUsers SET username = ? WHERE user_id = ?");
            $stmtUpdate->bind_param('si', $usernameSafe, $user_idSafe);
            $stmtUpdate->execute();
            $stmtUpdate->close();

            logMessage("$user_idSafe ha modificato il proprio username, database aggiornato", 'OK', 'BOT', $logDirectory);
        }
    }
    $stmt->close(); 
}

if($callback_query_idSafe){
    $stmt = $db->prepare("SELECT username FROM $tableUsers WHERE user_id = ?");
    $stmt->bind_param("i", $callback_user_idSafe);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if($row['username']!= $usernameSafe){
        $stmtUpdate = $db->prepare("UPDATE $tableUsers SET username = ? WHERE user_id = ?");
        $stmtUpdate->bind_param('si', $usernameSafe, $callback_user_idSafe);
        $stmtUpdate->execute();
        $stmtUpdate->close();

        logMessage("$callback_user_idSafe ha modificato il proprio username, database aggiornato", 'OK', 'BOT', $logDirectory);
    }
}