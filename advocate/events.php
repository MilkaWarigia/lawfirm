<?php
/**
 * ADVOCATE - EVENTS
 * View all events for assigned cases
 */

require_once '../config/database.php';
require_once '../config/session.php';
requireRole('advocate');

$advocate_id = $_SESSION['user_id'];

// Get all events for assigned cases
try {
    $stmt = $conn->prepare("SELECT e.*, c.CaseName 
                             FROM EVENT e 
                             JOIN `CASE` c ON e.CaseNo = c.CaseNo 
                             JOIN CASE_ASSIGNMENT ca ON e.CaseNo = ca.CaseNo 
                             WHERE ca.AdvtId = ? AND ca.Status = 'Active' 
                             ORDER BY e.Date ASC");
    $stmt->execute([$advocate_id]);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error loading events: " . $e->getMessage();
}

include 'header.php';
?>

<h2>My Events & Appointments</h2>

<?php if (isset($error)): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if (count($events) > 0): ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Event Name</th>
                    <th>Type</th>
                    <th>Date & Time</th>
                    <th>Case</th>
                    <th>Location</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($events as $event): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($event['EventName']); ?></td>
                        <td><?php echo htmlspecialchars($event['EventType']); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($event['Date'])); ?></td>
                        <td><?php echo htmlspecialchars($event['CaseName']); ?></td>
                        <td><?php echo htmlspecialchars($event['Location'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($event['Description'] ?? '-'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="card">
        <p class="empty-state">No events found</p>
    </div>
<?php endif; ?>

<?php include 'footer.php'; ?>
