<?php
/**
 * ADMIN - TASK MANAGEMENT
 * Create and manage tasks for cases
 */

require_once '../config/database.php';
require_once '../config/session.php';
requireRole('admin');

$message = "";
$error = "";

// Handle task creation/update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = $_POST['task_id'] ?? null;
    $case_no = $_POST['case_no'] ?? null;
    $task_title = trim($_POST['task_title'] ?? '');
    $task_description = trim($_POST['task_description'] ?? '');
    $assigned_to = $_POST['assigned_to'] ?? null;
    $assigned_role = $_POST['assigned_role'] ?? 'advocate';
    $priority = $_POST['priority'] ?? 'Medium';
    $due_date = $_POST['due_date'] ?? null;
    
    if (empty($case_no) || empty($task_title)) {
        $error = "Please fill in all required fields";
    } else {
        try {
            if ($task_id) {
                // Update task
                $stmt = $conn->prepare("UPDATE TASK SET CaseNo = ?, TaskTitle = ?, TaskDescription = ?, AssignedTo = ?, AssignedToRole = ?, Priority = ?, DueDate = ? WHERE TaskId = ?");
                $stmt->execute([$case_no, $task_title, $task_description, $assigned_to, $assigned_role, $priority, $due_date, $task_id]);
                $message = "Task updated successfully";
            } else {
                // Create task
                $stmt = $conn->prepare("INSERT INTO TASK (CaseNo, TaskTitle, TaskDescription, AssignedTo, AssignedToRole, Priority, DueDate, CreatedBy, CreatedByRole) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'admin')");
                $stmt->execute([$case_no, $task_title, $task_description, $assigned_to, $assigned_role, $priority, $due_date, $_SESSION['user_id']]);
                $message = "Task created successfully";
            }
        } catch(PDOException $e) {
            $error = "Error saving task: " . $e->getMessage();
        }
    }
}

// Handle task status update
if (isset($_GET['update_status']) && isset($_GET['id'])) {
    $task_id = $_GET['id'];
    $new_status = $_GET['update_status'];
    
    try {
        if ($new_status === 'Completed') {
            $stmt = $conn->prepare("UPDATE TASK SET Status = ?, CompletedAt = NOW() WHERE TaskId = ?");
        } else {
            $stmt = $conn->prepare("UPDATE TASK SET Status = ?, CompletedAt = NULL WHERE TaskId = ?");
        }
        $stmt->execute([$new_status, $task_id]);
        $message = "Task status updated";
    } catch(PDOException $e) {
        $error = "Error updating task: " . $e->getMessage();
    }
}

// Handle task delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM TASK WHERE TaskId = ?");
        $stmt->execute([$_GET['delete']]);
        $message = "Task deleted successfully";
    } catch(PDOException $e) {
        $error = "Error deleting task: " . $e->getMessage();
    }
}

// Get task for editing
$edit_task = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    try {
        $stmt = $conn->prepare("SELECT * FROM TASK WHERE TaskId = ?");
        $stmt->execute([$_GET['edit']]);
        $edit_task = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $error = "Error loading task: " . $e->getMessage();
    }
}

// Get all cases
try {
    $stmt = $conn->query("SELECT CaseNo, CaseName FROM `CASE` ORDER BY CaseName");
    $cases = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error loading cases: " . $e->getMessage();
}

// Get all advocates
try {
    $stmt = $conn->query("SELECT AdvtId, FirstName, LastName FROM ADVOCATE WHERE Status = 'Active' ORDER BY LastName");
    $advocates = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error loading advocates: " . $e->getMessage();
}

