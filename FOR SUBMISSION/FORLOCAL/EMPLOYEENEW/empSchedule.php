<?php
session_start();
include("../DBCONFIG.PHP");
include("../LoginControl.php");
include("../BASICLOGININFO.PHP");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['empId']) || !isset($_SESSION['uname'])) {
    header("Location: employee-dashboard.php");
    exit();
}

$empid = $_SESSION['empId'];

$employeeQuery = "SELECT acct_type FROM employees WHERE emp_id = '$empid'";
$employeeResult = mysqli_query($conn, $employeeQuery) or die("FAILED TO CHECK EMP ID " . mysqli_error($conn));

$employeeData = mysqli_fetch_assoc($employeeResult);

if (!$employeeData || ($employeeData['acct_type'] !== 'Faculty' && $employeeData['acct_type'] !== 'Faculty w/ Admin')) {
    header("Location: employee-dashboard.php");
    exit();
}



$logged_in_emp_id = $_SESSION['empID'];

$sy_query = "SELECT DISTINCT sy FROM schedule";
$sy_result = $conn->query($sy_query);

$sem_query = "SELECT DISTINCT semester FROM schedule";
$sem_result = $conn->query($sem_query);

$type_query = "SELECT DISTINCT schedule_type FROM schedule";
$type_result = $conn->query($type_query);

$sy = isset($_GET['sy']) ? $_GET['sy'] : '';
$semester = isset($_GET['semester']) ? $_GET['semester'] : '';
$schedule_type = isset($_GET['schedule_type']) ? $_GET['schedule_type'] : '';

$sql = "SELECT 
            s.schedule_id,
            s.emp_id, 
            e.last_name, 
            s.sy, 
            s.semester, 
            s.day_of_week, 
            s.start_time, 
            s.end_time, 
            s.schedule_type, 
            s.level 
        FROM schedule s
        JOIN employees e ON s.emp_id = e.emp_id
        WHERE s.emp_id = '$logged_in_emp_id'";

if ($sy) {
    $sql .= " AND s.sy = '$sy'";
}

if ($semester) {
    $sql .= " AND s.semester = '$semester'";
}

if ($schedule_type) {
    $sql .= " AND s.schedule_type = '$schedule_type'";
}

$sql .= " ORDER BY s.semester, s.schedule_type, s.sy";

$result = $conn->query($sql);

$schedule_data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $schedule_data[$row["day_of_week"]][] = $row;
    }
}

$day_order = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

uksort($schedule_data, function($a, $b) use ($day_order) {
    $pos_a = array_search($a, $day_order);
    $pos_b = array_search($b, $day_order);
    return $pos_a - $pos_b;
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js"></script>
    <style>
        .filter-card {
            margin-top: 50px;
        }
        .form-group {
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <?php include('navbar2.php'); ?>
    <div class="title d-flex justify-content-center">
        <h3 style="margin-top:20px;">Faculty Schedule</h3>
    </div>
    <div class="container-fluid mt-5">
        <div class="card filter-card">
            <div class="card-body">
                <form method="GET" action="">
                    <div class="row mb-3">
                        <div class="col-lg-4 col-md-6 form-group">
                            <label for="sy" class="form-label">School Year</label>
                            <select id="sy" class="form-select" name="sy" style="border-radius:10px;">
                                <option value="" selected>Select School Year</option>
                                <?php while ($row = $sy_result->fetch_assoc()) { ?>
                                    <option value="<?php echo $row['sy']; ?>" <?php if ($sy == $row['sy']) echo 'selected'; ?>><?php echo $row['sy']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-lg-4 col-md-6 form-group">
                            <label for="semester" class="form-label">Semester</label>
                            <select id="semester" class="form-select" name="semester" style="border-radius:10px;">
                                <option value="" selected>Select Semester</option>
                                <?php while ($row = $sem_result->fetch_assoc()) { ?>
                                    <option value="<?php echo $row['semester']; ?>" <?php if ($semester == $row['semester']) echo 'selected'; ?>><?php echo $row['semester']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-lg-4 col-md-6 form-group">
                            <label for="schedule_type" class="form-label">Schedule Type</label>
                            <select id="schedule_type" class="form-select" name="schedule_type" style="border-radius:10px;">
                                <option value="" selected>Select Schedule Type</option>
                                <?php while ($row = $type_result->fetch_assoc()) { ?>
                                    <option value="<?php echo $row['schedule_type']; ?>" <?php if ($schedule_type == $row['schedule_type']) echo 'selected'; ?>><?php echo $row['schedule_type']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6 form-group d-flex align-items-end">
                            <button class="btn btn-success px-3 mr-2" type="submit" style="border-radius:10px;">Apply</button>
                            <button onclick="window.location.href='empSchedule.php';" class="btn btn-success px-3 mr-2" type="button" style="border-radius:10px;">Refresh</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped col-12">
                <thead class="thead-light">
                    <tr>
                        <th>Day of Week</th>
                        <th>Employee ID</th>
                        <th>Last Name</th>
                        <th>SY</th>
                        <th>Semester</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Schedule Type</th>
                        <th>Level</th>
                    </tr>
                </thead>
                <tbody>
                  <?php
                    if (!empty($schedule_data)) {
                        foreach ($schedule_data as $day => $schedules) {
                            $rowCount = count($schedules);
                            $first = true;
                            foreach ($schedules as $index => $schedule) {
                                echo "<tr data-day='{$day}'>";
                                if ($first) {
                                    echo "<td rowspan='{$rowCount}'>" . $day . "</td>";
                                    $first = false;
                                }
                                echo "<td>{$schedule['emp_id']}</td>";
                                echo "<td>{$schedule['last_name']}</td>";
                                echo "<td>{$schedule['sy']}</td>";
                                echo "<td>{$schedule['semester']}</td>";
                                echo "<td>{$schedule['start_time']}</td>";
                                echo "<td>{$schedule['end_time']}</td>";
                                echo "<td>{$schedule['schedule_type']}</td>";
                                echo "<td>{$schedule['level']}</td>";
                                echo "</tr>";
                            }
                        }
                    } else {
                        echo "<tr><td colspan='9' class='text-center'>No schedule data available</td></tr>";
                    }
                  ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>