<?php
/**
 * USER SETUP SCRIPT
 * Run this script once after importing schema.sql to set up default users with correct passwords
 * 
 * INSTRUCTIONS:
 * 1. Make sure database is created and schema.sql is imported
 * 2. Open in browser: http://localhost/lawfirm/database/setup_users.php
 * 3. This will create/update default users with correct password hashes
 */

require_once '../config/database.php';

$message = "";
$error = "";
$debug_info = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup'])) {
    try {
        // Test database connection first
        $test_query = $conn->query("SELECT 1");
        $debug_info .= "Database connection: OK. ";
        
        // Hash passwords
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        $advocate_password = password_hash('advocate123', PASSWORD_DEFAULT);
        $receptionist_password = password_hash('receptionist123', PASSWORD_DEFAULT);
        
        $debug_info .= "Passwords hashed. ";
        
        // Check if admin exists
        $stmt = $conn->prepare("SELECT AdminId FROM ADMIN WHERE Username = 'admin'");
        $stmt->execute();
        $admin_exists = $stmt->fetch();
        
        if ($admin_exists) {
            // Update existing admin
            $stmt = $conn->prepare("UPDATE ADMIN SET Password = ? WHERE Username = 'admin'");
            $stmt->execute([$admin_password]);
            $message .= "Admin password updated. ";
        } else {
            // Insert new admin
            $stmt = $conn->prepare("INSERT INTO ADMIN (FirstName, LastName, PhoneNo, Email, Username, Password) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute(['System', 'Administrator', '1234567890', 'admin@lawfirm.com', 'admin', $admin_password]);
            $message .= "Admin user created. ";
        }
        
        // Check if advocate exists
        $stmt = $conn->prepare("SELECT AdvtId FROM ADVOCATE WHERE Username = 'advocate1'");
        $stmt->execute();
        $advocate_exists = $stmt->fetch();
        
        if ($advocate_exists) {
            // Update existing advocate
            $stmt = $conn->prepare("UPDATE ADVOCATE SET Password = ? WHERE Username = 'advocate1'");
            $stmt->execute([$advocate_password]);
            $message .= "Advocate password updated. ";
        } else {
            // Insert new advocate
            $stmt = $conn->prepare("INSERT INTO ADVOCATE (FirstName, LastName, PhoneNo, Email, Address, Username, Password, Status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute(['John', 'Munyoki', '0987654321', 'john.munyoki@lawfirm.com', 'Nairobi, Kenya', 'advocate1', $advocate_password, 'Active']);
            $message .= "Advocate user created. ";
        }
        
        // Check if receptionist exists
        $stmt = $conn->prepare("SELECT RecId FROM RECEPTIONIST WHERE Username = 'receptionist1'");
        $stmt->execute();
        $receptionist_exists = $stmt->fetch();
        
        if ($receptionist_exists) {
            // Update existing receptionist
            $stmt = $conn->prepare("UPDATE RECEPTIONIST SET Password = ? WHERE Username = 'receptionist1'");
            $stmt->execute([$receptionist_password]);
            $message .= "Receptionist password updated. ";
        } else {
            // Insert new receptionist
            $stmt = $conn->prepare("INSERT INTO RECEPTIONIST (FirstName, LastName, PhoneNo, Email, Username, Password) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute(['Mary', 'Maheli', '1122334455', 'mary.maheli@lawfirm.com', 'receptionist1', $receptionist_password]);
            $message .= "Receptionist user created. ";
        }
        
        // Get advocate ID for sample data
        $stmt = $conn->prepare("SELECT AdvtId FROM ADVOCATE WHERE Username = 'advocate1'");
        $stmt->execute();
        $advocate_data = $stmt->fetch(PDO::FETCH_ASSOC);
        $advocate_id = $advocate_data ? $advocate_data['AdvtId'] : null;
        
        // Insert sample data (only if advocate exists and sample data doesn't exist)
        if ($advocate_id) {
            // Check if sample client exists
            $stmt = $conn->prepare("SELECT ClientId FROM CLIENT WHERE PhoneNo = '254712345678'");
            $stmt->execute();
            $client_exists = $stmt->fetch();
            
            if (!$client_exists) {
                // Insert sample client
                $stmt = $conn->prepare("INSERT INTO CLIENT (FirstName, LastName, PhoneNo, Email, Address) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute(['Peter', 'Kamau', '254712345678', 'peter.kamau@email.com', 'Nairobi, Kenya']);
                $client_id = $conn->lastInsertId();
                
                // Insert sample case
                $stmt = $conn->prepare("INSERT INTO `CASE` (CaseName, CaseType, Court, ClientId, Status, Description) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute(['Kamau vs. ABC Company', 'Civil', 'High Court Nairobi', $client_id, 'Active', 'Contract dispute case']);
                $case_no = $conn->lastInsertId();
                
                // Insert sample case assignment
                $stmt = $conn->prepare("INSERT INTO CASE_ASSIGNMENT (CaseNo, AdvtId, AssignedDate, Status) VALUES (?, ?, CURDATE(), ?)");
                $stmt->execute([$case_no, $advocate_id, 'Active']);
                
                // Insert sample billing
                $stmt = $conn->prepare("INSERT INTO BILLING (ClientId, CaseNo, Date, Amount, Deposit, Installments, Status) VALUES (?, ?, CURDATE(), ?, ?, ?, ?)");
                $stmt->execute([$client_id, $case_no, 50000.00, 10000.00, 40000.00, 'Pending']);
                
                // Insert sample event
                $stmt = $conn->prepare("INSERT INTO EVENT (EventName, EventType, Date, CaseNo, Description, Location) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 7 DAY), ?, ?, ?)");
                $stmt->execute(['Initial Hearing', 'Court Hearing', $case_no, 'First court appearance', 'High Court Nairobi']);
                
                $message .= "Sample data inserted. ";
            }
        }
        
        $message = "Setup completed successfully! " . $message;
        
        // Verify users were created
        $verify_admin = $conn->query("SELECT COUNT(*) as count FROM ADMIN WHERE Username = 'admin'")->fetch();
        $verify_advocate = $conn->query("SELECT COUNT(*) as count FROM ADVOCATE WHERE Username = 'advocate1'")->fetch();
        $verify_receptionist = $conn->query("SELECT COUNT(*) as count FROM RECEPTIONIST WHERE Username = 'receptionist1'")->fetch();
        
        $debug_info .= "Verification: Admin=" . $verify_admin['count'] . ", Advocate=" . $verify_advocate['count'] . ", Receptionist=" . $verify_receptionist['count'];
        
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
        $debug_info = "Database error occurred. Check database connection and table existence.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Setup - Law Firm Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-container" style="max-width: 600px;">
        <div class="login-box glass-effect">
            <div class="login-header">
                <div class="icon-wrapper" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <i class="fas fa-user-cog"></i>
                </div>
                <h2>Default User Setup</h2>
                <p style="color: rgba(255, 255, 255, 0.9); margin-top: 8px;">Create default users with correct password hashes and sample data</p>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($debug_info)): ?>
                <div class="alert alert-info" style="margin-top: 15px;">
                    <i class="fas fa-info-circle"></i>
                    <strong>Debug Info:</strong> <?php echo htmlspecialchars($debug_info); ?>
                </div>
            <?php endif; ?>
            
            <div class="login-info glass-effect-light" style="margin-top: 24px;">
                <h3><i class="fas fa-key"></i> Default Login Credentials</h3>
                <div class="account-list">
                    <div class="account-item">
                        <i class="fas fa-user-shield"></i>
                        <span><strong>Admin:</strong> admin / admin123</span>
                    </div>
                    <div class="account-item">
                        <i class="fas fa-briefcase"></i>
                        <span><strong>Advocate:</strong> advocate1 / advocate123</span>
                    </div>
                    <div class="account-item">
                        <i class="fas fa-headset"></i>
                        <span><strong>Receptionist:</strong> receptionist1 / receptionist123</span>
                    </div>
                </div>
            </div>
            
            <form method="POST" action="" style="margin-top: 32px;">
                <button type="submit" name="setup" class="btn btn-primary btn-login">
                    <i class="fas fa-rocket"></i>
                    <span>Setup Default Users</span>
                </button>
            </form>
            
            <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid rgba(255, 255, 255, 0.2); text-align: center; display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
                <a href="check_users.php" class="btn btn-secondary" style="width: auto; padding: 12px 24px; background: rgba(16, 185, 129, 0.8);">
                    <i class="fas fa-users"></i> Check Users
                </a>
                <a href="../login.php" class="btn btn-secondary" style="width: auto; padding: 12px 24px;">
                    <i class="fas fa-sign-in-alt"></i> Go to Login
                </a>
                <a href="../index.php" class="btn btn-secondary" style="width: auto; padding: 12px 24px;">
                    <i class="fas fa-home"></i> Home
                </a>
            </div>
        </div>
    </div>
</body>
</html>
