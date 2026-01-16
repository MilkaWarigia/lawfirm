<?php
/**
 * RECEPTIONIST DASHBOARD
 * Main page for receptionists
 */

require_once '../config/database.php';
require_once '../config/session.php';
requireRole('receptionist');

// Initialize variables
$total_clients = 0;
$total_cases = 0;
$upcoming_events = 0;
$pending_bills = 0;
$error = "";

// Get statistics
try {
    // Total clients
    $stmt = $conn->query("SELECT COUNT(*) as total FROM CLIENT");
    $total_clients = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total cases
    $stmt = $conn->query("SELECT COUNT(*) as total FROM `CASE`");
    $total_cases = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Upcoming events
    $stmt = $conn->query("SELECT COUNT(*) as total FROM EVENT WHERE Date >= NOW()");
    $upcoming_events = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Pending bills
    $stmt = $conn->query("SELECT COUNT(*) as total FROM BILLING WHERE Status = 'Pending'");
    $pending_bills = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
} catch(PDOException $e) {
    $error = "Error loading statistics: " . $e->getMessage();
}

include 'header.php';
?>

<div class="dashboard">
    <h2>Receptionist Dashboard</h2>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Clients</h3>
            <div class="number"><?php echo $total_clients; ?></div>
        </div>
        
        <div class="stat-card">
            <h3>Total Cases</h3>
            <div class="number"><?php echo $total_cases; ?></div>
        </div>
        
        <div class="stat-card">
            <h3>Upcoming Events</h3>
            <div class="number"><?php echo $upcoming_events; ?></div>
        </div>
        
        <div class="stat-card">
            <h3>Pending Bills</h3>
            <div class="number"><?php echo $pending_bills; ?></div>
        </div>
    </div>
    
    <div class="card">
        <h3>Upcoming Events (Next 7 Days)</h3>
        <?php
        try {
            $stmt = $conn->query("SELECT e.*, c.CaseName 
                                  FROM EVENT e 
                                  JOIN `CASE` c ON e.CaseNo = c.CaseNo 
                                  WHERE e.Date >= NOW() AND e.Date <= DATE_ADD(NOW(), INTERVAL 7 DAY) 
                                  ORDER BY e.Date ASC 
                                  LIMIT 10");
            $upcoming_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($upcoming_events) > 0):
        ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Event Name</th>
                            <th>Type</th>
                            <th>Date & Time</th>
                            <th>Case</th>
                            <th>Location</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($upcoming_events as $event): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($event['EventName']); ?></td>
                                <td><?php echo htmlspecialchars($event['EventType']); ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($event['Date'])); ?></td>
                                <td><?php echo htmlspecialchars($event['CaseName']); ?></td>
                                <td><?php echo htmlspecialchars($event['Location'] ?? '-'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="empty-state">No upcoming events</p>
        <?php endif; ?>
        <?php } catch(PDOException $e) { ?>
            <div class="alert alert-error">Error: <?php echo htmlspecialchars($e->getMessage()); ?></div>
        <?php } ?>
    </div>
</div>

<?php include 'footer.php'; ?>
