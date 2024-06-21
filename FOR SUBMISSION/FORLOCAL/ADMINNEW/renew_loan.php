<!DOCTYPE html>
<html lang="en">
<head>
<title>Renew Loans</title>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap">
<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>

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

$adminId = $_SESSION['adminId'];
$error = false;

//for act log hehehehehe
$adminname = "SELECT first_name, last_name FROM employees where emp_id = '$adminId'";
$adminnameexecqry = mysqli_query($conn, $adminname) or die ("FAILED TO CHECK EMP ID ".mysqli_error($conn));
$adminData = mysqli_fetch_assoc($adminnameexecqry);

$adminFullName = $adminData['first_name'] . " " . $adminData['last_name'];
$selectedLoantypeorg ='';

if ($_SERVER["REQUEST_METHOD"] == "GET") {
$uniqueKey = mysqli_real_escape_string($conn, $_GET['uniquekey']);
$getloan = "SELECT * FROM loans WHERE uniquekey='$uniqueKey'";
$result = mysqli_query($conn, $getloan);
$loan = mysqli_fetch_assoc($result);
$loanID = $loan['loanidno'];
$loantype = $loan['loantype'];
$first = $loan['empfirstname'];
$middle11 = $loan['empmiddlename'];
$last = $loan['emplastname'];
$loantype = $loan['loantype'];
$loanorg = $loan['loanorg'];
$empid1 = $loan['emp_id'];
$start_date = $loan['start_date'];
$end_date= $loan['end_date'];
$monthly= $loan['monthly_deduct'];
$paymentnumber = $loan['no_of_pays'];
echo $empid1;

} 
if(isset($_POST['submit_btn'])){
  $uniqueKey = $_POST['uniquekey'];
  $loantype1 = $_POST['loantype'];
  $loanorg1= $_POST['loanorg'];
  $loanidno = $_POST['loanidno'];
  $gsisempid = $_POST['gsisempid'];

  // $empname = $_POST['empname'];
  $lastname = $_POST['last_name'];
  $firstname1 = $_POST['first_name'];
  $middlename1 = $_POST['middle_name'];
  $startdate = $_POST['startpicker'];
  $enddate = $_POST['endpicker'];
  $monthlydeductionamount = $_POST['monthlydeductionamount'];
  $noofpays = $_POST['payduration'];

  $empidqry = "SELECT emp_id FROM employees where emp_id = '$gsisempid'";
  $empidexecqry = mysqli_query($conn,$empidqry) or die ("FAILED TO CHECK EMP ID ".mysqli_error($conn));
  $empidcount = mysqli_num_rows($empidexecqry);

  if($empidcount!=1){
    $error = true;
    $empiderror = "Employee ID does not exist.";
  }

if (empty($startdate)){

  $error = true;
  $startdateerror = "Please indicate loan start date.";

}

if (empty($enddate)){
  $error = true;
  $enddateerror = "Please indicate loan end date.";

}


if(empty($monthlydeductionamount)){
  $error = true;
  $monthlydeductionamounterror = "Please enter the amount to be deducted every month.";

}

if(empty($noofpays)){
  $error = true;
  $paydurationerror = "Please enter number of payment months.";
}

  if (!$error){
      echo $uniqueKey, $gsisempid;
   $newdeptqry= "UPDATE loans SET start_date ='$startdate', end_date='$enddate',monthly_deduct='$monthlydeductionamount',no_of_pays='$noofpays',status='On-Going', adminname='$adminFullName' WHERE uniquekey ='$uniqueKey' AND emp_id ='$gsisempid'";
    // $newdeptqry = "INSERT INTO loans (uniquekey,loanidno,loanorg,loantype, emp_id,empfirstname,emplastname, empmiddlename, start_date,end_date,monthly_deduct,no_of_pays,status, adminname) VALUES ('$uniqueKey','$loanidno','$loanorg1','$loantype1','$gsisempid','$firstname1','$lastname','$middlename','$startdate','$enddate','$monthlydeductionamount','$noofpays','On-Going', '$adminFullName')";
    $newdeptqryresult = mysqli_query($conn,$newdeptqry) or die (" ".mysqli_error($conn));

   $updateQuery = "UPDATE loan_history SET remarks='Renewed' , status='Renewed'
                WHERE uniquekey='$uniqueKey' 
                ORDER BY loanhistory_id DESC 
                LIMIT 1";

    $updateResult = mysqli_query($conn, $updateQuery) or die(" " . mysqli_error($conn));
    
    // Insert the new record
    $loanhistory = "INSERT INTO loan_history (uniquekey, loan_id, loantype, loanorg, emp_id, lastname, firstname, middlename, start_date, end_date, monthly_payment, status, num_of_payments, admin_name) VALUES
        ('$uniqueKey', '$loanidno', '$loantype1', '$loanorg1', '$gsisempid', '$lastname', '$firstname1', '$middlename1', '$startdate', '$enddate', '$monthlydeductionamount', 'On-Going', '$noofpays', '$adminFullName')";
    $loanhistoryresult = mysqli_query($conn, $loanhistory) or die(" " . mysqli_error($conn));


    $activityLog = "Renewed Loan for ($firstname1 $lastname)";
    $adminActivityQuery = "INSERT INTO adminactivity_log (emp_id, adminname, activity,log_timestamp) VALUES ('$adminId','$adminFullName', '$activityLog', NOW())";
    $adminActivityResult = mysqli_query($conn, $adminActivityQuery);


    $notificationMessage = "Loan has been renewed for $firstname1 $lastname";
    $insertNotificationQuery = "INSERT INTO empnotifications (admin_id, adminname, emp_id, message, type, status) VALUES ('$adminId', '$adminFullName','$gsisempid','$notificationMessage','Loan','unread')";
    mysqli_query($conn, $insertNotificationQuery);

    if($newdeptqryresult){

      ?>
   
   <script>
   document.addEventListener('DOMContentLoaded', function() {
       swal({
         text: "Loan inserted successfully",
         icon: "success",
         button: "OK",
        }).then(function() {
           window.location.href = 'adminMasterLoans.php'; // Replace 'your_new_page.php' with the actual URL
       });
   });
</script>
    <?php
 
}
  } else {
    $errType = "danger";
    ?><script>
    document.addEventListener('DOMContentLoaded', function() {
        swal({
          text: "Something went wrong.",
          icon: "error",
          button: "Try Again",
        });
    }); </script>
    <?php
  }

} 

