<?php
set_time_limit(60);
include("../../DBCONFIG.PHP");
include("../../LoginControl.php");
include("BASICLOGININFO.PHP");

session_start();
$adminId = $_SESSION['adminId'];
$payperiod = $_SESSION['pregisterpayperiod'];
$printfrom = $_SESSION['payperiodfrom'];
$printto=$_SESSION['payperiodto'];

list($payperiodStart, $payperiodEnd) = explode(' to ', $payperiod);

// Create DateTime objects from the start and end dates
$startDate = new DateTime($payperiodStart);
$endDate = new DateTime($payperiodEnd);

// Format the dates
$formattedStartDate = $startDate->format('F d, Y');
$formattedEndDate = $endDate->format('F d, Y');

// Concatenate the formatted dates
$formattedDateRange = $formattedStartDate . ' to ' . $formattedEndDate;

$formattedDateRange = strtoupper($formattedDateRange);


date_default_timezone_set('Asia/Manila');
$currentDateTime = date('Y-m-d H:i:s');


// $payperiod = 2023-03-01 to 2023-03-31;
// $printfrom = '2024-05-01';
// $printto='2024-05-31';

$sql = "SELECT e.emp_id, 
               p.ugoverload, 
               p.gdoverload, 
               e.position, 
               e.last_name, 
               e.first_name, 
               e.middle_name,
               e.employment_TYPE,
               t.timekeep_day,
               s.level,
               SUM(o.overload_hours) AS total_overload_hours
        FROM overload o
        JOIN time_keeping t ON o.timekeep_id = t.timekeep_id
        JOIN schedule s ON o.schedule_id = s.schedule_id
        JOIN payrollinfo p ON p.emp_id = s.emp_id
        JOIN employees e ON p.emp_id = e.emp_id
        WHERE t.timekeep_day BETWEEN '$printfrom' AND '$printto'
        GROUP BY e.emp_id, s.level";
$result = mysqli_query($conn, $sql);


$adminname = "SELECT first_name, last_name FROM employees where emp_id = '$adminId'";
$adminnameexecqry = mysqli_query($conn, $adminname) or die ("FAILED TO CHECK EMP ID ".mysqli_error($conn));
$adminData = mysqli_fetch_assoc($adminnameexecqry);

$adminFullName = $adminData['first_name'] . " " . $adminData['last_name'];

require_once '../../vendor/autoload.php';

use Dompdf\Dompdf;
$empcount=0;
$GTGross = 0;
$GTTax = 0;
$GTSub = 0;