// Get all tasks
try {
    $stmt = $conn->query("SELECT t.*, c.CaseName, a.FirstName as AdvocateFirstName, a.LastName as AdvocateLastName 
                          FROM TASK t 
                          JOIN `CASE` c ON t.CaseNo = c.CaseNo 
                          LEFT JOIN ADVOCATE a ON t.AssignedTo = a.AdvtId AND t.AssignedToRole = 'advocate'
                          ORDER BY t.DueDate ASC, t.Priority DESC");
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error loading tasks: " . $e->getMessage();
}

include 'header.php';
?>

<h2>Task Management</h2>

<?php if ($message): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="form-container">
    <h3><?php echo $edit_task ? 'Edit Task' : 'Create New Task'; ?></h3>
    <form method="POST" action="">
        <?php if ($edit_task): ?>
            <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($edit_task['TaskId']); ?>">
        <?php endif; ?>
        
        <div class="form-group">
            <label for="case_no">Case *</label>
            <select id="case_no" name="case_no" required>
                <option value="">-- Select Case --</option>
                <?php foreach ($cases as $case): ?>
                    <option value="<?php echo htmlspecialchars($case['CaseNo']); ?>"
                            <?php echo ($edit_task && $edit_task['CaseNo'] == $case['CaseNo']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($case['CaseName']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="task_title">Task Title *</label>
            <input type="text" id="task_title" name="task_title" required 
                   value="<?php echo htmlspecialchars($edit_task['TaskTitle'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label for="task_description">Description</label>
            <textarea id="task_description" name="task_description" rows="3"><?php echo htmlspecialchars($edit_task['TaskDescription'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="assigned_role">Assign To Role *</label>
            <select id="assigned_role" name="assigned_role" required>
                <option value="advocate" <?php echo ($edit_task && $edit_task['AssignedToRole'] == 'advocate') ? 'selected' : ''; ?>>Advocate</option>
                <option value="receptionist" <?php echo ($edit_task && $edit_task['AssignedToRole'] == 'receptionist') ? 'selected' : ''; ?>>Receptionist</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="assigned_to">Assign To *</label>
            <select id="assigned_to" name="assigned_to" required>
                <option value="">-- Select --</option>
                <?php foreach ($advocates as $advocate): ?>
                    <option value="<?php echo htmlspecialchars($advocate['AdvtId']); ?>"
                            <?php echo ($edit_task && $edit_task['AssignedTo'] == $advocate['AdvtId']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($advocate['FirstName'] . ' ' . $advocate['LastName']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="priority">Priority *</label>
            <select id="priority" name="priority" required>
                <option value="Low" <?php echo ($edit_task && $edit_task['Priority'] == 'Low') ? 'selected' : ''; ?>>Low</option>
                <option value="Medium" <?php echo ($edit_task && $edit_task['Priority'] == 'Medium') ? 'selected' : ''; ?>>Medium</option>
                <option value="High" <?php echo ($edit_task && $edit_task['Priority'] == 'High') ? 'selected' : ''; ?>>High</option>
                <option value="Urgent" <?php echo ($edit_task && $edit_task['Priority'] == 'Urgent') ? 'selected' : ''; ?>>Urgent</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="due_date">Due Date</label>
            <input type="date" id="due_date" name="due_date" 
                   value="<?php echo $edit_task ? htmlspecialchars($edit_task['DueDate']) : ''; ?>">
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><?php echo $edit_task ? 'Update Task' : 'Create Task'; ?></button>
            <?php if ($edit_task): ?>
                <a href="tasks.php" class="btn btn-secondary">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="card mt-20">
    <h3>All Tasks</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Task</th>
                    <th>Case</th>
                    <th>Assigned To</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Due Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (isset($tasks) && count($tasks) > 0): ?>
                    <?php foreach ($tasks as $task): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($task['TaskTitle']); ?></strong>
                                <?php if ($task['TaskDescription']): ?>
                                    <br><small style="color: var(--gray);"><?php echo htmlspecialchars(substr($task['TaskDescription'], 0, 50)); ?>...</small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($task['CaseName']); ?></td>
                            <td>
                                <?php if ($task['AdvocateFirstName']): ?>
                                    <?php echo htmlspecialchars($task['AdvocateFirstName'] . ' ' . $task['AdvocateLastName']); ?>
                                <?php else: ?>
                                    <span style="color: var(--gray);">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;
                                    <?php
                                    if ($task['Priority'] == 'Urgent') echo 'background: #ef4444; color: white;';
                                    elseif ($task['Priority'] == 'High') echo 'background: #f59e0b; color: white;';
                                    elseif ($task['Priority'] == 'Medium') echo 'background: #3b82f6; color: white;';
                                    else echo 'background: #10b981; color: white;';
                                    ?>">
                                    <?php echo htmlspecialchars($task['Priority']); ?>
                                </span>
                            </td>
                            <td>
                                <select onchange="window.location.href='?update_status=' + this.value + '&id=<?php echo $task['TaskId']; ?>'" 
                                        style="padding: 4px 8px; border-radius: 4px; border: 1px solid var(--border);">
                                    <option value="Pending" <?php echo $task['Status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="In Progress" <?php echo $task['Status'] == 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="Completed" <?php echo $task['Status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="Cancelled" <?php echo $task['Status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </td>
                            <td>
                                <?php if ($task['DueDate']): ?>
                                    <?php 
                                    $due_date = strtotime($task['DueDate']);
                                    $today = strtotime('today');
                                    $style = ($due_date < $today && $task['Status'] != 'Completed') ? 'color: #ef4444; font-weight: bold;' : '';
                                    ?>
                                    <span style="<?php echo $style; ?>">
                                        <?php echo date('Y-m-d', $due_date); ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: var(--gray);">No due date</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="?edit=<?php echo $task['TaskId']; ?>" class="btn btn-sm btn-success">Edit</a>
                                <a href="?delete=<?php echo $task['TaskId']; ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Are you sure you want to delete this task?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="empty-state">No tasks found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>
