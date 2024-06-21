<?php
include("../DBCONFIG.PHP");
include("../LoginControl.php");
include("../BASICLOGININFO.PHP");

session_start();

if (isset($_SESSION['masterfilenotif'])) {
    $mfnotif = $_SESSION['masterfilenotif'];
    ?>
    <script>
    alert("<?php echo $mfnotif; ?>");
    </script>
    <?php
}

$currentempid = $_SESSION['empID'];
$userIdpage = $_SESSION['empID'];
$pageViewed = basename($_SERVER['PHP_SELF']);
$pageInfo = pathinfo($pageViewed);

// Get the filename without extension
$pageViewed1 = $pageInfo['filename'];

// Log the page view
// logPageView($conn, $userIdpage, $pageViewed1);

// Total number of rows
$pagecountqry = "SELECT COUNT(emp_id) from pay_per_period WHERE emp_id = '$currentempid'";
$pagecountres = mysqli_query($conn, $pagecountqry) or die("Failed to count pages " . mysqli_error($conn));
$pagecounttotal = mysqli_fetch_row($pagecountres);
$rows = $pagecounttotal[0];

// Number of results per page
$page_rows = 20;
// Page number of last page
$lastpage = ceil($rows / $page_rows);
// This makes sure $lastpage can't be less than 1
if ($lastpage < 1) {
    $lastpage = 1;
}

$pagenum = 1;
// Get pagenum from URL
if (isset($_GET['pn'])) {
    $pagenum = preg_replace('#[^0-9]#', '', $_GET['pn']);
}
// Makes sure page number isn't below 1 or more than $lastpage
if ($pagenum < 1) {
    $pagenum = 1;
} else if ($pagenum > $lastpage) {
    $pagenum = $lastpage;
}
// This set range of rows to query for $pagenum
$limit = "LIMIT " . ($pagenum - 1) * $page_rows . ", " . $page_rows;

// What page and number of pages
$pageline1 = "Page <b>$pagenum</b> of <b>$lastpage</b>";
// Page controls
$paginationCtrls = '';
// If more than 1 page
if ($lastpage != 1) {
    // Check if on page 1. If yes, previous link not needed. If not, we generate links to the first page and to the previous page.
    if ($pagenum > 1) {
        $previous = $pagenum - 1;
        $paginationCtrls .= '<li><a href="' . $_SERVER['PHP_SELF'] . '?id=' . $idres . '&pn=' . $previous . '">Prev</a></li>';
        // Number links left
        for ($i = $pagenum - 4; $i < $pagenum; $i++) {
            if ($i > 0) {
                $paginationCtrls .= '<li><a href="' . $_SERVER['PHP_SELF'] . '?id=' . $idres . '&pn=' . $i . '">' . $i . '</a></li>';
            }
        }
    }

    // Target page
    $paginationCtrls .= '<li class="active"><a href="' . $_SERVER['PHP_SELF'] . '"' . '</a>' . $pagenum . '</li>';
    // Render clickable number links appear on right target page
    for ($i = $pagenum + 1; $i <= $lastpage; $i++) {
        $paginationCtrls .= '<li><a href="' . $_SERVER['PHP_SELF'] . '?id=' . $idres . '&pn=' . $i . '">' . $i . '</a></li>';
        if ($i >= $pagenum + 4) {
            break;
        }
    }

    if ($pagenum != $lastpage) {
        $next = $pagenum + 1;
        $paginationCtrls .= '<li><a href="' . $_SERVER['PHP_SELF'] . '?id=' . $idres . '&pn=' . $next . '">Next</a></li> ';
    }
}

// Deduction type filters
$philhealth_checked = isset($_POST['philhealth']) ? 'checked' : '';
$gsis_checked = isset($_POST['gsis']) ? 'checked' : '';
$pagibig_checked = isset($_POST['pagibig']) ? 'checked' : '';
$withholding_tax_checked = isset($_POST['withholding_tax']) ? 'checked' : '';

