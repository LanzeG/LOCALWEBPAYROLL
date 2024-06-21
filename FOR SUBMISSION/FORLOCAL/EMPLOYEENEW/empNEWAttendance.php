<?php
session_start();

include("../DBCONFIG.PHP");
include("../LoginControl.php");
include("../BASICLOGININFO.PHP");

date_default_timezone_set('Asia/Manila');
$current_datetime = date('Y-m-d');

$datefrom = isset($_SESSION['dpfrom']) ? $_SESSION['dpfrom'] : $current_datetime;
$dateto = isset($_SESSION['dpto']) ? $_SESSION['dpto'] : $current_datetime;

$results_perpage = 20;

if (isset($_GET['page'])){
    $page = $_GET['page'];
} else {
    $page = 1;
}

$currentempid = $_SESSION['empID'];

$userIdpage  = $_SESSION['empID'];

$pageViewed = basename($_SERVER['PHP_SELF']);
$pageInfo = pathinfo($pageViewed);
$pageViewed1 = $pageInfo['filename'];

logPageView($conn, $userIdpage, $pageViewed1, $current_datetime);

if (isset($_POST['searchbydate_btn'])){
    $_SESSION['dpfrom'] = $_POST['dpfrom'];
    $_SESSION['dpto'] = $_POST['dpto'];
}

$datefrom = isset($_SESSION['dpfrom']) ? $_SESSION['dpfrom'] : $current_datetime;
$dateto = isset($_SESSION['dpto']) ? $_SESSION['dpto'] : $current_datetime;

$start_from = ($page-1) * $results_perpage;

$searchquery = "SELECT * FROM dtr,employees WHERE dtr.emp_id = '$currentempid' AND dtr.emp_id = employees.emp_id AND DATE(dtr_day) BETWEEN '$datefrom' and '$dateto' ORDER BY dtr_day DESC LIMIT $start_from, $results_perpage";
$search_result = filterTable($searchquery);

$countquery = "SELECT COUNT(*) AS total FROM dtr WHERE emp_id = '$currentempid' AND DATE(dtr_day) BETWEEN '$datefrom' and '$dateto'";
$count_result = mysqli_query($conn, $countquery);
$row = mysqli_fetch_assoc($count_result);
$totalpages = ceil($row['total'] / $results_perpage);

function filterTable($query){
    $conn1 = mysqli_connect("localhost:3307", "root", "", "masterdb");
    $filter_Result = mysqli_query($conn1, $query) or die("failed to query masterfile ".mysqli_error($conn1));
    return $filter_Result;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>Attendance Record</title>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<script src="../../jquery-ui-1.12.1/jquery-3.2.1.js"></script>
<script src="../../jquery-ui-1.12.1/jquery-ui.js"></script>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;700&display=swap">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script type="text/javascript">
   $(function() {
      $("#datepicker").datepicker({ dateFormat: 'yy-mm-dd' });
   });
</script>
</head>
<header>
  <?php include('navbar2.php'); ?> 
</header>
<body>
<div class="masterdiv">
<div class="titlediv pt-5"> 
<h3 style="text-align: center;">ATTENDANCE RECORD</h3>         
</div>
<div class="control-group">
  <label class="control-label" style="margin-bottom:10px; margin-top:10px;">Search by date: </label>
    <div class="controls">
      <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
          <div class="d-flex flex-row flex-wrap gap-1">
            <div>
            <input class="span8 form-control" type="text" id="date" name="dpfrom" placeholder="From" value="<?php echo $datefrom; ?>">
            </div>
            <div>
            <input class="span8 form-control" type="text" id="date" name="dpto" placeholder="To" value="<?php echo $dateto; ?>">
            </div>
            <div>
            <button type="submit" class="btn btn-md btn-primary" name="searchbydate_btn">
              <i class="fas fa-search text-white"></i>
            </button>
            </div>
            <a href="empAbsences.php" class="btn btn-primary" style="float:left; margin-right: 10px;"><span class="icon"><i class="icon-refresh"></i></span> View Absences</a>
          </div>
      </form>
    </div>                 
</div>        

<div class="align-items-center table-responsive">
  <table class="table table-striped">
    <thead class="table" style="background-color: #2ff29e; color: #4929aa;">   
      <tr>
        <th>Employee ID</th>
        <th>Last Name</th>
        <th>First Name</th>
        <th>Middle Name</th>
        <th>Time In</th>
        <th>Time Out</th>
        <th>Day of Record</th>
      </tr>
    </thead>
    <tbody> 
      <?php while($row1 = mysqli_fetch_array($search_result)): ?>
        <tr class="gradeX">
          <td><?php echo $row1['prefix_ID'] . $row1['emp_id']; ?></td>
          <td><?php echo $row1['last_name']; ?></td>
          <td><?php echo $row1['first_name']; ?></td>
          <td><?php echo $row1['middle_name']; ?></td>
          <td><?php echo $row1['in_morning']; ?></td>
          <td><?php echo $row1['out_afternoon']; ?></td>
          <td><?php echo $row1['DTR_day']; ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
  <div class="buttons">
    <a href="empNEWAttendance.php" class="btn btn-success" style="float:left; margin-right: 10px;"><span class="icon"><i class="icon-refresh"></i></span> Refresh</a>
  </div>
</div>

<tfoot>
  <tr>
    <td colspan="12">
      <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
          <?php
          // Get the current URL without the page parameter
          $urlParams = $_GET;
          unset($urlParams['page']);
          $queryString = http_build_query($urlParams);
    
          if ($page > 1) {
              echo "<li class='page-item'><a class='page-link' href='" . $_SERVER['PHP_SELF'] . "?page=" . ($page - 1) . "&" . $queryString . "'>&laquo; Previous</a></li>";
          }
    
          $startPage = max(1, $page - 2);
          $endPage = min($totalpages, $page + 2);
    
          for ($i = $startPage; $i <= $endPage; $i++) {
              echo "<li class='page-item";
              if ($i == $page) {
                  echo " active";
              }
              echo "'><a class='page-link' href='" . $_SERVER['PHP_SELF'] . "?page=" . $i . "&" . $queryString . "'>" . $i . "</a></li>";
          }
    
          if ($page < $totalpages) {
              echo "<li class='page-item'><a class='page-link' href='" . $_SERVER['PHP_SELF'] . "?page=" . ($page + 1) . "&" . $queryString . "'>Next &raquo;</a></li>";
          }
          ?>
        </ul>
      </nav>
    </td>
  </tr>
</tfoot>

<?php
unset($_SESSION['masterfilenotif']);
?>

<style>
body{
  font-family: 'Poppins', sans-serif;
}
.widget-box {
  border-radius: 10px;
  border: 1px solid #ccc;
  padding: 15px; 
}
@media (max-width: 768px) {
  .widget-box {
    margin: auto;
    margin-top: 70px; 
  }

  .widget-title li {
    display: block;
    margin-bottom: 10px;
  }
}
.table {
  margin-left: 0px;
  margin-top: 40px;
  width: 100%;
  table-layout: auto;
}
.table-responsive {
  overflow-x: auto;
  max-width: 100%;
}
</style>
<script>
document.addEventListener("DOMContentLoaded", function () {
  flatpickr("#date", {
    dateFormat: "Y-m-d",
  });
});
</script>
</body>
</html>
