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
    $updates = json_decode(file_get_contents('php://input'), true);

    foreach ($updates as $update) {
        $schedule_id = intval($update['schedule_id']);
        $emp_id = intval($update['emp_id']);
        $last_name = $update['last_name'];
        $sy = $update['sy'];
        $semester = $update['semester'];
        $start_time = $update['start_time'];
        $end_time = $update['end_time'];
        $schedule_type = $update['schedule_type'];
        $level = $update['level'];

        $stmt = $conn->prepare("UPDATE schedule SET emp_id=?, sy=?, semester=?, start_time=?, end_time=?, schedule_type=?, level=? WHERE schedule_id=?");
        $stmt->bind_param("issssssi", $emp_id, $sy, $semester, $start_time, $end_time, $schedule_type, $level, $schedule_id);
        //activity log
        $activityLog = "Updated schedule ($emp_id)";
        $adminActivityQuery = "INSERT INTO adminactivity_log (emp_id, adminname, activity,log_timestamp) VALUES ('$adminId', '$adminFullName','$activityLog', '$current_datetime')";
        $adminActivityResult = mysqli_query($conn, $adminActivityQuery);
        
        if (!$stmt->execute()) {
            echo "Error updating schedule for schedule_id {$schedule_id}: " . $stmt->error;
            $stmt->close();
            $conn->close();
            exit;
        }

        $stmt->close();
    }

    echo "Schedules updated successfully";
} else {
    echo "Invalid request method";
}

$conn->close();
