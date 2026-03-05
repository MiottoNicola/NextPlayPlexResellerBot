<?php
set_time_limit(0);

require "bot.php";
$bot = new Bot("7288803688:AAH7v28f0A6HgThoDci6YHzfm4_35Bqjtlw");

$db = mysqli_connect("localhost", "enigmaelaboration", "", "my_enigmaelaboration");
$tableIscritti      = "plexpanelnextplay_iscritti";
$tableClienti       = "plexpanelnextplay_clienti";
$tableRichieste     = 'plexpanelnextplay_richieste';

$admin1 = 318975968;
$admin2 = 178441112;
$dev = NULL;

// Date di riferimento
$oggi = date('d/m/Y');
$domani = date('d/m/Y', strtotime('+1 day'));
$ieri = date('d/m/Y', strtotime('-1 day'));

// Funzione per scrivere nel log
function scriviLog($messaggio) {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $messaggio\n";
    file_put_contents('log.txt', $logEntry, FILE_APPEND | LOCK_EX);
}

// Inizializzazione log
scriviLog("=== INIZIO CONTROLLO SCADENZE ===");

$query = mysqli_query($db, "SELECT * FROM $tableClienti");
while($row = mysqli_fetch_array($query)){
    $email = $row['email'];
    $scadenza = trim($row['scadenza']);
    $scadenzaPass = trim($row['scadenzaPass']);
    $reseller = $row['reseller'];
    $id = $row['id'];

    if($scadenzaPass == "" || $scadenzaPass == null){
        $scadenzaPass = 'ASSENTE';
    }

    // Controllo notifica il giorno prima della scadenza (messaggio unificato)
    $notificaDomani = false;
    $messaggioNotifica = "⚠️ <b>Notifica Scadenza</b> ⚠️\n📧 Email: $email\n";
    
    if($scadenza == $domani) {
        $messaggioNotifica .= "📅 Scadenza Account: $scadenza ⏰ <i>Scade domani!</i>\n";
        $notificaDomani = true;
    }
    
    if($scadenzaPass != 'ASSENTE' && $scadenzaPass == $domani) {
        $messaggioNotifica .= "🎫 Scadenza Pass: $scadenzaPass ⏰ <i>Pass scade domani!</i>\n";
        $notificaDomani = true;
    }
    
    if($notificaDomani) {
        $bot->sendMessage($admin1, $messaggioNotifica);
        $bot->sendMessage($admin2, $messaggioNotifica);
        scriviLog("NOTIFICA SCADENZA DOMANI: $email (Account: $scadenza, Pass: $scadenzaPass)");
    }

    // Controllo account scaduti (messaggio unificato)
    $accountScaduto = false;
    $messaggioScaduto = "🔴 <b>Account SCADUTO</b> 🔴\n📧 Email: $email\n";
    
    if($scadenza == $ieri || $scadenza == $oggi) {
        $status = ($scadenza == $oggi) ? "oggi" : "ieri";
        $messaggioScaduto .= "📅 Scadenza Account: $scadenza ❌ <i>Account scaduto $status!</i>\n";
        $accountScaduto = true;
    }
    
    if($scadenzaPass != 'ASSENTE' && ($scadenzaPass == $ieri || $scadenzaPass == $oggi)) {
        $status = ($scadenzaPass == $oggi) ? "oggi" : "ieri";
        $messaggioScaduto .= "🎫 Scadenza Pass: $scadenzaPass ❌ <i>Pass scaduto $status!</i>\n";
        $accountScaduto = true;
    }
    
    if($accountScaduto) {
        $bot->sendMessage($admin1, $messaggioScaduto);
        $bot->sendMessage($admin2, $messaggioScaduto);
        scriviLog("ACCOUNT SCADUTO: $email (Account: $scadenza, Pass: $scadenzaPass)");
    }

    // Controllo date troppo vecchie (più di 7 giorni fa) - notifica unificata al dev
    $setteGiorniFa = date('d/m/Y', strtotime('-7 days'));
    $dataScadenzaTimestamp = DateTime::createFromFormat('d/m/Y', $scadenza);
    $setteGiorniFaTimestamp = DateTime::createFromFormat('d/m/Y', $setteGiorniFa);
    
    $alertDev = false;
    $messaggioAlertDev = "🛠️ <b>ALERT DEV - Date Troppo Vecchie</b> 🛠️\n📧 Email: $email\n Reseller: $reseller\n\n";
    
    if($dataScadenzaTimestamp && $dataScadenzaTimestamp < $setteGiorniFaTimestamp) {
        $messaggioAlertDev .= "📅 Scadenza Account: $scadenza ⚠️ <i>Più vecchia di 7 giorni</i>\n";
        $alertDev = true;
    }
    
    // Controllo anche la scadenza del Pass se presente
    if($scadenzaPass != 'ASSENTE') {
        $dataScadenzaPassTimestamp = DateTime::createFromFormat('d/m/Y', $scadenzaPass);
        if($dataScadenzaPassTimestamp && $dataScadenzaPassTimestamp < $setteGiorniFaTimestamp) {
            $messaggioAlertDev .= "🎫 Scadenza Pass: $scadenzaPass ⚠️ <i>Più vecchia di 7 giorni</i>\n";
            $alertDev = true;
        }
    }
    
    if($alertDev) {
        $messaggioAlertDev .= "\n🔍 <i>Verificare database per possibili errori</i>";
        $bot->sendMessage($dev, $messaggioAlertDev);
        scriviLog("DEV ALERT DATE VECCHIE: $email (Account: $scadenza, Pass: $scadenzaPass)");
    }
}

// Log finale
scriviLog("=== FINE CONTROLLO SCADENZE ===");

// Chiusura connessione database
mysqli_close($db);