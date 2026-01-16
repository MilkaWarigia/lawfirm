<?php
/**
 * ADVOCATE - CLIENTS
 * View clients from assigned cases
 */

require_once '../config/database.php';
require_once '../config/session.php';
requireRole('advocate');

$advocate_id = $_SESSION['user_id'];

// Get all clients from assigned cases
try {
    $stmt = $conn->prepare("SELECT DISTINCT cl.* 
                             FROM CLIENT cl 
                             JOIN `CASE` c ON cl.ClientId = c.ClientId 
                             JOIN CASE_ASSIGNMENT ca ON c.CaseNo = ca.CaseNo 
                             WHERE ca.AdvtId = ? AND ca.Status = 'Active' 
                             ORDER BY cl.LastName, cl.FirstName");
    $stmt->execute([$advocate_id]);
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error loading clients: " . $e->getMessage();
}

include 'header.php';
?>

<h2>My Clients</h2>

<?php if (isset($error)): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if (count($clients) > 0): ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Client ID</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Address</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clients as $client): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($client['ClientId']); ?></td>
                        <td><?php echo htmlspecialchars($client['FirstName'] . ' ' . $client['LastName']); ?></td>
                        <td><?php echo htmlspecialchars($client['PhoneNo']); ?></td>
                        <td><?php echo htmlspecialchars($client['Email'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($client['Address'] ?? '-'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="card">
        <p class="empty-state">No clients found</p>
    </div>
<?php endif; ?>

<?php include 'footer.php'; ?>
