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



$master = $_SESSION['master'];

$default_results_per_page = 12;

$results_perpage = isset($_SESSION['results_per_page']) ? $_SESSION['results_per_page'] : $default_results_per_page;

if (isset($_GET['page'])) {
    $page = $_GET['page'];
} else {
    $page = 1;
}

$start_from = ($page - 1) * $results_perpage;

$query = "SELECT * FROM notifications";
if (isset($_GET['sort_by'])) {
    $sort_by = $_GET['sort_by'];
    $query .= " WHERE type='$sort_by'";
}
$query .= " ORDER BY created_at DESC LIMIT $start_from, $results_perpage";

$result = mysqli_query($conn, $query);

$total_rows = mysqli_num_rows($result);

$totalPages = ceil($total_rows / $results_perpage);

if (isset($_GET['refresh'])) {
  header("Location: absence.php");
  exit(); 
}

$sortColumn = isset($_GET['sort']) ? $_GET['sort'] : 'last_name';
$sortOrder = isset($_GET['order']) ? $_GET['order'] : 'asc';

$deptchecked = isset($_GET['dept']) ? $_GET['dept'] : '';
$emptypechecked = isset($_GET['employmenttype']) ? $_GET['employmenttype'] : '';
$shiftchecked = isset($_GET['shifts']) ? $_GET['shifts'] : '';
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
$shiftFilter = $shiftchecked ? $shiftchecked : '';
$positionFilter = $positionchecked ? $positionchecked : '';
$genderFilter = $gender ? "'" . $gender . "'" : ''; // Assuming gender is a string in the database
$employeeStatusFilter = $employeeStatus ? "'" . $employeeStatus . "'" : ''; // Assuming employee_status is a string in the database

$monthFilter = $selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('m');
$dayFilter = $selectedDay = isset($_GET['day']) ? $_GET['day'] : date('d');
$yearFilter = $selectedYear = isset($_GET['year']) ? $_GET['year'] : date('Y');

$filterByFilter = $filterBy ? $filterBy : '';  // New parameter
$searchValueFilter = $searchValue ? "" . $searchValue . "" : '';


  $filterConditions = [];

  if ($deptFilter) {
    $filterConditions[] = "department.dept_ID IN ($deptFilter)";
  }

  if ($emptypeFilter) {
    $filterConditions[] = "employmenttypes.employment_ID IN ($emptypeFilter)";
  }

  if ($shiftFilter) {
    $filterConditions[] = "shift.shift_ID IN ($shiftFilter)";
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
    $filterConditions[] = "MONTH(absences.absence_date) = $monthFilter";
  }

  if ($dayFilter) {
    $filterConditions[] = "DAY(absences.absence_date) = $dayFilter";
  }

  if ($yearFilter) {
    $filterConditions[] = "YEAR(absences.absence_date) = $yearFilter";
  }

  if ($filterByFilter && $searchValueFilter) {
    // Add a condition for the specific search based on the selected field
    $filterConditions[] = "LOWER(employees.$filterByFilter)  LIKE LOWER ('%$searchValueFilter%')";
  }
// Main search query
$searchquery = "SELECT * FROM employees 
    JOIN department ON department.dept_NAME = employees.dept_NAME 
    JOIN employmenttypes ON employmenttypes.employment_TYPE = employees.employment_TYPE
    JOIN position ON position.position_name = employees.position
    JOIN absences ON employees.emp_id = absences.emp_id";

// Count query
$countdataqry = "SELECT COUNT(employees.emp_id) AS total FROM employees 
    JOIN department ON department.dept_NAME = employees.dept_NAME 
    JOIN employmenttypes ON employmenttypes.employment_TYPE = employees.employment_TYPE
    JOIN position ON position.position_name = employees.position
    JOIN absences ON employees.emp_id = absences.emp_id";

// Add WHERE clause if filter conditions are present
if (!empty($filterConditions)) {
    $searchquery .= " WHERE " . implode(" AND ", $filterConditions);
    $countdataqry .= " WHERE " . implode(" AND ", $filterConditions);
}

// Execute the count query
$countdataqryresult = mysqli_query($conn, $countdataqry) or die("FAILED TO EXECUTE COUNT QUERY " . mysqli_error($conn));
$row = $countdataqryresult->fetch_assoc();
$total_rows = $row['total'];

// Add ORDER BY and LIMIT clauses to the main search query
$searchquery .= " ORDER BY $sortColumn $sortOrder  
    LIMIT $start_from, $results_perpage";

// Execute the main search query
$search_result = filterTable($searchquery);

// Calculate total pages
$totalpages = ceil($total_rows / $results_perpage);



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.css" />
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
    <!-- Bootstrap JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js"></script>
    <title>Manage Leave</title>
    <link rel="icon" type="image/png" href="../img/icon1 (3).png">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        tbody tr {
            display: table-row;
            vertical-align: middle; /* You can change this to 'top' or 'bottom' based on your preference */
        }
    </style>
</head>
<body>
<?php
include('navbarAdmin.php');
?>
<div class="title d-flex justify-content-center pt-3">
    <h3>
        MANAGE ABSENT
    </h3>
