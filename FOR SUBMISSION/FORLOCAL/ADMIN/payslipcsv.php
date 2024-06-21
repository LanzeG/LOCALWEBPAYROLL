<?php

set_time_limit(60);
include("../DBCONFIG.PHP");
include("../LoginControl.php");
include("../BASICLOGININFO.PHP");

session_start();
date_default_timezone_set('Asia/Hong_Kong');

if (isset($_GET['id'])) {
    $printid = $_GET['id'];
}

$adminId = $_SESSION['adminId'];

$adminname = "SELECT first_name, last_name FROM employees WHERE emp_id = '$adminId'";
$adminnameexecqry = mysqli_query($conn, $adminname) or die("FAILED TO CHECK EMP ID " . mysqli_error($conn));
$adminData = mysqli_fetch_assoc($adminnameexecqry);

$adminFullName = $adminData['first_name'] . " " . $adminData['last_name'];

$printfrom = $_SESSION['payperiodfrom'];
$printto = $_SESSION['payperiodto'];
$payperiod = $_SESSION['payperiodrange'];
list($startDate, $endDate) = explode(' to ', $payperiod);

$startMonth = date('F', strtotime($startDate));
$startDay = date('j', strtotime($startDate));
$endDay = date('j', strtotime($endDate));
$year = date('Y', strtotime($endDate));
$formattedDateRange = "$startMonth $startDay-$endDay $year";

$d = strtotime("now");
$currtime = date("Y-m-d H:i:s", $d);

if (isset($_GET['print_all'])) {
    $payslipdetailsqry = "SELECT * FROM employees, payrollinfo, pay_per_period WHERE pay_per_period.pperiod_range = '$payperiod' AND pay_per_period.emp_id = employees.emp_id AND payrollinfo.emp_id = employees.emp_id ORDER BY pay_per_period.emp_id ASC";
} elseif (isset($_GET['print_displayed'])) {
    $payslipdetailsqry = $_SESSION['printpayrollquery'];
} else {
    $payslipdetailsqry = "SELECT * FROM employees, payrollinfo, pay_per_period WHERE pay_per_period.pperiod_range = '$payperiod' AND pay_per_period.emp_id = employees.emp_id AND payrollinfo.emp_id = employees.emp_id AND employees.emp_id ='$printid'";
}

$payslipdetailsexecqry = mysqli_query($conn, $payslipdetailsqry) or die("FAILED TO GET PAYROLL DETAILS " . mysqli_error($conn));

$csvContent = "Emp ID,Name,Dept,Position,Step,Pay Period,Basic Salary,PERA/AdCom,Compensation,Gross Amount Due,Ref Salary,Disallowance,Withholding Tax,Philhealth,GSIS-Integrated Insurance,HDMF Contribution,Loans,1st Half,2nd Half,Total Deductions,Net Amount,Printed by\n";

if (mysqli_num_rows($payslipdetailsexecqry) > 0) {
    while ($psdarray = mysqli_fetch_array($payslipdetailsexecqry)) {
        $prefix = $psdarray['prefix_ID'];
        $idno = $psdarray['emp_id'];
        $lname = $psdarray['last_name'];
        $fname = $psdarray['first_name'];
        $mname = $psdarray['middle_name'];
        $dept = $psdarray['dept_NAME'];
        $rph = $psdarray['rate_per_hour'];
        $sg = $psdarray['salarygrade'];
        $step = $psdarray['step'];
        $dph = ($rph * 8);
        $position = $psdarray['position'];
        $name = "$lname $fname $mname";
        $empID = "$prefix$idno";
        $comp = $psdarray['compensation'];

        $payinfo1qry = "SELECT * FROM pay_per_period WHERE emp_id = '$idno' AND pay_per_period.pperiod_range = '$payperiod'";
        $payinfo1execqry = mysqli_query($conn, $payinfo1qry) or die("FAILED TO GET PAYROLL INFO");
        $piarray1 = mysqli_fetch_array($payinfo1execqry);
        if ($piarray1) {
            $wtax = $piarray1['tax_deduct'];
            $gsis = $piarray1['sss_deduct'];
            $absences = $piarray1['absences'];
            $pagibig = $piarray1['pagibig_deduct'];
            $undertime = $piarray1['undertimehours'];
            $phEE = $piarray1['philhealth_deduct'];
            $totaldeduct = $piarray1['total_deduct'];
            $netpay = $piarray1['net_pay'];
            $first = $piarray1['firsthalf'];
            $second = $piarray1['secondhalf'];
            $refsalary = $piarray1['refsalary'];
            $disallowance = $piarray1['disallowance'];
            $monthlyrate = $piarray1['reg_pay'];
        } else {
            $monthlyrate = 0;
            $semimonthlyrate = 0;
            $smrate = 0.00;
        }

        $loanTypesQuery = "SELECT * FROM loantype";
        $loanTypesResult = mysqli_query($conn, $loanTypesQuery);

        $loanHistoryQuery = "SELECT * FROM loan_history WHERE emp_id = '$idno' AND payperiod = '$payperiod' AND remarks != 'Disallowanced'";
        $loanHistoryResult = mysqli_query($conn, $loanHistoryQuery);

        $loanCSVContent = '';
        while ($loanTypeRow = mysqli_fetch_assoc($loanTypesResult)) {
            $loanTypeName = $loanTypeRow['loantype'];
            $loanorg = $loanTypeRow['loanorg'];

            $loanAmount = '0.00';
            mysqli_data_seek($loanHistoryResult, 0);
            while ($loanHistoryRow = mysqli_fetch_assoc($loanHistoryResult)) {
                if ($loanHistoryRow['loantype'] == $loanTypeName) {
                    $loanAmount = $loanHistoryRow['monthly_payment'];
                    break;
                }
            }
            $loanCSVContent .= "$loanorg $loanTypeName: $loanAmount ";
        }

        $csvContent .= "$empID,$name,$dept,$position,$step,$formattedDateRange,$monthlyrate,$refsalary,$comp," . ($monthlyrate + $refsalary + $comp) . "," . ($absences + $undertime) . ",$disallowance,$wtax,$phEE,$gsis,$pagibig,\"$loanCSVContent\",$first,$second,$totaldeduct,$netpay,$adminFullName\n";
    }
} else {
    echo "No data found for the specified payroll period.";
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="payslip.csv"');

echo $csvContent;
?>
