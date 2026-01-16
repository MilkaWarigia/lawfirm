<?php
/**
 * ADVOCATE - CASE DETAILS
 * View detailed information about a specific case
 */

require_once '../config/database.php';
require_once '../config/session.php';
requireRole('advocate');

$advocate_id = $_SESSION['user_id'];
$case_no = $_GET['id'] ?? null;

if (!$case_no || !is_numeric($case_no)) {
    header("Location: my_cases.php");
    exit();
}

// Verify that this case is assigned to the advocate
try {
    $stmt = $conn->prepare("SELECT c.*, cl.FirstName, cl.LastName, cl.PhoneNo as ClientPhone, cl.Email as ClientEmail, cl.Address as ClientAddress 
                            FROM `CASE` c 
                            JOIN CLIENT cl ON c.ClientId = cl.ClientId 
                            JOIN CASE_ASSIGNMENT ca ON c.CaseNo = ca.CaseNo 
                            WHERE c.CaseNo = ? AND ca.AdvtId = ? AND ca.Status = 'Active'");
    $stmt->execute([$case_no, $advocate_id]);
    $case = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$case) {
        header("Location: my_cases.php");
        exit();
    }
} catch(PDOException $e) {
    $error = "Error loading case: " . $e->getMessage();
}

// Get events for this case
try {
    $stmt = $conn->prepare("SELECT * FROM EVENT WHERE CaseNo = ? ORDER BY Date ASC");
    $stmt->execute([$case_no]);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error loading events: " . $e->getMessage();
}

include 'header.php';
?>

<h2>Case Details</h2>

<?php if (isset($error)): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($case): ?>
    <div class="card">
        <h3>Case Information</h3>
        <table>
            <tr>
                <th width="200">Case Number:</th>
                <td><?php echo htmlspecialchars($case['CaseNo']); ?></td>
            </tr>
            <tr>
                <th>Case Name:</th>
                <td><?php echo htmlspecialchars($case['CaseName']); ?></td>
            </tr>
            <tr>
                <th>Case Type:</th>
                <td><?php echo htmlspecialchars($case['CaseType']); ?></td>
            </tr>
            <tr>
                <th>Court:</th>
                <td><?php echo htmlspecialchars($case['Court'] ?? '-'); ?></td>
            </tr>
            <tr>
                <th>Status:</th>
                <td><?php echo htmlspecialchars($case['Status']); ?></td>
            </tr>
            <tr>
                <th>Description:</th>
                <td><?php echo htmlspecialchars($case['Description'] ?? '-'); ?></td>
            </tr>
        </table>
    </div>
    
    <div class="card mt-20">
        <h3>Client Information</h3>
        <table>
            <tr>
                <th width="200">Name:</th>
                <td><?php echo htmlspecialchars($case['FirstName'] . ' ' . $case['LastName']); ?></td>
            </tr>
            <tr>
                <th>Phone:</th>
                <td><?php echo htmlspecialchars($case['ClientPhone']); ?></td>
            </tr>
            <tr>
                <th>Email:</th>
                <td><?php echo htmlspecialchars($case['ClientEmail'] ?? '-'); ?></td>
            </tr>
            <tr>
                <th>Address:</th>
                <td><?php echo htmlspecialchars($case['ClientAddress'] ?? '-'); ?></td>
            </tr>
        </table>
    </div>
    
    <div class="card mt-20">
        <h3>Events & Appointments</h3>
        <?php if (count($events) > 0): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Event Name</th>
                            <th>Type</th>
                            <th>Date & Time</th>
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
                                <td><?php echo htmlspecialchars($event['Location'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($event['Description'] ?? '-'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="empty-state">No events scheduled for this case</p>
        <?php endif; ?>
    </div>
    
    <div class="mt-20">
        <a href="my_cases.php" class="btn btn-secondary">Back to My Cases</a>
    </div>
<?php endif; ?>

<?php include 'footer.php'; ?>