</div>
<div class="filter pt-3">
<div class="card shadow p-4" style="border: 1px solid #F6E3F3; width: 100%; border-radius:10px;">
<form method="GET" action="">
<?php
                    $deptchecked = isset($_GET['dept']) ? $_GET['dept'] : '';
                    $emptypechecked = isset($_GET['employmenttype']) ? $_GET['employmenttype'] : '';
                    $shiftchecked = isset($_GET['shifts']) ? $_GET['shifts'] : '';
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
    <select id="dept" class="form-select" name="dept"  style="border-radius:10px;">
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
    <select id="employmenttype" class="form-select" name="employmenttype"  style="border-radius:10px;">
      <option selected disabled>Select Employment Type</option>
      <?php
                          if (mysqli_num_rows($total_row) > 0) {
                            foreach ($total_row as $row) {
                              ?>
                              <!-- <li style="color:#333; font-size:12px; font-family: 'Roboto', sans-serif;"> -->
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
    <select name="position" id="position" class="form-select"  style="border-radius:10px;">
      <option selected disabled>Select Position</option>
      <?php
                          if (mysqli_num_rows($total_row) > 0) {
                            foreach ($total_row as $row) {
                              ?>
                              <!-- <li style="color:#333; font-size:12px; font-family: 'Roboto', sans-serif;"> -->
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
    <select id="employee_status" class="form-select" name="employee_status"  style="border-radius:10px;">
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

<div class="col-lg-3 col-sm-6">
<label for="Gender" class="form-label">Sex</label>
    <select id="Gender" class="form-select" name="Gender"  style="border-radius:10px;">
      <option selected disabled>Sex</option>
      
                          <option value="Male" <?php if (isset($_GET['Gender']) && $_GET['Gender'] == 'Male')
                            echo 'selected'; ?>>Male</option>
                          <option value="Female" <?php if (isset($_GET['Gender']) && $_GET['Gender'] == 'Female')
                            echo 'selected'; ?>>Female</option>
    </select>
</div>
<div class="row col-lg-3 col-sm-6">
<label class="form-label">Absence Date</label>

  <div class="col-4">
  <select name="month" id="month" class="form-select"  style="border-radius:10px;">
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
<select name="day" id="day" class="form-select"  style="border-radius:10px;">
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
<select name="year" id="year" class="form-select"  style="border-radius:10px;">
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
    <select id="filter_by" class="form-select" name="filter_by"  style="border-radius:10px;">
      
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
<div class="row mt-3 mb-1 d-flex justify-content-end">
    <div class="table d-flex align-items-center table-responsive">
        <table class="table table-striped">
            <thead class="table-striped" style="background-color: #2ff29e; color: #4929aa;">
                <tr>
                    <th style="border-top-left-radius: 10px; color: #4929aa;">EMP ID</th>
                    <th style="">Last Name</th>
                    <th style="">First Name</th>
                    <th style="">Middle Name</th>
                    <th style="">Username</th>
                    <th style="">Department</th>
                    <th style="">Employment Type</th>
                    <th style="">Position</th>
                    <th style="border-top-right-radius: 10px; color: #4929aa;">ABSENT DATE</th>
                </tr>
            </thead>
            <tbody>
            <?php
                function filterTable($searchquery)
                {
                    $conn1 = mysqli_connect("localhost:3307", "root", "", "masterdb");
                    $filter_Result = mysqli_query($conn1, $searchquery) or die("failed to query masterfile " . mysqli_error($conn1));
                    return $filter_Result;
                }

                while ($row1 = mysqli_fetch_array($search_result)) :
                ?>
                    <tr class="gradeX">
                        <td><a href="adminVIEWprofile.php?id=<?php echo $row1['emp_id']; ?>">
                                <?php echo $row1['prefix_ID']; ?>
                                <?php echo $row1['emp_id']; ?>
                            </a></td>
                        <td><?php echo $row1['last_name']; ?></td>
                        <td><?php echo $row1['first_name']; ?></td>
                        <td><?php echo $row1['middle_name']; ?></td>
                        <td><?php echo $row1['user_name']; ?></td>
                        <td><?php echo $row1['dept_NAME']; ?></td>
                        <td><?php echo $row1['employment_TYPE']; ?></td>
                        <td><?php echo $row1['position']; ?></td>
                        <td><?php echo $row1['absence_date']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr>
                <td colspan="12">
                    <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <?php
                        // Construct the base URL with existing filter parameters
                        $baseURL = $_SERVER['PHP_SELF'] . "?";

                        // Add existing filter parameters to the base URL
                        if (!empty($_GET)) {
                            foreach ($_GET as $key => $value) {
                                // Exclude 'page' parameter from the base URL
                                if ($key !== 'page') {
                                    $baseURL .= "$key=$value&";
                                }
                            }
                        }

                        for ($i = 1; $i <= $totalpages; $i++) {
                            echo "<li class='page-item";
                            if ($i == $page) {
                                echo " active";
                            }
                            echo "'><a class='page-link' href='" . $baseURL . "page=$i'>$i</a></li>";
                        }
                        ?>
                    </ul>

                    </nav>
                </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
<?php
unset($_SESSION['delnotif']);
unset($_SESSION['masterfilenotif']);
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>
</html>
