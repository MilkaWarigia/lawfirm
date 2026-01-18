<?php
/**
 * ADMIN - CREATE CLIENT PORTAL ACCOUNT
 * Allow admin to create login credentials for clients
 */

require_once '../config/database.php';
require_once '../config/session.php';
requireRole('admin');

$message = "";
$error = "";

// Predefined security questions
$security_questions = [
    "What was the name of your first pet?",
    "What city were you born in?",
    "What was your mother's maiden name?",
    "What was the name of your elementary school?",
    "What was your childhood nickname?",
    "What street did you grow up on?",
    "What was the make of your first car?",
    "What is your favorite movie?",
    "What was the name of your first teacher?",
    "What is your favorite food?",
    "What was your favorite sport in high school?",
    "What is the name of your best friend from childhood?",
    "What was your favorite book as a child?",
    "What is the name of the hospital where you were born?",
    "What was your favorite vacation destination?"
];

// Password validation function
function validatePassword($password) {
    $errors = [];
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = "Password must contain at least one special character";
    }
    return $errors;
}

// Handle account creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = $_POST['client_id'] ?? null;
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $security_question = trim($_POST['security_question'] ?? '');
    $security_answer = trim($_POST['security_answer'] ?? '');
    
    if (empty($client_id) || empty($username) || empty($password) || empty($security_question) || empty($security_answer)) {
        $error = "Please fill in all fields";
    } else {
        // Validate password strength
        $password_errors = validatePassword($password);
        if (!empty($password_errors)) {
            $error = implode(". ", $password_errors);
        }
    }
    
    if (empty($error)) {
        try {
            // Check if username already exists
            $stmt = $conn->prepare("SELECT AuthId FROM CLIENT_AUTH WHERE Username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = "Username already exists";
            } else {
                // Check if client already has account
                $stmt = $conn->prepare("SELECT AuthId FROM CLIENT_AUTH WHERE ClientId = ?");
                $stmt->execute([$client_id]);
                if ($stmt->fetch()) {
                    // Update existing account
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE CLIENT_AUTH SET Username = ?, Password = ?, SecurityQuestion = ?, SecurityAnswer = ?, IsActive = TRUE WHERE ClientId = ?");
                    $stmt->execute([$username, $hashed_password, $security_question, strtolower(trim($security_answer)), $client_id]);
                    $message = "Client account updated successfully";
                } else {
                    // Create new account
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("INSERT INTO CLIENT_AUTH (ClientId, Username, Password, SecurityQuestion, SecurityAnswer) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$client_id, $username, $hashed_password, $security_question, strtolower(trim($security_answer))]);
                    $message = "Client portal account created successfully";
                }
            }
        } catch(PDOException $e) {
            $error = "Error creating account: " . $e->getMessage();
        }
    }
}

// Get all clients
try {
    $stmt = $conn->query("SELECT c.*, ca.Username, ca.IsActive as HasAccount 
                          FROM CLIENT c 
                          LEFT JOIN CLIENT_AUTH ca ON c.ClientId = ca.ClientId 
                          ORDER BY c.LastName, c.FirstName");
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error loading clients: " . $e->getMessage();
}

include 'header.php';
?>

<h2>Create Client Portal Accounts</h2>

<?php if ($message): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="form-container">
    <h3>Create/Update Client Account</h3>
    <form method="POST" action="">
        <div class="form-group">
            <label for="client_id">Client *</label>
            <select id="client_id" name="client_id" required>
                <option value="">-- Select Client --</option>
                <?php foreach ($clients as $client): ?>
                    <option value="<?php echo htmlspecialchars($client['ClientId']); ?>">
                        <?php echo htmlspecialchars($client['FirstName'] . ' ' . $client['LastName']); ?>
                        <?php if ($client['HasAccount']): ?>
                            (Has Account: <?php echo htmlspecialchars($client['Username']); ?>)
                        <?php endif; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="username">Username *</label>
            <input type="text" id="username" name="username" required 
                   placeholder="Enter username for client portal">
        </div>
        
        <div class="form-group">
            <label for="password">Password *</label>
            <input type="password" id="password" name="password" required minlength="8"
                   placeholder="Enter password">
            <small style="color: var(--gray); font-size: 12px; display: block; margin-top: 5px;">
                Password must be at least 8 characters and contain: uppercase, lowercase, number, and special character
            </small>
        </div>
        
        <div class="form-group">
            <label for="security_question">Security Question *</label>
            <select id="security_question" name="security_question" required>
                <option value="">-- Select a Security Question --</option>
                <?php foreach ($security_questions as $question): ?>
                    <option value="<?php echo htmlspecialchars($question); ?>"><?php echo htmlspecialchars($question); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="security_answer">Security Answer *</label>
            <input type="text" id="security_answer" name="security_answer" required 
                   placeholder="Enter security answer">
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Create/Update Account</button>
        </div>
    </form>
</div>

<div class="card mt-20">
    <h3>Client Portal Accounts</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Client Name</th>
                    <th>Email</th>
                    <th>Username</th>
                    <th>Status</th>
                    <th>Last Login</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($clients) > 0): ?>
                    <?php foreach ($clients as $client): ?>
                        <?php if ($client['HasAccount']): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($client['FirstName'] . ' ' . $client['LastName']); ?></td>
                                <td><?php echo htmlspecialchars($client['Email'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($client['Username']); ?></td>
                                <td>
                                    <span style="color: var(--success-color);">Active</span>
                                </td>
                                <td>
                                    <?php
                                    try {
                                        $stmt = $conn->prepare("SELECT LastLogin FROM CLIENT_AUTH WHERE ClientId = ?");
                                        $stmt->execute([$client['ClientId']]);
                                        $auth = $stmt->fetch(PDO::FETCH_ASSOC);
                                        echo $auth && $auth['LastLogin'] ? date('Y-m-d H:i', strtotime($auth['LastLogin'])) : 'Never';
                                    } catch(PDOException $e) {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="empty-state">No client accounts created yet</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>