?>
</head>
<style>
  textarea {
    max-width: 100%;
    width: 100%;
    height: auto;
    box-sizing: border-box;
  }

  .userinfo {
    margin-bottom: 10px;
  }
</style>
<body>

<!--Header-part-->

<?php
INCLUDE ('navbarAdmin.php');
?>

<div id="content">
  <div class="title d-flex justify-content-center pt-4">
    <h3>ADD LOAN</h3>
  </div>


<form action="" method="POST">
    <input type="hidden" name="uniquekey" value="<?php echo htmlspecialchars($_GET['uniquekey']); ?>">
    <div class="nopadding col-6 card shadow mx-auto my-5 p-3 mt-5">
    <div class="row">
      <div class="col-lg-6">
        <label class="control-label" for="loantype">Loan Type:</label>
        <input type="text" name="loantype" id="loantype" class="form-control" required value="<?php echo $loantype ?? ''; ?>" readonly>
      </div>
      <div class="col-lg-6">
        <label class="control-label" for="loanorg">Loan Org:</label>
        <input type="text" name="loanorg" id="loanorg" class="form-control" required value="<?php echo $loanorg ?? ''; ?>" readonly>
      </div>
    </div>
  <div class="row">
    <div class="col-lg-6 col-md-12">
      <label class="control-label">Loan ID Number:</label>
        <input type="text" class="form-control" placeholder="Loan ID Number" name="loanidno" value="<?php echo $loanID ?? ''; ?>" readonly/>
    </div>
            
    <div class="col-lg-6 col-md-12">
      <label class="control-label">Employee ID:</label>
        <input type="text" class="form-control" placeholder="Employee ID" name="gsisempid" value="<?php echo $empid1 ?? ''; ?>" readonly/>
    </div>

    <div class="col-lg-4 col-md-6">
      <label class="control-label">Last Name:</label>
        <input type="text" class="form-control" placeholder="Last Name" name="last_name" value = "<?php echo $last ?? '';?>" readonly/>
    </div>

    <div class="col-lg-4 col-md-6">
      <label class="control-label">First Name:</label>
        <input type="text" class="form-control" placeholder="First Name" name="first_name" value = "<?php echo $first ?? '';?>" readonly/>
    </div>

    <div class="col-lg-4 col-md-6">
      <label class="control-label">Middle Name:</label>
        <input type="text" class="form-control" placeholder="Middle Name" name="middle_name" value = "<?php echo $middle11 ?? '';?>" readonly/>
    </div>

    <div class="col-lg-6 col-md-12">
      <label class="control-label">Start Date:</label>
        <input type="text" class="form-control datepicker" id="startdatepicker" name ="startpicker" placeholder="Start Date" value="<?php echo $start_date ?? '';?>" required >
    </div>

    <div class="col-lg-6 col-md-12">
      <label class="control-label">End Date:</label>
        <input type="text" class="form-control datepicker" id="enddatepicker" name ="endpicker" placeholder="End Date" value="<?php echo $end_date ?? '';?>" required>
    </div>
    

    <div class="col-lg-6 col-md-6">
      <label class="control-label">Monthly Deduction:</label>
        <input type="text" class="form-control" placeholder="Monthly Deduction Amount" name="monthlydeductionamount" value="<?php echo $monthly ?? '';?>" required/>
    </div>

 

    <div class="col-lg-6 col-md-6">
      <label class="control-label">Pay Duration:</label>
        <input type="text" class="form-control" placeholder="" name="payduration" value="<?php echo $paymentnumber ?? '';?>" readonly required/>
          <span> months</span>
    </div>
  </div>

  <div class="button d-flex justify-content-center">
    <div class="form-actions">
      <button type="submit" class="inline-block bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-md border border-green-500 hover:border-green-600 transition duration-300 ease-in-out" name="submit_btn" id="submit_btn" style="float: right;">Submit</button>
    </div>
  </div>
