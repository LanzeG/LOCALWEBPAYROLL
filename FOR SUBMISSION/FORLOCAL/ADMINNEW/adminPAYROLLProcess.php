<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap">
<?php
include("../DBCONFIG.PHP");
include("../LoginControl.php");
include("../BASICLOGININFO.PHP");

session_start();
  if (!isset($_SESSION['adminId'])) {
  // Redirect to the desired page
  header("Location: ../default.php"); // Change 'login.php' to the desired page
  exit; // Terminate script execution after redirection
}
if (isset($_SESSION['payrollprocess'])) {
  echo "<script>alert('{$_SESSION['payrollprocess']}');</script>";
  unset($_SESSION['payrollprocess']);
}

$page = isset($_GET['page']) ? $_GET['page'] : 1;
$results_perpage = 10;
$searchby = isset($_GET['searchoption']) ? $_GET['searchoption'] : "";
$searchvalue = isset($_GET['searchoption']) ? $_GET['searchoption'] : "";

if (isset($_GET['findTasks'])) {

}
$sortColumn = isset($_GET['sort']) ? $_GET['sort'] : 'last_name';
$sortOrder = isset($_GET['order']) ? $_GET['order'] : 'asc';

if (isset($_GET['print_btn'])) {

  $payperiod = isset($_GET['payperiod']) ? mysqli_real_escape_string($conn, $_GET['payperiod']) : "";

  if ($payperiod !== "" && $payperiod !== "novalue") {

    $pperiodquery = "SELECT * FROM payperiods WHERE pperiod_range = '$payperiod'";
    $pperiodexecquery = mysqli_query($conn, $pperiodquery) or die("FAILED TO SET PAY PERIOD " . mysqli_error($conn));
    $pperiodarray = mysqli_fetch_array($pperiodexecquery);

    if ($pperiodarray) {
      $_SESSION['payperiodfrom'] = $pperiodarray['pperiod_start'];
      $_SESSION['payperiodto'] = $pperiodarray['pperiod_end'];
      $_SESSION['payperiodrange'] = $pperiodarray['pperiod_range'];
      $payperiod_to = $pperiodarray['pperiod_start'];
      $payperiod_from = $pperiodarray['pperiod_end'];
    }

    $deptchecked = isset($_GET['dept']) ? $_GET['dept'] : '';
    $emptypechecked = isset($_GET['employmenttype']) ? $_GET['employmenttype'] : '';
    $positionchecked = isset($_GET['position']) ? $_GET['position'] : '';
    $gender = isset($_GET['Gender']) ? $_GET['Gender'] : '';
    $employeeStatus = isset($_GET['employee_status']) ? $_GET['employee_status'] : '';
    $selectedMonth = isset($_GET['month']) ? $_GET['month'] : '';
    $selectedDay = isset($_GET['day']) ? $_GET['day'] : '';
    $selectedYear = isset($_GET['year']) ? $_GET['year'] : '';
    $filterBy = isset($_GET['filter_by']) ? $_GET['filter_by'] : '';  // New parameter
    $searchValue = isset($_GET['search_value']) ? $_GET['search_value'] : '';  // New parameter

    $deptFilter = $deptchecked ? $deptchecked : '';
    $emptypeFilter = $emptypechecked ? $emptypechecked : '';
    $positionFilter = $positionchecked ? $positionchecked : '';
    $genderFilter = $gender ? "'" . $gender . "'" : ''; // Assuming gender is a string in the database
    $employeeStatusFilter = $employeeStatus ? "'" . $employeeStatus . "'" : ''; // Assuming employee_status is a string in the database

    $monthFilter = $selectedMonth ? "'" . $selectedMonth . "'" : '';
    $dayFilter = $selectedDay ? "'" . $selectedDay . "'" : '';
    $yearFilter = $selectedYear ? "'" . $selectedYear . "'" : '';

    $filterByFilter = $filterBy ? $filterBy : '';  // New parameter
    $searchValueFilter = $searchValue ? "" . $searchValue . "" : '';

    $filterConditions = [];

    if ($deptFilter) {
      $filterConditions[] = "department.dept_ID IN ($deptFilter)";
    }

    if ($emptypeFilter) {
      $filterConditions[] = "employmenttypes.employment_ID IN ($emptypeFilter)";
    }

    if ($positionFilter) {
      $filterConditions[] = "position.position_id IN ($positionFilter)";
    }
    if ($genderFilter) {
      $filterConditions[] = "employees.emp_gender = $genderFilter";
    }

    if ($employeeStatusFilter) {
      $filterConditions[] = "employees.emp_status = $employeeStatusFilter";
    }

    if ($monthFilter) {
      $filterConditions[] = "MONTH(employees.date_hired) = $monthFilter";
    }

    if ($dayFilter) {
      $filterConditions[] = "DAY(employees.date_hired) = $dayFilter";
    }

    if ($yearFilter) {
      $filterConditions[] = "YEAR(employees.date_hired) = $yearFilter";
    }

    if ($filterByFilter && $searchValueFilter) {
      // Add a condition for the specific search based on the selected field
      $filterConditions[] = "LOWER(employees.$filterByFilter)  LIKE LOWER ('%$searchValueFilter%')";
    }
    if (!empty($filterConditions)) {

      $searchquery = "SELECT DISTINCT employees.emp_id, employees.*
                        FROM employees
                        JOIN time_keeping ON employees.emp_id = time_keeping.emp_id
                        JOIN department ON department.dept_NAME = employees.dept_NAME 
                        JOIN employmenttypes ON employmenttypes.employment_TYPE = employees.employment_TYPE
                        LEFT JOIN position ON position.position_name = employees.position
                        WHERE employees.emp_status = 'Active'
                          AND time_keeping.timekeep_day BETWEEN '$payperiod_to' AND '$payperiod_from'
                          AND " . implode(" AND ", $filterConditions);
    } else {
      $searchquery = "SELECT DISTINCT employees.emp_id, employees.*
                        FROM employees
                        JOIN time_keeping ON employees.emp_id = time_keeping.emp_id
                        JOIN department ON department.dept_NAME = employees.dept_NAME 
                        JOIN employmenttypes ON employmenttypes.employment_TYPE = employees.employment_TYPE
                        LEFT JOIN position ON position.position_name = employees.position
                        WHERE employees.emp_status = 'Active'
                          AND time_keeping.timekeep_day BETWEEN '$payperiod_to' AND '$payperiod_from'";

    }

    $searchquery .= " ORDER BY $sortColumn $sortOrder";
    $_SESSION['processallpayroll'] = $searchquery;
    $search_result = filterTable($searchquery);

  } else {
    $searchvalue = isset($_GET['searchoption']) ? $_GET['searchoption'] : "";
    $searchquery = "SELECT * from employees WHERE emp_id = '0' ORDER BY emp_id";
    $search_result = filterTable($searchquery);

  }

} else {
  $searchvalue = isset($_GET['searchoption']) ? $_GET['searchoption'] : "";
  $searchquery = "SELECT * from employees WHERE emp_id = '0' ORDER BY emp_id";
  $search_result = filterTable($searchquery);
  $_SESSION['processallpayroll'] = $searchquery;
}
// echo $searchquery;

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <title>Process Payroll</title>
  <link rel="icon" type="image/png" href="../img/icon1 (3).png">

  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.css" />
  <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.min.js"></script>
  <script src="../timepicker/jquery.timepicker.min.js"></script>
  <script src="../timepicker/jquery.timepicker.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
    <!-- Bootstrap JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js"></script>
