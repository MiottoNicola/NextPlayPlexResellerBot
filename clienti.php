<?php

include "config.php";

$type = $_GET['type'];
$hash = trim($_GET['hash']);

echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">';


if($hash != $admin1 && $hash != $admin2 && $hash != $dev){
    die("Accesso Negato - Hash non valido");
}

if($type == null){
    die("Accesso Negato - Tipo non specificato");
}

if($type!="completa" &&$type != "clienti" && $type != "reseller" && $type != "scaduti"){
    die("Accesso Negato - Tipo non valido");
}

if($type == "completa"){
    $query = mysqli_query($db, "SELECT * FROM $tableClienti");
    echo '<div class="container mt-5">';
    echo '<h1>Clienti</h1>';
    echo '<table class="table table-striped">';
    echo '<thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>Email</th>
                <th>Scadenza</th>
                <th>Scadenza Pass</th>
                <th>Reseller</th>
            </tr>
          </thead>';
    echo '<tbody>';
    while($row = mysqli_fetch_array($query)){
        $id = $row['ID'];
        $email = $row['email'];
        $scadenza = trim($row['scadenza']);
        $scadenzaPass = trim($row['scadenzaPass']);
        $reseller = $row['reseller'];

        echo "<tr>
                <td>$id</td>
                <td>$email</td>
                <td>$scadenza</td>
                <td>$scadenzaPass</td>
                <td>$reseller</td>
              </tr>";
    }
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}else if($type == "clienti"){
    $query = mysqli_query($db, "SELECT * FROM $tableClienti WHERE scadenza IS NOT NULL");
    echo '<div class="container mt-5">';
    echo '<h1>Clienti</h1>';
    echo '<table class="table table-striped">';
    echo '<thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>Email</th>
                <th>Scadenza</th>
                <th>Scadenza Pass</th>
                <th>Reseller</th>
            </tr>
          </thead>';
    echo '<tbody>';
    while($row = mysqli_fetch_array($query)){
        $id = $row['ID'];
        $email = $row['email'];
        $scadenza = trim($row['scadenza']);
        $scadenzaPass = trim($row['scadenzaPass']);
        $reseller = $row['reseller'];

        echo "<tr>
                <td>$id</td>
                <td>$email</td>
                <td>$scadenza</td>
                <td>$scadenzaPass</td>
                <td>$reseller</td>
              </tr>";
    }
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}else if($type == "reseller"){
    $query = mysqli_query($db, "SELECT * FROM $tableIscritti WHERE type=1");
    echo '<div class="container mt-5">';
    echo '<h1>Reseller</h1>';
    echo '<table class="table table-striped">';
    echo '<thead class="thead-dark">
            <tr>
                <th>UserID</th>
                <th>Username</th>
                <th>Credito</th>
                <th></th>
            </tr>
          </thead>';
    echo '<tbody>';
    while($row = mysqli_fetch_array($query)){
        $user_id = $row['user_id'];
        $username = $row['username'];
        $credito = trim($row['coin']);

        echo "<tr>
                <td>$user_id</td>
                <td>$username</td>
                <td>$credito</td>
                <td>
                    <a href='reseller.php?type=clienti&hash=$hash&reseller=$user_id' class='btn btn-primary btn-sm'>Visualizza Clienti</a>
                    <a href='reseller.php?type=modifica_credito&hash=$hash&reseller=$user_id' class='btn btn-primary btn-sm'>Modifica Credito</a>
                </td>
            </tr>";
    }
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}else if($type='scaduti'){
    $query = mysqli_query($db, "SELECT * FROM $tableClienti WHERE scadenza IS NOT NULL AND STR_TO_DATE(scadenza, '%d/%m/%Y') < NOW()");
    echo '<div class="container mt-5">';
    echo '<table class="table table-striped">';
    echo '<thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>Email</th>
                <th>Scadenza</th>
                <th>Scadenza Pass</th>
                <th>Reseller</th>
            </tr>
          </thead>';
    echo '<tbody>';
    while($row = mysqli_fetch_array($query)){
        $id = $row['ID'];
        $email = $row['email'];
        $scadenza = trim($row['scadenza']);
        $scadenzaPass = trim($row['scadenzaPass']);
        $reseller = $row['reseller'];

        echo "<tr>
                <td>$id</td>
                <td>$email</td>
                <td>$scadenza</td>
                <td>$scadenzaPass</td>
                <td>$reseller</td>
              </tr>";
    }
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}

?>