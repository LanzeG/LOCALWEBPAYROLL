  <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
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
$adminname = "SELECT first_name, last_name FROM employees where emp_id = '$adminId'";
$adminnameexecqry = mysqli_query($conn, $adminname) or die ("FAILED TO CHECK EMP ID ".mysqli_error($conn));
$adminData = mysqli_fetch_assoc($adminnameexecqry);

$adminFullName = $adminData['first_name'] . " " . $adminData['last_name'];

$master = $_SESSION['master'];
$emp_id = $_GET['id'];
$pperiod_range = $_GET['pperiod_range'];

$escaped_pperiod_range = $conn->real_escape_string($pperiod_range);

$sql = "SELECT * FROM pay_per_period JOIN employees ON pay_per_period.emp_id = employees.emp_id  
        WHERE employees.emp_id = '$emp_id' AND pay_per_period.emp_id = '$emp_id' AND pperiod_range = '$escaped_pperiod_range'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row5 = $result->fetch_assoc();
}

//loans
$loanSql = "SELECT * FROM loan_history WHERE emp_id = '$emp_id' AND payperiod = '$pperiod_range'";
$loanResult = $conn->query($loanSql);
$loanData = [];
if ($loanResult->num_rows > 0) {
    while ($loanRow = $loanResult->fetch_assoc()) {
        $loanData[] = $loanRow;
    }
}

$isAdminEditingOwnProfile = ($adminId == $emp_id);

if (isset($_POST['delete_confirmed']) && $_POST['delete_confirmed'] === 'true') {
    $DELquery2 = "DELETE FROM pay_per_period WHERE emp_id = '$emp_id' AND pperiod_range = '$pperiod_range'";
    $delval = mysqli_query($conn, $DELquery2);

   if ($delval) {
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
          swal({
            text: "Record deleted successfully.",
            icon: "success",
            button: "OK",
          }).then(function() {
            window.location.href = 'adminPayPerPeriod.php';
          });
        });
        </script>
        <?php
        exit; // Terminate script execution after deletion
    } else {
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
          swal({
            text: "Error deleting record.",
            icon: "error",
            button: "Try Again",
          });
        });
        </script>
        <?php
        exit; // Terminate script execution after error message
    }
}

