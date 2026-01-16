<?php
/**
 * ADMIN - MANAGE BILLING
 * View and manage billing information
 */

require_once '../config/database.php';
require_once '../config/session.php';
requireRole('admin');

$message = "";
$error = "";

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM BILLING WHERE BillId = ?");
        $stmt->execute([$_GET['delete']]);
        $message = "Bill deleted successfully";
    } catch(PDOException $e) {
        $error = "Error deleting bill: " . $e->getMessage();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bill_id = $_POST['bill_id'] ?? null;
    $client_id = $_POST['client_id'] ?? null;
    $case_no = $_POST['case_no'] ?? null;
    $date = $_POST['date'] ?? '';
    $amount = $_POST['amount'] ?? 0;
    $deposit = $_POST['deposit'] ?? 0;
    $installments = $_POST['installments'] ?? 0;
    $status = trim($_POST['status'] ?? 'Pending');
    $description = trim($_POST['description'] ?? '');
    
    if (empty($client_id) || empty($date)) {
        $error = "Please fill in all required fields";
    } else {
        try {
            if ($bill_id) {
                // Update existing bill
                $stmt = $conn->prepare("UPDATE BILLING SET ClientId = ?, CaseNo = ?, Date = ?, Amount = ?, Deposit = ?, Installments = ?, Status = ?, Description = ? WHERE BillId = ?");
                $stmt->execute([$client_id, $case_no, $date, $amount, $deposit, $installments, $status, $description, $bill_id]);
                $message = "Bill updated successfully";
            } else {
                // Insert new bill
                $stmt = $conn->prepare("INSERT INTO BILLING (ClientId, CaseNo, Date, Amount, Deposit, Installments, Status, Description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$client_id, $case_no, $date, $amount, $deposit, $installments, $status, $description]);
                $message = "Bill added successfully";
            }
        } catch(PDOException $e) {
            $error = "Error saving bill: " . $e->getMessage();
        }
    }
}

// Get bill for editing
$edit_bill = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    try {
        $stmt = $conn->prepare("SELECT * FROM BILLING WHERE BillId = ?");
        $stmt->execute([$_GET['edit']]);
        $edit_bill = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $error = "Error loading bill: " . $e->getMessage();
    }
}

// Get all clients for dropdown
try {
    $stmt = $conn->query("SELECT ClientId, FirstName, LastName FROM CLIENT ORDER BY LastName, FirstName");
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error loading clients: " . $e->getMessage();
}

// Get all cases for dropdown
try {
    $stmt = $conn->query("SELECT CaseNo, CaseName FROM `CASE` ORDER BY CaseName");
    $cases = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error loading cases: " . $e->getMessage();
}

// Get all bills
try {
    $stmt = $conn->query("SELECT b.*, c.FirstName, c.LastName, cs.CaseName 
                          FROM BILLING b 
                          JOIN CLIENT c ON b.ClientId = c.ClientId 
                          LEFT JOIN `CASE` cs ON b.CaseNo = cs.CaseNo 
                          ORDER BY b.Date DESC");
    $bills = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error loading bills: " . $e->getMessage();
}

include 'header.php';
?>

<h2>Manage Billing</h2>

<?php if ($message): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="form-container">
    <h3><?php echo $edit_bill ? 'Edit Bill' : 'Add New Bill'; ?></h3>
    <form method="POST" action="">
        <?php if ($edit_bill): ?>
            <input type="hidden" name="bill_id" value="<?php echo htmlspecialchars($edit_bill['BillId']); ?>">
        <?php endif; ?>
        
        <div class="form-group">
            <label for="client_id">Client *</label>
            <select id="client_id" name="client_id" required>
                <option value="">-- Select Client --</option>
                <?php foreach ($clients as $client): ?>
                    <option value="<?php echo $client['ClientId']; ?>" 
                            <?php echo ($edit_bill && $edit_bill['ClientId'] == $client['ClientId']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($client['FirstName'] . ' ' . $client['LastName']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="case_no">Case (Optional)</label>
            <select id="case_no" name="case_no">
                <option value="">-- Select Case --</option>
                <?php foreach ($cases as $case): ?>
                    <option value="<?php echo $case['CaseNo']; ?>" 
                            <?php echo ($edit_bill && $edit_bill['CaseNo'] == $case['CaseNo']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($case['CaseName']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="date">Date *</label>
            <input type="date" id="date" name="date" 
                   value="<?php echo $edit_bill ? $edit_bill['Date'] : date('Y-m-d'); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="amount">Total Amount</label>
            <input type="number" id="amount" name="amount" step="0.01" min="0"
                   value="<?php echo htmlspecialchars($edit_bill['Amount'] ?? '0'); ?>">
        </div>
        
        <div class="form-group">
            <label for="deposit">Deposit</label>
            <input type="number" id="deposit" name="deposit" step="0.01" min="0"
                   value="<?php echo htmlspecialchars($edit_bill['Deposit'] ?? '0'); ?>">
        </div>
        
        <div class="form-group">
            <label for="installments">Installments</label>
            <input type="number" id="installments" name="installments" step="0.01" min="0"
                   value="<?php echo htmlspecialchars($edit_bill['Installments'] ?? '0'); ?>">
        </div>
        
        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="Pending" <?php echo ($edit_bill && $edit_bill['Status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                <option value="Paid" <?php echo ($edit_bill && $edit_bill['Status'] == 'Paid') ? 'selected' : ''; ?>>Paid</option>
                <option value="Partial" <?php echo ($edit_bill && $edit_bill['Status'] == 'Partial') ? 'selected' : ''; ?>>Partial</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description"><?php echo htmlspecialchars($edit_bill['Description'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><?php echo $edit_bill ? 'Update Bill' : 'Add Bill'; ?></button>
            <?php if ($edit_bill): ?>
                <a href="billing.php" class="btn btn-secondary">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="card mt-20">
    <h3>All Bills</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Bill ID</th>
                    <th>Client</th>
                    <th>Case</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Deposit</th>
                    <th>Installments</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($bills) > 0): ?>
                    <?php foreach ($bills as $bill): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($bill['BillId']); ?></td>
                            <td><?php echo htmlspecialchars($bill['FirstName'] . ' ' . $bill['LastName']); ?></td>
                            <td><?php echo htmlspecialchars($bill['CaseName'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($bill['Date']); ?></td>
                            <td><?php echo number_format($bill['Amount'], 2); ?></td>
                            <td><?php echo number_format($bill['Deposit'], 2); ?></td>
                            <td><?php echo number_format($bill['Installments'], 2); ?></td>
                            <td><?php echo htmlspecialchars($bill['Status']); ?></td>
                            <td>
                                <a href="?edit=<?php echo $bill['BillId']; ?>" class="btn btn-sm btn-success">Edit</a>
                                <a href="?delete=<?php echo $bill['BillId']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Are you sure you want to delete this bill?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="empty-state">No bills found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>