if (isset($_POST['submit'])) {
    // Construct the query based on selected deduction types
    $deductions = [];
    if (isset($_POST['philhealth'])) {
        $deductions[] = 'philhealth_deduct';
    }
    if (isset($_POST['gsis'])) {
        $deductions[] = 'sss_deduct AS gsis_deduct';
    }
    if (isset($_POST['pagibig'])) {
        $deductions[] = 'pagibig_deduct';
    }
    if (isset($_POST['withholding_tax'])) {
        $deductions[] = 'tax_deduct AS withholding_tax';
    }

    // Check if deductions array is empty and set default fields if necessary
    if (empty($deductions)) {
        $deductions = ['philhealth_deduct', 'sss_deduct AS gsis_deduct', 'pagibig_deduct', 'tax_deduct AS withholding_tax'];
    }

    $deduction_fields = implode(', ', $deductions);

    $searchquery = "SELECT 
                        last_name, 
                        first_name, 
                        middle_name, 
                        pperiod_range, 
                        $deduction_fields
                    FROM 
                        employees
                    INNER JOIN 
                        pay_per_period 
                    ON 
                        employees.emp_id = pay_per_period.emp_id 
                    WHERE 
                        pay_per_period.emp_id = '$currentempid'";

    if (isset($_POST['pperiod_btn'])) {
        $payperiod = $_POST['payperiod'];
        $searchquery .= " AND pay_per_period.pperiod_range = '$payperiod'";
    }

    $searchquery .= " ORDER BY pay_per_period.pperiod_range DESC $limit";

    $search_result = filterTable($searchquery);
} else {
    // Default query without filtering
    $searchquery = "SELECT 
                        last_name, 
                        first_name, 
                        middle_name, 
                        pperiod_range, 
                        philhealth_deduct, 
                        sss_deduct AS gsis_deduct, 
                        pagibig_deduct, 
                        tax_deduct AS withholding_tax
                    FROM 
                        employees
                    INNER JOIN 
                        pay_per_period 
                    ON 
                        employees.emp_id = pay_per_period.emp_id 
                    WHERE 
                        pay_per_period.emp_id = '$currentempid'";

    if (isset($_POST['pperiod_btn'])) {
        $payperiod = $_POST['payperiod'];
        $searchquery .= " AND pay_per_period.pperiod_range = '$payperiod'";
    }

    $searchquery .= " ORDER BY pay_per_period.pperiod_range DESC $limit";

    $search_result = filterTable($searchquery);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>Payroll Deductions</title>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
<!-- Bootstrap JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js"></script>
<script src="../jquery-ui-1.12.1/jquery-3.2.1.js"></script>
<script src="../jquery-ui-1.12.1/jquery-ui.js"></script>
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
        <h3 style="text-align: center;">PAYROLL DEDUCTIONS</h3>
    </div>
    <div class="control-group">
        <form action="<?php $_SERVER['PHP_SELF']; ?>" method="post">
            <?php
            $payperiodsquery = "SELECT * FROM payperiods";
            $payperiodsexecquery = mysqli_query($conn, $payperiodsquery) or die("FAILED TO EXECUTE PAYPERIOD QUERY " . mysqli_error($conn));
            ?>
            
            <div class="d-flex gap-2 flex-wrap">
                <div>
                    <label class="control-label" style="margin-bottom: 10px; margin-top: 10px;">Select Payroll Period: </label>
            <div class="controls" style="display: flex; align-items: center;">
                <select name="payperiod" class="form-select" style="width: 250px; margin-right: 10px;">
                    <option></option>
                    <?php while ($payperiodchoice = mysqli_fetch_array($payperiodsexecquery)) :
                        $selected = '';
                        if (isset($_POST['payperiod']) && $_POST['payperiod'] == $payperiodchoice['pperiod_range']) {
                            $selected = 'selected';
                        }
                    ?>
                        <option <?php echo $selected; ?>><?php echo $payperiodchoice['pperiod_range']; ?></option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" class="btn btn-success printbtn" name="pperiod_btn">Go</button>
            </div>
                </div>
                
                <div class="d-flex row p-4 pt-0 align-items-center" >           
                <div class="row col-lg-8 col-sm-12 pt-4">  
            <div class="form-check col-lg-6 col-sm-12">
                <input class="form-check-input" type="checkbox" name="philhealth" value="1" <?php echo $philhealth_checked; ?>>
                <label class="form-check-label">Philhealth</label>
            </div>
            <div class="form-check col-lg-6 col-sm-12">
                <input class="form-check-input" type="checkbox" name="gsis" value="1" <?php echo $gsis_checked; ?>>
                <label class="form-check-label">GSIS</label>
            </div>
            <div class="form-check col-lg-6 col-sm-12">
                <input class="form-check-input" type="checkbox" name="pagibig" value="1" <?php echo $pagibig_checked; ?>>
                <label class="form-check-label">PAG-IBIG/HDMF</label>
            </div>
            <div class="form-check col-lg-6 col-sm-12">
                <input class="form-check-input" type="checkbox" name="withholding_tax" value="1" <?php echo $withholding_tax_checked; ?>>
                <label class="form-check-label">Withholding Tax</label>
            </div>
            </div>
            
            <div class="d-flex align-items-center col-lg-4 col-sm-12">
                                            <button type="submit" class="btn btn-primary" name="submit">Apply Filters</button>

            </div>
            
</div>
            </div>
            
        
        </form>
    </div>
    <div class="d-flex align-items-center table-responsive">
        <table class="table table-striped">
            <thead class="table" style="background-color: #2ff29e; color: #4929aa;">
                <tr>
                    <th>Pay Period</th>
                    <?php
                    // Display column headers for selected filters
                    if (isset($_POST['philhealth'])) {
                        echo '<th>Philhealth</th>';
                    }
                    if (isset($_POST['gsis'])) {
                        echo '<th>GSIS</th>';
                    }
                    if (isset($_POST['pagibig'])) {
                        echo '<th>PAG-IBIG/HDMF</th>';
                    }
                    if (isset($_POST['withholding_tax'])) {
                        echo '<th>Withholding Tax</th>';
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
function filterTable($searchquery) {
    global $conn; // Include the global $conn variable inside the function
    $filter_Result = mysqli_query($conn, $searchquery) or die("Failed to query masterfile " . mysqli_error($conn));
    return $filter_Result;
}                while ($row1 = mysqli_fetch_array($search_result)) :
                ?>
                <tr class="gradeX">
                    <td><?php echo $row1['pperiod_range']; ?></td>
                    <?php
                    // Check which filters are selected and display corresponding columns
                    if (isset($_POST['philhealth'])) {
                        echo '<td>' . $row1['philhealth_deduct'] . '</td>';
                    }
                    if (isset($_POST['gsis'])) {
                        echo '<td>' . $row1['gsis_deduct'] . '</td>';
                    }
                    if (isset($_POST['pagibig'])) {
                        echo '<td>' . $row1['pagibig_deduct'] . '</td>';
                    }
                    if (isset($_POST['withholding_tax'])) {
                        echo '<td>' . $row1['withholding_tax'] . '</td>';
                    }
                    ?>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <div class="pagination alternate" style="float:right;">
            <?php echo $paginationCtrls; ?>
        </div>
    </div>
</div>

<?php unset($_SESSION['masterfilenotif']); ?>
<style>
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
    width: 100%;
    table-layout: auto;
}
.table-responsive {
    overflow-x: auto;
    max-width: 100%;
}
body {
    font-family: 'Poppins', sans-serif;
}
</style>
</body>
</html>