</head>
<script>
  function toggleCollapse() {
    var content = document.getElementById("content1");
    content.classList.toggle("collapsed");

    // Store the collapse state in local storage
    localStorage.setItem("collapseState", content.classList.contains("collapsed"));
  }

  // Check local storage for the collapse state on page load
  window.onload = function() {
    var isCollapsed = localStorage.getItem("collapseState");

    // If the collapse state is true, toggle the collapse
    if (isCollapsed === "true") {
      toggleCollapse();
    }
  };
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
body{
  font-family: 'Poppins', sans-serif;
}
tbody tr {
  display: table-row;
  vertical-align: middle; /* You can change this to 'top' or 'bottom' based on your preference */
}
</style>

<div class="title d-flex justify-content-center pt-4">
  <h3>Payroll Processing Management</h3>
</div>

<div id="content">
  <a class="inline-block bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md border border-blue-500 hover:border-blue-600 transition duration-300 ease-in-out" id="collapseBtn" onclick="toggleCollapse()" style="float: right; margin-bottom: 10px;">Filter Options <i class="fas fa-arrow-down ml-2"></i></a>
<!-- end filter -->

<div id="content1">
  <div class="filter pt-3">
    <div class="card shadow p-4" style="border: 1px solid #F6E3F3; width: 100%; border-radius:10px;">
      <form method="GET" action="">
        <?php
          $deptchecked = isset($_GET['dept']) ? $_GET['dept'] : '';
          $emptypechecked = isset($_GET['employmenttype']) ? $_GET['employmenttype'] : '';
          $positionchecked = isset($_GET['position']) ? $_GET['position'] : '';
          $gender = isset($_GET['Gender']) ? $_GET['Gender'] : '';
          $employeeStatus = isset($_GET['employee_status']) ? $_GET['employee_status'] : '';
          $month = isset($_GET['month']) ? $_GET['month'] : '';
          $filterBy = isset($_GET['filter_by']) ? $_GET['filter_by'] : '';  // New parameter
          $searchValue = isset($_GET['search_value']) ? $_GET['search_value'] : '';  // New parameter
          $query = "SELECT * FROM department";
            $total_row = mysqli_query($conn, $query) or die('error');
        ?>

    <div class="row row1">
      <div class="col-lg-3 col-xl- col-sm-6">
        <label for="dept" class="form-label">Deparment</label>
          <select id="dept" class="form-select" name="dept" style="border-radius:10px;">
            <option selected disabled>Select Department</option>
            <?php
              if (mysqli_num_rows($total_row) > 0) {
                foreach ($total_row as $row) {
            ?>
            <option value="<?php echo $row['dept_ID']; ?>" <?php if ($deptchecked == $row['dept_ID'])
              echo "selected"; ?>>
            <?php echo $row['dept_NAME']; ?>
            </option>
            <?php
              }
            } else {
              echo 'No Data Found';
            }
            ?>
          </select>
      </div>
      <?php
        $query1 = "SELECT * FROM employmenttypes";
          $total_row = mysqli_query($conn, $query1) or die('error');
      ?>
  <div class="col-lg-3 col-sm-6"><label for="employmenttype" class="form-label">Employment Type</label>
    <select id="employmenttype" class="form-select" name="employmenttype" style="border-radius:10px;">
      <option selected disabled>Select Employment Type</option>
      <?php
        if (mysqli_num_rows($total_row) > 0) {
          foreach ($total_row as $row) {
      ?>
        <option value="<?php echo $row['employment_ID']; ?>" <?php if ($emptypechecked == $row['employment_ID'])
          echo "selected"; ?>>
        <?php echo $row['employment_TYPE']; ?>
        </option>
        <?php
          }
        } else {
          echo 'No Data Found';
        }
        ?>
    </select>
  </div>
