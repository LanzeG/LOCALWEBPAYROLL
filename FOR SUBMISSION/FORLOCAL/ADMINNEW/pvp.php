<?php
include("../DBCONFIG.PHP");
include("../LoginControl.php");
include("../BASICLOGININFO.PHP");

$status = '';
$message = '';

// Fetch the selected year for PvP table display
$selectedYear = isset($_GET['year']) ? intval($_GET['year']) : date("Y");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Fetch user inputs from the form
    $totalDaysServed = intval($_POST['totaldays']);
    $totalPvp = intval($_POST['totalpvp']);
    $schoolStart = $_POST['sstart'];
    $schoolEnd = $_POST['send'];
    $vacationStart = $_POST['vstart'];
    $vacationEnd = $_POST['vend'];

    // Calculate the multiplier
    $multiplier = $totalPvp / $totalDaysServed;

    // Check if the PvP calculation for the current year has already been done
    $currentYear = date("Y");
    $pvpCheckSql = "SELECT COUNT(*) AS count FROM pvp WHERE year = $currentYear";
    $pvpCheckResult = $conn->query($pvpCheckSql);
    $pvpCheckRow = $pvpCheckResult->fetch_assoc();
    if ($pvpCheckRow['count'] > 0) {
        $status = 'danger';
        $message = 'PvP calculation for the current year has already been done.';
    } else {
        // Fetch all employees with acct_type = 'Faculty'
        $sql = "SELECT emp_id FROM employees WHERE acct_type = 'Faculty'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $emp_id = $row['emp_id'];

                // Count each employee's total number of attendance days from time_keeping
                $attendanceSql = "SELECT COUNT(*) AS totalAttendance FROM time_keeping WHERE emp_id = $emp_id AND timekeep_day BETWEEN '$schoolStart' AND '$schoolEnd'";
                $attendanceResult = $conn->query($attendanceSql);
                $attendanceRow = $attendanceResult->fetch_assoc();
                $totalAttendance = $attendanceRow['totalAttendance'];

                // Calculate the total PVP for each employee
                $employeePvp = $totalAttendance * $multiplier;

                // Insert into the pvp table
                $insertPvpSql = "INSERT INTO pvp (emp_id, total_attendance, total_pvp, year) VALUES ($emp_id, $totalAttendance, $employeePvp, $currentYear)";
                $conn->query($insertPvpSql);

                // Insert entries into timekeeping and dtr for the vacation period based on employeePvp
                $daysToInsert = ceil($employeePvp); // Ensure we insert at least 1 day if there's any PVP
                $vacationPeriod = new DatePeriod(
                    new DateTime($vacationStart),
                    new DateInterval('P1D'),
                    (new DateTime($vacationEnd))->modify('+1 day')
                );

                $insertedDays = 0;
                foreach ($vacationPeriod as $date) {
                    if ($insertedDays >= $daysToInsert) {
                        break;
                    }
                    $dateString = $date->format('Y-m-d');

                    // Insert into time_keeping
                    $insertTimeKeepingSql = "INSERT INTO time_keeping (emp_id, in_morning, out_afternoon, timekeep_day, timekeep_remarks) VALUES ($emp_id, '00:00:00', '00:00:00', '$dateString', 'Vacation')";
                    $conn->query($insertTimeKeepingSql);

                    // Insert into dtr
                    $insertDtrSql = "INSERT INTO dtr (emp_id, in_morning, out_afternoon, DTR_day, DTR_remarks) VALUES ($emp_id, '00:00:00', '00:00:00', '$dateString', 'Vacation')";
                    $conn->query($insertDtrSql);

                    $insertedDays++;
                }
            }
            $status = 'success';
            $message = 'PVP and vacation records have been successfully updated.';
        } else {
            $status = 'warning';
            $message = 'No faculty members found.';
        }
    }
}

