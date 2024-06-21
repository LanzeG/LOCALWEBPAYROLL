<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
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

$uname = $_SESSION['uname'];
$empid = $_SESSION['empId'];
$adminId = $_SESSION['adminId'];
$adminname = "SELECT first_name, last_name FROM employees where emp_id = '$adminId'";
$adminnameexecqry = mysqli_query($conn, $adminname) or die ("FAILED TO CHECK EMP ID ".mysqli_error($conn));
$adminData = mysqli_fetch_assoc($adminnameexecqry);

$adminFullName = $adminData['first_name'] . " " . $adminData['last_name'];

// if (isset($_GET['refresh'])) {
//   header("Location: adminMasterLoans.php");
//   exit(); 
// }

$sortColumn = isset($_GET['sort']) ? $_GET['sort'] : 'last_name';
$sortOrder = isset($_GET['order']) ? $_GET['order'] : 'asc';



if (isset($_GET['print_btn'])) {
  $deptchecked = isset($_GET['dept']) ? $_GET['dept'] : '';
  $emptypechecked = isset($_GET['employmenttype']) ? $_GET['employmenttype'] : '';
  $positionchecked = isset($_GET['position']) ? $_GET['position'] : '';
  $gender = isset($_GET['Gender']) ? $_GET['Gender'] : '';
  $loanstatus = isset($_GET['loan_status']) ? $_GET['loan_status'] : '';
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
  $loanStatusFilter = $loanstatus ? "'" . $loanstatus . "'" : ''; // Assuming employee_status is a string in the database

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
  if ($loanStatusFilter) {
    $filterConditions[] = "loans.status = $loanStatusFilter";
  }

  if ($filterByFilter && $searchValueFilter) {
    $filterConditions[] = "LOWER(employees.$filterByFilter)  LIKE LOWER ('%$searchValueFilter%')";
  }

  if (!empty($filterConditions)) {
    $searchquery = "SELECT * FROM employees
      JOIN department ON department.dept_NAME = employees.dept_NAME 
      JOIN employmenttypes ON employmenttypes.employment_TYPE = employees.employment_TYPE
      JOIN loans ON loans.emp_id = employees.emp_id
      LEFT JOIN position ON position.position_name = employees.position
      WHERE " . implode(" AND ", $filterConditions);

    $searchquery .= " ORDER BY $sortColumn $sortOrder";
    
    $searchresult = filterTable($searchquery);
    $_SESSION['printgsis_query'] = $searchquery;
  } else {
    echo "No filters selected. Please select at least one filter.";
    $searchquery = "SELECT * FROM loans, employees WHERE employees.emp_id = loans.emp_id ORDER BY $sortColumn $sortOrder";
    $searchresult = filterTable($searchquery);
    $_SESSION['printgsis_query'] = $searchquery;
  }

} else {
  $searchquery = "SELECT * FROM loans, employees WHERE employees.emp_id = loans.emp_id ORDER BY $sortColumn $sortOrder";
  $searchresult = filterTable($searchquery);
  $_SESSION['printgsis_query'] = $searchquery;
}

?>



<!DOCTYPE html>
<html lang="en">

<head>
  <title>Manage Loans</title>
  <link rel="icon" type="image/png" href="../img/icon1 (3).png">
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

<div class="title d-flex justify-content-center pt-4">
  <h3>Loans Management</h3>
</div>
<div id="content">
<!-- end filter -->

<div class="row mt-3 mb-1 d-flex justify-content-end  ">
  <div class="col-12">
    <div class="buttons  ">
      <a href="../../ADMIN/REPORTS/printsss.php?printAll" class="inline-block bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-md border border-green-500 hover:border-green-600 transition duration-300 ease-in-out mr-1" target="_blank"><i class="fas fa-print mr-2"></i> Print All Loans Record</a>
      <a href="../../ADMIN/REPORTS/printsss.php?printDisplayed" class="inline-block bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-md border border-green-500 hover:border-green-600 transition duration-300 ease-in-out mr-1" target="_blank"><i class="fas fa-print mr-2"></i> Print Displayed Loans Record</a>
      <a href="addLoan.php" class="inline-block bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-md border border-green-500 hover:border-green-600 transition duration-300 ease-in-out mr-1"><i class="fas fa-plus mr-2"></i> Add Loan</a>
      <a class="inline-block bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md border border-blue-500 hover:border-blue-600 transition duration-300 ease-in-out" id="collapseBtn" onclick="toggleCollapse()" style="float: right;">Filter Options <i class="fas fa-arrow-down ml-2"></i></a>
    </div>
  </div>
