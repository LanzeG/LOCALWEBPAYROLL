<!DOCTYPE html>
<html lang="en">

<head>
  <title>Manual Time In</title>
  <link rel="icon" type="image/png" href="../img/icon1 (3).png">

  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <!-- <link rel="stylesheet" href="../css/bootstrap.min.css" /> -->
<!-- <link rel="stylesheet" href="../css/bootstrap-responsive.min.css" /> -->
<!-- <link rel="stylesheet" href="../css/fullcalendar.css" /> -->
<!-- <link rel="stylesheet" href="../css/maruti-style.css" /> -->
<!-- <link rel="stylesheet" href="../css/maruti-media.css" class="skin-color" /> -->
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.css" />
<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.min.js"></script>
<script src="../timepicker/jquery.timepicker.min.js"></script>
<script src="../timepicker/jquery.timepicker.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>.
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap">
    <!-- Bootstrap JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js"></script>
<?php
include("../DBCONFIG.PHP");
include("../LoginControl.php");
include("../BASICLOGININFO.PHP");

$adminId = $_SESSION['adminId'];
$error = false;
$adminname = "SELECT first_name, last_name FROM employees where emp_id = '$adminId'";
$adminnameexecqry = mysqli_query($conn, $adminname) or die ("FAILED TO CHECK EMP ID ".mysqli_error($conn));
$adminData = mysqli_fetch_assoc($adminnameexecqry);

$adminFullName = $adminData['first_name'] . " " . $adminData['last_name'];

session_start();
  if (!isset($_SESSION['adminId'])) {
  // Redirect to the desired page
  header("Location: ../default.php"); // Change 'login.php' to the desired page
  exit; // Terminate script execution after redirection
}
$getemp = "SELECT * FROM employees ORDER BY last_name ASC";
$result = mysqli_query($conn, $getemp);

