<?php
/**
 * EXPORT USERS SQL DUMP
 * Generates SQL INSERT statements for all users created by admin
 * This can be used to backup or migrate user data
 */

require_once '../config/database.php';
require_once '../config/session.php';
requireRole('admin');

header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="users_export_' . date('Y-m-d_His') . '.sql"');

try {
    echo "-- =====================================================\n";
    echo "-- USER EXPORT SQL DUMP\n";
    echo "-- Generated: " . date('Y-m-d H:i:s') . "\n";
    echo "-- Database: lawfirm_db\n";
    echo "-- =====================================================\n\n";
    
    // Export Admins
    echo "-- =====================================================\n";
    echo "-- ADMIN USERS\n";
    echo "-- =====================================================\n";
    $stmt = $conn->query("SELECT * FROM ADMIN ORDER BY AdminId");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($admins) > 0) {
        foreach ($admins as $admin) {
            echo "INSERT INTO ADMIN (FirstName, LastName, PhoneNo, Email, Username, Password) VALUES (";
            echo "'" . addslashes($admin['FirstName']) . "', ";
            echo "'" . addslashes($admin['LastName']) . "', ";
            echo "'" . addslashes($admin['PhoneNo']) . "', ";
            echo "'" . addslashes($admin['Email']) . "', ";
            echo "'" . addslashes($admin['Username']) . "', ";
            echo "'" . addslashes($admin['Password']) . "');\n";
        }
    } else {
        echo "-- No admin users found\n";
    }
    
    echo "\n";
    
    // Export Advocates
    echo "-- =====================================================\n";
    echo "-- ADVOCATE USERS\n";
    echo "-- =====================================================\n";
    $stmt = $conn->query("SELECT * FROM ADVOCATE ORDER BY AdvtId");
    $advocates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($advocates) > 0) {
        foreach ($advocates as $advocate) {
            echo "INSERT INTO ADVOCATE (FirstName, LastName, PhoneNo, Email, Address, Username, Password, Status) VALUES (";
            echo "'" . addslashes($advocate['FirstName']) . "', ";
            echo "'" . addslashes($advocate['LastName']) . "', ";
            echo "'" . addslashes($advocate['PhoneNo']) . "', ";
            echo "'" . addslashes($advocate['Email']) . "', ";
            echo "'" . addslashes($advocate['Address'] ?? '') . "', ";
            echo "'" . addslashes($advocate['Username']) . "', ";
            echo "'" . addslashes($advocate['Password']) . "', ";
            echo "'" . addslashes($advocate['Status']) . "');\n";
        }
    } else {
        echo "-- No advocate users found\n";
    }
    
    echo "\n";
    
    // Export Receptionists
    echo "-- =====================================================\n";
    echo "-- RECEPTIONIST USERS\n";
    echo "-- =====================================================\n";
    $stmt = $conn->query("SELECT * FROM RECEPTIONIST ORDER BY RecId");
    $receptionists = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($receptionists) > 0) {
        foreach ($receptionists as $receptionist) {
            echo "INSERT INTO RECEPTIONIST (FirstName, LastName, PhoneNo, Email, Username, Password) VALUES (";
            echo "'" . addslashes($receptionist['FirstName']) . "', ";
            echo "'" . addslashes($receptionist['LastName']) . "', ";
            echo "'" . addslashes($receptionist['PhoneNo']) . "', ";
            echo "'" . addslashes($receptionist['Email']) . "', ";
            echo "'" . addslashes($receptionist['Username']) . "', ";
            echo "'" . addslashes($receptionist['Password']) . "');\n";
        }
    } else {
        echo "-- No receptionist users found\n";
    }
    
    echo "\n-- =====================================================\n";
    echo "-- END OF EXPORT\n";
    echo "-- =====================================================\n";
    
} catch(PDOException $e) {
    echo "-- Error: " . $e->getMessage() . "\n";
}
?>
