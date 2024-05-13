<?php
session_start();
if(!isset($_SESSION['user_id']))
{
    header("Location: index.php");
    exit();
}
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

if (isset($_POST['ajouter'])) {
    // Récupérer les valeurs des champs du formulaire
    $categorieID = $_POST['categorie']; // Nom de la catégorie sélectionnée
    $montant = $_POST['montant'];
    $date = $_POST['date'];
    $description = $_POST['description'];

        // Utiliser les valeurs dans la requête d'insertion
        $sql_insert = "INSERT INTO transactions (UserID, CategorieID, Montant, Date, Description) VALUES ({$_SESSION['user_id']}, $categorieID, $montant, '$date', '$description')";

        if ($conn->query($sql_insert) === TRUE) {
            // Message de succès
            $message = "<div class='succes'>La transaction a été ajoutée avec succès.</div>";
        } else {
            // Gérer les erreurs d'insertion
            $message = "<div class='erreur'>Erreur lors de l'insertion de la transaction : " . $conn->error . "</div>";
        }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartSpend - Espace Personnel</title>
    <link rel="stylesheet" href="espace_personnel.css">
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
        <!-- Section pour ajouter une nouvelle transaction -->
        <section id="ajouter-transaction">
            <h2>Ajouter une Nouvelle Transaction</h2>
            <form action="" method="post">
                <label for="categorie">Catégorie :</label>
                <select name="categorie" id="categorie">
                    <option value="1">Nourriture</option>
                    <option value="2">Logement</option>
                    <option value="3">Transport</option>
                    <option value="4">Loisirs</option>
                    <option value="5">Santé</option>
                    <option value="6">Éducation</option>
                    <option value="7">Factures (électricité, eau, téléphone, etc.)</option>
                    <option value="8">Vêtements</option>
                    <option value="9">Épargne</option>
                    <option value="10">Divertissement</option>
                    <option value="11">Voyage</option>
                    <option value="12">Assurance</option>
                    <option value="13">Impôts</option>
                    <option value="14">Remboursements de dettes</option>
                    <option value="15">Autres</option>
                </select><br>
                <label for="montant">Montant :</label>
                <input type="number" name="montant" id="montant" step="0.01" min="0" required><br>
                <label for="date">Date :</label>
                <input type="date" name="date" id="date" required><br>
                <label for="description">Description :</label>
                <textarea name="description" id="description" rows="3"></textarea><br>
                <button type="submit" name="ajouter">Ajouter Transaction</button>
            </form>
            <?php echo $message;?>
        </section>
    </main>
    <footer>
        <p>© 2024 SmartSpend. Tous droits réservés.</p>
    </footer>
</body>
</html>

