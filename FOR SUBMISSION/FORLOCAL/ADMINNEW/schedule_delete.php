<?php
include("../DBCONFIG.PHP");
include("../LoginControl.php");
include("../BASICLOGININFO.PHP");

date_default_timezone_set('Asia/Manila');
$current_datetime = date('Y-m-d H:i:s');

$adminId = $_SESSION['adminId'];
$adminname = "SELECT first_name, last_name FROM employees where emp_id = '$adminId'";
$adminnameexecqry = mysqli_query($conn, $adminname) or die ("FAILED TO CHECK EMP ID ".mysqli_error($conn));
$adminData = mysqli_fetch_assoc($adminnameexecqry);

$adminFullName = $adminData['first_name'] . " " . $adminData['last_name'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['schedule_id'])) {
        $schedule_id = $_POST['schedule_id'];

        // Prepare the SQL statement to delete the record
        $stmt = $conn->prepare("DELETE FROM schedule WHERE schedule_id = ?");
        $stmt->bind_param("i", $schedule_id);

        if ($stmt->execute()) {
            echo "Schedule deleted successfully";
                //activity log
            $activityLog = "Deleted Schedule";
            $adminActivityQuery = "INSERT INTO adminactivity_log (emp_id, adminname, activity,log_timestamp) VALUES ('$adminId', '$adminFullName','$activityLog', '$current_datetime')";
            $adminActivityResult = mysqli_query($conn, $adminActivityQuery);
        } else {
            // Return detailed error message
            echo "Error deleting schedule: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Schedule ID not provided";
    }
} else {
    echo "Invalid request method";
}

$conn->close();
?>
