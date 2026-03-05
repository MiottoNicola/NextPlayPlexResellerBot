<?php
include "../config.php";
?>

<?php include "../inc/header.php"; ?>

<div class="w3-padding w3-margin-top">
    <h1 class="w3-center">Reseller</h1>
    <a href="addReseller.php" class="w3-button w3-green w3-round w3-right">Aggiungi Reseller</a>
    <br /><br />
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
            <th>UserID</th>
            <th>Username</th>
            <th>Coin</th>
            <th>Hash</th>
            <th>Azioni</th>
        </tr>
        <?php
        $stm = $db->prepare("SELECT * FROM $tableIscritti WHERE type=1 ORDER BY ID");
        $stm->execute();
        $res = $stm->get_result();
        if ($res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
                echo '<tr>
                                    <td>' . $row['ID'] . '</td>
                                    <td>' . $row['user_id'] . '</td>
                                    <td>' . $row['username'] . '</td>
                                    <td>' . $row['coin'] . '</td>
                                    <td>' . $row['hash'] . '</td>
                                    <td>
                                        <a href="modificaReseller.php?id=' . $row['ID'] . '" class="w3-button w3-blue w3-round">Modifica</a>
                                        <a href="visualizzaReseller.php?id=' . $row['ID'] . '" class="w3-button w3-blue w3-round">Clienti</a>
                                        <a href="eliminaReseller.php?id=' . $row['ID'] . '" class="w3-button w3-red w3-round">Elimina</a>
                                    </td>
                                </tr>';
            }
        } else {
            echo '<tr>
                                <td colspan="6" class="w3-center">Nessun reseller trovato</td>
                            </tr>';
        }
        ?>
    </table>
</div>
<?php include "../inc/footer.php"; ?>