<?php
  $query3 = "SELECT * FROM position";
    $total_row = mysqli_query($conn, $query3) or die('error');
?>
<div class="col-lg-3 col-sm-6">
  <label for="position" class="form-label">Position</label>
    <select name="position" id="position" class="form-select" style="border-radius:10px;">
      <option selected disabled>Select Position</option>
      <?php
        if (mysqli_num_rows($total_row) > 0) {
          foreach ($total_row as $row) {
      ?>
        <option value="<?php echo $row['position_id']; ?>" <?php if ($positionchecked == $row['position_id'])
          echo "selected"; ?>>
        <?php echo $row['position_name']; ?>
        </option>
      <?php
          }
        } else {
          echo 'No Data Found';
        }
      ?>
    </select>
</div>
<div class="col-lg-3 col-sm-6">
  <label for="employee_status" class="form-label">Employee Status</label>
    <select id="employee_status" class="form-select" name="employee_status" style="border-radius:10px;">
      <option selected disabled>Select Status</option>
      <option value="Active" <?php if (isset($_GET['employee_status']) && $_GET['employee_status'] == 'Active')
        echo 'selected'; ?>>Active</option>
      <option value="Inactive" <?php if (isset($_GET['employee_status']) && $_GET['employee_status'] == 'Inactive')
        echo 'selected'; ?>>Inactive</option>
    </select>
