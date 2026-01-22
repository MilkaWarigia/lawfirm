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
$message = "";
$error = "";

// Handle progress update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_progress'])) {
    $progress = trim($_POST['progress'] ?? '');
    $case_no_update = $_POST['case_no'] ?? null;
    
    if (!$case_no_update || !is_numeric($case_no_update)) {
        $error = "Invalid case number";
    } else {
        // Verify that this case is assigned to the advocate
        try {
            $stmt = $conn->prepare("SELECT c.CaseNo FROM `CASE` c 
                                    JOIN CASE_ASSIGNMENT ca ON c.CaseNo = ca.CaseNo 
                                    WHERE c.CaseNo = ? AND ca.AdvtId = ? AND ca.Status = 'Active'");
            $stmt->execute([$case_no_update, $advocate_id]);
            if (!$stmt->fetch()) {
                $error = "You are not authorized to update this case";
            } else {
                // Update progress
                $stmt = $conn->prepare("UPDATE `CASE` SET Progress = ? WHERE CaseNo = ?");
                $stmt->execute([$progress, $case_no_update]);
                $message = "Case progress updated successfully";
                // Reload case data
                $case_no = $case_no_update;
            }
        } catch(PDOException $e) {
            $error = "Error updating progress: " . $e->getMessage();
        }
    }
}

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

<?php if ($message): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<?php if ($error): ?>
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
        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border);">
            <a href="javascript:void(0)" onclick="document.getElementById('editProgressModal').style.display='block'" class="btn btn-primary">
                <i class="fas fa-edit"></i> Update Case Progress
            </a>
        </div>
    </div>
    
    <div class="card mt-20">
        <h3>Case Progress</h3>
        <?php if (!empty($case['Progress'])): ?>
            <div style="padding: 15px; background: #f8f9fa; border-radius: 4px; white-space: pre-wrap;"><?php echo nl2br(htmlspecialchars($case['Progress'])); ?></div>
            <div style="margin-top: 10px; color: var(--gray); font-size: 14px;">
                <i class="fas fa-info-circle"></i> Last updated by advocate
            </div>
        <?php else: ?>
            <p class="empty-state">No progress updates yet. Click "Update Case Progress" to add progress information.</p>
        <?php endif; ?>
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

<!-- Edit Progress Modal -->
<div id="editProgressModal" class="modal" style="display: none;" onclick="if(event.target === this) document.getElementById('editProgressModal').style.display='none'">
    <div class="modal-content" style="max-width: 600px;" onclick="event.stopPropagation()">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3>Update Case Progress</h3>
            <span class="close" onclick="document.getElementById('editProgressModal').style.display='none'" style="cursor: pointer; font-size: 28px; font-weight: bold; color: var(--gray);">&times;</span>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="case_no" value="<?php echo htmlspecialchars($case['CaseNo'] ?? ''); ?>">
            <div class="form-group">
                <label for="progress">Case Progress *</label>
                <textarea id="progress" name="progress" rows="10" required placeholder="Enter case progress updates, milestones, recent developments, etc."><?php echo htmlspecialchars($case['Progress'] ?? ''); ?></textarea>
                <small style="color: var(--gray); margin-top: 5px; display: block;">Provide detailed updates about the case progress, including recent developments, milestones achieved, and any important notes.</small>
            </div>
            <div class="form-actions">
                <button type="submit" name="update_progress" class="btn btn-primary">Update Progress</button>
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('editProgressModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 30px;
    border: 1px solid #888;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.close:hover,
.close:focus {
    color: #000;
}

@media (max-width: 768px) {
    .modal-content {
        margin: 10% auto;
        padding: 20px;
        width: 90%;
    }
}
</style>

<script>
// Close modal when clicking outside or pressing Escape key
window.onclick = function(event) {
    const modal = document.getElementById('editProgressModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        document.getElementById('editProgressModal').style.display = 'none';
    }
});
</script>

<?php include 'footer.php'; ?>