if (mysqli_num_rows($result) > 0) {
    $html = '
        <html>
        <head>
            <style>
                .header {
                    text-align: center;
                }
                .left {
                    text-align: left;
                }
                .watermark {
                    position: absolute;
                    top: 30%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    font-size: 100px;
                    opacity: 0.4; /* Adjust the opacity as needed */
                    color: #CCCCCC; /* Adjust the color as needed */
                    white-space:nowrap;
                }
            </style>
        </head>
        <body>
        <div class="watermark">Computer Generated</div>
            <div class="header">
                <p><b>GENERAL PAYROLL</b></p>
                <p><b>REGULAR FACULTY HONORARIUM (OVERLOAD) - ' . $formattedDateRange . '</b></p>
            </div>
            <div class="left">
                <p>We acknowledge receipt of cash shown opposite names as full<br> compensation for services rendered for the period covered</p>
            </div>
            <table border="1" style="border: 1px solid black;">>
                <tr>
                    <th rowspan="2">NO</th>
                    <th rowspan="2">NAME</th>
                    <th rowspan="2">POSITION</th>
                    <th rowspan="2">UG OR GP</th>
                    <th rowspan="2">TOTAL HRS</th>
                    <th rowspan="2">RATE per HOUR</th>
                    <th rowspan="2">GROSS AMOUNT</th>
                    <th rowspan="2">Tax Rate</th>
                    <th rowspan="2">W/TAX AMOUNT</th>
                    <th colspan="2">NET AMOUNT RECEIVED</th>
                    <th rowspan="2">NO.</th>
                    <th rowspan="2">SIGNATURE OF PAYEE</th>
                    <th rowspan="2">Initial of Witness to Paymment</th>
                    <th rowspan="2">REMARKS</th>
                </tr>
                <tr>
                    <th rowspan="1">Sub-Total</th>
                    <th rowspan="1">GRAND TOTAL</th>';

                 while ($row = mysqli_fetch_assoc($result)) {
                     $overload = ($row['level'] == 'gd') ? $row['gdoverload'] : $row['ugoverload'];
                     $wtaxRate = $row['employment_TYPE'] == 'Permanent' ? 0.20 : 0.05;
                     $wtaxDisplay = $row['employment_TYPE'] =='Permanent' ? '20%' : '5%';
                     $gross = $overload * $row['total_overload_hours'];
                     $wtaxAmount = $wtaxRate * ($overload * $row['total_overload_hours']);
                     $subgrand = $gross - $wtaxAmount;
                     $empcount++;
                     $html .= '
                        <tr>
                        <td>'.$empcount.'</td>
                        <td>'. $row['last_name'] . ', ' . $row['first_name'] . ' ' . $row['middle_name'] . '</td>
                        <td>' . $row['position'] . '</td>
                        <td>'  . ($row['level'] === 'gd' ? 'GP' : strtoupper($row['level'])) . '</td>
                        <td> '. $row['total_overload_hours'] .'</td>
                        <td>'.$overload.'</td>
                        <td>'.number_format($gross, 2).'</td>
                        <td>'.$wtaxDisplay.'</td>
                        <td>'.number_format($wtaxAmount, 2).'</td>
                        <td>'.number_format($subgrand, 2).'</td>
                        <td>'.number_format($subgrand, 2).'</td>
                        <td>'.$empcount.'</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        ';
                        
                        $GTGross = $GTGross +  $gross;
                        $GTTax = $GTTax +  $wtaxAmount;
                        $GTSub = $GTSub + $subgrand;
                 }
                
    
        $html .= '<tr>
                <td colspan="6" style="text-align:center;">SUB TOTAL</td>
                <td>'.number_format($GTGross,2).'</td>
                <td>-</td>
                <td>'.number_format($GTTax,2).'</td>
                <td>'.number_format($GTSub,2).'</td>
                <td>'.number_format($GTSub,2).'</td>
                <td></td>
                <td colspan="3"></td>
                <tr><td rowspan="4">A</td>
                <td rowspan="4" colspan="7"><p>CERTIFIED: Service duly rendered as stated:</p><p style="text-align:center;">____________________________</p></td>
                <td rowspan="4">B</td>
                <td rowspan="4" colspan="7"><p>Approved for Payment:</p><p style="text-align:center;">____________________________</p></td></tr>
                <tr></tr>
                <tr></tr>
                <tr></tr>
                <tr><td rowspan="4">C</td>
                <td rowspan="4" colspan="7"><p>CERTIFIED:  Supporting socuments complete and proper;<br>			
                and cash available in the amount of P</p><p style="text-align:center;">____________________________</p></td>
                <td rowspan="4">D</td>
                <td rowspan="4" colspan="7"><p>CERTIFIED:  Each employee whom name appears shown has been <br>				
                paid the amount indicated opposite is/her name.</p><p style="text-align:center;">____________________________</p></td></tr>
            </table>

            <p>Printed By: '.$adminFullName .'</p>
            <p>Date Printed: '.$currentDateTime.'</p>
            </body>
            </html>';
               

    // Create a Dompdf instance
    $dompdf = new Dompdf();
    $dompdf->setPaper('A3', 'landscape');
    $dompdf->loadHtml($html);
    $dompdf->render();

    // Set headers to indicate that the PDF should be displayed inline
    header('Content-Type: application/pdf');
    $dompdf->stream("", array("Attachment" => false));
}else{
    echo "<script>alert('No data found for the specified payroll period.'); window.close();</script>";
}
    // } else {
    //     // Handle the case when $_SESSION['pregisterpayperiod'] is not set
    //     echo "Pay period is not set.";
    // }
?>