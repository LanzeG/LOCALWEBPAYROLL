<?php
include("/home/u387373332/domains/wbtkpayrollportal.com/public_html/DBCONFIG.PHP");

// Get the current date and year
$current_month = date("m");
$current_year = date("Y");

// Get the list of full-time employees from the employees table
$sql_full_time = "SELECT emp_id, acct_type FROM employees WHERE employment_type = 'Full Time'";
$result_full_time = $conn->query($sql_full_time);

if ($result_full_time) {
    while ($row = $result_full_time->fetch_assoc()) {
        $emp_id = $row['emp_id'];
        $acct_type = $row['acct_type'];

        // Check if the account type is not 'Faculty'
        if ($acct_type !== 'Faculty') {
            // Check if the employee already has a record for the current month and year
            $sql_existing_record = "SELECT * FROM leaves WHERE emp_id = $emp_id";
            $result_existing_record = $conn->query($sql_existing_record);

            if ($result_existing_record->num_rows == 0) {
                // If no record exists, insert a new record with leaves_count = 1.5
                $sql_insert_leave = "INSERT INTO leaves (emp_id, leave_count, vacleave_count) VALUES ($emp_id, 1.25, 1.25)";
                $conn->query($sql_insert_leave);
            } else {
                // If a record exists, update leaves_count by adding 1.5
                $sql_update_leave = "UPDATE leaves SET leave_count = leave_count + 1.25, vacleave_count = vacleave_count + 1.25 WHERE emp_id = $emp_id";
                $conn->query($sql_update_leave);
            }
        }
    }

    echo "<script>Leaves count updated successfully for full-time employees (excluding Faculty).</script>";
} else {
    echo "Error retrieving full-time employees: " . $conn->error . "";
}

// Close the database connection
$conn->close();
?>
