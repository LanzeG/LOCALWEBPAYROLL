<?php
include 'DBCONFIG.PHP';
include 'logfunctions.PHP';
session_start();
$master = $_SESSION['master'];

// Log the logout action
if (isset($_SESSION['empID'])) {
    $userId = $_SESSION['empID'];
    $logoutTime = date('Y-m-d H:i:s');
    
    
    // Assuming you have a function like logLogout in your dbconfig.php
    logLogout($conn, $userId, $logoutTime);
} 
if (!$master && isset($_SESSION['adminId'])) {
    $userId = $_SESSION['adminId'];
    $logoutTime = date('Y-m-d H:i:s');

     //for adminname
     $adminname = "SELECT first_name, last_name FROM employees where emp_id = '$userId'";
     $adminnameexecqry = mysqli_query($conn, $adminname) or die ("FAILED TO CHECK EMP ID ".mysqli_error($conn));
     $adminData = mysqli_fetch_assoc($adminnameexecqry);

     $adminFullName = $adminData['first_name'] . " " . $adminData['last_name'];
    
    // Assuming you have a function like logLogout in your dbconfig.php
    adminlogLogout($conn, $userId, $adminFullName, $logoutTime);

}else if(isset($_SESSION['master'])) {
    $userId = $_SESSION['adminId'];
    $logoutTime = date('Y-m-d H:i:s');

     //for adminname
     $adminname = "SELECT first_name, last_name FROM employees where emp_id = '$userId'";
     $adminnameexecqry = mysqli_query($conn, $adminname) or die ("FAILED TO CHECK EMP ID ".mysqli_error($conn));
     $adminData = mysqli_fetch_assoc($adminnameexecqry);

     $adminFullName = $adminData['first_name'] . " " . $adminData['last_name'];
    
    // Assuming you have a function like logLogout in your dbconfig.php
    masteradminlogLogout($conn, $userId, $adminFullName, $logoutTime);
}


// Unset and destroy the session
session_unset();
session_destroy();

// Redirect to the login page
header("Location: default.php");
?>
