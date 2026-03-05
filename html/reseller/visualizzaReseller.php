<?php
include "../config.php";

$id = (int) $_GET['id'];
$stm = $db->prepare("SELECT * FROM $tableIscritti WHERE ID = ?");
$stm->bind_param("i", $id);
$stm->execute();
$result = $stm->get_result()->fetch_assoc();
$stm->close();

?>

<?php include "../inc/header.php"; ?>
<div class="w3-padding w3-margin-top">
    <h1 class="w3-center">Clienti di <?php echo $result['username']; ?></h1>
    <?php
    if (isset($_GET['success'])) {
        echo '<div class="w3-panel w3-green w3-round w3-margin-top">
                            <p>' . $_GET['success'] . '</p>
                        </div>';
    }
    ?>
    <table class="w3-table w3-striped w3-hoverable w3-white">
        <tr>
            <th>ID</th>
            <th>Email</th>
            <th>Scadenza</th>
            <th>Scadenza Pass</th>
            <th>Reseller</th>
            <th>Azioni</th>
        </tr>
        <?php

        $stm = $db->prepare("SELECT * FROM $tableClienti WHERE reseller = " . $result['user_id'] . " ORDER BY ID");
        $stm->execute();
        $res = $stm->get_result();
        if ($res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
                echo '<tr>
                                    <td>' . $row['ID'] . '</td>
                                    <td>' . $row['email'] . '</td>
                                    <td>' . $row['scadenza'] . '</td>
                                    <td>' . $row['scadenzaPass'] . '</td>
                                    <td>' . $result['username'] . '</td>
                                    <td>
                                        <a href="../clienti/modificaCliente.php?id=' . $row['ID'] . '" class="w3-button w3-blue w3-round">Modifica</a>
                                        <a href="../clienti/eliminaCliente.php?id=' . $row['ID'] . '" class="w3-button w3-red w3-round">Elimina</a>
                                    </td>
                                </tr>';
            }
        } else {
            echo '<tr>
                                <td colspan="6" class="w3-center">Nessun cliente trovato</td>
                            </tr>';
        }
        ?>
    </table>
    <?php include "../inc/footer.php"; ?>