if (isset($_POST['submittime'])) {

    // Get values from the form
    $timein = $_POST['timein'] ?? '00:00';
    $timeout = $_POST['timeout'] ?? '00:00';
    $employeeID = mysqli_real_escape_string($conn, $_POST['emp']);
    $date = mysqli_real_escape_string($conn, $_POST['date']) ?? '0000-00-00';

    // Create DateTime objects for time calculations
    $timeinObj = new DateTime($timein);
    $timeoutObj = new DateTime($timeout);
    
    //check if they have input
    if ($employeeID != "" && !empty($timeout)  && !empty($date)) {
        $searchquery = "SELECT * FROM time_keeping WHERE emp_id = '$employeeID' AND timekeep_day = '$date'";
        $searchexecquery = mysqli_query($conn,$searchquery) or die ("FAILED TO SEARCH ".mysqli_error($conn));
        $searchrows = mysqli_num_rows($searchexecquery);
        $searcharray = mysqli_fetch_array($searchexecquery);
        
            if(!$searchrows>0){
                $employeeType = ''; // Initialize employee type variable

                // Perform a database query to get the employee type directly
                $typeQuery = "SELECT acct_type FROM employees WHERE emp_id = '$employeeID'";
                $result = mysqli_query($conn, $typeQuery);
            
                if ($result && mysqli_num_rows($result) > 0) {
                    $row = mysqli_fetch_assoc($result);
                    $employeeType = $row['acct_type'];
                } else {
                    echo "Error: Unable to determine employee type.";
                }
                switch ($employeeType) {
                    case 'Faculty':
                       handleFacultyActions($conn, $employeeID, $date, $timein, $timeout);
                        break;
                    case 'Faculty w/ Admin':
                        handleFacultyAdminActions($conn, $employeeID, $date, $timein, $timeout);
                        break;
                    case 'Administrator':
                    case 'Master':
                    case 'Employee':
                        handleAdminActions($conn, $employeeID, $date, $timein, $timeout);
                        break;
                    default:
                        echo "Invalid employee type.";
                        break;
                }
                
                
                
            } else {
                
              ?><script>
              document.addEventListener('DOMContentLoaded', function() {
                  swal({
                    // title: "Data ",
                    text: "Already have Timelog for today.",
                    icon: "error",
                    button: "Try Again",
                  });
              }); </script>
              <?php
            }


    }else{
      ?><script>
      document.addEventListener('DOMContentLoaded', function() {
          swal({
            // title: "Data ",
            text: "Something went wrong",
            icon: "error",
            button: "Try Again",
          });
      }); </script>
      <?php
    }


// Close the connection
mysqli_close($conn);
}
?>
</head>
<script>
<?php
// Function to handle actions for admin employees
function handleAdminActions($conn, $employeeID, $date, $timein, $timeout) {
    $adminId = $_SESSION['adminId'];
    $error = false;
    $adminname = "SELECT first_name, last_name FROM employees where emp_id = '$adminId'";
    $adminnameexecqry = mysqli_query($conn, $adminname) or die ("FAILED TO CHECK EMP ID ".mysqli_error($conn));
    $adminData = mysqli_fetch_assoc($adminnameexecqry);
    
    $adminFullName = $adminData['first_name'] . " " . $adminData['last_name'];
    
    // Create DateTime objects for time calculations
    $timeinObj = new DateTime($timein);
    $timeoutObj = new DateTime($timeout);
    
    $standardStartTime = new DateTime('07:00'); // Standard start time as DateTime object
    if ($timeinObj < $standardStartTime) {
        $latenessInterval = new DateInterval('PT0S'); // Set lateness interval to zero seconds
    } else {
        // Calculate the difference between the given time and the standard start time
        $latenessInterval = $timeinObj->diff($standardStartTime);
    }

    // Extract the total lateness in minutes
    $latenessMinutes = ($latenessInterval->days * 24 * 60) + ($latenessInterval->h * 60) + $latenessInterval->i;

    $employmentQuery = "SELECT employment_type FROM employees WHERE emp_id = '$employeeID'";
    $employmentResult = mysqli_query($conn, $employmentQuery);
    $employmentData = mysqli_fetch_assoc($employmentResult);

    if ($employmentData) {
        $employmentType = $employmentData['employment_type'];

        // Now you can check if the employee is full-time or part-time
        if ($employmentType == 'Permanent') {
            // Employee is full-time
            $standardWorkingHours = 9;

            $actualWorkingHours = ($timeoutObj->getTimestamp() - $timeinObj->getTimestamp()) / 3600;

            if ($actualWorkingHours >= 12) {
                $serviceType = "Evening Service";
            } else {
                $serviceType = "";
            }
        } elseif ($employmentType == 'Contractual') {
            // Employee is part-time
            $standardWorkingHours = 5;
            $actualWorkingHours = min(9, ($timeoutObj->getTimestamp() - $timeinObj->getTimestamp()) / 3600);
            $serviceType = "";
        }

        if ($actualWorkingHours < $standardWorkingHours) {
            $undertimeHours = $standardWorkingHours - $actualWorkingHours;
            $totalLatenessUndertime = $latenessMinutes + $undertimeHours * 60;  // Combine lateness and undertime in minutes
        } elseif ($actualWorkingHours > $standardWorkingHours) {
            $actualWorkingHours = $standardWorkingHours;
            $undertimeHours = 0;
            $totalLatenessUndertime = $latenessMinutes + $undertimeHours * 60;
        } else {
            $undertimeHours = 0;
            $totalLatenessUndertime = ($latenessMinutes + $undertimeHours) * 60; // Combine lateness and undertime in minutes
        }

        // Insert data into the timekeeping table
        $insertQuery = "INSERT INTO time_keeping (emp_id, in_morning, out_afternoon, timekeep_day, undertime_hours, hours_work, timekeep_remarks) VALUES ('$employeeID', '$timein', '$timeout', '$date','$totalLatenessUndertime', '$actualWorkingHours', '$serviceType')";
        $insertResult = mysqli_query($conn, $insertQuery);

        if ($insertResult) {
            //dtr
            $dtrQuery = "INSERT INTO dtr (emp_id, in_morning, out_afternoon, hours_worked, undertimehours, DTR_day, DTR_remarks) values ('$employeeID', '$timein', '$timeout', '$actualWorkingHours','$totalLatenessUndertime','$date','$serviceType')";
            $dtrResult = mysqli_query($conn, $dtrQuery);
            //absent
            $deleteQuery = "DELETE FROM absences WHERE emp_id = '$employeeID' AND absence_date = '$date' ";
            $deleteResult = mysqli_query($conn, $deleteQuery);
            //actlog
            $activityLog = "Added a new time in for $employeeID";
            $adminActivityQuery = "INSERT INTO adminactivity_log (emp_id, adminname, activity,log_timestamp) VALUES ('$adminId', '$adminFullName','$activityLog', NOW())";
            $adminActivityResult = mysqli_query($conn, $adminActivityQuery);
            if (!$adminActivityResult) {
                // If there's an error, display the error message
                echo 'Error: ' . mysqli_error($conn);
            }

            ?>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    swal({
                        //  title: "Good job!",
                        text: "Data inserted successfully",
                        icon: "success",
                        button: "OK",
                    }).then(function () {
                        window.location.href = 'manualtime.php'; // Replace 'your_new_page.php' with the actual URL
                    });
                });
            </script>
            <?php
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}


