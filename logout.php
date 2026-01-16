<?php
/**
 * LOGOUT PAGE
 * This file destroys the session and logs the user out
 */

session_start();

// Destroy all session data
session_unset();
session_destroy();

// Redirect to login page
header("Location: index.php");
exit();
?>
