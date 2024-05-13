<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$database = "smartspenddb";
$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

$userID = $_SESSION['user_id'];
$currentMonthYear = date('Y-m-01');
$alerts = [];
$budgetSet = false; // Flag to check if budget is set

// Retrieve the current month's budget and saving goal
$stmt = $conn->prepare("SELECT BudgetMensuel, MontantAEconomiser FROM budgets WHERE UtilisateurID = ? AND MoisAnnee = ?");
$stmt->bind_param("is", $userID, $currentMonthYear);
$stmt->execute();
$budgetData = $stmt->get_result()->fetch_assoc();

if ($budgetData) {
    $budgetSet = true; // Budget is set
} else {
    $alerts[] = "Aucun budget ou montant à économiser n'a été défini pour ce mois. Veuillez mettre à jour vos informations de budget.";
    $budgetData = ['BudgetMensuel' => 0, 'MontantAEconomiser' => 0]; // Default values
}

if ($budgetSet) {
    $stmt = $conn->prepare("SELECT IFNULL(SUM(Montant), 0) AS TotalSpent FROM Transactions WHERE UserID = ? AND MONTH(Date) = MONTH(?) AND YEAR(Date) = YEAR(?)");
    $stmt->bind_param("iss", $userID, $currentMonthYear, $currentMonthYear);
    $stmt->execute();
    $spendingData = $stmt->get_result()->fetch_assoc();

    // Calculate actual savings
    $economieReelle = $budgetData['BudgetMensuel'] - $spendingData['TotalSpent'];
    $remainingBudget = $budgetData['BudgetMensuel'] - $spendingData['TotalSpent'] - $budgetData['MontantAEconomiser'];

    // Generate alerts based on spending relative to budget and saving goals
    if ($remainingBudget < 0.1 * $budgetData['BudgetMensuel']) {
        $alerts[] = "Attention! Il reste moins de 10% de votre budget total.";
    }

    if ($spendingData['TotalSpent'] > $budgetData['BudgetMensuel']) {
        $alerts[] = "Vous avez dépassé votre budget mensuel!";
    }
}

// Retrieve historical data from the last 12 months for trends
$monthlySpendingData = [];
$monthlyBudgetData = [];
if ($budgetSet) {
    for ($i = 11; $i >= 0; $i--) {
        $month = date('Y-m-01', strtotime("-$i months"));
        $stmt = $conn->prepare("SELECT IFNULL(SUM(Montant), 0) AS TotalSpent FROM Transactions WHERE UserID = ? AND MONTH(Date) = MONTH(?) AND YEAR(Date) = YEAR(?)");
        $stmt->bind_param("iss", $userID, $month, $month);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $monthlySpendingData[] = $result['TotalSpent'];

        $stmt = $conn->prepare("SELECT BudgetMensuel FROM budgets WHERE UtilisateurID = ? AND MoisAnnee = ?");
        $stmt->bind_param("is", $userID, $month);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $monthlyBudgetData[] = $result ? $result['BudgetMensuel'] : 0;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartSpend - Alertes</title>
    <link rel="stylesheet" href="alertes.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header>
        <h1>Alertes</h1>
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
        <div class="alerts">
            <?php foreach ($alerts as $alert): ?>
                <p class="alert"><?php echo $alert; ?></p>
            <?php endforeach; ?>
        </div>
        <?php if ($budgetSet): ?>
        <div>
            <canvas id="monthlySpendingChart"></canvas>
            <canvas id="budgetChart"></canvas>
        </div>
        <script>
            var ctx = document.getElementById('monthlySpendingChart').getContext('2d');
            var monthlySpendingChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [...Array(12).keys()].map(i => new Date(new Date().setMonth(new Date().getMonth() - i)).toLocaleString('fr', { month: 'short', year: 'numeric' }).toUpperCase()).reverse(),
                    datasets: [{
                        label: 'Dépenses Mensuelles (DH)',
                        data: [<?php echo implode(',', $monthlySpendingData); ?>],
                        borderColor: 'rgba(255, 99, 132, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        fill: true
                    }, {
                        label: 'Budget Mensuel (DH)',
                        data: [<?php echo implode(',', $monthlyBudgetData); ?>],
                        borderColor: 'rgba(54, 162, 235, 1)',
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        fill: false
                    }, {
                        label: 'Économies Mensuelles (DH)',
                        data: [<?php echo implode(',', array_map(function($m, $b) { return $b - $m; }, $monthlySpendingData, $monthlyBudgetData)); ?>],
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        fill: false
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            var ctx2 = document.getElementById('budgetChart').getContext('2d');
            var budgetChart = new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: ['Budget Total', 'Dépenses', 'Économies'],
                    datasets: [{
                        label: 'Résumé Financier Du Mois',
                        data: [<?php echo $budgetData['BudgetMensuel']; ?>, <?php echo $spendingData['TotalSpent']; ?>, <?php echo $economieReelle; ?>],
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.2)', // Blue for total budget
                            'rgba(255, 99, 132, 0.2)', // Red for expenses
                            'rgba(75, 192, 192, 0.2)'  // Green for savings
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 99, 132, 1)',
                            'rgba(75, 192, 192, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        </script>
        <?php endif; ?>
    </main>
    <footer>
        <p>© 2024 SmartSpend. Tous droits réservés.</p>
    </footer>
</body>
</html>