//function pure faculty
function handleFacultyActions($conn, $employeeID, $date, $timein, $timeout){
    $adminId = $_SESSION['adminId'];
    $adminname = "SELECT first_name, last_name FROM employees WHERE emp_id = '$adminId'";
    $adminnameexecqry = mysqli_query($conn, $adminname) or die ("FAILED TO CHECK EMP ID " . mysqli_error($conn));
    $adminData = mysqli_fetch_assoc($adminnameexecqry);
    $adminFullName = $adminData['first_name'] . " " . $adminData['last_name'];
    
    $sy = "";
    $sem = "";
    
    $sql_sy = "SELECT CONCAT(start_year, '-', end_year) AS school_year FROM school_years WHERE is_current = 1";
    $result_sy = mysqli_query($conn, $sql_sy);
    if (mysqli_num_rows($result_sy) > 0) {
      $row_sy = mysqli_fetch_assoc($result_sy);
      $sy = $row_sy['school_year'];
    } 
    
    $sql_sem = "SELECT id FROM semesters WHERE is_current = 1";
    $result_sem = mysqli_query($conn, $sql_sem);
    if (mysqli_num_rows($result_sem) > 0) {
      $row_sem = mysqli_fetch_assoc($result_sem);
      $sem = $row_sem['id'];
    }
    
    // echo $sy, $sem;
        
    $checkScheduleSql = "SELECT COUNT(*) AS schedule_count
                         FROM schedule
                         WHERE emp_id = '$employeeID'
                         AND day_of_week = '" . date('l', strtotime($date)) . "'
                         AND sy = '$sy'
                         AND semester = '$sem'";
                     
    $scheduleResult = mysqli_query($conn, $checkScheduleSql);
    $scheduleRow = mysqli_fetch_assoc($scheduleResult);
    $scheduleCount = $scheduleRow['schedule_count'];
    
    if ($scheduleCount > 0) {
        $insertSql = "INSERT INTO time_keeping (emp_id, in_morning, out_afternoon, timekeep_day) VALUES ('$employeeID', '$timein', '$timeout', '$date')";
        if (mysqli_query($conn, $insertSql)) {

            $updateSql = "UPDATE time_keeping AS t
                          JOIN schedule AS s ON t.emp_id = s.emp_id 
                                              AND s.day_of_week = '" . date('l', strtotime($date)) . "' 
                                              AND s.schedule_type = 'Official'
                          SET t.hours_work = 
                              CASE
                                  WHEN t.in_morning > s.start_time THEN TIMESTAMPDIFF(MINUTE, t.in_morning, LEAST(t.out_afternoon, s.end_time)) / 60.0
                                  ELSE TIMESTAMPDIFF(MINUTE, s.start_time, LEAST(t.out_afternoon, s.end_time)) / 60.0
                              END,
                              t.undertime_hours = 
                                  CASE
                                      WHEN t.in_morning > s.start_time THEN TIMESTAMPDIFF(MINUTE, s.start_time, t.in_morning)
                                      ELSE 0
                                  END +
                                  CASE
                                      WHEN t.out_afternoon < s.end_time THEN TIMESTAMPDIFF(MINUTE, t.out_afternoon, s.end_time)
                                      ELSE 0
                                  END
                          WHERE t.timekeep_day = '$date' AND t.emp_id = '$employeeID'";

            if (mysqli_query($conn, $updateSql)) {

                $dtrInsertSql = "INSERT INTO dtr (emp_id, in_morning, out_afternoon, hours_worked, undertimehours, DTR_day) 
                                 SELECT emp_id, in_morning, out_afternoon, hours_work, undertime_hours, '$date'
                                 FROM time_keeping
                                 WHERE timekeep_day = '$date' AND emp_id = '$employeeID'";
                if (mysqli_query($conn, $dtrInsertSql)) {

                    // Get all overload schedules for the employee on that day
                    $checkCoverageSql = "SELECT s.schedule_id, s.start_time, s.end_time
                                         FROM schedule AS s
                                         WHERE s.emp_id = '$employeeID'
                                         AND s.day_of_week = '" . date('l', strtotime($date)) . "'
                                         AND s.schedule_type = 'Overload'";
                    $result = mysqli_query($conn, $checkCoverageSql);
                    if ($result) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $scheduleId = $row['schedule_id'];
                            $overloadStart = $row['start_time'];
                            $overloadEnd = $row['end_time'];

                            // Calculate the overlap
                            $overlapStart = max($timein, $overloadStart);
                            $overlapEnd = min($timeout, $overloadEnd);
                            $overloadHours = max(0, (strtotime($overlapEnd) - strtotime($overlapStart)) / 3600);

                            if ($overloadHours > 0) {
                                $insertOverloadSql = "INSERT INTO overload (timekeep_id, schedule_id, overload_hours)
                                                      SELECT t.timekeep_id, '$scheduleId', '$overloadHours'
                                                      FROM time_keeping AS t
                                                      WHERE t.timekeep_day = '$date' AND t.emp_id = '$employeeID'";
                                if (mysqli_query($conn, $insertOverloadSql)) {
                                    // Log the admin activity
                                    $activityLog = "Added a new time in for $employeeID";
                                    $adminActivityQuery = "INSERT INTO adminactivity_log (emp_id, adminname, activity, log_timestamp) VALUES ('$adminId', '$adminFullName', '$activityLog', NOW())";
                                    mysqli_query($conn, $adminActivityQuery);
                                } else {
                                    echo "<script>
                                            swal({
                                                text: 'Error inserting overload record: " . mysqli_error($conn) . "',
                                                icon: 'error',
                                                button: 'OK'
                                            });
                                          </script>";
                                }
                            }
                        }

                        // Remove absence if any
                        $removeAbsenceSql = "DELETE FROM absences WHERE emp_id = '$employeeID' AND absence_date = '$date'";
                        mysqli_query($conn, $removeAbsenceSql);

                        echo "<script>
                                swal({
                                    text: 'Record inserted and updated successfully.',
                                    icon: 'success',
                                    button: 'OK'
                                }).then(function() {
                                    window.location.href = 'manualtime.php'; // Redirect to the desired page
                                });
                              </script>";
                    } else {
                        echo "<script>
                                swal({
                                    text: 'Error checking coverage: " . mysqli_error($conn) . "',
                                    icon: 'error',
                                    button: 'OK'
                                });
                              </script>";
                    }
                } else {
                    echo "<script>
                            swal({
                                text: 'Error inserting into DTR: " . mysqli_error($conn) . "',
                                icon: 'error',
                                button: 'OK'
                            });
                          </script>";
                }
            } else {
                echo "<script>
                        swal({
                            text: 'Error updating record: " . mysqli_error($conn) . "',
                            icon: 'error',
                            button: 'OK'
                        });
                      </script>";
            }
        } else {
            echo "<script>
                    swal({
                        text: 'Error inserting record: " . mysqli_error($conn) . "',
                        icon: 'error',
                        button: 'OK'
                    });
                  </script>";
        }
    } else {
        echo "<script>
                swal({
                    text: 'Error: No schedule found for the given date, employee, academic year, and semester.',
                    icon: 'error',
                    button: 'OK'
                });
              </script>";
    }
}