</form>
</div>
</div>
  
<div class="row-fluid">
  <div id="footer" class="span12"> 2023 &copy; WEB-BASED TIMEKEEPING AND PAYROLL SYSTEM USING FINGERPRINT BIOMETRICS</div>
</div>

<?php
unset($_SESSION['anewdept']);
?>

<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });
    });
</script>
<script>
  $(document).ready(function() {
    // Assuming date format is yyyy mm dd
    $("#enddatepicker, #startdatepicker").on("change", function() {
      var startDate = $("#startdatepicker").val();
      var endDate = $("#enddatepicker").val();

      if (startDate && endDate) {
        var start = new Date(startDate);
        var end = new Date(endDate);

        var monthDiff = (end.getFullYear() - start.getFullYear()) * 12 + end.getMonth() - start.getMonth();

        if (monthDiff < 0) {
                monthDiff = 0;
            }

            $("input[name='payduration']").val(monthDiff);

            // Disable button if payduration is 0
            if (monthDiff === 0) {
                $("#submit_btn").prop("disabled", true);
            } else {
                $("#submit_btn").prop("disabled", false);
            }
      }
    });
  });

  $(document).ready(function() {
    // Assuming date format is yyyy mm dd
    $("#enddatepicker, #startdatepicker, input[name='loanamount'], input[name='payduration']").on("change", function() {
        var startDate = $("#startdatepicker").val();
        var endDate = $("#enddatepicker").val();
        var loanAmount = parseFloat($("input[name='loanamount']").val());
        var payDuration = parseInt($("input[name='payduration']").val());

        if (startDate && endDate && !isNaN(loanAmount) && !isNaN(payDuration)) {
            var start = new Date(startDate);
            var end = new Date(endDate);

            var monthDiff = (end.getFullYear() - start.getFullYear()) * 12 + end.getMonth() - start.getMonth() + 1;

           
        }
    });
  });
</script>

</body>
<style>
 body{
  font-family: 'Poppins', sans-serif;
  background-image: #ffff;
}
</style>
</html>