</div>
</div>
<!-- end row 1 -->
<div class="row row2 mt-2">
  <div class="col-lg-2 col-md-6">
    <?php
      $payperiodsquery = "SELECT * FROM payperiods ORDER BY pperiod_start ASC";
      $payperiodsexecquery = mysqli_query($conn, $payperiodsquery) or die("FAILED TO EXECUTE PAYPERIOD QUERY " . mysqli_error($conn));
    ?>
<label for="payperiod" class="form-label">Payroll Period</label>
  <select name="payperiod" id="payperiod" class="form-select" style="border-radius:10px;">
    <option value="novalue" selected disabled> Select Payroll Period</option>
      <?php while ($payperiodchoice = mysqli_fetch_array($payperiodsexecquery)):;
        $selected = ($payperiod == $payperiodchoice['pperiod_range']) ? 'selected' : ''; ?>
          <option <?php echo $selected; ?>>
          <?php echo $payperiodchoice['pperiod_range']; ?>
          </option>
      <?php endwhile; ?>
  </select>
</div>

<div class="col-lg-1 col-sm-6">
  <label for="Gender" class="form-label">Sex</label>
    <select id="Gender" class="form-select" name="Gender" style="border-radius:10px;">
      <option selected disabled>Sex</option>
        <option value="Male" <?php if (isset($_GET['Gender']) && $_GET['Gender'] == 'Male')
          echo 'selected'; ?>>Male</option>
        <option value="Female" <?php if (isset($_GET['Gender']) && $_GET['Gender'] == 'Female')
          echo 'selected'; ?>>Female</option>
    </select>
</div>

<div class="row col-lg-3 col-sm-6">
  <label class="form-label">Date Hired</label>
    <div class="col-4">
      <select name="month" id="month" class="form-select" style="border-radius:10px;">
        <option selected disabled>Month</option>
        <?php
          $months = [
            'Jan' => 1,
            'Feb' => 2,
            'Mar' => 3,
            'Apr' => 4,
            'May' => 5,
            'Jun' => 6,
            'Jul' => 7,
            'Aug' => 8,
            'Sep' => 9,
            'Oct' => 10,
            'Nov' => 11,
            'Dec' => 12
          ];

          foreach ($months as $monthName => $monthNumber) {
            $selected = (isset($_GET['month']) && $_GET['month'] == $monthNumber) ? 'selected' : '';
              echo '<option value="' . $monthNumber . '" ' . $selected . '>' . $monthName . '</option>';
          }
        ?>
      </select>
  </div>

<div class="col-4">
  <select name="day" id="day" class="form-select" style="border-radius:10px;">
    <option selected disabled>Date</option>
      <?php
        // Adding options for days (assuming up to 31 for simplicity)
          for ($day = 1; $day <= 31; $day++) {
            $selected = (isset($_GET['day']) && $_GET['day'] == sprintf('%02d', $day)) ? 'selected' : '';
              echo '<option value="' . sprintf('%02d', $day) . '" ' . $selected . '>' . sprintf('%02d', $day) . '</option>';
          }
      ?>
    </select>
