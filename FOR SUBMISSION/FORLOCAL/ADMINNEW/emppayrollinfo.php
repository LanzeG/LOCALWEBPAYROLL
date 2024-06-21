<!DOCTYPE html>
<html lang="en">
<head>
<title>Admin</title>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@10">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

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

$idres = $_GET['emp_id'];
$adminId = $_SESSION['adminId'];
$error = false;
$adminname = "SELECT first_name, last_name FROM employees where emp_id = '$adminId'";
$adminnameexecqry = mysqli_query($conn, $adminname) or die ("FAILED TO CHECK EMP ID ".mysqli_error($conn));
$adminData = mysqli_fetch_assoc($adminnameexecqry);

$adminFullName = $adminData['first_name'] . " " . $adminData['last_name'];

$master = $_SESSION['master'];


$sql = "SELECT * FROM payrollinfo JOIN employees ON payrollinfo.emp_id = employees.emp_id  WHERE employees.emp_id = $idres AND payrollinfo.emp_id  = $idres";
$result = $conn->query($sql);
$row5 = $result->fetch_assoc();

$isAdminEditingOwnProfile = ($adminId == $idres);


if (isset($_POST['submit_btn']) ){
    
    $basePay = isset($_POST['basepay']) ? $_POST['basepay'] : '';
    $dailyRate = isset($_POST['dailyrate']) ? $_POST['dailyrate'] : '';
    $hourlyRate = isset($_POST['hourlyrate']) ? $_POST['hourlyrate'] : '';
    $refSalary = isset($_POST['refsalary']) ? $_POST['refsalary'] : '';
    $gsis = isset($_POST['gsis']) ? $_POST['gsis'] : '';
    $philhealth = isset($_POST['philhealth']) ? $_POST['philhealth'] : '';
    $pagibig = isset($_POST['pagibig']) ? $_POST['pagibig'] : '';
    $wtax = isset($_POST['wtax']) ? $_POST['wtax'] : '';
    $disallowance = isset($_POST['disallowance']) ? $_POST['disallowance'] : '';
    $currDisallowance = isset($_POST['currdisallowance']) ? $_POST['currdisallowance'] : '';
    $compensation = isset($_POST['compensation']) ? $_POST['compensation'] : '';
    $eveningservice = isset($_POST['eveningservice']) ? $_POST['eveningservice'] : '';
    $ugoverload = isset($_POST['ugoverload']) ? $_POST['ugoverload'] : '';
    $gdoverload = isset($_POST['gdoverload']) ? $_POST['gdoverload'] : '';

  $updateSql = "UPDATE payrollinfo SET 
      base_pay = '$basePay',
      daily_rate = '$dailyRate',
      hourly_rate = '$hourlyRate',
      refsalary = '$refSalary',
      gsis = '$gsis',
      philhealth = '$philhealth',
      pagibig = '$pagibig',
      wtax = '$wtax',
      compensation = '$compensation',
      disallowance = '$disallowance',
      current_disallowance = '$currDisallowance',
      eveningservicerate ='$eveningservice',
      ugoverload = '$ugoverload',
      gdoverload = '$gdoverload'
      WHERE emp_id = $idres";

if ($conn->query($updateSql) === TRUE) {
  ?>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            title: "Record Updated",
            icon: "success", // Corrected the casing
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            showCancelButton: false,
            timer: 3000, // Auto close after 5 seconds
            timerProgressBar: true // Display a progress bar
        }).then(function() {
            // Redirect to another location
            window.location.href = "adminPAYROLLINFO.php";
        });
    });
