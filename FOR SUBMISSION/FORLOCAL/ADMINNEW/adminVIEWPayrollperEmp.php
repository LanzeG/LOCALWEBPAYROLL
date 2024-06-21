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

$idres = $_GET['id'];

//total number of rows
$pagecountqry = "SELECT COUNT(emp_id) from PAY_PER_PERIOD WHERE emp_id = '$idres'";
$pagecountres = mysqli_query($conn,$pagecountqry) or die ("Failed to count pages ".mysqli_error($conn));
$pagecounttotal = mysqli_fetch_row($pagecountres);
$rows = $pagecounttotal[0];


//number of results per page
$page_rows = 20;
//page number of last page
$lastpage = ceil($rows/$page_rows);
//This makes sure $lastpage cant be less than 1
if ($lastpage < 1){
  $lastpage=1;
}
//
$pagenum = 1;
//get pagenum from URL
if (isset($_GET['pn'])){
  $pagenum = preg_replace('#[^0-9]#', '', $_GET['pn']);
}
//makes sure page number isnt below 1 or more than $lastpage
if ($pagenum < 1){
  $pagenum = 1;
}else if ($pagenum > $lastpage){
  $pagenum = $lastpage;
}
//This set range of rows to query for $pagenum
$limit = "LIMIT "  .($pagenum-1)* $page_rows . ',' .$page_rows;

//What page and number of pages
$pageline1 = "Page <b>$pagenum</b> of <b>$lastpage</b>";
//pagectrls
$paginationCtrls = '';
//If more than 1 page
if ($lastpage !=1){
  /*Check if on page 1. If yes, previous link not needed. If not, we generate links to the first page and to the previos page. */
  if ($pagenum>1){
      $previous = $pagenum-1;
      $paginationCtrls .= '<li><a href="'.$_SERVER['PHP_SELF'].'?id='.$idres.'&pn='.$previous.'">Prev</a></li>';
      //number links left
      for ($i = $pagenum-4; $i < $pagenum; $i++){

        if($i > 0){
          $paginationCtrls .= '<li><a href="'.$_SERVER['PHP_SELF'].'?id='.$idres.'&pn='.$i.'">'.$i.'</a></li>';
        }

      }
  }

  //target page
    $paginationCtrls .='<li class = "active"><a href="'.$_SERVER['PHP_SELF'].'"</a>'.$pagenum.'</li>';
  //render clickable number links appear on right target page
    for ($i = $pagenum+1; $i <= $lastpage; $i++){
      $paginationCtrls .='<li><a href="'.$_SERVER['PHP_SELF'].'?id='.$idres.'&pn='.$i.'">'.$i.'</a></li>';
      if ($i >= $pagenum+4){
        break;
      }
    }

    if ($pagenum != $lastpage) {
        $next = $pagenum + 1;
        $paginationCtrls .= '<li><a href = "'.$_SERVER['PHP_SELF'].'?id='.$idres.'&pn='.$next.'">Next</a></li> ';
    }
}

$DELquery = "SELECT * from TIME_KEEPING, employees WHERE TIME_KEEPING.emp_id = employees.emp_id AND employees.emp_id ='$idres'";
$DELselresult = mysqli_query($conn,$DELquery) or die ("Failed to search DB. ".mysql_error());
  $DELcurr = mysqli_fetch_array($DELselresult);
  $DELcount = mysqli_num_rows($DELselresult);

   if($DELcount!=0 && $DELcurr) {

        $currprefixid = $DELcurr['prefix_ID'];
        $currempid = $DELcurr['emp_id'];
        $currfingerprintid = $DELcurr['fingerprint_id'];
        $currusername = $DELcurr['user_name'];
        $currlastname = $DELcurr['last_name'];
        $currfirstname = $DELcurr['first_name'];
        $currmiddlename = $DELcurr['middle_name'];
        $currdateofbirth = $DELcurr['date_of_birth'];
        $curraddress = $DELcurr['emp_address'];
        $currnationality = $DELcurr['emp_nationality'];
        $currdeptname = $DELcurr['dept_NAME'];
        $currshiftsched = $DELcurr['shift_SCHEDULE'];
        $currcontact = $DELcurr['contact_number'];
        $currdatehired = $DELcurr['date_hired'];
        $currdateregularized = $DELcurr['date_regularized'];
        $currdateresigned = $DELcurr['date_resigned'];
         $currimg = $DELcurr['img_tmp'];
        $currTIN = $DELcurr['TIN_number'];
        $currgsis = $DELcurr['GSIS_idno'];
        $currph = $DELcurr['PHILHEALTH_idno'];
        $currpagibig = $DELcurr['PAGIBIG_idno'];
    }
    else {
          $updateselecterror ="Employee information not found.";
          }/*2nd else end*/

