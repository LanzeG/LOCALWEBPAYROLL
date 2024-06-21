<?php
// Include necessary files and initialize variables
include("../DBCONFIG.PHP");
include("../LoginControl.php");
include("../BASICLOGININFO.PHP");

session_start();
  if (!isset($_SESSION['adminId'])) {
  // Redirect to the desired page
  header("Location: ../default.php"); // Change 'login.php' to the desired page
  exit; // Terminate script execution after redirection
}

date_default_timezone_set('Asia/Manila');
$current_datetime = date('Y-m-d H:i:s');

// Assuming 'notification_id' is the parameter in the URL
$notification_id = isset($_GET['notification_id']) ? $_GET['notification_id'] : null;

// Validate and sanitize the input
$notification_id = intval($notification_id);  // Ensure it's an integer

// Fetch data from the database based on the notification_id
$sql = "SELECT * FROM notifications WHERE notification_id = $notification_id";
$result = $conn->query($sql);

$uname = $_SESSION['uname'];
$empid = $_SESSION['empId'];
$adminId = $_SESSION['adminId'];
$adminname = "SELECT first_name, last_name FROM employees where emp_id = '$adminId'";
$adminnameexecqry = mysqli_query($conn, $adminname) or die ("FAILED TO CHECK EMP ID ".mysqli_error($conn));
$adminData = mysqli_fetch_assoc($adminnameexecqry);

$adminFullName = $adminData['first_name'] . " " . $adminData['last_name'];
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.css" />
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <title>Issue</title>
</head>

<body>

    <?php
    INCLUDE('navbarAdmin.php');
    ?>

    <div class="content d-flex align-items-center justify-content-center" style="height: 100vh; margin-top:10%;">

        <div class="container" style="width:100vh;">
            <div class="card shadow">
                <div class="card-header">
                    Announcement
                </div>
                <form class="row g-3 p-3" method="post" action="">
                    <div class="col-12">
                        <label for="announcementTitle" class="form-label">Announcement Title</label>
                        <?php $row = $result->fetch_assoc();
                        $filePath = $row['filepath'];
                       ?>
                        
                        <input type="text" class="form-control" id="announcementTitle" name="title"
                            value="<?php echo isset($row['title']) ? $row['title'] : ''; ?>" required readonly>
                        <!-- Use 'notifid' instead of 'notification_id' -->
                        <input type="hidden" class="form-control" name="notifid"
                            value="<?php echo isset($row['notification_id']) ? $row['notification_id'] : ''; ?>">
                    </div>
                    <!-- <div class="col-12">
                        <label for="message" class="form-label">From</label>
                        <textarea class="form-control" id="emp_name" name="emp_name" rows="3" required
                            readonly><?php echo isset($row['emp_name']) ? $row['emp_name'] : ''; ?></textarea>
                    </div> -->
                    <div class="col-12">
                        <label for="message" class="form-label">Message from: <?php echo isset($row['emp_name']) ? $row['emp_name'] : ''; ?></label>
                        <textarea class="form-control" id="message" name="message" rows="3" required
                            readonly><?php echo isset($row['message']) ? $row['message'] : ''; ?></textarea>
                            <!--<img src="../EMPLOYEENEW/uploads/shopping-cart.png">-->
                    </div>
                      <div class="col-12">
                        <div id="filePreview">
                            <?php if (!empty($filePath)): ?>
                                <?php if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $filePath)): ?>
                                  <img id="imagePreview" src="<?php echo '../EMPLOYEENEW/' . $filePath; ?>" alt="Image Preview" style="max-width: 60%; display: block; margin: 0 auto;">
                                <?php elseif (preg_match('/\.pdf$/i', $filePath)): ?>
                                    <iframe id="pdfPreview" src="<?php echo '../EMPLOYEENEW/' . $filePath; ?>" style="width: 100%; height: 260px;"></iframe>
                                <?php else: ?>
                                    <p id="fileName">File: <a href="<?php echo '../EMPLOYEENEW/' . $filePath; ?>" target="_blank"><?php echo basename($filePath); ?></a></p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                
                    <div class="col-12 text-center">
                        <button type="button" class="btn btn-success delete-button">Resolve</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>

    <script>
        $(document).ready(function () {

            $('.delete-button').click(function () {
                var positionName = $(this).closest('form').find('input[name="notifid"]').val();

                swal({
                    title: "Are you sure?",
                    text: "Once resolve, you will not be able to recover this issue!",
                    icon: "info",
                    buttons: true,
                    dangerMode: true,
                })
                    .then((willDelete) => {
                        if (willDelete) {
                            $(this).closest('form').submit();
                        }
                    });
            });
        });
    </script>
    <?php
    if (isset($_POST['notifid'])) {
        $notif = $_POST['notifid'];

        // Add this line to log information to the console
        echo "<script>console.log('Resolve Issue: $notif');</script>";

        $deleteQuery = "DELETE FROM notifications WHERE notification_id = '$notif'";
        $deleteResult = mysqli_query($conn, $deleteQuery);

        $notifquery ="INSERT INTO empnotifications (admin_id,adminname, emp_id, message, type, status,created_at) VALUES ('$adminId','$adminFullName', '{$row['emp_id']}','Issue Resolved - {$row['title']}','Resolved','unread','$current_datetime')";
        $notifqueryResult = mysqli_query($conn, $notifquery);
        



        if ($deleteResult) {
            $activityLog = "Issue $notif has been resolved";
            $adminActivityQuery = "INSERT INTO adminactivity_log (emp_id, adminname, activity, log_timestamp) VALUES ('$adminId', '$adminFullName', '$activityLog', '$current_datetime')";
            $adminActivityResult = mysqli_query($conn, $adminActivityQuery);
            ?>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    swal({
                        text: "Issue resolved successfully",
                        icon: "success",
                        button: "OK",
                    }).then(function () {
                        window.location.href = 'all_notifications.php';
                    });
                });
            </script>
            <?php
        } else {
            ?>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    swal({
                        text: "Something went wrong while deleting",
                        icon: "error",
                        button: "Try Again",
                    });
                });
            </script>
            <?php
        }

        exit();
    }
    ?>

</body>
<style>
     body{
  font-family: 'Poppins', sans-serif;
}
</style>
</html>
