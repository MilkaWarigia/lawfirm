<?php
/**
 * ADMIN - CALENDAR VIEW
 * Monthly calendar view for events
 */

require_once '../config/database.php';
require_once '../config/session.php';
requireRole('admin');

// Get current month/year or from query
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Validate month/year
if ($month < 1 || $month > 12) $month = date('n');
if ($year < 2020 || $year > 2100) $year = date('Y');

// Get first day of month and number of days
$first_day = mktime(0, 0, 0, $month, 1, $year);
$days_in_month = date('t', $first_day);
$day_of_week = date('w', $first_day); // 0 = Sunday

// Get events for this month
try {
    $start_date = date('Y-m-01', $first_day);
    $end_date = date('Y-m-t', $first_day);
    $stmt = $conn->prepare("SELECT e.*, c.CaseName 
                            FROM EVENT e 
                            JOIN `CASE` c ON e.CaseNo = c.CaseNo 
                            WHERE DATE(e.Date) >= ? AND DATE(e.Date) <= ?
                            ORDER BY e.Date ASC");
    $stmt->execute([$start_date, $end_date]);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Organize events by day
    $events_by_day = [];
    foreach ($events as $event) {
        $day = date('j', strtotime($event['Date']));
        $events_by_day[$day][] = $event;
    }
} catch(PDOException $e) {
    $error = "Error loading events: " . $e->getMessage();
}

// Navigation
$prev_month = $month - 1;
$prev_year = $year;
if ($prev_month < 1) {
    $prev_month = 12;
    $prev_year--;
}

$next_month = $month + 1;
$next_year = $year;
if ($next_month > 12) {
    $next_month = 1;
    $next_year++;
}

include 'header.php';
?>

<h2>Calendar View</h2>

<div class="card" style="margin-bottom: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <a href="?month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?>" class="btn btn-secondary">
            <i class="fas fa-chevron-left"></i> Previous
        </a>
        <h3 style="margin: 0;"><?php echo date('F Y', $first_day); ?></h3>
        <a href="?month=<?php echo $next_month; ?>&year=<?php echo $next_year; ?>" class="btn btn-secondary">
            Next <i class="fas fa-chevron-right"></i>
        </a>
        <a href="calendar.php" class="btn btn-primary">
            <i class="fas fa-calendar-day"></i> Today
        </a>
    </div>
</div>

<style>
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 1px;
        background: var(--border);
        border: 1px solid var(--border);
        border-radius: 8px;
        overflow: hidden;
    }
    .calendar-day-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: white;
        padding: 12px;
        text-align: center;
        font-weight: 600;
        font-size: 14px;
    }
    .calendar-day {
        background: white;
        min-height: 100px;
        padding: 8px;
        position: relative;
    }
    .calendar-day.other-month {
        background: #f8f9fa;
        color: #adb5bd;
    }
    .calendar-day.today {
        background: rgba(139, 92, 246, 0.1);
        border: 2px solid var(--primary-color);
    }
    .day-number {
        font-weight: 600;
        margin-bottom: 4px;
        font-size: 14px;
    }
    .event-item {
        background: var(--primary-color);
        color: white;
        padding: 2px 6px;
        margin: 2px 0;
        border-radius: 4px;
        font-size: 11px;
        cursor: pointer;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .event-item:hover {
        background: var(--primary-dark);
    }
</style>

<div class="calendar-grid">
    <?php
    $day_names = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    foreach ($day_names as $day_name): ?>
        <div class="calendar-day-header"><?php echo $day_name; ?></div>
    <?php endforeach; ?>
    
    <?php
    // Fill empty days before month starts
    for ($i = 0; $i < $day_of_week; $i++): ?>
        <div class="calendar-day other-month"></div>
    <?php endfor; ?>
    
    <?php
    // Days of the month
    $today = date('Y-m-d');
    for ($day = 1; $day <= $days_in_month; $day++):
        $current_date = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
        $is_today = ($current_date === $today);
        $day_events = $events_by_day[$day] ?? [];
    ?>
        <div class="calendar-day <?php echo $is_today ? 'today' : ''; ?>">
            <div class="day-number"><?php echo $day; ?></div>
            <?php foreach ($day_events as $event): ?>
                <div class="event-item" title="<?php echo htmlspecialchars($event['EventName'] . ' - ' . $event['CaseName']); ?>">
                    <?php echo htmlspecialchars(substr($event['EventName'], 0, 15)); ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endfor; ?>
    
    <?php
    // Fill remaining days
    $total_cells = 42; // 6 weeks * 7 days
    $filled_cells = $day_of_week + $days_in_month;
    $remaining = $total_cells - $filled_cells;
    for ($i = 0; $i < $remaining; $i++): ?>
        <div class="calendar-day other-month"></div>
    <?php endfor; ?>
</div>

<div style="margin-top: 20px;">
    <a href="events.php" class="btn btn-primary">
        <i class="fas fa-calendar-plus"></i> Manage Events
    </a>
</div>

<?php include 'footer.php'; ?>
