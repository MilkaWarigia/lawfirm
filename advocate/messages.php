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

// Debug: Show POST info on page (temporary for debugging)
$debug_post = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $debug_post = "POST received! Keys: " . implode(', ', array_keys($_POST));
    if (isset($_POST['send_message'])) {
        $debug_post .= " | send_message: YES | Case: " . ($_POST['case_no'] ?? 'missing') . " | Client: " . ($_POST['client_id'] ?? 'missing') . " | Message: '" . ($_POST['message'] ?? 'missing') . "'";
    } else {
        $debug_post .= " | send_message: NO";
    }
}


// Handle sending message - check for POST with message data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['send_message']) || (isset($_POST['message']) && !empty(trim($_POST['message']))))) {
    // Debug: Log POST data
    error_log("POST received - send_message: " . (isset($_POST['send_message']) ? 'yes' : 'no'));
    error_log("POST data: " . print_r($_POST, true));
    
    $case_no = $_POST['case_no'] ?? null;
    $client_id = $_POST['client_id'] ?? null;
    $message_text = trim($_POST['message'] ?? '');
    
    if (empty($case_no) || empty($client_id)) {
        $error = "Invalid case or client";
    } elseif (empty($message_text)) {
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
                $result = $stmt->execute([$case_no, $client_id, $advocate_id, $message_text]);
                
                if ($result) {
                    $message_id = $conn->lastInsertId();
                    if ($message_id > 0) {
                        header("Location: messages.php?case=" . urlencode($case_no) . "&sent=1");
                        exit();
                    } else {
                        $error_info = $stmt->errorInfo();
                        $error = "Message was not inserted. Error: " . ($error_info[2] ?? 'Unknown');
                    }
                } else {
                    $error_info = $stmt->errorInfo();
                    $error = "Failed to send message. SQL Error: " . ($error_info[2] ?? 'Unknown error');
                }
            } catch(PDOException $e) {
                if ($e->getCode() == '42S02') {
                    $error = "MESSAGE table does not exist in database. Please run database/create_message_table.sql in phpMyAdmin first.";
                } else {
                    $error = "Error sending message: " . $e->getMessage() . " (Code: " . $e->getCode() . ")";
                }
            }
        }
    }
}

// Check if message was just sent (used for auto-refresh cooldown, no alert shown)
// The sent parameter is used by JavaScript to prevent auto-refresh after sending

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

        <?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if (!empty($debug_post)): ?>
    <div class="alert" style="background: #fff3cd; border: 1px solid #ffc107; padding: 10px; margin: 10px 0;">
        <strong>Debug Info:</strong> <?php echo htmlspecialchars($debug_post); ?>
    </div>
<?php endif; ?>

<style>
.chat-container {
    display: flex;
    gap: 20px;
    height: 600px;
}
.chat-cases {
    width: 250px;
    background: white;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 15px;
    overflow-y: auto;
}
.case-item {
    padding: 10px;
    margin-bottom: 8px;
    border: 1px solid var(--border);
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s;
}
.case-item:hover {
    background: rgba(139, 92, 246, 0.1);
    border-color: var(--primary-color);
}
.case-item.active {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}
.chat-messages {
    flex: 1;
    background: white;
    border: 1px solid var(--border);
    border-radius: 8px;
    display: flex;
    flex-direction: column;
}
.messages-header {
    padding: 15px;
    border-bottom: 1px solid var(--border);
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    color: white;
    border-radius: 8px 8px 0 0;
}
.messages-list {
    flex: 1;
    padding: 15px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 15px;
    background: #f0f2f5;
}
.message-item {
    display: flex;
    gap: 10px;
}
/* Advocate view: Advocate messages on RIGHT, Client messages on LEFT */
.message-item.advocate {
    justify-content: flex-end;
}
.message-item.client {
    justify-content: flex-start;
}
.message-bubble {
    max-width: 70%;
    padding: 10px 14px;
    border-radius: 12px;
    word-wrap: break-word;
    position: relative;
}
/* Advocate messages (sent by advocate) - green, right side */
.message-item.advocate .message-bubble {
    background: #dcf8c6;
    color: #000;
    border-bottom-right-radius: 4px;
}
/* Client messages (received from client) - white, left side */
.message-item.client .message-bubble {
    background: white;
    color: var(--dark-text);
    border-bottom-left-radius: 4px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}
.message-time {
    font-size: 11px;
    margin-top: 4px;
    opacity: 0.7;
}
.message-form {
    padding: 10px 15px;
    border-top: 1px solid var(--border);
    background: white;
    display: flex;
    align-items: center;
    gap: 8px;
}
.message-input-wrapper {
    flex: 1;
    display: flex;
    align-items: center;
    background: #f0f2f5;
    border-radius: 24px;
    padding: 8px 16px;
    gap: 8px;
}
.message-input-wrapper input[type="text"] {
    flex: 1;
    border: none;
    background: transparent;
    outline: none;
    padding: 6px 0;
    font-size: 15px;
    color: var(--dark-text);
}
.message-input-wrapper input[type="text"]::placeholder {
    color: #999;
}
.send-btn {
    background: var(--primary-color);
    border: none;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
    font-size: 18px;
}
.send-btn:hover {
    background: var(--secondary-color);
    transform: scale(1.05);
}
.send-btn:disabled {
    background: #ccc;
    cursor: not-allowed;
}
</style>

