<?php
include("../DBCONFIG.PHP");
include("../LoginControl.php");
include("../BASICLOGININFO.PHP");
date_default_timezone_set('Asia/Hong_Kong'); 
session_start();
  if (!isset($_SESSION['adminId'])) {
  // Redirect to the desired page
  header("Location: ../default.php"); // Change 'login.php' to the desired page
  exit; // Terminate script execution after redirection
}

$error = false;
$adminId = $_SESSION['adminId'];
$adminname = "SELECT first_name, last_name FROM employees where emp_id = '$adminId'";
$adminnameexecqry = mysqli_query($conn, $adminname) or die ("FAILED TO CHECK EMP ID ".mysqli_error($conn));
$adminData = mysqli_fetch_assoc($adminnameexecqry);

$adminFullName = $adminData['first_name'] . " " . $adminData['last_name'];
$master = $_SESSION['master'];
$periodid = $_GET['id'];


$sql = "SELECT * FROM payperiods WHERE payperiod_ID = $periodid";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row5 = $result->fetch_assoc();
}

if (isset($_POST['delete_btn'])) {


    $DELquery2 = "DELETE FROM payperiods WHERE payperiod_ID = '$periodid'";
    $delval = mysqli_query($conn, $DELquery2);


    if ($delval) {
        echo "success";
    } else {
        echo "Error deleting profile.";
    }
  
    exit(); // Ensure nothing else is sent in the response
}

if(isset($_POST['submit_btn'])){

  $pperiodstart = $_POST['dppstart'];
  $pperiodend = $_POST['dppend'];
  $pperiodrange = "$pperiodstart to $pperiodend";
  $date = strtotime($pperiodstart);
  $pperiodyear = date("Y",$date);

  $startdateinit = strtotime($pperiodstart);
  //$startdateconv = strtotime('-3 days',$startdateinit);
  $startdate = date("Y-m-d", $startdateinit);


  $enddateinit = strtotime($pperiodend);
  //$enddateconv = strtotime('-3 days',$enddateinit);
  $cutoff = date("Y-m-d", $enddateinit);

  $pperiodstartdate = new DateTime($pperiodstart);
  $pperiodenddate = new DateTime($pperiodend);
  $payperioddays = $pperiodenddate->diff($pperiodstartdate)->format("%a");
  $pres = ($payperioddays + 1);
  
  

  
  if(empty($pperiodstart)){

    $error = true;
    $periodstarterror = "Please enter payroll period start date.";

  }

  if(empty($pperiodend)){

    $error = true;
    $periodenderror = "Please payroll period end date.";

  }

  if(empty($pperiodrange)){

    $error = true;
    $periodrangeerror = "No payroll period range.";

  }

  if(empty($pperiodyear)){

    $error = true;
    $periodyearerror = "No payroll period year.";

  }

  if (empty($pres)){

    $error = true;
    $perioddayserror = "Payroll number of days not specified.";
  }


  
  if (!$error){

    $newshiftqry = "UPDATE payperiods SET pperiod_start = '$pperiodstart', pperiod_end ='$pperiodend',pperiod_range='$pperiodrange', payperiod_days='$pres', pperiod_year='$pperiodyear' WHERE payperiod_ID = $periodid";
    $newshiftqryresult = mysqli_query($conn,$newshiftqry) or die ("FAILED TO CREATE NEW PAYROLL PERIOD ".mysqli_error($conn));
    if ($newshiftqryresult) {
      ?>
      <script>
 document.addEventListener('DOMContentLoaded', function() {
     swal({
      //  title: "Good job!",
       text: "Payroll Period inserted successfully",
       icon: "success",
       button: "OK",
      }).then(function() {
         window.location.href = 'adminPAYROLLPERIODS.php'; // Replace 'your_new_page.php' with the actual URL
     });
 });
</script>
      <?php
  }
} else {
  ?>
  <script>
  document.addEventListener('DOMContentLoaded', function() {
      swal({
        // title: "Data ",
        text: "Something went wrong.",
        icon: "error",
        button: "Try Again",
      });
  }); </script>
  <?php
}
}
?>
<script>
    function confirmDelete() {
        swal({
            title: "Are you sure?",
            text: "",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
            .then((willDelete) => {
                if (willDelete) {
                    var xhttp = new XMLHttpRequest();
                    xhttp.onreadystatechange = function () {
                        if (this.readyState == 4 && this.status == 200) {
                            var response = this.responseText.trim();
                            if (response === "success") {
                                swal("Payroll Period deleted successfully!", { icon: "success" })
                                    .then(() => {
                                        window.location.href = "adminPAYROLLPERIODS.php";
                                    });
                            } else {
                                swal("Error deleting payroll period: " + response, { icon: "error" });
                            }
                        }
                    };

                    xhttp.open("POST", "", true);
                    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    xhttp.send("id=<?php echo $periodid; ?>&delete_btn=1");
                } else {
                    swal("Payroll Period is safe!", { icon: "info" });
                }
            });
    }
</script>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Admin Home</title>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap">
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

</head>
<body>


<?php
INCLUDE ('navbarAdmin.php');
?>


<div id="content">
    <div class="title text-center pt-4">
    <h3>EDIT PAYROLL PERIOD</h3>

    </div>
<div class="flex d-flex justify-content-center">
<div class="card card1 shadow mt-5">


<div class="container">
    <div class = "row">
        
    <div class="span6">
        <div class="widget-box">

          <div class="widget-title my-4 text-center">
            <h5>Payroll Period Information</h5>
          </div>

          <div class="widget-content nopadding">
            <form action="<?php $_SERVER['PHP_SELF']; ?>" method="POST" class="form-horizontal">
              

            <div class="row">
                <div class="col-6">
                <div class="control-group">
                <label class="control-label">Payroll Period Start:</label>
                <div class="controls">
                <input type="text" class="form-control datepicker" id="start-date" name="dppstart" placeholder="Date" value="<?php echo $row5['pperiod_start']; ?>"/>

                </div>
              </div>
                </div>
                <div class="col-6">
                <div class="control-group">
                <label class="control-label">Payroll Period End:</label>
                <div class="controls">
                <input type="text" class="form-control datepicker" id="end-date" name="dppend" placeholder="Date" value="<?php echo $row5['pperiod_end']; ?>" />

                </div>
              </div>
                </div>

              
                <div class="form-actions d-flex justify-content-center my-3">
                <button type="submit" class="inline-block bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-md border border-green-500 hover:border-green-600 transition duration-300 ease-in-out" name="submit_btn" style="float: right;">Submit</button>
                <button type="button" class="inline-block btn btn-danger hover:bg-green-600 text-white py-2 px-4 rounded-md border border-green-500 hover:border-green-600 transition duration-300 ease-in-out" name="delete_btn" style="float: right;" onclick="confirmDelete()">Delete</button>

              </div>
              </div>
            </div>
          
        
              
            </form>
        </div>
    </div>
 
  
</div>
</div>
</div>
</div>


 
</div>



<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
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