</div>

<div class="col-2 d-flex justify-content-end ">
</div>
</div>
<div id="content1">
  <div class="filter pt-3">
    <div class="card shadow p-4" style="border: 1px solid #F6E3F3; width: 100%; border-radius:10px;">
      <form method="GET" action="">
      <?php
        $deptchecked = isset($_GET['dept']) ? $_GET['dept'] : '';
        $emptypechecked = isset($_GET['employmenttype']) ? $_GET['employmenttype'] : '';
        $shiftchecked = isset($_GET['shifts']) ? $_GET['shifts'] : '';
        $positionchecked = isset($_GET['position']) ? $_GET['position'] : '';
        $gender = isset($_GET['Gender']) ? $_GET['Gender'] : '';
        $loanstatus = isset($_GET['loan_status']) ? $_GET['loan_status'] : '';
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
    <label for="loan_status" class="form-label">Loan Status</label>
      <select name="loan_status" id="loan_status" class="form-select" style="border-radius:10px;">
        <option value="" <?php if (isset($_GET['loan_status']) && $_GET['loan_status'] == '')
          echo 'selected'; ?>>Select Loan Status</option>
        <option value="Paused" <?php if (isset($_GET['loan_status']) && $_GET['loan_status'] == 'Paused')
          echo 'selected'; ?>>Paused </option>
        <option value="On-Going" <?php if (isset($_GET['loan_status']) && $_GET['loan_status'] == 'On-Going')
          echo 'selected'; ?>>On-Going</option>
        <option value="Paid" <?php if (isset($_GET['loan_status']) && $_GET['loan_status'] == 'Paid')
          echo 'selected'; ?>>Paid</option>
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
    <button type="submit" class="inline-block bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-md border border-green-500 hover:border-green-600 transition duration-300 ease-in-out" name="print_btn">Apply</button>
    <button type="submit" class="inline-block bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-md border border-green-500 hover:border-green-600 transition duration-300 ease-in-out mr-5" name="refresh">Refresh</button>
</div>
</div>
</form>
</div>
<!-- end form -->
</div>
</div>

