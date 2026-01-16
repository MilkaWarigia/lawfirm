<?php
/**
 * CLIENT INVOICES
 * View invoices for client's cases
 */

session_start();
require_once '../config/database.php';

if (!isset($_SESSION['client_id'])) {
    header("Location: login.php");
    exit();
}

$client_id = $_SESSION['client_id'];

try {
    $stmt = $conn->prepare("SELECT b.*, c.CaseName 
                            FROM BILLING b 
                            JOIN `CASE` c ON b.CaseNo = c.CaseNo 
                            WHERE c.ClientId = ? 
                            ORDER BY b.Date DESC");
    $stmt->execute([$client_id]);
    $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error loading invoices: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoices - Client Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>My Invoices - Client Portal</h1>
            <div class="header-user">
                <a href="dashboard.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
                <a href="logout.php" class="btn btn-secondary btn-sm"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </div>
    
    <div class="nav">
        <div class="nav-content">
            <ul class="nav-menu">
                <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="my_cases.php"><i class="fas fa-folder-open"></i> My Cases</a></li>
                <li><a href="invoices.php" class="active"><i class="fas fa-file-invoice-dollar"></i> Invoices</a></li>
                <li><a href="documents.php"><i class="fas fa-file"></i> Documents</a></li>
                <li><a href="messages.php"><i class="fas fa-comments"></i> Messages</a></li>
            </ul>
        </div>
    </div>
    
    <div class="container">
        <div class="card">
            <h3>My Invoices</h3>
            <?php if (isset($invoices) && count($invoices) > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Invoice Date</th>
                                <th>Case</th>
                                <th>Amount</th>
                                <th>Deposit</th>
                                <th>Balance</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invoices as $invoice): ?>
                                <tr>
                                    <td><?php echo date('Y-m-d', strtotime($invoice['Date'])); ?></td>
                                    <td><?php echo htmlspecialchars($invoice['CaseName']); ?></td>
                                    <td><?php echo number_format($invoice['Amount'], 2); ?></td>
                                    <td><?php echo number_format($invoice['Deposit'], 2); ?></td>
                                    <td><?php echo number_format($invoice['Amount'] - $invoice['Deposit'], 2); ?></td>
                                    <td>
                                        <span style="padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;
                                            <?php
                                            if ($invoice['Status'] == 'Paid') echo 'background: #10b981; color: white;';
                                            elseif ($invoice['Status'] == 'Pending') echo 'background: #f59e0b; color: white;';
                                            else echo 'background: #ef4444; color: white;';
                                            ?>">
                                            <?php echo htmlspecialchars($invoice['Status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="empty-state">No invoices found</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
