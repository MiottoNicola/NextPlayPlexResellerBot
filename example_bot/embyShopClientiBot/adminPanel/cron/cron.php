<?php
include_once '../config/db.php';
include_once '../config/secretKey.php';
include_once '../../embyFunction/embyFunction.php';
set_time_limit(0);

/* CHECK EXPIRATION DATES */
// Notifica se expiration scade tra 7 giorni
$stmt = $db->prepare("SELECT * FROM $tableClients WHERE DATE(expiration) = DATE_ADD(CURDATE(), INTERVAL 7 DAY)");
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $chatId = $row['user_id'];
        $message = "⏰ <b>Notifica di Scadenza</b> ⏰\n\n👤 <b>Utente:</b> " . htmlspecialchars($row['mail']) . "\n📅 <b>Scadenza:</b> " . invertiData($row['expiration']) . "\n\n<i>Rinnova il tuo account per continuare a utilizzare il servizio.</i>";
        $url = "https://api.telegram.org/bot$botToken/sendMessage?chat_id={$chatId}&text=" . urlencode($message) . "&parse_mode=HTML";
        file_get_contents($url);
    }
}

// Notifica se expiration scade tra 1 giorno
$stmt = $db->prepare("SELECT * FROM $tableClients WHERE DATE(expiration) = DATE_ADD(CURDATE(), INTERVAL 1 DAY)");
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $chatId = $row['user_id'];
        $message = "⏰ <b>Notifica di Scadenza</b> ⏰\n\n👤 <b>Utente:</b> " . htmlspecialchars($row['mail']) . "\n📅 <b>Scadenza:</b> " . invertiData($row['expiration']) . "\n\n<i>Rinnova il tuo account per continuare a utilizzare il servizio.</i>";
        $url = "https://api.telegram.org/bot$botToken/sendMessage?chat_id={$chatId}&text=" . urlencode($message) . "&parse_mode=HTML";
        file_get_contents($url);
    }
}

// Notifica se expiration scaduto
$stmt = $db->prepare("SELECT * FROM $tableClients WHERE DATE(expiration) < CURDATE()");
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $chatId = $row['user_id'];
        if(delete_user($row['embyID'])) {
            $stmtDelete = $db->prepare("DELETE FROM $tableClients WHERE ID = ?");
            $stmtDelete->bind_param("i", $row['ID']);
            $stmtDelete->execute();
            $stmtDelete->close();

            $message = "⚠️ <b>Account Scaduto</b> ⚠️\n\n👤 <b>Utente:</b> " . htmlspecialchars($row['mail']) . "\n📅 <b>Data di scadenza:</b> " . invertiData($row['expiration']) . "\n\n<i>Il tuo account è scaduto e non è più attivo.\nSe desideri continuare a utilizzare il servizio avvia un nuovo ordine.</i>";
            $url = "https://api.telegram.org/bot$botToken/sendMessage?chat_id={$chatId}&text=" . urlencode($message) . "&parse_mode=HTML";
            file_get_contents($url);
        }else{
            $message = "⚠️ <b>Errore nella cancellazione dell'account</b> ⚠️\n\n👤 <b>Utente:</b> " . htmlspecialchars($row['mail']) . "\n📅 <b>Data di scadenza:</b> " . invertiData($row['expiration']) . "\n\n<i>Si è verificato un errore durante la cancellazione del tuo account.</i>";
            $url = "https://api.telegram.org/bot$botToken/sendMessage?chat_id={$dev}&text=" . urlencode($message) . "&parse_mode=HTML";
            file_get_contents($url);
        }
    }
}
?>