</div>

<div class="col-4">
  <select name="year" id="year" class="form-select" style="border-radius:10px;">
  <option selected disabled>Year</option>
        <?php
        // Adding options for years (current year - 5 to current year + 5)
          $currentYear = date("Y");
          $startYear = $currentYear - 5;
          $endYear = $currentYear + 5;

          for ($year = $startYear; $year <= $endYear; $year++) {
            $selected = (isset($_GET['year']) && $_GET['year'] == $year) ? 'selected' : '';
              echo '<option value="' . $year . '" ' . $selected . '>' . $year . '</option>';
          }
        ?>
  </select>
</div>
</div>

<div class="col-lg-2 col-md-6">
  <label for="filter_by" class="form-label">Search By:</label>
    <select id="filter_by" class="form-select" name="filter_by" style="border-radius:10px;">
      <option value="" <?php if (isset($_GET['filter_by']) && $_GET['filter_by'] == '')
        echo 'selected'; ?>>Search by</option>
      <option value="emp_id" <?php if (isset($_GET['filter_by']) && $_GET['filter_by'] == 'emp_id')
        echo 'selected'; ?>>Employee ID</option>
      <option value="last_name" <?php if (isset($_GET['filter_by']) && $_GET['filter_by'] == 'last_name')
        echo 'selected'; ?>>Last Name</option>
      <option value="first_name" <?php if (isset($_GET['filter_by']) && $_GET['filter_by'] == 'first_name')
        echo 'selected'; ?>>First Name</option>
      <option value="user_name" <?php if (isset($_GET['filter_by']) && $_GET['filter_by'] == 'user_name')
        echo 'selected'; ?>>Username</option>
    </select>
</div>

<div class="col-lg-4 col-md-6">
  <label for="search_value" class="form-label">Search</label>
  <input type="text" class="form-control" placeholder="Search" aria-label="Search" name="search_value" id="search_value"
    value="<?php echo isset($_GET['search_value']) ? htmlspecialchars($_GET['search_value']) : ''; ?>" style="border-radius:10px;">
</div>
</div>

<div class=" d-flex align-items-center justify-content-center">
  <div class="  form-actions mt-3" >
    <a href="../ADMIN/allpayrollprocess.php" class="inline-block bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-md border border-green-500 hover:border-green-600 transition duration-300 ease-in-out" name="printAll">Process All</a>
      <button type="submit" class="inline-block bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-md border border-green-500 hover:border-green-600 transition duration-300 ease-in-out" name="print_btn">Apply</button>
      <button type="submit" class="inline-block bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-md border border-green-500 hover:border-green-600 transition duration-300 ease-in-out mr-5" name="refresh">Refresh</button>
</div>

</div>
</form>
<a href="pvp.php"><p>Process PVP</p></a>
</div>
<!-- end form -->
</div>
</div>

<div class="row mt-3 mb-1 d-flex justify-content-end">
  <div class="table d-flex align-items-center table-responsive">
    <table class="table table-striped">
    <thead class="table-striped" style="background-color: #2ff29e; color: #4929aa;">
      <tr>
        <th style="border-top-left-radius: 10px;">Employee ID</th>
        <th><a href="?print_btn=1&sort=last_name&order=<?php echo $sortColumn == 'last_name' ? ($sortOrder == 'asc' ? 'desc' : 'asc') : 'asc'; ?>&dept=<?php echo $deptchecked ?? ''; ?>&employmenttype=<?php echo $emptypechecked ?? ''; ?>&position=<?php echo $positionchecked ?? ''; ?>&Gender=<?php echo $gender ?? ''; ?>&employee_status=<?php echo $employeeStatus ?? ''; ?>&month=<?php echo $selectedMonth ?? ''; ?>&day=<?php echo $selectedDay ?? ''; ?>&year=<?php echo $selectedYear ?? ''; ?>&filter_by=<?php echo $filterBy ?? ''; ?>&search_value=<?php echo $searchValue ?? ''; ?>&payperiod=<?php echo $payperiod ?? ''; ?>">Last Name <?php echo ($sortColumn == 'last_name') ? ($sortOrder == 'asc' ? '&#9650;' : '&#9660;') : ''; ?></a></th>
        <th>First Name</th>
        <th>Middle Name</th>
        <th>Department</th>
        <th>Employment Type</th>
        <th style="border-top-right-radius: 10px;">Action</th>
      </tr>
    </thead>
    <tbody>
