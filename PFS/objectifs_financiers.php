<?php
session_start();

// Database connection setup
$servername = "localhost";
$username = "root";
$password = "";
$database = "smartspenddb";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

$message = "";
$userID = $_SESSION['user_id'];

// Handle setting or updating the monthly budget and amount to save
if (isset($_POST['set_budget'])) {
    $moisAnnee = $_POST['mois_annee'] . '-01';  // Assurer que la date est le premier jour du mois
    $budgetMensuel = $_POST['budget_mensuel'];
    $montantAEconomiser = $_POST['montant_a_economiser'];

    // Vérifier si le budget pour le mois existe, puis mettre à jour ou insérer
    $sql = "SELECT ID FROM budgets WHERE UtilisateurID = ? AND MoisAnnee = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $userID, $moisAnnee);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $id = $row['ID'];
        $sql_update = "UPDATE budgets SET BudgetMensuel = ?, MontantAEconomiser = ? WHERE ID = ?";
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param("ddi", $budgetMensuel, $montantAEconomiser, $id);
    } else {
        $sql_insert = "INSERT INTO budgets (UtilisateurID, MoisAnnee, BudgetMensuel, MontantAEconomiser) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql_insert);
        $stmt->bind_param("isdd", $userID, $moisAnnee, $budgetMensuel, $montantAEconomiser);
    }
    if ($stmt->execute()) {
        $message = "<div class='success'>Budget et montant à économiser définis avec succès.</div>";
    } else {
        $message = "<div class='error'>Erreur lors de la définition du budget et du montant à économiser : " . $conn->error . "</div>";
    }
    $stmt->close();
}

// Always retrieve the current month's budget and savings goal for display
$currentMonthYear = date('Y-m') . '-01'; // Cela garantit qu'il récupère le début du mois en cours
$sql_current = "SELECT BudgetMensuel, MontantAEconomiser FROM budgets WHERE UtilisateurID = ? AND MoisAnnee = ?";
$stmt_current = $conn->prepare($sql_current);
$stmt_current->bind_param("is", $userID, $currentMonthYear);
$stmt_current->execute();
$result_current = $stmt_current->get_result();
$current_settings = $result_current->fetch_assoc();
$stmt_current->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartSpend - Objectifs Financiers</title>
    <link rel="stylesheet" href="objectifs_financiers.css">
</head>
<body>
    <header>
        <h1>Objectifs Financiers</h1>
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
    <div class="container">
        <form action="" method="post">
            <label for="mois_annee">Mois et année :</label>
            <input type="month" id="mois_annee" name="mois_annee" required value="<?php echo isset($current_settings['BudgetMensuel']) ? date('Y-m', strtotime($currentMonthYear)) : date('Y-m'); ?>">

            <label for="budget_mensuel">Budget mensuel :</label>
            <input type="number" id="budget_mensuel" name="budget_mensuel" step="0.01" required value="<?php echo $current_settings['BudgetMensuel'] ?? ''; ?>">

            <label for="montant_a_economiser">Montant à économiser :</label>
            <input type="number" id="montant_a_economiser" name="montant_a_economiser" step="0.01" required value="<?php echo $current_settings['MontantAEconomiser'] ?? ''; ?>">

            <button type="submit" name="set_budget">Définir le budget et le montant à économiser</button>
        </form>
        <?php echo $message; ?>
    </div>
</body>
</html>