//function for fac w/ admin
function handleFacultyAdminActions($conn, $employeeID, $date, $timein, $timeout) {
    $adminId = $_SESSION['adminId'];
    $adminname = "SELECT first_name, last_name FROM employees WHERE emp_id = '$adminId'";
    $adminnameexecqry = mysqli_query($conn, $adminname) or die ("FAILED TO CHECK EMP ID " . mysqli_error($conn));
    $adminData = mysqli_fetch_assoc($adminnameexecqry);
    $adminFullName = $adminData['first_name'] . " " . $adminData['last_name'];

    // Create DateTime objects for time calculations
    $timeinObj = new DateTime($timein);
    $timeoutObj = new DateTime($timeout);
    $standardStartTime = new DateTime('07:00'); // Standard start time as DateTime object

    if ($timeinObj < $standardStartTime) {
        $latenessInterval = new DateInterval('PT0S'); // Set lateness interval to zero seconds
    } else {
        // Calculate the difference between the given time and the standard start time
        $latenessInterval = $timeinObj->diff($standardStartTime);
    }

    // Extract the total lateness in minutes
    $latenessMinutes = ($latenessInterval->days * 24 * 60) + ($latenessInterval->h * 60) + $latenessInterval->i;

    $employmentQuery = "SELECT employment_type FROM employees WHERE emp_id = '$employeeID'";
    $employmentResult = mysqli_query($conn, $employmentQuery);
    $employmentData = mysqli_fetch_assoc($employmentResult);

    if ($employmentData) {
        $employmentType = $employmentData['employment_type'];

        if ($employmentType == 'Permanent') {
            $standardWorkingHours = 9;
            $actualWorkingHours = ($timeoutObj->getTimestamp() - $timeinObj->getTimestamp()) / 3600;
        } elseif ($employmentType == 'Contractual') {
            $standardWorkingHours = 5;
            $actualWorkingHours = min(5, ($timeoutObj->getTimestamp() - $timeinObj->getTimestamp()) / 3600);
        }

        if ($actualWorkingHours < $standardWorkingHours) {
            $undertimeHours = $standardWorkingHours - $actualWorkingHours;
            $totalLatenessUndertime = $latenessMinutes + $undertimeHours * 60; // Combine lateness and undertime in minutes
        } else {
            $undertimeHours = 0;
            $totalLatenessUndertime = $latenessMinutes; // Only lateness, no undertime
        }

        // Insert data into the timekeeping table
        $insertQuery = "INSERT INTO time_keeping (emp_id, in_morning, out_afternoon, timekeep_day, undertime_hours, hours_work, timekeep_remarks) VALUES ('$employeeID', '$timein', '$timeout', '$date', '$totalLatenessUndertime', '$actualWorkingHours', '')";
        $insertResult = mysqli_query($conn, $insertQuery);

        if ($insertResult) {
            // Insert into dtr
            $dtrQuery = "INSERT INTO dtr (emp_id, in_morning, out_afternoon, hours_worked, undertimehours, DTR_day, DTR_remarks) VALUES ('$employeeID', '$timein', '$timeout', '$actualWorkingHours','$totalLatenessUndertime','$date','')";
            $dtrResult = mysqli_query($conn, $dtrQuery);

            // Delete any absence record for the same day
            $deleteQuery = "DELETE FROM absences WHERE emp_id = '$employeeID' AND absence_date = '$date'";
            $deleteResult = mysqli_query($conn, $deleteQuery);

            // Insert into admin activity log
            $activityLog = "Added a new time in for $employeeID";
            $adminActivityQuery = "INSERT INTO adminactivity_log (emp_id, adminname, activity, log_timestamp) VALUES ('$adminId', '$adminFullName', '$activityLog', NOW())";
            $adminActivityResult = mysqli_query($conn, $adminActivityQuery);
            if (!$adminActivityResult) {
                echo 'Error: ' . mysqli_error($conn);
            }

            $sy = "";
            $sem = "";
            
            $sql_sy = "SELECT CONCAT(start_year, '-', end_year) AS school_year FROM school_years WHERE is_current = 1";
            $result_sy = mysqli_query($conn, $sql_sy);
            if (mysqli_num_rows($result_sy) > 0) {
              $row_sy = mysqli_fetch_assoc($result_sy);
              $sy = $row_sy['school_year'];
            } 
            
            $sql_sem = "SELECT id FROM semesters WHERE is_current = 1";
            $result_sem = mysqli_query($conn, $sql_sem);
            if (mysqli_num_rows($result_sem) > 0) {
              $row_sem = mysqli_fetch_assoc($result_sem);
              $sem = $row_sem['id'];
            }
                

            // Get all overload schedules for the employee on that day
            $checkCoverageSql = "SELECT s.schedule_id, s.start_time, s.end_time
                                 FROM schedule AS s
                                 WHERE s.emp_id = '$employeeID'
                                   AND s.day_of_week = '" . date('l', strtotime($date)) . "'
                                   AND s.schedule_type = 'Overload'
                                   AND s.sy = '$sy'
                                   AND s.semester = '$sem'";

            $result = mysqli_query($conn, $checkCoverageSql);
            if ($result) {
                $rowCount = mysqli_num_rows($result);
                    if ($rowCount > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                    $scheduleId = $row['schedule_id'];
                    $overloadStart = $row['start_time'];
                    $overloadEnd = $row['end_time'];

                    // Calculate the overlap
                    $overlapStart = max($timein, $overloadStart);
                    $overlapEnd = min($timeout, $overloadEnd);
                    $overloadHours = max(0, (strtotime($overlapEnd) - strtotime($overlapStart)) / 3600);

                    if ($overloadHours > 0) {
                        $insertOverloadSql = "INSERT INTO overload (timekeep_id, schedule_id, overload_hours)
                                              SELECT t.timekeep_id, '$scheduleId', '$overloadHours'
                                              FROM time_keeping AS t
                                              WHERE t.timekeep_day = '$date' AND t.emp_id = '$employeeID'";
                        if (mysqli_query($conn, $insertOverloadSql)) {
                            // Overload record inserted successfully
                        } else {
                            echo "<script>
                                    swal({
                                        text: 'Error inserting overload record: " . mysqli_error($conn) . "',
                                        icon: 'error',
                                        button: 'OK'
                                    });
                                  </script>";
                        }
                    }
                }
                    } else {
                         
                        // Check for evening service eligibility if no overload hours
                        if ($actualWorkingHours >= 12) {
                            $serviceType = "Evening Service";
        
                            // Update the timekeeping and dtr tables with the evening service remark
                            $updateTimekeeping = "UPDATE time_keeping SET timekeep_remarks = '$serviceType' WHERE emp_id = '$employeeID' AND timekeep_day = '$date'";
                            $updateDtr = "UPDATE dtr SET DTR_remarks = '$serviceType' WHERE emp_id = '$employeeID' AND DTR_day = '$date'";
                            
                            mysqli_query($conn, $updateTimekeeping);
                            mysqli_query($conn, $updateDtr);
                        }
                    }  

                ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        swal({
                            text: "Data inserted successfully",
                            icon: "success",
                            button: "OK",
                        }).then(function () {
                            window.location.href = 'manualtime.php';
                        });
                    });
                </script>
                <?php

        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}
}


