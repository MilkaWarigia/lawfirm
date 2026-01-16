<?php
/**
 * PASSWORD HELPER SCRIPT
 * Use this to generate password hashes for new users
 * 
 * INSTRUCTIONS:
 * 1. Open this file in browser: http://localhost/lawfirm/database/password_helper.php
 * 2. Enter your desired password
 * 3. Copy the generated hash
 * 4. Use it in SQL INSERT or UPDATE statements
 */

// Check if form was submitted
$password = $_POST['password'] ?? '';
$hash = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($password)) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Hash Generator</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #667eea;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="password"], input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            background: #667eea;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #5568d3;
        }
        .hash-result {
            margin-top: 20px;
            padding: 15px;
            background: #e8f5e9;
            border: 1px solid #4caf50;
            border-radius: 5px;
            word-break: break-all;
        }
        .hash-result strong {
            color: #2e7d32;
        }
        .example {
            margin-top: 30px;
            padding: 15px;
            background: #fff3e0;
            border-left: 4px solid #ff9800;
        }
        .example code {
            display: block;
            margin-top: 10px;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Password Hash Generator</h1>
        <p>Generate secure password hashes for database users</p>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="password">Enter Password:</label>
                <input type="password" id="password" name="password" required autofocus>
            </div>
            <button type="submit">Generate Hash</button>
        </form>
        
        <?php if (!empty($hash)): ?>
            <div class="hash-result">
                <strong>Generated Hash:</strong><br>
                <code><?php echo htmlspecialchars($hash); ?></code>
            </div>
        <?php endif; ?>
        
        <div class="example">
            <strong>Example SQL Usage:</strong>
            <p>To create a new admin user:</p>
            <code>
                INSERT INTO ADMIN (FirstName, LastName, PhoneNo, Email, Username, Password)<br>
                VALUES ('John', 'Doe', '1234567890', 'john@example.com', 'john', '<?php echo !empty($hash) ? htmlspecialchars($hash) : 'PASTE_HASH_HERE'; ?>');
            </code>
        </div>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
            <a href="../login.php" style="color: #667eea;">← Back to Login</a>
        </div>
    </div>
</body>
</html>
