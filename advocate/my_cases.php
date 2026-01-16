<?php
/**
 * ADVOCATE - MY CASES
 * View all assigned cases with details
 */

require_once '../config/database.php';
require_once '../config/session.php';
requireRole('advocate');

$advocate_id = $_SESSION['user_id'];

// Get all assigned cases
try {
    $stmt = $conn->prepare("SELECT c.*, cl.FirstName, cl.LastName, cl.PhoneNo as ClientPhone, cl.Email as ClientEmail, cl.Address as ClientAddress 
                             FROM `CASE` c 
                             JOIN CLIENT cl ON c.ClientId = cl.ClientId 
                             JOIN CASE_ASSIGNMENT ca ON c.CaseNo = ca.CaseNo 
                             WHERE ca.AdvtId = ? AND ca.Status = 'Active' 
                             ORDER BY c.CreatedAt DESC");
    $stmt->execute([$advocate_id]);
    $cases = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error loading cases: " . $e->getMessage();
}

include 'header.php';
?>

<h2>My Assigned Cases</h2>

<?php if (isset($error)): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if (count($cases) > 0): ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Case No</th>
                    <th>Case Name</th>
                    <th>Client</th>
                    <th>Type</th>
                    <th>Court</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cases as $case): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($case['CaseNo']); ?></td>
                        <td><?php echo htmlspecialchars($case['CaseName']); ?></td>
                        <td><?php echo htmlspecialchars($case['FirstName'] . ' ' . $case['LastName']); ?></td>
                        <td><?php echo htmlspecialchars($case['CaseType']); ?></td>
                        <td><?php echo htmlspecialchars($case['Court'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($case['Status']); ?></td>
                        <td>
                            <a href="case_details.php?id=<?php echo $case['CaseNo']; ?>" class="btn btn-sm btn-success">View Details</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="card">
        <p class="empty-state">No cases assigned to you</p>
    </div>
<?php endif; ?>

<?php include 'footer.php'; ?>