<div class="row mt-3 mb-1 d-flex justify-content-end">
  <div class="table d-flex align-items-center ">
    <table class="table table-striped ">
      <thead class="table-striped" style="background-color: #2ff29e; color: #4929aa;">
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
          <tr>
            <th style="border-top-left-radius: 10px; color: #4929aa;">Loan ID No.</th>
            <th>Loan Org</th>
            <th>Loan Type</th>
            <th>Employee ID</th>
            <th><a href="?print_btn=1&sort=last_name&order=<?php echo $sortColumn == 'last_name' ? ($sortOrder == 'asc' ? 'desc' : 'asc') : 'asc'; ?>&dept=<?php echo $deptchecked ?? ''; ?>&employmenttype=<?php echo $emptypechecked ?? ''; ?>&shifts=<?php echo $shiftchecked ?? ''; ?>&position=<?php echo $positionchecked ?? ''; ?>&Gender=<?php echo $gender ?? ''; ?>&employee_status=<?php echo $employeeStatus ?? ''; ?>&month=<?php echo $selectedMonth ?? ''; ?>&day=<?php echo $selectedDay ?? ''; ?>&year=<?php echo $selectedYear ?? ''; ?>&filter_by=<?php echo $filterBy ?? ''; ?>&search_value=<?php echo $searchValue ?? ''; ?>&loan_status=<?php echo $loanstatus ?? ''; ?>">Last Name <?php echo ($sortColumn == 'last_name') ? ($sortOrder == 'asc' ? '&#9650;' : '&#9660;') : ''; ?></a></th>
            <th>First Name</th>
            <th>Department</th>
            <th>Employment Type</th>
            <th>Start Date</th>
            <th>End Date</th>
            <!--<th>Loan Amount</th>-->
            <th>Monthly Amortization</th>
            <!--<th>Balance</th>-->
            <th>Status</th>
            <th style="border-top-right-radius: 10px; color: #4929aa;">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php
          function filterTable($searchquery){
            $conn = mysqli_connect("localhost:3307", "root", "", "masterdb");
            $filter_Result = mysqli_query($conn, $searchquery) or die("failed to query masterfile " .mysqli_error());
            return $filter_Result;
          }
            while ($row1DEPT = mysqli_fetch_array($searchresult)):;
        ?>
            <tr class="gradeX">
              <td><?php echo $row1DEPT['loanidno']; ?></td>
              <td><?php echo $row1DEPT['loanorg']; ?></td>
              <td><?php echo $row1DEPT['loantype']; ?></td>
              <td><?php echo $row1DEPT['emp_id']; ?></td>
              <td>
                <a href="adminLoanHistory.php?emp_id=<?php echo $row1DEPT['emp_id']; ?>"><?php echo $row1DEPT['emplastname']; ?></a></td>
              <td><?php echo $row1DEPT['empfirstname']; ?></td>
               <td><?php echo $row1DEPT['dept_NAME']; ?></td>
               <td><?php echo $row1DEPT['employment_TYPE']; ?></td>
               <td><?php echo $row1DEPT['start_date']; ?></td>
               <td><?php echo $row1DEPT['end_date']; ?></td>
               <!--<td><?php echo $row1DEPT['loan_amount']; ?></td>-->
               <td><?php echo $row1DEPT['monthly_deduct']; ?></td>
               <!--<td><?php echo $row1DEPT['loan_balance']; ?></td>-->
               <td><?php echo $row1DEPT['status']; ?></td>
               <td>
                   <div class="d-grid gap-1 d-md-block">
                          <?php
                  if ($row1DEPT['status']=='On-Going' || $row1DEPT['status']=='Paused'){
                ?>
                <a href="renew_loan.php?uniquekey=<?php echo $row1DEPT['uniquekey']; ?>" class="btn btn-primary hover:bg-red-600 w-100 text-white font-bold py-2 px-4 rounded">
                  <i class="fas fa-sync-alt"></i> <!-- Font Awesome icon for renew -->
                </a>
                
                <!-- Replace "Stop" button with icon -->
                <button onclick="stopLoan('<?php echo $row1DEPT['uniquekey']; ?>','<?php echo $adminFullName ?>')" class="btn btn-danger mt-1 w-100 text-white font-bold py-2 px-4 rounded">
                  <i class="bi bi-stop-fill"></i> <!-- Bootstrap Icons icon for stop -->
                </button>
                
                <form id="pauseResumeForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <input type="hidden" name="loanID" value="<?php echo $row1DEPT['uniquekey']; ?>">
                    <input type="hidden" name="loanStatus" value="<?php echo $row1DEPT['status']; ?>">
                    <button type="submit" id="pauseResumeButton" class="btn mt-1 w-100 text-white font-bold py-2 px-4 rounded bg-secondary">
                        <?php if ($row1DEPT['status'] === 'Paused'): ?>
                            <!-- Icon for Resuming the loan -->
                            <i class="fas fa-play"></i> <!-- Assuming you're using Font Awesome -->
                        <?php else: ?>
                            <!-- Icon for Pausing the loan -->
                            <i class="fas fa-pause"></i> <!-- Assuming you're using Font Awesome -->
                        <?php endif; ?>
                    </button>
                </form>
                
                

                <?php
                  }else{
                ?>
                <?php
                  }
                ?>
                   </div>
                   
             
                </td>
              </tr>
            <?php endwhile; ?>
      </tbody>
    <tfoot>
  <tr>
  <td colspan="12">
    <nav aria-label="Page navigation">
      <ul class="pagination justify-content-center">
        <?php

        ?>
        </ul>
        </nav>
      </td>
    </tr>
  </tfoot>