if (isset($_POST['submit_btn']) ){
    // Get input values
    $basePay = $_POST['basepay'];
    $hourlyRate = $_POST['hourlyrate'];
    $netpay = $_POST['netpay'];
    $refSalary = $_POST['refsalary'];
    $gsis = $_POST['gsis'];
    $philhealth = $_POST['philhealth'];
    $pagibig = $_POST['pagibig'];
    $wtax = $_POST['wtax'];
    $compensation = $_POST['compensation'];
    $disallowance = $_POST['disallowance'];
    $absences = $_POST['absences'];
    $undertime = $_POST['undertime'];
    
    
    $totalLoan = 0;
    if (isset($_POST['loan_amount'])) {
        $loanAmounts = $_POST['loan_amount'];
        foreach ($loanAmounts as $loanAmount) {
            $totalLoan += $loanAmount;
        }
    }
    
       // Compute net pay
    $totalDeductions = $gsis + $pagibig + $philhealth + $wtax + $disallowance + $absences + $undertime + $totalLoan;
    $netpay = ($basePay + $refSalary + $compensation) - $totalDeductions;

    // Divide netpay by 2 and format first half
    $firsthalf = floor($netpay / 2);
    $secondhalf = $netpay - $firsthalf;

    // Ensure first half is in multiples of 1,000
    $firsthalf = floor($firsthalf / 1000) * 1000;
    $secondhalf = $netpay - $firsthalf;
    
 // Update pay_per_period table
    $updateSql = "UPDATE pay_per_period SET 
        reg_pay = '$basePay',
        rate_per_hour = '$hourlyRate',
        net_pay = '$netpay',
        refsalary = '$refSalary',
        sss_deduct = '$gsis',
        philhealth_deduct = '$philhealth',
        pagibig_deduct = '$pagibig',
        tax_deduct = '$wtax',
        compensation = '$compensation',
        disallowance = '$disallowance',
        undertimehours = '$undertime',
        absences = '$absences',
        firsthalf = '$firsthalf',
        secondhalf = '$secondhalf',
        total_deduct = '$totalDeductions'
        WHERE emp_id = $emp_id AND pperiod_range = '$pperiod_range'";
        
  if ($conn->query($updateSql) === TRUE) {
      if (isset($_POST['loanhistory_id']) && isset($_POST['loan_amount'])) {
      $loanIds = $_POST['loanhistory_id'];
      $loanAmounts = $_POST['loan_amount'];
      
      foreach ($loanIds as $index => $loanId) {
        $loanAmount = $loanAmounts[$index];
        $updateLoanSql = "UPDATE loan_history SET monthly_payment = '$loanAmount' WHERE loanhistory_id = '$loanId' AND emp_id = '$emp_id' AND payperiod = '$pperiod_range'";
        $conn->query($updateLoanSql);
        }
    } else {
        echo "Error updating record: " . $conn->error;
    }
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
      swal({
        text: "Information updated successfully",
        icon: "success",
        button: "OK",
      }).then(function() {
        window.location.href = 'adminPayPerPeriod.php';
      });
    });
    </script>
    <?php
  } else {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
      swal({
        text: "Something went wrong.",
        icon: "error",
        button: "Try Again",
      });
    });
    </script>
    <?php
  }
}
$basePayValue = $row5["employment_TYPE"] === "Permanent" ? $row5["reg_pay"] : $row5["rate_per_hour"] * $row5["hours_worked"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Admin</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.css">
  <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.min.js"></script>

  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
      <!-- Bootstrap JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js"></script>

  <?php INCLUDE ('navbarAdmin.php'); ?>
</head>
<body>
  <div class="content">
    <div class="form pt-3">
      <form action="" id ="delete_form" method="POST" enctype="multipart/form-data">
        <div class="row m-4 d-flex justify-content-center">
          <div class="col-lg-8 col-sm-12 card shadow ">
            <div class="title pt-2 text-center">
              <h1 class="text-3xl pb-2">PAYROLL INFORMATION</h1>
              <hr class="mx-auto pb-4 w-75">
            </div>
            <div class="col-12 ">
              <div class="row">
                <div class="col-lg-3 col-sm-12">
                  <div class="control-group">
                    <label class="control-label">Employee ID :</label>
                    <div class="controls">
                      <input type="text" class="span11 form-control" placeholder="EMPID" name="employeeID" value='<?php echo $row5['emp_id']; ?>' required disabled/>
                    </div>         
                  </div>
                </div>
                <div class="col-lg-3 col-sm-12">
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
                      <input type="text" class="span11 form-control" placeholder="Middle name" name="middlename" value="<?php echo $row5["middle_name"];?>" disabled/>
                    </div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-lg-6 col-sm-12">
                  <div class="control-group">
                    <label class="control-label">Department:</label>
                    <div class="controls">
                      <input type="text" class="span11 form-control" placeholder="dept" name="dept" value="<?php echo $row5["dept_NAME"];?>" required disabled/>
                    </div>
                  </div>
                </div>
                <div class="col-lg-6 col-sm-12">
                  <div class="control-group">
                    <label class="control-label">Employment Type:</label>
                    <div class="controls">
                      <input type="text" class="span11 form-control" placeholder="emptype" name="emptype" value="<?php echo $row5["employment_TYPE"];?>" required disabled/>
                    </div>
                  </div>
                </div>

                <div class="col-lg-4 col-sm-12">
                <label class="control-label">Base Pay: </label>
                <div class="controls">
                    <!-- Hidden input to store the value -->
                    <input type="hidden" name="basepay" value="<?php echo $basePayValue; ?>">
                    <!-- Actual input field (disabled) -->
                    <input type="text" class="validate span3 form-control" placeholder="basepay" value="<?php echo $basePayValue; ?>" disabled>
                </div>
            </div>
            
            <div class="col-lg-4 col-sm-12">
                <label class="control-label">Hourly Rate: </label>
                <div class="controls">
                    <!-- Hidden input to store the value -->
                    <input type="hidden" name="hourlyrate" value="<?php echo $row5["rate_per_hour"]; ?>">
                    <!-- Actual input field (disabled) -->
                    <input type="text" class="validate span3 form-control" placeholder="hourlyrate" value="<?php echo $row5["rate_per_hour"]; ?>" disabled>
                </div>
            </div>
            
            <div class="col-lg-4 col-sm-12">
                <label class="control-label">Net Pay: </label>
                <div class="controls">
                    <!-- Hidden input to store the value -->
                    <input type="hidden" name="netpay" value="<?php echo $row5["net_pay"]; ?>">
                    <!-- Actual input field (disabled) -->
                    <input type="text" class="validate span3 form-control" placeholder="netpay" value="<?php echo $row5["net_pay"]; ?>" disabled>
                </div>
            </div>
            
            <div class="col-lg-4 col-sm-12">
                <label class="control-label">First Half:</label>
                <div class="controls">
                    <!-- Hidden input to store the value -->
                    <input type="hidden" name="firsthalf" value="<?php echo $row5["firsthalf"]; ?>">
                    <!-- Actual input field (disabled) -->
                    <input type="text" class="validate span3 form-control" placeholder="firsthalf" value="<?php echo $row5["firsthalf"]; ?>" disabled>
                </div>
            </div>
            
            <div class="col-lg-4 col-sm-12">
                <label class="control-label">Second Half:</label>
                <div class="controls">
                    <!-- Hidden input to store the value -->
                    <input type="hidden" name="secondhalf" value="<?php echo $row5["secondhalf"]; ?>">
                    <!-- Actual input field (disabled) -->
                    <input type="text" class="validate span3 form-control" placeholder="secondhalf" value="<?php echo $row5["secondhalf"]; ?>" disabled>
                </div>
            </div>

                <div class="col-lg-4 col-sm-12">
                  <label class="control-label">PERA: </label>
                  <div class="controls">
                    <input type="text" class="validate span3 form-control" name="refsalary" placeholder="refsalary" value="<?php echo $row5["refsalary"];?>" required <?php if ($isAdminEditingOwnProfile|| !$master) echo 'disabled'; ?>>     
                  </div>
                </div>
                <div class="col-lg-4 col-sm-12">
                  <label class="control-label">GSIS:</label>
                  <div class="controls">
                    <input type="text" class="validate span3 form-control" name="gsis" placeholder="gsis" value="<?php echo $row5["sss_deduct"];?>" required <?php if ($isAdminEditingOwnProfile|| !$master) echo 'disabled'; ?>>      
                  </div>
                </div>
                <div class="col-lg-4 col-sm-12">
                  <label class="control-label">Philhealth:</label>
                  <div class="controls">
                    <input type="text" class="validate span3 form-control" name="philhealth" placeholder="philhealth" value="<?php echo $row5["philhealth_deduct"];?>" required <?php if ($isAdminEditingOwnProfile|| !$master|| !$master) echo 'disabled'; ?>>      
                  </div>
                </div>
                <div class="col-lg-4 col-sm-12">
                  <label class="control-label">Pag-Ibig:</label>
                  <div class="controls">
                    <input type="text" class="validate span3 form-control" name="pagibig" placeholder="pagibig" value="<?php echo $row5["pagibig_deduct"];?>" required <?php if ($isAdminEditingOwnProfile|| !$master) echo 'disabled'; ?>>       
                  </div>
                </div>
                <div class="col-lg-4 col-sm-12">
                  <label class="control-label">Withholding Tax:</label>
                  <div class="controls">
                    <input type="text" class="validate span3 form-control" name="wtax" placeholder="wtax" value="<?php echo $row5["tax_deduct"];?>" required <?php if ($isAdminEditingOwnProfile|| !$master) echo 'disabled'; ?>>       
                  </div>
                </div>
                <div class="col-lg-4 col-sm-12">
                  <label class="control-label">Compensation:</label>
                  <div class="controls">
                    <input type="text" class="validate span3 form-control" name="compensation" placeholder="compensation" value="<?php echo $row5["compensation"];?>" required <?php if ($isAdminEditingOwnProfile|| !$master) echo 'disabled'; ?>>       
                  </div>
                </div>
                <div class="col-lg-4 col-sm-12">
                  <label class="control-label">Disallowance:</label>
                  <div class="controls">
                    <input type="text" class="validate span3 form-control" name="disallowance" placeholder="disallowance" value="<?php echo $row5["disallowance"];?>" required <?php if ($isAdminEditingOwnProfile|| !$master) echo 'disabled'; ?>>       
                  </div>
                </div>
                 <?php foreach ($loanData as $loan) { ?>
                    <div class="col-lg-4 col-sm-12">
                      <label class="control-label"><?php echo $loan['loanorg'] .' '. $loan['loantype']; ?></label>
                      <div class="controls">
                        <input type="text" class="validate span3 form-control" name="loan_amount[]" placeholder="Monthly Payment" value="<?php echo $loan['monthly_payment']; ?>" <?php if ($isAdminEditingOwnProfile || !$master) echo 'disabled'; ?>>
                        <input type="hidden" name="loanhistory_id[]" value="<?php echo $loan['loanhistory_id']; ?>">
                      </div>
                    </div>
                <?php } ?>
                <div class="col-lg-4 col-sm-12">
                  <label class="control-label">Absences:</label>
                  <div class="controls">
                    <input type="text" class="validate span3 form-control" name="absences" placeholder="absences" value="<?php echo $row5["absences"];?>" required <?php if ($isAdminEditingOwnProfile|| !$master) echo 'disabled'; ?>>       
                  </div>
                </div>
                <div class="col-lg-4 col-sm-12">
                  <label class="control-label">Undertime:</label>
                  <div class="controls">
                    <input type="text" class="validate span3 form-control" name="undertime" placeholder="undertime" value="<?php echo $row5["undertimehours"];?>" required <?php if ($isAdminEditingOwnProfile|| !$master) echo 'disabled'; ?>>        
                  </div>
                </div>

              </div>
            </div>
            <div class="d-flex justify-content-center pt-5 pb-4 gap-1">
              <button type="submit" class="btn btn-success" name="submit_btn" <?php if ($isAdminEditingOwnProfile || !$master) echo 'disabled'; ?>>Save</button>
              <!--<button type="reset" class="btn btn-outline-danger w-50">Clear</button>-->
                <button type="button" class="btn btn-danger" id="delete_btn" name="delete_btn" <?php if ($isAdminEditingOwnProfile || !$master) echo 'disabled'; ?>>Delete</button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>

<script>
document.getElementById('delete_btn').addEventListener('click', function() {
    swal({
        title: "Are you sure?",
        text: "Once deleted, you will not be able to recover this record!",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    })
    .then((willDelete) => {
        if (willDelete) {
            // Create a hidden input to indicate confirmation
            var input = document.createElement("input");
            input.type = "hidden";
            input.name = "delete_confirmed";
            input.value = "true";
            document.getElementById("delete_form").appendChild(input);
            
            // Submit the form
            document.getElementById("delete_form").submit();
        } else {
            // User cancelled deletion
            console.log('Deletion cancelled');
        }
    });
});

</script>

</body>
</html>