</script>


          <?php
} else {
  ?><script>
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
</head>
<body>


<!--Header-part-->

<?php
INCLUDE ('navbarAdmin.php');
?>

<div class="content">
  <div class="form pt-3">
    <form action="" method="POST" enctype="multipart/form-data">
      <div class="row m-2 d-flex justify-content-center">
        <div class="col-sm-12 col-lg-8 card shadow ">
          <div class="title pt-2">
            <h4 class="text-center">PAYROLL INFORMATION</h4>
            <hr>
          </div>
          <div class="col-12 ">
            <div class="row">
                <div class="col-lg-3 col-sm-12">
                  <div class="control-group ">
                    <label class="control-label">Employee ID :</label>
                      <div class="controls">
                      <input type="text" class="span11 form-control" placeholder="EMPID" name="employeeID" value='<?php echo $idres; ?>' required disabled/>
                      </div>         
                  </div>
                        </div>
                <div class="col-lg-3 col-sm-">
                  <div class="control-group">
                    <label class="control-label">Last Name :</label> 
                      <div class="controls">
                        <input type="text" class="span11 form-control" placeholder="Last name" name="lastname" value="<?php echo $row5["last_name"];?>" required disabled/>
                      </div>
                  </div>
                </div>
                <div class="col-lg-3 col-sm-12">
                  <div class="control-group">
                    <label class="control-label">First Name :</label> 
                      <div class="controls">
                        <input type="text" class="span11 form-control" placeholder="First name" name="firstname" value="<?php echo $row5["first_name"];?>" required disabled/>
                      </div>
                  </div>
                </div>
                <div class="col-lg-3 col-sm-12">
                  <div class="control-group">
                    <label class="control-label">Middle Name</label>
                      <div class="controls">
                        <input type="text"  class="span11 form-control" placeholder="Middle name" name="middlename" value="<?php echo $row5["middle_name"];?>" disabled/>
                      </div>
                    </div>
                  </div>
                </div>
            <div class="row">
                <div class="col-lg-6 col-sm-12">
                  <div class="control-group">
                    <label class="control-label">Department:</label>
                  <div class="controls">
                    <input type="text" class="span11 form-control " placeholder="dept" name="dept" value="<?php echo $row5["dept_NAME"];?>" required disabled/>
                  </div>
                  </div>
                </div>
                    <div class="col-lg-6 col-sm-12">
                      <div class="control-group ">
                        <label class="control-label">Employment Type:</label>
                          <div class="controls">
                            <input type="text" class="span11 form-control" placeholder="emptype" name="emptype" value="<?php echo $row5["employment_TYPE"];?>" required disabled/>
                          </div>
                        </div>
                    </div>
                 </div>
            <div class="row">
                <div class="col-lg-4 col-sm-12">
                  <label class="control-label">Base Pay: </label>
                    <div class="controls">
                     <input type="text" class = "validate span3 form-control"  name ="basepay" placeholder="basepay" value="<?php echo $row5["base_pay"];?>" required <?php if ($isAdminEditingOwnProfile || !$master) echo 'disabled'; ?>>       
                  </div>
                </div>

                <div class="col-lg-4 col-sm-12">
                 <label class="control-label">Daily Rate:</label>
                   <div class="controls">
                   <input type="text" class = "span3 form-control"  name ="dailyrate" placeholder="dailyrate" value="<?php echo $row5["daily_rate"];?>" required  <?php if ($isAdminEditingOwnProfile || !$master) echo 'disabled'; ?>>       

                  </div>
                    </div>
                <div class="col-lg-4 col-sm-12">
                 <label class="control-label">Hourly Rate:</label>
                   <div class="controls">
                   <input type="text" class = "validate span3 form-control"  name ="hourlyrate" placeholder="hourlyrate" value="<?php echo $row5["hourly_rate"];?>" required <?php if ($isAdminEditingOwnProfile || !$master) echo 'disabled'; ?>>       

                  </div>
                </div>
                 <div class="row mt-1">
                 <div class="col-lg-4 col-sm-12">
                     <label class="control-label">PERA:</label>
                       <div class="controls">
                       <input type="text" class = "validate span3 form-control"  name ="refsalary" placeholder="refsalary" value="<?php echo $row5["refsalary"];?>" required <?php if ($isAdminEditingOwnProfile || !$master) echo 'disabled'; ?>>       

                      </div>
                        </div>
                 <div class="col-lg-4 col-sm-12">
                     <label class="control-label">Compensation:</label>
                       <div class="controls">
                       <input type="text" class = "validate span3 form-control"  name ="compensation" placeholder="compensation" value="<?php echo $row5["compensation"];?>" required <?php if ($isAdminEditingOwnProfile || !$master) echo 'disabled'; ?>>      
                      </div>
                        </div>
                    <div class="col-lg-4 col-sm-12">
                     <label class="control-label">GSIS</label>
                      <div class="controls">
                        <input type="text" class="validate span11 form-control" placeholder="gsis" name="gsis" value="<?php echo $row5["gsis"];?>" required <?php if ($isAdminEditingOwnProfile || !$master) echo 'disabled'; ?>/>
                      </div>
                    </div>
                    
                </div>
                 
                 <div class="row">
                     <div class="col-lg-4 col-sm-12">
                    <label class="control-label">Philhealth: </label>
                    <div class="controls">
                        <input type="text" class="validate span11 form-control" placeholder="philhealth" name="philhealth" value="<?php echo $row5["philhealth"];?>" required <?php if ($isAdminEditingOwnProfile || !$master) echo 'disabled'; ?>/>
                    </div>
                    </div>
                    <div class="col-lg-4 col-sm-12">
                    <label class="control-label">PAGIBIG:</label>
                      <div class="controls">
                        <input type="text" class="validate span11 form-control" placeholder="pagibig" name="pagibig" value="<?php echo $row5["pagibig"];?>" required <?php if ($isAdminEditingOwnProfile || !$master) echo 'disabled'; ?>/>
                      </div>
                    </div>
                    <div class="col-lg-4 col-sm-12">
                    <label class="control-label">WTAX:</label>
                      <div class="controls">
                      <input type="text" class="validate span11 form-control" placeholder="wtax" name="wtax" value="<?php echo $row5["wtax"];?>" required <?php if ($isAdminEditingOwnProfile || !$master) echo 'disabled'; ?> />
                      </div>
                    </div>
                    <?php if ($row5['acct_type'] == 'Faculty' || $row5['acct_type'] == 'Faculty w/ Admin') { ?>
                    <div class="col-lg-6 col-sm-12">
                    <label class="control-label">UG Overload:</label>
                      <div class="controls">
                      <input type="text" class="validate span11 form-control" placeholder="ugoverload" name="ugoverload" value="<?php echo $row5["ugoverload"];?>" required <?php if ($isAdminEditingOwnProfile || !$master) echo 'disabled'; ?> />
                      </div>
                    </div>
                    <div class="col-lg-6 col-sm-12">
                    <label class="control-label">GD Overload:</label>
                      <div class="controls">
                      <input type="text" class="validate span11 form-control" placeholder="gdoverload" name="gdoverload" value="<?php echo $row5["gdoverload"];?>" required <?php if ($isAdminEditingOwnProfile || !$master) echo 'disabled'; ?> />
                      </div>
                    </div>
                    <?php } 
                    if ($row5['acct_type'] == 'Faculty') {
                    ?>
                    <div class="col-lg-6 col-sm-12">
                    <label class="control-label">Disallowance:</label>
                      <div class="controls">
                        <input type="text" class="validate span6 form-control" placeholder="disallowance" name="disallowance" value="<?php echo $row5["disallowance"];?>" required <?php if ($isAdminEditingOwnProfile || !$master) echo 'disabled'; ?>>
                      </div>
                    </div>
                    <div class="col-lg-6 col-sm-12">
                    <label class="control-label">Current Disallowance:</label>
                      <div class="controls">
                      <input type="text" class="validate span6 form-control" placeholder="Current Disallowance" name="currdisallowance" value="<?php echo $row5["current_disallowance"];?>"  required <?php if ($isAdminEditingOwnProfile || !$master) echo 'disabled'; ?>>
                      </div>
                      
                    </div>
                    <?php
                    }else{
                    ?>
                    <div class="col-lg-4 col-sm-12">
                    <label class="control-label">Evening Service:</label>
                      <div class="controls">
                        <input type="text" class="validate span6 form-control" placeholder="eveningservice" name="eveningservice" value="<?php echo $row5["eveningservicerate"];?>" required <?php if ($isAdminEditingOwnProfile || !$master) echo 'disabled'; ?>>
                      </div>
                    </div>
                    <div class="col-lg-4 col-sm-12">
                    <label class="control-label">Disallowance:</label>
                      <div class="controls">
                        <input type="text" class="validate span6 form-control" placeholder="disallowance" name="disallowance" value="<?php echo $row5["disallowance"];?>" required <?php if ($isAdminEditingOwnProfile || !$master) echo 'disabled'; ?>>
                      </div>
                    </div>
                    <div class="col-lg-4 col-sm-12">
                    <label class="control-label">Current Disallowance:</label>
                      <div class="controls">
                      <input type="text" class="validate span6 form-control" placeholder="Current Disallowance" name="currdisallowance" value="<?php echo $row5["current_disallowance"];?>"  required <?php if ($isAdminEditingOwnProfile || !$master) echo 'disabled'; ?>>
                      </div>
                      
                    </div>
                    <?php
                    }
                    
                    ?>
                 </div>


                    <div class="form-actions text-center pb-3 pt-2">
                    <button type="submit" class="btn btn-success" name="submit_btn"  <?php if ($isAdminEditingOwnProfile || !$master) echo 'disabled'; ?>>Submit</button>
                  </div>
              
              </div>
            </div>
            </div>

           
<!-- end ng main row -->
</form>
</div>
<!-- row -->
<!-- col-8 -->
</div>
<!-- end ng span6 -->
  
<div class="row-fluid">
  <!-- <div id="footer" class="span12"> 2023 &copy; WEB-BASED TIMEKEEPING AND PAYROLL SYSTEM USING FINGERPRINT BIOMETRICS</div> -->
</div>
</div>
  <!-- end ng content -->
<?php
unset($_SESSION['addprofilenotif']);
?>


<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script>
  // Get all input elements with class 'inputText'
var inputFields = document.querySelectorAll('.validate');

// Loop through each input field and attach the event listener
inputFields.forEach(function(inputField) {
    inputField.addEventListener('input', function(e) {
        let value = e.target.value.replace(/[^0-9.]/g, ''); // Allow only numbers and '.'
        e.target.value = value;
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
