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

$adminname = "SELECT first_name, last_name FROM employees where emp_id = '$adminId'";
$adminnameexecqry = mysqli_query($conn, $adminname) or die("FAILED TO CHECK EMP ID " . mysqli_error($conn));
$adminData = mysqli_fetch_assoc($adminnameexecqry);

$adminFullName = $adminData['first_name'] . " " . $adminData['last_name'];

$printfrom = $_SESSION['payperiodfrom'];
$printto = $_SESSION['payperiodto'];
$payperiod = $_SESSION['payperiodrange'];
list($startDate, $endDate) = explode(' to ', $payperiod);

// Convert start date to month name and day number
$startMonth = date('F', strtotime($startDate));
$startDay = date('j', strtotime($startDate));

// Convert end date to day number
$endDay = date('j', strtotime($endDate));

// Get the year from the end date
$year = date('Y', strtotime($endDate));

// Construct the formatted date range string
$formattedDateRange = "$startMonth $startDay-$endDay, $year";

$d = strtotime("now");
$currtime = date("Y-m-d H:i:s", $d);

if (isset($_GET['print_all'])) {
    $payslipdetailsqry = "SELECT * from employees, payrollinfo, pay_per_period WHERE pay_per_period.pperiod_range = '$payperiod' and pay_per_period.emp_id = employees.emp_id and payrollinfo.emp_id = employees.emp_id ORDER BY pay_per_period.emp_id ASC";
} elseif (isset($_GET['print_displayed'])) {
    $payslipdetailsqry = $_SESSION['printpayrollquery'];
    echo $payslipdetailsqry;
} else {
    $payslipdetailsqry = "SELECT * from employees, payrollinfo, pay_per_period WHERE pay_per_period.pperiod_range = '$payperiod' and pay_per_period.emp_id = employees.emp_id and payrollinfo.emp_id = employees.emp_id AND employees.emp_id ='$printid'";
}

$payslipdetailsexecqry = mysqli_query($conn, $payslipdetailsqry) or die("FAILED TO GET PAYROLL DETAILS " . mysqli_error($conn));

require_once '../vendor/autoload.php';

use Dompdf\Dompdf;