if (isset($_POST['pperiod_btn'])){

   $payperiod = $_POST['payperiod'];
   
   $searchquery = "SELECT * FROM PAY_PER_PERIOD WHERE emp_id = '$idres' AND pperiod_range = '$payperiod' ORDER BY pperiod_range DESC $limit";
   $search_result = filterTable($searchquery);

} else  {
  $searchquery = "SELECT * from PAY_PER_PERIOD WHERE emp_id = '$idres' ORDER BY pperiod_range DESC $limit";  
  $search_result = filterTable($searchquery);
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>Employee Profile</title>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link rel="stylesheet" href="../css/bootstrap-responsive.min.css" />
<link rel="stylesheet" href="../css/fullcalendar.css" />

<link rel="stylesheet" href="../jquery-ui-1.12.1/jquery-ui.css">
<script src="../jquery-ui-1.12.1/jquery-3.2.1.js"></script>
<script src="../jquery-ui-1.12.1/jquery-ui.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.css" />
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap">
<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
<script type ="text/javascript">
  $( function() {
      $( "#datepickerfrom" ).datepicker({ dateFormat: 'yy-mm-dd'});
      } );
  $( function() {
      $( "#datepickerto" ).datepicker({ dateFormat: 'yy-mm-dd'});
      } );
  
</script>
<style>

.userinfo {
  margin-left:40px;
  
}

.uinfotab2 {
  display:block;
  float:right;
  margin-right: 350px;
}

.control-group{
  float:left;
  display: block;
}

</style>
</head>

<body>

<!--Header-part-->

<?php
INCLUDE ('navbaradmin.php');
?><div id="content">
<div class="title d-flex justify-content-center pt-3">
      <h3>
      EMPLOYEE INFORMATION
      </h3>
    </div>

    <div class="row-fluid">

      <div class = "span2">
      </div>

      <div class ="span8">

            <span class = "span4">
              <br>
              <span class="userinfo form-control">Employee ID: <b><?php echo $currempid; ?></b></span><br>
              <span class="userinfo form-control">Name: <b><?php echo $currlastname; ?>, <?php echo $currfirstname;?> <?php echo $currmiddlename;?></b></span><br>
              <span class="userinfo form-control">Username: <b><?php echo $currusername; ?></b></span><br>
              <span class="userinfo form-control">Department: <b><?php echo $currdeptname; ?> </b></span><br>
              <span class="userinfo form-control">DOB: <b><?php echo $currdateofbirth; ?></b></span><br>
              <span class="userinfo form-control">Nationality: <b><?php echo $currnationality; ?></b></span><br>
              <span class="userinfo form-control">Shift: <b><?php echo $currshiftsched; ?></b></span><br>
              <span class="userinfo form-control">Contact: <b><?php echo $currcontact; ?></b></span>
            </span>
            
            <span class = "span4">
              <br>
              <span class="userinfo form-control">TIN:<b> <?php echo $currTIN;?></b></span><br>
              <span class="userinfo form-control">Philhealth Number:<b> <?php echo $currph;?></b></span><br>
              <span class="userinfo form-control">GSIS Number:<b> <?php echo $currgsis;?></b></span><br>
              <span class="userinfo form-control">PAG-IBIG Number:<b> <?php echo $currpagibig;?></b></span><br>
              <span class="userinfo form-control">Date Hired:<b> <?php echo $currdatehired;?></b></span><br>
              <span class="userinfo form-control">Date Regularized:<b> <?php echo $currdateregularized; ?></b></span><br>
              <span class="userinfo form-control">Date Resigned:<b> <?php echo $currdateresigned; ?></b></span><br>
              <br>
              <br>

            </span>


            <span class = "span4">
              <br>
              <span class="userinfo"><img height = "157" width="157" src="data:image;base64,<?php echo $currimg?>"></span>
            </span>

      </div>

      <div class="table">
            <div class="tables col-12 text-align-center">  <div class="">
    <div class="table d-flex align-items-center table-responsive ">
<table class="table table-striped table-bordered ">
<thead class="table-dark">
                <tr>
                  
                  <th>Payroll Period</th>
                  <th>Basic Pay</th>
                  <!-- <th>OT Pay</th> -->
                  <th>Regular Holiday Pay</th>
                  <th>Special Non-working Holiday Pay</th>
                  <th>Gross Salary</th>
                  <th>Philhealth</th>
                  <th>GSIS</th>
                  <th>PAG-IBIG/HDMF</th>
                  <th>GSIS Loan</th>
                  <th>PAG-IBIG Loan</th>
                  <!-- <th>Withholding Tax</th> -->
                  <th>Total Deductions</th>
                  <th>Net Pay</th>
              
                </tr>
              </thead>
               <tbody> 

               <?php

function filterTable($searchquery)
{

     $conn1 = mysqli_connect("localhost:3307", "root", "", "masterdb");
     $filter_Result = mysqli_query($conn1,$searchquery) or die ("failed to query masterfile ".mysqli_error($conn1));
     return $filter_Result;
}


while($row1 = mysqli_fetch_array($search_result)):;
 $basepay = $row1['reg_pay'];
 // $otpay = $row1['ot_pay'];
 $shdaypay = $row1['shday_pay'];
 $hdaypay  =$row1['hday_pay'];

 $grosspay = ($basepay +  $shdaypay + $hdaypay);
 $gpay = number_format((float)$grosspay,2,'.','');
 $philhealth = $row1['philhealth_deduct'];
 $sss = $row1['sss_deduct'];
 $pagibig = $row1['pagibig_deduct'];
 $sssloan = $row1['sssloan_deduct'];
 $pagibigloan = $row1['pagibigloan_deduct'];
 // $withholdingtax = $row1['tax_deduct'];
 $totaldeduct = $row1['total_deduct'];

 $netpay = ($grosspay - $totaldeduct);
 $npay = number_format((float)$netpay,2,'.','');           
?>
   <tr class="gradeX">
   
   <td><?php echo $row1['pperiod_range'];?></td>
   <td><?php echo $basepay;?></td>
   <!-- <td><?php echo $otpay;?></td> -->
   <td><?php echo $hdaypay;?></td>
   <td><?php echo $shdaypay;?></td>
   <td><?php echo $gpay;?></td>
   <td><?php echo $philhealth; ?></td>
   <td><?php echo $sss; ?></td>
   <td><?php echo $pagibig; ?></td>
   <td><?php echo $sssloan; ?></td>
   <td><?php echo $pagibigloan; ?></td>
   <!-- <td><?php echo $withholdingtax; ?></td> -->
   <td><?php echo $totaldeduct; ?></td>
   <td><center><b>&#8369; <?php echo $npay;?></td>

   </tr>
              <?php endwhile;?>
              </tbody>
            </table>
            
</div>

<div class="row-fluid">
    <div class = "pagination alternate">
               <ul>
               <?php echo $paginationCtrls; ?>
               </ul>
            </div>
    </div>


<script src="../js/maruti.dashboard.js"></script> 
<script src="../js/excanvas.min.js"></script> 

<script src="../js/bootstrap.min.js"></script> 
<script src="../js/jquery.flot.min.js"></script> 
<script src="../js/jquery.flot.resize.min.js"></script> 
<script src="../js/jquery.peity.min.js"></script> 
<script src="../js/fullcalendar.min.js"></script> 
<script src="../js/maruti.js"></script> 
</body>
</html>

<style>
  /* Pagination Styles */
.pagination {
  display: flex;
  justify-content: center;
  margin-top: 20px;
}

.pagination ul {
  list-style: none;
  padding: 0;
  display: flex;
}

.pagination li {
  margin: 0 5px;
}

.pagination a, .pagination span {
  display: inline-block;
  padding: 8px 12px;
  text-decoration: none;
  color: #333;
  background-color: #fff;
  border: 1px solid #ddd;
  border-radius: 4px;
  transition: background-color 0.3s;
}

.pagination a:hover {
  background-color: #ddd;
}

.pagination .current {
  background-color: #007BFF;
  color: #fff;
  border: 1px solid #007BFF;
}

/* Alternate Pagination Styles */
.pagination.alternate a, .pagination.alternate span {
  background-color: #007BFF;
  color: #fff;
  border: 1px solid #007BFF;
}

.pagination.alternate a:hover {
  background-color: #0056b3;
}

     body{
  font-family: 'Poppins', sans-serif;
}

</style>
