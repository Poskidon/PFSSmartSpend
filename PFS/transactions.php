<?php
session_start();

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$database = "smartspenddb";

// Créer la connexion
$conn = new mysqli($servername, $username, $password, $database);

// Vérifier la connexion
if ($conn->connect_error) {
    die("La connexion a échoué : " . $conn->connect_error);
}

$message = "";

// Traitement de la suppression
if (isset($_POST['supprimer'])) {
    $transactionID = $_POST['transaction_id'];
    $sql_delete = "DELETE FROM transactions WHERE TransactionID = $transactionID";

    if ($conn->query($sql_delete) === TRUE) {
        // Rediriger vers la même page après la suppression réussie
        header("Location: $_SERVER[PHP_SELF]");
        exit();
    } else {
        $message = "<div class='erreur'>Erreur lors de la suppression de la transaction : " . $conn->error . "</div>";
    }
}

// Traitement de la modification
if (isset($_POST['modifier'])) {
    $transactionID = $_POST['transaction_id'];
    // Récupérer les nouvelles valeurs des champs du formulaire
    $nouveauMontant = $_POST['nouveau_montant'];
    $nouvelleDate = $_POST['nouvelle_date'];
    $nouvelleDescription = $_POST['nouvelle_description'];

    $sql_update = "UPDATE transactions SET Montant = $nouveauMontant, Date = '$nouvelleDate', Description = '$nouvelleDescription' WHERE TransactionID = $transactionID";

    if ($conn->query($sql_update) === TRUE) {
        // Rediriger vers la même page après la modification réussie
        header("Location: $_SERVER[PHP_SELF]");
        exit();
    } else {
        $message = "<div class='erreur'>Erreur lors de la modification de la transaction : " . $conn->error . "</div>";
    }
}

// Récupérer les transactions de l'utilisateur connecté
$userID = $_SESSION['user_id'];
$sql_select = "SELECT * FROM transactions WHERE UserID = $userID";
$result = $conn->query($sql_select);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartSpend - Espace Personnel</title>
    <link rel="stylesheet" href="transaction.css">
    <style>
        
    </style>
</head>
<body>
    <header>
        <h1>Espace Personnel - SmartSpend</h1>
        <nav>
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="espace_personnel.php">Espace Personnel</a></li>
                <li><a href="objectifs_financiers.php">Objectifs Financiers</a></li>
                <li><a href="alertes.php">Alertes</a></li>
                <li><a href="transactions.php">Transactions</a></li>
                <li><a href="deconnexion.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <section id="transactions">
            <h2>Mes Transactions</h2>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='transaction'>";
                    echo "<table class='transactions-table'>";
                    echo "<thead>";
                    echo "<tr>";
                    echo "<th>Date</th>";
                    echo "<th>Montant</th>";
                    echo "<th>Description</th>";
                    echo "<th>Actions</th>";
                    echo "</tr>";
                    echo "</thead>";
                    echo "<tbody>";
                    echo "<tr>";
                    echo "<td>" . $row["Date"] . "</td>";
                    echo "<td>" . $row["Montant"] . "</td>";
                    echo "<td>" . $row["Description"] . "</td>";
                    echo "<td>";
                    echo "<button class='modifier-btn' data-transaction-id='" . $row["TransactionID"] . "'>Modifier</button>";
                    echo "<form action='' method='post'>";
                    echo "<input type='hidden' name='transaction_id' value='" . $row["TransactionID"] . "'>";
                    echo "<button type='submit' name='supprimer'>Supprimer</button>";
                    echo "</form>";
                    echo "</td>";
                    echo "</tr>";
                    echo "<tr class='modification-form' id='form-" . $row["TransactionID"] . "'>";
                    echo "<td colspan='4'>";
                    echo "<form action='' method='post'>";
                    echo "<input type='hidden' name='transaction_id' value='" . $row["TransactionID"] . "'>";
                    echo "<label for='nouveau_montant'>Nouveau Montant :</label>";
                    echo "<input type='number' id='nouveau_montant' name='nouveau_montant' required>";
                    echo "<label for='nouvelle_date'>Nouvelle Date :</label>";
                    echo "<input type='date' id='nouvelle_date' name='nouvelle_date' required>";
                    echo "<label for='nouvelle_description'>Nouvelle Description :</label>";
                    echo "<input type='text' id='nouvelle_description' name='nouvelle_description' required>";
                    echo "<button type='submit' name='modifier'>Modifier</button>";
                    echo "</form>";
                    echo "</td>";
                    echo "</tr>";
                    echo "</tbody>";
                    echo "</table>";
                    echo "</div>";
                }
            } else {
                echo "<p>Aucune transaction disponible.</p>";
            }
            ?>
        </section>
    </main>
    <footer>
        <p>© 2024 SmartSpend. Tous droits réservés.</p>
    </footer>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var modifierBtns = document.querySelectorAll('.modifier-btn');
            modifierBtns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var transactionID = this.getAttribute('data-transaction-id');
                    var form = document.getElementById('form-' + transactionID);
                    if (form.classList.contains('show')) {
                        form.classList.remove('show');
                    } else {
                        form.classList.add('show');
                    }
                });
            });
        });
    </script>
</body>
</html>