<div class="chat-container">
    <div class="chat-cases">
        <h4 style="margin-bottom: 15px; color: var(--primary-color);">My Cases</h4>
        <?php foreach ($cases as $case): ?>
            <div class="case-item <?php echo ($selected_case == $case['CaseNo']) ? 'active' : ''; ?>" 
                 onclick="window.location.href='?case=<?php echo $case['CaseNo']; ?>'">
                <strong><?php echo htmlspecialchars($case['CaseName']); ?></strong>
                <br><small><?php echo htmlspecialchars($case['FirstName'] . ' ' . $case['LastName']); ?></small>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="chat-messages">
        <?php if ($selected_case && $selected_client): ?>
            <div class="messages-header">
                <h3 style="margin: 0; color: white;"><?php echo htmlspecialchars($selected_client['CaseName']); ?></h3>
                <p style="margin: 5px 0 0 0; font-size: 14px; opacity: 0.9;">Client: <?php echo htmlspecialchars($selected_client['FirstName'] . ' ' . $selected_client['LastName']); ?></p>
            </div>
            
            <div class="messages-list" id="messagesList">
                <?php if (count($messages) > 0): ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="message-item <?php echo $msg['SenderRole']; ?>">
                            <div class="message-bubble">
                                <?php if (!empty($msg['Message'])): ?>
                                    <div><?php echo nl2br(htmlspecialchars($msg['Message'])); ?></div>
                                <?php endif; ?>
                                <div class="message-time">
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
            
            <form method="POST" action="" class="message-form" id="messageForm" onsubmit="return handleFormSubmit(event);">
                <input type="hidden" name="case_no" value="<?php echo $selected_case; ?>">
                <input type="hidden" name="client_id" value="<?php echo $selected_client['ClientId']; ?>">
                <input type="hidden" name="1">
                <div class="message-input-wrapper">
                    <input type="text" name="message" id="messageInput" placeholder="Type a message..." autocomplete="off" required>
                </div>
                <button type="submit" name="send_message" class="send-btn" id="sendBtn" title="Send">
                    <i class="fas fa-paper-plane"></i>
                </button>
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
    
    const messageInput = document.getElementById('messageInput');
    const sendBtn = document.getElementById('sendBtn');
    
    function checkInput() {
        const hasText = messageInput.value.trim().length > 0;
        sendBtn.disabled = !hasText;
    }
    
    messageInput.addEventListener('input', checkInput);
    
    // Handle Enter key to submit form
    messageInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            const form = document.getElementById('messageForm');
            if (form && !formSubmitting) {
                const messageValue = messageInput.value.trim();
                if (messageValue) {
                    form.submit();
                }
            }
        }
    });
    
    checkInput();
    
    // Prevent duplicate form submissions
    let formSubmitting = false;
    function handleFormSubmit(e) {
        const messageInput = document.getElementById('messageInput');
        const messageValue = messageInput.value.trim();
        
        if (!messageValue) {
            e.preventDefault();
            alert('Please enter a message');
            return false;
        }
        
        if (formSubmitting) {
            e.preventDefault();
            console.log('Form already submitting, preventing duplicate');
            return false;
        }
        
        formSubmitting = true;
        const sendBtn = document.getElementById('sendBtn');
        // Don't disable - let it submit naturally with its name attribute
        sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        console.log('Form submitting...');
        // Form will submit naturally, button name will be included
        return true;
    }
    
    // Track if user is typing
    let isTyping = false;
    let typingTimeout;
    
    messageInput.addEventListener('focus', function() {
        isTyping = true;
    });
    
    messageInput.addEventListener('input', function() {
        isTyping = true;
        clearTimeout(typingTimeout);
        // Reset typing flag after 2 seconds of no typing
        typingTimeout = setTimeout(function() {
            isTyping = false;
        }, 2000);
    });
    
    messageInput.addEventListener('blur', function() {
        setTimeout(function() {
            isTyping = false;
        }, 1000);
    });
    
    // Track if message was just sent
    let messageJustSent = <?php echo (isset($_GET['sent']) && $_GET['sent'] == '1') ? 'true' : 'false'; ?>;
    let sendCooldown = 0;
    
    // Disable auto-refresh for 10 seconds after sending
    if (messageJustSent) {
        sendCooldown = 10;
        let cooldownInterval = setInterval(function() {
            sendCooldown--;
            if (sendCooldown <= 0) {
                clearInterval(cooldownInterval);
            }
        }, 1000);
    }
    
    // Auto-refresh every 5 seconds (only if user is not typing and not in cooldown)
    setInterval(function() {
        if (document.visibilityState === 'visible' && !isTyping && messageInput.value.trim() === '' && sendCooldown <= 0) {
            location.reload();
        }
    }, 5000);
    
</script>

<?php include 'footer.php'; ?>