if (mysqli_num_rows($payslipdetailsexecqry) > 0) {
    $dompdf = new Dompdf();
    $html = '';
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
        $name = "$lname, $fname $mname";
        $empID = "$prefix$idno";

        $payinfo1qry = "SELECT * FROM pay_per_period WHERE emp_id = '$idno' AND pay_per_period.pperiod_range = '$payperiod'";
        $payinfo1execqry = mysqli_query($conn, $payinfo1qry) or die("FAILED TO GET PAYROLL INFO");
        $piarray1 = mysqli_fetch_array($payinfo1execqry);
        if ($piarray1) {
            $wtax = $piarray1['tax_deduct'];
            $compensation = $piarray1['compensation'];
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
            $rph = $piarray1['rate_per_hour'];
            $hw = $piarray1['hours_worked'];
        } else {
            $monthlyrate = 0;
            $semimonthlyrate = 0;
            $smrate = 0.00;
        }

        //loantypes
        $loanTypesQuery = "SELECT * FROM loantype";
        $loanTypesResult = mysqli_query($conn, $loanTypesQuery);

        //loans
        $loanHistoryQuery = "SELECT * FROM loan_history WHERE emp_id = '$idno' AND payperiod = '$payperiod' AND remarks != 'Disallowanced'";
        $loanHistoryResult = mysqli_query($conn, $loanHistoryQuery);

        $html .= '
        <!DOCTYPE html>
        <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            table {
                border-collapse: collapse;
                width: 100%;
                border: none;
                font-size: 12px;
            }
            td {
                border: none;
                text-align: left;
            }
            .watermark {
                position: absolute;
                top: 30%;
                left: 50%;
                transform: translate(-50%, -50%);
                font-size: 72px;
                opacity: 0.4; /* Adjust the opacity as needed */
                color: #CCCCCC; /* Adjust the color as needed */
                white-space:nowrap;
            }
            .page {
                page-break-before: always;
             }
        </style>
        </head>
        <body class="page">
        <h4 style="text-align:center; margin-bottom:0px;" class="text-primary">PAYSLIP</h4>
        <p style="text-align:center;  margin-top:0px; margin-bottom:10px;">For the period: ' . $formattedDateRange . '</p>
        <table>
            <tr>
                <td>Employee ID: ' . $empID . '</td>
                <td></td>
                <td>Date Printed: ' . date("Y-m-d H:i:s") . ' </td>
            </tr>
            <tr>
                <td>Name: ' . $name . '</td>
                <td></td>
                <td>Pay Period: ' . $payperiod . '</td>
            </tr>
            <tr>
                <td>Department: ' . $dept . ' </td>
                <td></td>
                <td>SG: ' . $sg . '</td>
            </tr>
            <tr>
                <td>Position: ' . $position . '</td>
                <td></td>
                <td>Step: ' . $step . '</td>
            </tr>
        </table>
         <br/>';

        if ($hw != 0.00) {
            $html .= '<span style="font-size:12px;">Hours Worked: ---------------------------------------------------------------------------------------------------------------------- ' . $hw . ' hours</span><br/>';
            $html .= '<span style="font-size:12px;">Hourly Rate: ------------------------------------------------------------------------------------------------------------------------- ' . $rph . '</span><br/>';
            $html .= '<span style="font-size:12px;">Compensation: ----------------------------------------------------------------------------------------------------------------------- ' . $compensation . '</span><br/>';
            $html .= '<span style="font-size:12px;">Gross Amount Due: ---------------------------------------------------------------------------------------------------------------- ' . number_format((float)$hw * $rph + $compensation, 2) . '</span>';
        } else {
            $html .= '<span style="font-size:12px;">Basic Salary: ---------------------------------------------------------------------------------------------------------------------- ' . $monthlyrate . '</span><br/>';
            $html .= '<span style="font-size:12px;">PERA/AdCom: ------------------------------------------------------------------------------------------------------------------- ' . $refsalary . '</span><br/>';
            $html .= '<span style="font-size:12px;">Compensation: -------------------------------------------------------------------------------------------------------------------- ' . $compensation . '</span><br/>';
            $html .= '<span style="font-size:12px;">Gross Amount Due: -------------------------------------------------------------------------------------------------------------- ' . number_format((float)$monthlyrate + $refsalary+$compensation, 2) . '</span>';
        }
        
        $html .= '

        <table style="margin-top:15px;  ">
            <tr>
                <td>Ref Salary: ' . number_format($absences + $undertime, 2) . '</td>
                <td>Disallowance: ' . $disallowance . '</td>
                <td>Withholding Tax: ' . $wtax . ' </td>
            </tr>
            <tr>
                <td>Philhealth: ' . $phEE . '</td>
                <td>GSIS-Integrated Insurance: ' . $gsis . '</td>
                <td>HDMF Contribution: ' . $pagibig . ' </td>
            </tr>';

        $html .= '<tr>';
        $tdCount = 0; // Initialize the count of table cells

        // Loop through the loan types to generate table cells
        while ($loanTypeRow = mysqli_fetch_assoc($loanTypesResult)) {
            $loanTypeName = $loanTypeRow['loantype'];
            $loanorg = $loanTypeRow['loanorg'];

            $loanAmount = ''; // Default value
            mysqli_data_seek($loanHistoryResult, 0); // Reset pointer
            while ($loanHistoryRow = mysqli_fetch_assoc($loanHistoryResult)) {
                if ($loanHistoryRow['loantype'] == $loanTypeName) {
                    $loanAmount = $loanHistoryRow['monthly_payment'];
                    break;
                }
            }
            if ($loanAmount === '') {
                $loanAmount = '0.00';
            }
            // Add table cell for each loan type
            $html .= '<td>' . $loanorg . ' ' . $loanTypeName . ': ' . $loanAmount . '</td>';
            $tdCount++; // Increment the count of table cells

            // Check if the count reaches 3
            if ($tdCount == 3) {
                $html .= '</tr>'; // Close the current row
                $html .= '<tr>'; // Start a new row
                $tdCount = 0; // Reset the count of table cells
            }
        }

        // Add empty cells to complete the row if needed
        while ($tdCount < 3) {
            $html .= '<td></td>'; // Add an empty cell
            $tdCount++; // Increment the count of table cells
        }

        $html .= '</tr>
        </table>
        <hr style="border: none; border-top: 4px solid black;">
        <table>
        <tr>
        <td><u>1st Half: ' . $first . '</u></td>
        <td><u>2nd Half: ' . $second . '</u></td>
        </tr>
        <tr>
        <td></td>
        <td></td>
        <td style="text-align:right;">Total Deductions: ' . $totaldeduct . '</td>
        </tr>
        <tr>
        <td></td>
        <td></td>
        <td style="text-align:right;">Net Amount: <u>' . $netpay . '</u></td>
        </tr>
        <tr>
        <td>Received by: ___________</td>
        <td></td>
        <td></td>
        </tr>
        <br/>
        <tr>
        <td>Printed by: ' . $adminFullName . '</td>
        <td></td>
        <td></td>
        </tr>
        </table>
        <div class="watermark">Computer Generated</div>
        </body>';

    }
} else {
    echo "<script>alert('No data found for the specified payroll period.'); window.close();</script>";
}

$dompdf->setPaper('LETTER', '');
$dompdf->loadHtml($html);
$dompdf->render();

header('Content-Type: application/pdf');
$dompdf->stream("", array("Attachment" => false));
?>
