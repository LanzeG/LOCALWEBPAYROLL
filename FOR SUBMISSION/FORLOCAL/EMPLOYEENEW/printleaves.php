<?php
include("../DBCONFIG.PHP");
include("../LoginControl.php");
include("../BASICLOGININFO.PHP");
require_once("fpdf181/fpdf.php");

// Function to fetch and display data as PDF
function printDataAsPDF($result) {
    $pdf = new FPDF('L', 'mm', 'LETTER');
    $pdf->AddPage();
    //  $pdf->Image('../img/images.png',10,6,15); // Adjust the image path and position as needed
    $pdf->SetFont('times','B',14);
    // $pdf->Cell(60);
    $pdf->Cell(30,10,'LEAVE LIST',0,0,'C');

    // Add watermark
    // $pdf->SetFont('times', 'B', 30);
    // $pdf->SetTextColor(220, 220, 220); // Set a light gray color
    // $pdf->Text(80, 50, 'COMPUTER-GENERATED'); // Set the text and position
    // $pdf->SetTextColor(0); // Reset text color

    $pdf->SetFont('times', 'B', 30);
    $pdf->SetTextColor(220, 220, 220); // Set a light gray color
    $pdf->Text(70, 85, 'COMPUTER-GENERATED'); // Set the text and position
    $pdf->SetTextColor(0); // Reset text color

    // $pdf->SetFont('times', 'B', 30);
    // $pdf->SetTextColor(220, 220, 220); // Set a light gray color
    // $pdf->Text(110, 130, 'LEAVES LIST'); // Set the text and position
    // $pdf->SetTextColor(0); // Reset text color

    $pdf->SetFont('times', 'B', 10);
    $pdf->Cell(60,3,'',0,0);
	$pdf->Cell(130,10,'',0,1);// end of line
    $pdf->Cell(30, 9, 'Employee ID', 1);
    $pdf->Cell(30, 9, 'Last Name', 1);
    $pdf->Cell(30, 9, 'First Name', 1);
    $pdf->Cell(28, 9, 'Middle Name', 1);
    $pdf->Cell(30, 9, 'Department', 1);
    $pdf->Cell(25, 9, 'Emp Type', 1);
    // $pdf->Cell(22, 10, 'Shift', 1);
    $pdf->Cell(30, 9, 'Leave Type', 1);
    $pdf->Cell(30, 9, 'Leave Start', 1);
    $pdf->Cell(25, 9, 'Remarks', 1,1);
        $pdf->SetFillColor(51, 255, 175); 
    $pdf->Cell(30, 1, '', 1, 0, '', true);
    $pdf->Cell(30, 1, '', 1, 0, '', true);
    $pdf->Cell(30, 1, '', 1, 0, '', true);
    $pdf->Cell(28, 1, '', 1, 0, '', true);
    $pdf->Cell(30, 1, '', 1, 0, '', true);
    $pdf->Cell(25, 1, '', 1, 0, '', true);
    // $pdf->Cell(22, 1, '', 1, 0, '', true);
    $pdf->Cell(30, 1, '', 1, 0, '', true);
    $pdf->Cell(30, 1, '', 1, 0, '', true);
    $pdf->Cell(25, 1, '', 1, 0, '', true);

    
    

    // Data
    $pdf->SetFont('times', '', 10);
    

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $pdf->Ln();
            $pdf->Cell(30, 7, $row['emp_id'], 1);
            $pdf->Cell(30, 7, $row['last_name'], 1);
            $pdf->Cell(30, 7, $row['first_name'], 1);
            $pdf->Cell(28, 7, $row['middle_name'], 1);
            $pdf->Cell(30, 7, $row['dept_NAME'], 1);
            $pdf->Cell(25, 7, $row['employment_TYPE'], 1);
            // $pdf->Cell(22, 10, $row['shift_SCHEDULE'], 1);
            $pdf->Cell(30, 7, $row['leave_type'], 1);
            $pdf->Cell(30, 7, $row['leave_datestart'], 1);
            $pdf->Cell(25, 7, $row['leave_status'], 1);

        }
    
        $pdf->Ln();
        // $pdf->Cell(18, 10, 'Printed by:', 0);
        // $pdf->Cell(62, 10, $adminFullName, 0, 1);
        $pdf->Cell(62, 30, 'Signature: ______________________', 0, 1, 'C');
        
    
    } else {
        $pdf->Cell(100, 10, 'No data found', 1, 1);
    }

    // Output the PDF
    ob_start();  // Start output buffering
    $pdf->Output();
    ob_end_flush();  // Flush output buffer
}
    
// Check if the print button is clicked
    $empid = $_SESSION['empId'];
    // Print data as PDF query
    $query = "SELECT * 
    FROM leaves_application 
    JOIN employees ON employees.emp_id = leaves_application.emp_id 
    WHERE employees.emp_id = '$empid' AND leaves_application.leave_status = 'Approved';";
    $result = mysqli_query($conn, $query);

    if ($result === false) {
        die("Failed to fetch data: " . mysqli_error($conn));
    }

    printDataAsPDF($result);
    

mysqli_close($conn);
?>