</table>
</div>

                <?php
                  // Function to pause a loan
                function pauseLoan($conn, $uniquekey) {
                    $adminId = $_SESSION['adminId'];
                    $adminname = "SELECT first_name, last_name FROM employees where emp_id = '$adminId'";
                    $adminnameexecqry = mysqli_query($conn, $adminname) or die ("FAILED TO CHECK EMP ID ".mysqli_error($conn));
                    $adminData = mysqli_fetch_assoc($adminnameexecqry);
                    
                    $adminFullName = $adminData['first_name'] . " " . $adminData['last_name'];
                    
                    $pauseStart = date('Y-m-d H:i:s'); // Current date and time
                    
                    // Fetch loan details from loans table
                    $selectQuery = "SELECT * FROM loans WHERE uniquekey = '$uniquekey'";
                    $result = mysqli_query($conn, $selectQuery);
                    if ($result && mysqli_num_rows($result) > 0) {
                        $row = mysqli_fetch_assoc($result);
                        // Extract loan details
                        $loanidno = $row['loanidno'];
                        $loantype1 = $row['loantype'];
                        $loanorg1 = $row['loanorg'];
                        $gsisempid = $row['emp_id'];
                        $lastname = $row['emplastname'];
                        $firstname1 = $row['empfirstname'];
                        $middlename1 = $row['empmiddlename'];
                        $startdate = $row['start_date'];
                        $enddate = $row['end_date'];
                        $monthlydeductionamount = $row['monthly_deduct'];
                        $noofpays = $row['no_of_pays'];
                
                        // Update loans table
                        $updateQuery = "UPDATE loans SET pause_date = '$pauseStart', status = 'Paused' WHERE uniquekey = '$uniquekey'";
                        mysqli_query($conn, $updateQuery) or die("Error updating loan status: " . mysqli_error($conn));
                
                        // Insert into loan_history
                        $loanhistory = "INSERT INTO loan_history (uniquekey, loan_id, loantype, loanorg, emp_id, lastname, firstname, middlename, start_date, end_date, monthly_payment, status, num_of_payments, admin_name) VALUES ('$uniquekey', '$loanidno', '$loantype1', '$loanorg1', '$gsisempid', '$lastname', '$firstname1', '$middlename1', '$startdate', '$enddate', '$monthlydeductionamount', 'Paused', '$noofpays', '$adminFullName')";
                            $play=mysqli_query($conn, $loanhistory) or die("Error inserting into loan history: " . mysqli_error($conn));
                            

                        
                    } else {
                        die("Loan details not found");
                    }
                    //  header("Location: admintry.php"); // Change 'login.php' to the desired page
                    //  exit; // Terminate script execution after redirection
                }
                function resumeLoan($conn, $uniquekey) {
                    $pauseEnd = date('Y-m-d H:i:s'); // Current date and time
                    
                    // Fetch loan details including pause_start date
                    $selectQuery = "SELECT start_date, end_date, pause_date, emp_id FROM loans WHERE uniquekey = '$uniquekey'";
                    $result = mysqli_query($conn, $selectQuery);
                    
                    if ($result && mysqli_num_rows($result) > 0) {
                        $row = mysqli_fetch_assoc($result);
                        $startDate = $row['start_date'];
                        $endDate = $row['end_date'];
                        $pauseStart = $row['pause_date'];
                        $emp_id = $row['emp_id'];
                        
                        // Fetch payroll processing dates during the pause period
                        $payrollQuery = "
                            SELECT COUNT(*) AS payroll_count 
                            FROM pay_per_period 
                            WHERE emp_id = '$emp_id' 
                            AND (
                                (STR_TO_DATE(SUBSTRING_INDEX(pperiod_range, ' to ', 1), '%Y-%m-%d') BETWEEN '$pauseStart' AND '$pauseEnd') OR 
                                (STR_TO_DATE(SUBSTRING_INDEX(pperiod_range, ' to ', -1), '%Y-%m-%d') BETWEEN '$pauseStart' AND '$pauseEnd') OR
                                ('$pauseStart' BETWEEN STR_TO_DATE(SUBSTRING_INDEX(pperiod_range, ' to ', 1), '%Y-%m-%d') AND STR_TO_DATE(SUBSTRING_INDEX(pperiod_range, ' to ', -1), '%Y-%m-%d')) OR
                                ('$pauseEnd' BETWEEN STR_TO_DATE(SUBSTRING_INDEX(pperiod_range, ' to ', 1), '%Y-%m-%d') AND STR_TO_DATE(SUBSTRING_INDEX(pperiod_range, ' to ', -1), '%Y-%m-%d'))
                            )";
                        $payrollResult = mysqli_query($conn, $payrollQuery);
                        $payrollCount = 0;
                        
                        if ($payrollResult) {
                            $payrollRow = mysqli_fetch_assoc($payrollResult);
                            $payrollCount = $payrollRow['payroll_count'];
                            echo$payrollCount;
                        }
                        
                        // Update the loan start and end dates by adding the pause duration (in months)
                        $newStartDate = (new DateTime($startDate))->add(new DateInterval('P' . $payrollCount . 'M'))->format('Y-m-d H:i:s');
                        $newEndDate = (new DateTime($endDate))->add(new DateInterval('P' . $payrollCount . 'M'))->format('Y-m-d H:i:s');
                        
                        // Update the loan record in the database
                        $updateQuery = "UPDATE loans SET start_date = '$newStartDate', end_date = '$newEndDate', pause_date='', status = 'On-Going' WHERE uniquekey = '$uniquekey'";
                        $updateResult = mysqli_query($conn, $updateQuery);
                        
                        if ($updateResult) {
                            // Log the change to loan history
                            $loanhistory = "INSERT INTO loan_history (uniquekey, loan_id, loantype, loanorg, emp_id, lastname, firstname, middlename, start_date, end_date, monthly_payment, status, num_of_payments, admin_name)
                                            SELECT uniquekey, loanidno, loantype, loanorg, emp_id, emplastname, empfirstname, empmiddlename, '$newStartDate', '$newEndDate', monthly_deduct, 'On-Going', no_of_pays, 'Admin'
                                            FROM loans WHERE uniquekey = '$uniquekey'";
                            mysqli_query($conn, $loanhistory) or die("Error inserting into loan_history: " . mysqli_error($conn));
                           
                        // } else {
                        //     echo "Error resuming the loan: " . mysqli_error($conn);
                        }
                    } else {
                        // echo "Loan data not found.";
                    }
                    //  header("Location: admintry.php"); // Change 'login.php' to the desired page
                    //  exit; // Terminate script execution after redirection
                }
                    ob_start();
                    if ($_SERVER["REQUEST_METHOD"] == "POST") {
                        
                        // Retrieve loan ID and status from the form
                        $uniquekey = $_POST['loanID'];
                        $loanStatus = $_POST['loanStatus'];
                        
                        // Execute the appropriate PHP code based on the loan status
                        if ($loanStatus === 'Paused') {
                            // Call the function to resume the loan
                            resumeLoan($conn, $uniquekey);
                            
                        } else {
                            // Call the function to pause the loan
                            pauseLoan($conn, $uniquekey);
                        }
                        echo "<script> location.href='adminMasterLoans.php'; </script>";
                        exit;
                    }
                    ob_end_flush();?>
  
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

