<?php
/**
 * FIX SAMPLE DATA SCRIPT
 * Use this if you got foreign key errors when importing schema.sql
 * This will insert sample data in the correct order
 */

require_once '../config/database.php';

$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fix'])) {
    try {
        // Check if advocate exists
        $stmt = $conn->prepare("SELECT AdvtId FROM ADVOCATE WHERE Username = 'advocate1' LIMIT 1");
        $stmt->execute();
        $advocate_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$advocate_data) {
            $error = "Advocate with username 'advocate1' does not exist. Please run setup_users.php first to create users.";
        } else {
            $advocate_id = $advocate_data['AdvtId'];
            
            // Check if sample client already exists
            $stmt = $conn->prepare("SELECT ClientId FROM CLIENT WHERE PhoneNo = '254712345678' LIMIT 1");
            $stmt->execute();
            $client_exists = $stmt->fetch();
            
            if ($client_exists) {
                $client_id = $client_exists['ClientId'];
                $message .= "Sample client already exists. ";
            } else {
                // Insert sample client
                $stmt = $conn->prepare("INSERT INTO CLIENT (FirstName, LastName, PhoneNo, Email, Address) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute(['Peter', 'Kamau', '254712345678', 'peter.kamau@email.com', 'Nairobi, Kenya']);
                $client_id = $conn->lastInsertId();
                $message .= "Sample client created. ";
            }
            
            // Check if sample case exists
            $stmt = $conn->prepare("SELECT CaseNo FROM `CASE` WHERE CaseName = 'Kamau vs. ABC Company' LIMIT 1");
            $stmt->execute();
            $case_exists = $stmt->fetch();
            
            if ($case_exists) {
                $case_no = $case_exists['CaseNo'];
                $message .= "Sample case already exists. ";
            } else {
                // Insert sample case
                $stmt = $conn->prepare("INSERT INTO `CASE` (CaseName, CaseType, Court, ClientId, Status, Description) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute(['Kamau vs. ABC Company', 'Civil', 'High Court Nairobi', $client_id, 'Active', 'Contract dispute case']);
                $case_no = $conn->lastInsertId();
                $message .= "Sample case created. ";
            }
            
            // Check if case assignment exists
            $stmt = $conn->prepare("SELECT AssId FROM CASE_ASSIGNMENT WHERE CaseNo = ? AND AdvtId = ? LIMIT 1");
            $stmt->execute([$case_no, $advocate_id]);
            $assignment_exists = $stmt->fetch();
            
            if (!$assignment_exists) {
                // Insert sample case assignment
                $stmt = $conn->prepare("INSERT INTO CASE_ASSIGNMENT (CaseNo, AdvtId, AssignedDate, Status) VALUES (?, ?, CURDATE(), ?)");
                $stmt->execute([$case_no, $advocate_id, 'Active']);
                $message .= "Case assignment created. ";
            } else {
                $message .= "Case assignment already exists. ";
            }
            
            // Check if billing exists
            $stmt = $conn->prepare("SELECT BillId FROM BILLING WHERE ClientId = ? AND CaseNo = ? LIMIT 1");
            $stmt->execute([$client_id, $case_no]);
            $billing_exists = $stmt->fetch();
            
            if (!$billing_exists) {
                // Insert sample billing
                $stmt = $conn->prepare("INSERT INTO BILLING (ClientId, CaseNo, Date, Amount, Deposit, Installments, Status) VALUES (?, ?, CURDATE(), ?, ?, ?, ?)");
                $stmt->execute([$client_id, $case_no, 50000.00, 10000.00, 40000.00, 'Pending']);
                $message .= "Sample billing created. ";
            } else {
                $message .= "Sample billing already exists. ";
            }
            
            // Check if event exists
            $stmt = $conn->prepare("SELECT EventId FROM EVENT WHERE CaseNo = ? AND EventName = 'Initial Hearing' LIMIT 1");
            $stmt->execute([$case_no]);
            $event_exists = $stmt->fetch();
            
            if (!$event_exists) {
                // Insert sample event
                $stmt = $conn->prepare("INSERT INTO EVENT (EventName, EventType, Date, CaseNo, Description, Location) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 7 DAY), ?, ?, ?)");
                $stmt->execute(['Initial Hearing', 'Court Hearing', $case_no, 'First court appearance', 'High Court Nairobi']);
                $message .= "Sample event created. ";
            } else {
                $message .= "Sample event already exists. ";
            }
            
            $message = "Sample data setup completed! " . $message;
        }
        
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Sample Data - Law Firm Management System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container" style="max-width: 800px; margin: 50px auto;">
        <div class="card">
            <h2>Fix Sample Data</h2>
            <p>Use this script if you encountered foreign key errors when importing schema.sql.</p>
            <p><strong>Note:</strong> Make sure you have run <code>setup_users.php</code> first to create the users.</p>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <div style="background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;">
                <h3>What this script does:</h3>
                <ul>
                    <li>Checks if advocate exists (required)</li>
                    <li>Creates sample client (if doesn't exist)</li>
                    <li>Creates sample case (if doesn't exist)</li>
                    <li>Creates case assignment (if doesn't exist)</li>
                    <li>Creates sample billing (if doesn't exist)</li>
                    <li>Creates sample event (if doesn't exist)</li>
                </ul>
            </div>
            
            <form method="POST" action="">
                <button type="submit" name="fix" class="btn btn-primary">Fix Sample Data</button>
            </form>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
                <a href="setup_users.php" class="btn btn-secondary">Go to User Setup</a>
                <a href="../login.php" class="btn btn-secondary" style="margin-left: 10px;">Go to Login Page</a>
            </div>
        </div>
    </div>
</body>
</html>