?>

  function toggleCollapse() {
    var content = document.getElementById("content1");
    content.classList.toggle("collapsed");
}
</script>
<body>

  <!--Header-part-->

  <?php
  include('navbarAdmin.php');
  ?>
<style>
  #content1 {
    display: block;
}

#content1.collapsed {
    display: none;
}
</style>
<div class="title d-flex justify-content-center pt-4 py-3">
    <h3>Manual Timekeeping Record Entry</h3>
</div>

<div id="content" class="container">
    <div class="card shadow p-3">
        <form action="" method="POST">
            <div class="row">
                <div class="col-md-8">
                    <label for="employee">Select an Employee</label>
                    <select name="emp" class="form-select" id="emp" required>
                        <option value="">Select an employee</option>
                        <?php
                        if ($result) {
                            // Fetch rows from the result set
                            while ($row = mysqli_fetch_array($result)) {
                                // Output an <option> element for each employee
                                echo '<option value="' . $row['emp_id'] . '">' . $row['last_name'] . '</option>';
                            }

                            // Move the fetch to the beginning to get the first row
                            mysqli_data_seek($result, 0);
                            $initialRow = mysqli_fetch_array($result);

                            // Output the initial employee ID directly in the input field
                            echo '<script>document.getElementById("empID").value = ' . $initialRow['emp_id'] . ';</script>';
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4 mt-3 mt-md-0">
                    <label for="empID">Employee ID</label>
                    <input type="text" class="form-control" id="empID" readonly required>
                </div>
                <div class="col-md-4 mt-3">
                    <label for="timein">Enter Time In:</label>
                    <input name="timein" type="time" class="form-control" id="timein" required>
                </div>
                <div class="col-md-4 mt-3">
                    <label for="timeout">Enter Time Out:</label>
                    <input name="timeout" type="time" class="form-control" id="timeout" required>
                </div>
                <div class="col-md-4 mt-3">
                    <label for="date">Enter Date:</label>
                    <input name="date" type="date" class="form-control" id="date" required>
                </div>
            </div>

            <div class="buttons btn-group mt-3 d-flex justify-content-center">
                <!-- <button type="submit"  class="btn btn-info m-2"><i class="fa-solid fa-pen-to-square"></i> Edit Record</button> -->
                <button name="submittime" type="submit" class="inline-block bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-md border border-green-500 hover:border-green-600 transition duration-300 ease-in-out m-3"><i class="fas fa-plus mr-2"></i>Add Record</button>
                <!-- <button  type="submit" class="btn btn-danger m-2"><i class="fa-solid fa-trash"></i>Delete Record</button> -->
            </div>
        </form>
    </div>
</div>
   </div>
  </div>



<!-- end filter -->

<script>
    // jQuery script to handle dropdown change event
    $(document).ready(function () {
        // When the dropdown changes
        $('#emp').change(function () {
            // Get the selected employee's ID
            var selectedEmpID = $(this).val();

            // Update the empID input field with the selected ID
            $('#empID').val(selectedEmpID);
        });
    });
</script>

<script>
                flatpickr("#timein", {
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: "H:i",
                    time_24hr: true
                });
                flatpickr("#timeout", {
                  enableTime: true,
                  noCalendar: true,
                  dateFormat: "H:i",
                  time_24hr: true,
                  minTime: "00:00", // Minimum selectable time
                  maxTime: "20:00", // Maximum selectable time (7 PM)
              });
              document.addEventListener("DOMContentLoaded", function () {
                flatpickr("#date", {
                    dateFormat: "Y-m-d", // Adjust the date format as needed
                });
            });
            </script>

</body>
<style>
     body{
  font-family: 'Poppins', sans-serif;
}
</style>
</html>