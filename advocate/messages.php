<?php
/**
 * ADVOCATE MESSAGES
 * Chat/messaging system - advocates can respond to client messages
 */

require_once '../config/database.php';
require_once '../config/session.php';
requireRole('advocate');

$advocate_id = $_SESSION['user_id'];
$message = "";
$error = "";

// Handle sending message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $case_no = $_POST['case_no'] ?? null;
    $client_id = $_POST['client_id'] ?? null;
    $message_text = trim($_POST['message'] ?? '');
    
    if (empty($case_no) || empty($client_id) || empty($message_text)) {
        $error = "Please enter a message";
    } else {
        // Verify advocate is assigned to case
        $stmt = $conn->prepare("SELECT CaseNo FROM CASE_ASSIGNMENT WHERE CaseNo = ? AND AdvtId = ? AND Status = 'Active'");
        $stmt->execute([$case_no, $advocate_id]);
        if (!$stmt->fetch()) {
            $error = "You are not assigned to this case";
        } else {
            try {
                $stmt = $conn->prepare("INSERT INTO MESSAGE (CaseNo, ClientId, AdvocateId, SenderRole, Message) VALUES (?, ?, ?, 'advocate', ?)");
                $stmt->execute([$case_no, $client_id, $advocate_id, $message_text]);
                $message = "Message sent successfully";
            } catch(PDOException $e) {
                $error = "Error sending message: " . $e->getMessage();
            }
        }
    }
}

// Get advocate's assigned cases
try {
    $stmt = $conn->prepare("SELECT DISTINCT c.*, cl.ClientId, cl.FirstName, cl.LastName 
                            FROM `CASE` c 
                            JOIN CLIENT cl ON c.ClientId = cl.ClientId 
                            JOIN CASE_ASSIGNMENT ca ON c.CaseNo = ca.CaseNo 
                            WHERE ca.AdvtId = ? AND ca.Status = 'Active' 
                            ORDER BY c.CaseName");
    $stmt->execute([$advocate_id]);
    $cases = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error loading cases: " . $e->getMessage();
}

// Get selected case or first case
$selected_case = $_GET['case'] ?? ($cases[0]['CaseNo'] ?? null);
$selected_client = null;

// Get messages for selected case
$messages = [];
if ($selected_case) {
    foreach ($cases as $c) {
        if ($c['CaseNo'] == $selected_case) {
            $selected_client = $c;
            break;
        }
    }
    
    if ($selected_client) {
        try {
            $stmt = $conn->prepare("SELECT m.*, a.FirstName, a.LastName 
                                    FROM MESSAGE m 
                                    LEFT JOIN ADVOCATE a ON m.AdvocateId = a.AdvtId 
                                    WHERE m.CaseNo = ? AND m.ClientId = ? 
                                    ORDER BY m.CreatedAt ASC");
            $stmt->execute([$selected_case, $selected_client['ClientId']]);
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Mark messages as read
            $stmt = $conn->prepare("UPDATE MESSAGE SET IsRead = TRUE WHERE CaseNo = ? AND ClientId = ? AND SenderRole = 'client'");
            $stmt->execute([$selected_case, $selected_client['ClientId']]);
        } catch(PDOException $e) {
            $error = "Error loading messages: " . $e->getMessage();
        }
    }
}

include 'header.php';
?>

<h2>Client Messages</h2>

<?php if ($message): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="chat-container" style="display: flex; gap: 20px; height: 600px;">
    <div class="chat-cases" style="width: 250px; background: white; border: 1px solid var(--border); border-radius: 8px; padding: 15px; overflow-y: auto;">
        <h4 style="margin-bottom: 15px; color: var(--primary-color);">My Cases</h4>
        <?php foreach ($cases as $case): ?>
            <div class="case-item <?php echo ($selected_case == $case['CaseNo']) ? 'active' : ''; ?>" 
                 onclick="window.location.href='?case=<?php echo $case['CaseNo']; ?>'"
                 style="padding: 10px; margin-bottom: 8px; border: 1px solid var(--border); border-radius: 6px; cursor: pointer; transition: all 0.3s;">
                <strong><?php echo htmlspecialchars($case['CaseName']); ?></strong>
                <br><small><?php echo htmlspecialchars($case['FirstName'] . ' ' . $case['LastName']); ?></small>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="chat-messages" style="flex: 1; background: white; border: 1px solid var(--border); border-radius: 8px; display: flex; flex-direction: column;">
        <?php if ($selected_case && $selected_client): ?>
            <div class="messages-header" style="padding: 15px; border-bottom: 1px solid var(--border); background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%); color: white; border-radius: 8px 8px 0 0;">
                <h3 style="margin: 0; color: white;"><?php echo htmlspecialchars($selected_client['CaseName']); ?></h3>
                <p style="margin: 5px 0 0 0; font-size: 14px; opacity: 0.9;">Client: <?php echo htmlspecialchars($selected_client['FirstName'] . ' ' . $selected_client['LastName']); ?></p>
            </div>
            
            <div class="messages-list" id="messagesList" style="flex: 1; padding: 15px; overflow-y: auto; display: flex; flex-direction: column; gap: 15px;">
                <?php if (count($messages) > 0): ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="message-item <?php echo $msg['SenderRole']; ?>" style="display: flex; gap: 10px;">
                            <div class="message-bubble" style="max-width: 70%; padding: 12px 16px; border-radius: 12px; word-wrap: break-word;
                                <?php
                                if ($msg['SenderRole'] == 'client') {
                                    echo 'background: #e5e7eb; color: var(--dark-text); margin-left: auto;';
                                } else {
                                    echo 'background: var(--primary-color); color: white;';
                                }
                                ?>">
                                <div><?php echo nl2br(htmlspecialchars($msg['Message'])); ?></div>
                                <div style="font-size: 11px; margin-top: 4px; opacity: 0.8;">
                                    <?php if ($msg['SenderRole'] == 'advocate'): ?>
                                        <?php echo htmlspecialchars($msg['FirstName'] . ' ' . $msg['LastName']); ?> • 
                                    <?php else: ?>
                                        Client • 
                                    <?php endif; ?>
                                    <?php echo date('M d, Y H:i', strtotime($msg['CreatedAt'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align: center; color: var(--gray); margin: auto;">No messages yet. Start the conversation!</p>
                <?php endif; ?>
            </div>
            
            <form method="POST" action="" class="message-form" style="padding: 15px; border-top: 1px solid var(--border);">
                <input type="hidden" name="case_no" value="<?php echo $selected_case; ?>">
                <input type="hidden" name="client_id" value="<?php echo $selected_client['ClientId']; ?>">
                <div style="display: flex; gap: 10px;">
                    <textarea name="message" rows="2" style="flex: 1; padding: 10px; border: 1px solid var(--border); border-radius: 6px; resize: none;" 
                              placeholder="Type your message..." required></textarea>
                    <button type="submit" name="send_message" class="btn btn-primary" style="padding: 10px 20px;">
                        <i class="fas fa-paper-plane"></i> Send
                    </button>
                </div>
            </form>
        <?php else: ?>
            <div style="display: flex; align-items: center; justify-content: center; height: 100%;">
                <p style="color: var(--gray);">Please select a case to view messages</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    const messagesList = document.getElementById('messagesList');
    if (messagesList) {
        messagesList.scrollTop = messagesList.scrollHeight;
    }
    
    setInterval(function() {
        if (document.visibilityState === 'visible') {
            location.reload();
        }
    }, 5000);
</script>

<?php include 'footer.php'; ?>
