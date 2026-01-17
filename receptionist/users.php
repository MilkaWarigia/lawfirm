<?php
/**
 * RECEPTIONIST - MANAGE USERS
 * Receptionist can delete advocates and clients (but not other receptionists or admins)
 */

require_once '../config/database.php';
require_once '../config/session.php';
requireRole('receptionist');

$message = "";
$error = "";

// Handle delete advocate
if (isset($_GET['delete_advocate']) && is_numeric($_GET['delete_advocate'])) {
    try {
        // Check if advocate has active case assignments
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM CASE_ASSIGNMENT WHERE AdvtId = ? AND Status = 'Active'");
        $stmt->execute([$_GET['delete_advocate']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            $error = "Cannot delete advocate with active case assignments. Please reassign cases first.";
        } else {
            $stmt = $conn->prepare("DELETE FROM ADVOCATE WHERE AdvtId = ?");
            $stmt->execute([$_GET['delete_advocate']]);
            $message = "Advocate deleted successfully";
        }
    } catch(PDOException $e) {
        $error = "Error deleting advocate: " . $e->getMessage();
    }
}

// Handle delete client
if (isset($_GET['delete_client']) && is_numeric($_GET['delete_client'])) {
    try {
        // Check if client has active cases
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM `CASE` WHERE ClientId = ? AND Status = 'Active'");
        $stmt->execute([$_GET['delete_client']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            $error = "Cannot delete client with active cases. Please close or reassign cases first.";
        } else {
            $stmt = $conn->prepare("DELETE FROM CLIENT WHERE ClientId = ?");
            $stmt->execute([$_GET['delete_client']]);
            $message = "Client deleted successfully";
        }
    } catch(PDOException $e) {
        $error = "Error deleting client: " . $e->getMessage();
    }
}

// Get all advocates
try {
    $stmt = $conn->query("SELECT * FROM ADVOCATE ORDER BY LastName, FirstName");
    $advocates = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error loading advocates: " . $e->getMessage();
}

// Get all clients
try {
    $stmt = $conn->query("SELECT * FROM CLIENT ORDER BY LastName, FirstName");
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error loading clients: " . $e->getMessage();
}

include 'header.php';
?>

<h2>Manage Users</h2>

<?php if ($message): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="card mt-20">
    <h3><i class="fas fa-user-tie"></i> Advocates</h3>
    <p style="color: var(--gray); margin-bottom: 15px;">You can delete advocates, but not other receptionists or admins.</p>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($advocates) > 0): ?>
                    <?php foreach ($advocates as $advocate): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($advocate['AdvtId']); ?></td>
                            <td><?php echo htmlspecialchars($advocate['FirstName'] . ' ' . $advocate['LastName']); ?></td>
                            <td><?php echo htmlspecialchars($advocate['Email'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($advocate['PhoneNo'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($advocate['Status'] ?? 'Active'); ?></td>
                            <td>
                                <a href="?delete_advocate=<?php echo $advocate['AdvtId']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Are you sure you want to delete this advocate? This action cannot be undone.');">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="empty-state">No advocates found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card mt-20">
    <h3><i class="fas fa-users"></i> Clients</h3>
    <p style="color: var(--gray); margin-bottom: 15px;">You can delete clients who have no active cases.</p>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($clients) > 0): ?>
                    <?php foreach ($clients as $client): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($client['ClientId']); ?></td>
                            <td><?php echo htmlspecialchars($client['FirstName'] . ' ' . $client['LastName']); ?></td>
                            <td><?php echo htmlspecialchars($client['Email'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($client['PhoneNo'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($client['Address'] ?? '-'); ?></td>
                            <td>
                                <a href="?delete_client=<?php echo $client['ClientId']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Are you sure you want to delete this client? This action cannot be undone.');">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="empty-state">No clients found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>
