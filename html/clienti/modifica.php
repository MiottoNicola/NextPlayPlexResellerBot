<?

include "../config.php";

if(isset($_POST['email']) && isset($_POST['scadenza']) && isset($_POST['scadenzaPass']) && isset($_POST['id']) && isset($_POST['reseller'])){
    $email = $db->real_escape_string($_POST['email']);
    $scadenza = $db->real_escape_string($_POST['scadenza']);
    $scadenzaPass = $db->real_escape_string($_POST['scadenzaPass']);
    $id = (int) $_POST['id'];
    $reseller = (int) $_POST['reseller'];

    $escapeScadenza = explode("/", $scadenza);
    if(!isset($escapeScadenza[0]) || !isset($escapeScadenza[1]) || !isset($escapeScadenza[2])){
        header("Location: error.php?error=Data di scadenza non valida!");
        exit;
    }

    $escapeScadenzaPass = explode("/", $scadenzaPass);
    if(!empty($scadenzaPass) && (!isset($escapeScadenzaPass[0]) || !isset($escapeScadenzaPass[1]) || !isset($escapeScadenzaPass[2]))){
        header("Location: error.php?error=Data di scadenza pass non valida!");
        exit;
    }

    if(empty($scadenzaPass)){
        $scadenzaPass = NULL;
    }
    $stm = $db->prepare("UPDATE $tableClienti SET email = ?, scadenza = ?, scadenzaPass = ?, reseller = ? WHERE ID = ?");
    $stm->bind_param("sssii", $email, $scadenza, $scadenzaPass, $reseller, $id);
    $stm->execute();
    $stm->close();

    header("Location: clienti.php?success=Cliente ".$email." modificato con successo!");
}else{
    header("Location: error.php?error=Campi non compilati nel form di modifica!");
}