// Fetch PvP data for the selected year
$pvpData = [];
$pvpSql = "SELECT p.emp_id, e.last_name, p.total_attendance, p.total_pvp FROM pvp p JOIN employees e ON p.emp_id = e.emp_id WHERE p.year = $selectedYear";
$pvpResult = $conn->query($pvpSql);
if ($pvpResult->num_rows > 0) {
    while ($row = $pvpResult->fetch_assoc()) {
        $pvpData[] = $row;
    }
}

// Fetch distinct years from the pvp table for the year selection dropdown
$years = [];
$yearsSql = "SELECT DISTINCT year FROM pvp ORDER BY year DESC";
$yearsResult = $conn->query($yearsSql);
if ($yearsResult->num_rows > 0) {
    while ($row = $yearsResult->fetch_assoc()) {
        $years[] = $row['year'];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PVP Calculation</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js"></script>
  <style>
    body {
      font-family: Poppins, sans-serif;
      background-color: #f9fafb;
    }
    .container {
      max-width: 800px;
      margin: auto;
      padding: 20px;
    }
    .card {
      background-color: white;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      padding: 20px;
    }
    .form-group {
      margin-bottom: 1rem;
    }
    .form-label {
      display: block;
      font-weight: 600;
      margin-bottom: 0.5rem;
    }
    .form-input {
      width: 100%;
      padding: 0.5rem;
      border: 1px solid #e5e7eb;
      border-radius: 4px;
    }
    .form-button {
      background-color: #3b82f6;
      color: white;
      padding: 0.5rem 1rem;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-weight: 600;
    }
    .form-button:hover {
      background-color: #2563eb;
    }
    .table-responsive {
      margin-top: 20px;
    }
  </style>
</head>

<body>
  <?php include('navbarAdmin.php'); ?>
  <div class="container py-5">
    <div class="card">
      <h2 class="text-center mb-4">PVP Calculation</h2>
      <?php if ($status && $message): ?>
        <div class="alert alert-<?php echo $status; ?>"><?php echo $message; ?></div>
      <?php endif; ?>
      <form method="POST" id="pvp-form">
        <div class="form-group">
          <label for="totaldays" class="form-label">Total Days Served:</label>
          <input type="number" name="totaldays" id="totaldays" class="form-input" required>
        </div>
        <div class="form-group">
          <label for="totalpvp" class="form-label">Total PVP:</label>
          <input type="number" name="totalpvp" id="totalpvp" class="form-input" required>
        </div>
        <div class="form-group">
          <label for="sstart" class="form-label">School Start:</label>
          <input type="date" name="sstart" id="sstart" class="form-input" required>
        </div>
        <div class="form-group">
          <label for="send" class="form-label">School End:</label>
          <input type="date" name="send" id="send" class="form-input" required>
        </div>
        <div class="form-group">
          <label for="vstart" class="form-label">Vacation Start:</label>
          <input type="date" name="vstart" id="vstart" class="form-input" required>
        </div>
        <div class="form-group">
          <label for="vend" class="form-label">Vacation End:</label>
          <input type="date" name="vend" id="vend" class="form-input" required>
        </div>
        <button type="submit" name="submit" class="form-button">Compute</button>
      </form>
      <div class="table-responsive">
        <form method="GET">
          <label for="year" class="form-label">Select Year:</label>
          <select name="year" id="year" class="form-input" onchange="this.form.submit()">
            <?php foreach ($years as $year): ?>
              <option value="<?php echo $year; ?>" <?php echo $selectedYear == $year ? 'selected' : ''; ?>><?php echo $year; ?></option>
            <?php endforeach; ?>
          </select>
        </form>
        <?php if (!empty($pvpData)): ?>
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Employee ID</th>
                <th>Employee Name</th>
                <th>Total Attendance</th>
                <th>Total PVP</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($pvpData as $pvp): ?>
                <tr>
                  <td><?php echo $pvp['emp_id']; ?></td>
                  <td><?php echo $pvp['last_name']; ?></td>
                  <td><?php echo $pvp['total_attendance']; ?></td>
                  <td><?php echo $pvp['total_pvp']; ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p class="text-center">No PvP data available for the selected year.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</body>

</html>
