<?php
include "../config.php";
?>
<?php include "../inc/header.php"; ?>

<div class="w3-padding w3-margin-top">
    <h1 class="w3-center">Clienti</h1>
    <a href="addCliente.php" class="w3-button w3-green w3-round w3-right">Aggiungi Cliente</a>
    <br /> <br />
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
        $stm = $db->prepare("SELECT * FROM $tableClienti ORDER BY ID");
        $stm->execute();
        $res = $stm->get_result();
        if ($res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
                echo '<tr>
                                    <td>' . $row['ID'] . '</td>
                                    <td>' . $row['email'] . '</td>
                                    <td>' . $row['scadenza'] . '</td>
                                    <td>' . $row['scadenzaPass'] . '</td>';
                                    $stmt = $db->prepare("SELECT * FROM $tableIscritti WHERE user_id = ? AND type = 1 LIMIT 1");
                                    $stmt->bind_param("i", $row['reseller']);
                                    $stmt->execute();
                                    $resellerResult = $stmt->get_result();
                                    $stmt->close();
                                    if ($resellerResult->num_rows > 0) {
                                        $resellerRow = $resellerResult->fetch_assoc();
                                        $row['reseller'] = $resellerRow['username'];
                                    }
                            echo    '<td>' . $row['reseller'] . '</td>
                                    <td>
                                        <a href="modificaCliente.php?id=' . $row['ID'] . '" class="w3-button w3-blue w3-round">Modifica</a>
                                        <a href="eliminaCliente.php?id=' . $row['ID'] . '" class="w3-button w3-red w3-round">Elimina</a>
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
</div>
<?php include "../inc/footer.php"; ?>