<!--<script src="../js/maruti.dashboard.js"></script>-->
<!--<script src="../js/excanvas.min.js"></script>-->
<!--<script src="../js/jquery.min.js"></script>-->
<!--<script src="../js/jquery.ui.custom.js"></script>-->
<!--<script src="../js/bootstrap.min.js"></script>-->
<!--<script src="../js/jquery.flot.min.js"></script>-->
<!--<script src="../js/jquery.flot.resize.min.js"></script>-->
<!--<script src="../js/jquery.peity.min.js"></script>-->
<!--<script src="../js/fullcalendar.min.js"></script>-->
<!--<script src="../js/maruti.js"></script>-->
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
<script>
function stopLoan(uniqueKey, adminName) {
    // Show toast notification
    Swal.fire({
        icon: 'warning',
        title: 'Stop Loan Confirmation',
        text: 'Are you sure you want to stop this loan?',
        showCancelButton: true,
        toast: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, stop it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // If user confirms, proceed with AJAX request
            // AJAX request using jQuery
            $.ajax({
                url: 'functions/stoploan.php',
                type: 'POST',
                data: {
                    action: 'stop',
                    uniquekey: uniqueKey,
                    adminname: adminName
                },
                success: function(response) {
                    // Handle success response if needed
                    console.log('AJAX Response:', response);
                    // Show success toast
                    Swal.fire({
                        icon: 'success',
                        title: 'Loan Stopped Successfully!',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        customClass: {
                            popup: 'swal-toast',
                            title: 'swal-toast-title',
                            icon: 'swal-toast-icon'
                        }
                    });
                    reloadTableData();
                },
                error: function(xhr, status, error) {
                    // Handle error response if needed
                    console.error('AJAX Error:', error);
                    // Show error toast
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to stop the loan. Please try again later.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        customClass: {
                            popup: 'swal-toast',
                            title: 'swal-toast-title',
                            icon: 'swal-toast-icon'
                        }
                    });
                }
            });
        }
    });
}

function reloadTableData() {
    location.reload();
}



</script>
</body>
</html>