<?php

function filterTable($searchquery){
  $conn1 = mysqli_connect("localhost:3307", "root", "", "masterdb");
  $filter_Result = mysqli_query($conn1, $searchquery) or die("failed to query masterfile " . mysqli_error($conn1));
  return $filter_Result;
}

while ($row1 = mysqli_fetch_array($search_result)):;
  ?>
  <tr class="gradeX">
    <td>
      <?php echo $row1['prefix_ID']; ?>
      <?php echo $row1['emp_id']; ?>
    </td>
    <td>
      <?php echo $row1['last_name']; ?>
    </td>
    <td>
      <?php echo $row1['first_name']; ?>
    </td>
    <td>
      <?php echo $row1['middle_name']; ?>
    </td>
    <td>
      <?php echo $row1['dept_NAME']; ?>
    </td>
    <td>
      <?php echo $row1['employment_TYPE']; ?>
    </td>
    <td>
      <center><a href="../ADMIN/adminCOMPUTEPayroll.php?id=<?php echo $row1['emp_id']; ?>" class="inline-block bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md border border-blue-500 hover:border-blue-600 transition duration-300 ease-in-out" target="_blank">
    <span class="icon"><i class="far fa-calendar-alt"></i></span> Process Payroll
    </a>
  </tr>
<?php endwhile; ?>
</tbody>

</table>
</div>

  
<!-- end of content -->
</tbody>
</table>
<div class="pagination alternate" style="float:right;">
</div>
</div>
<!--EMPLOYEE TAB--><!--EMPLOYEE TAB--><!--EMPLOYEE TAB--><!--EMPLOYEE TAB--><!--EMPLOYEE TAB--><!--EMPLOYEE TAB--><!--EMPLOYEE TAB--><!--EMPLOYEE TAB-->
</div>
</div>
</div>
</div>
</div>
</div>
<div class="row-fluid">
    <div id="footer" class="span12"> 2023 &copy; WEB-BASED TIMEKEEPING AND PAYROLL SYSTEM USING FINGERPRINT BIOMETRICS</div>
</div>


<script src="../js/maruti.dashboard.js"></script>
<script src="../js/excanvas.min.js"></script>
<script src="../js/jquery.min.js"></script>
<script src="../js/jquery.ui.custom.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../js/jquery.flot.min.js"></script>
<script src="../js/jquery.flot.resize.min.js"></script>
<script src="../js/jquery.peity.min.js"></script>
<script src="../js/fullcalendar.min.js"></script>
<script src="../js/maruti.js"></script>
<script>
    // Function to update the position dropdown state
  function updatePositionDropdownState() {
    var positionDropdown = document.getElementById('position');
    var employmentTypeDropdown = document.getElementById('employmenttype');

    var isContractual = employmentTypeDropdown.value === '4001'; // Change to the actual value for contractual

      // Save the selected value before disabling
    var selectedValue = positionDropdown.value;

      // Disable/enable based on employment type
    positionDropdown.disabled = isContractual;

      // Set the selected value after updating options
    positionDropdown.value = selectedValue;
  }

    // Initial setup on page load
  updatePositionDropdownState(); // Ensure the initial state is correct

    // Event listener for changes in the employment type dropdown
  document.getElementById('employmenttype').addEventListener('change', function () {
    updatePositionDropdownState();
  });
</script>
</body>

</html>