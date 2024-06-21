<?php
include("../DBCONFIG.PHP");
include("../LoginControl.php");
include("../BASICLOGININFO.PHP");

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Retrieve selected school year and semester
  $selectedSy = $_POST['current_sy'];
  $selectedSem = $_POST['current_sem'];

  // Update database with selected values
  $conn = mysqli_connect($host, $user, $pass, $dbname);
  if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
  }

  // Reset is_current for school years if no school year is selected
  if ($selectedSy == '') {
    $sql_reset_sy = "UPDATE school_years SET is_current = 0";
    mysqli_query($conn, $sql_reset_sy);
  } else {
    // Update the is_current column for the selected school year
    $sql_reset_sy = "UPDATE school_years SET is_current = 0";
    $sql_update_sy = "UPDATE school_years SET is_current = 1 WHERE CONCAT(start_year, '-', end_year) = ?";
    $stmt_update_sy = mysqli_prepare($conn, $sql_update_sy);
    mysqli_stmt_bind_param($stmt_update_sy, "s", $selectedSy);
    mysqli_query($conn, $sql_reset_sy);
    mysqli_stmt_execute($stmt_update_sy);
  }

  // Reset is_current for semesters if no semester is selected
  if ($selectedSem == '') {
    $sql_reset_sem = "UPDATE semesters SET is_current = 0";
    mysqli_query($conn, $sql_reset_sem);
  } else {
    // Update the is_current column for the selected semester
    $sql_reset_sem = "UPDATE semesters SET is_current = 0";
    $sql_update_sem = "UPDATE semesters SET is_current = 1 WHERE id = ?";
    $stmt_update_sem = mysqli_prepare($conn, $sql_update_sem);
    mysqli_stmt_bind_param($stmt_update_sem, "s", $selectedSem);
    mysqli_query($conn, $sql_reset_sem);
    mysqli_stmt_execute($stmt_update_sem);
  }

  // Determine the message to display based on the updates
  if ($selectedSy == '' && $selectedSem == '') {
    $message = "No school year and sem selected.";
  } elseif ($selectedSy == '') {
    $message = "Current semester has been updated.";
  } elseif ($selectedSem == '') {
    $message = "Current school year has been updated.";
  } else {
    $message = "Current school year and semester updated successfully.";
  }

  mysqli_close($conn);

  // Redirect back to the same page with a status message
  header("Location: {$_SERVER['PHP_SELF']}?status=success&message=" . urlencode($message));
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Current SY and Sem</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js"></script>
  <style>
    .day-label {
      min-width: 100px;
    }
    body {
      font-family: Poppins, sans-serif;
    }
    .dropdown {
      position: relative;
      display: inline-block;
    }
    .dropdown-content {
      display: none;
      position: absolute;
      left: 50%;
      transform: translateX(-33%);
      background-color: white;
      min-width: 300px;
      box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
      z-index: 1;
      padding: 20px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }
    .drop  {
      display: none;
      position: absolute;
      left: 50%;
      transform: translateX(0%) !important;
      background-color: white;
      min-width: 300px;
      box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
      z-index: 1;
      padding: 20px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }
    .show {
      display: block;
    }
  </style>
</head>

<body>
  <?php include('navbarAdmin.php'); ?>
  <div class="container mx-auto py-5 flex justify-center items-center min-h-screen">
    <div class="card shadow p-2">
      <h2 class="text-center mb-4">
        Current SY and Sem
      </h2>
      <?php
      // Display status message if any
      if (isset($_GET['status']) && isset($_GET['message'])) {
        $status = $_GET['status'];
        $message = $_GET['message'];
        echo "<div class='alert alert-$status'>$message</div>";
      }
      ?>
      <form method="POST" id="current-sy-sem-form">
        <div class="flex justify-center space-x-4">
          <select name="current_sy" id="current-sy" class="border border-gray-300 p-2 rounded-md">
            <option value="">Select School Year</option>
            <?php
            // Fetch school years from the database
            $conn = mysqli_connect($host, $user, $pass, $dbname);
            if (!$conn) {
              die("<div class='alert alert-danger'>Connection failed: " . mysqli_connect_error() . "</div>");
            }

            $sql = "SELECT CONCAT(start_year, '-', end_year) AS school_year, is_current FROM school_years";
            $result = mysqli_query($conn, $sql);

            if (mysqli_num_rows($result) > 0) {
              // Output data of each row
              while ($row = mysqli_fetch_assoc($result)) {
                $selected = ($row['is_current'] == 1) ? "selected" : ""; // Set selected attribute if the school year is current
                echo "<option value='" . $row['school_year'] . "' $selected>" . $row['school_year'] . "</option>";
              }
            } else {
              echo "<option value=''>No school years found</option>";
            }

            mysqli_close($conn);
            ?>
          </select>

          <select name="current_sem" id="current-sem" class="border border-gray-300 p-2 rounded-md">
            <option value="">Select Semester</option>
            <?php
            // Fetch semesters from the database
            $conn = mysqli_connect($host, $user, $pass, $dbname);
            if (!$conn) {
              die("<div class='alert alert-danger'>Connection failed: " . mysqli_connect_error() . "</div>");
            }

            $sql = "SELECT id, semester, is_current FROM semesters";
            $result = mysqli_query($conn, $sql);

            if (mysqli_num_rows($result) > 0) {
              while ($row = mysqli_fetch_assoc($result)) {
                $selected = ($row['is_current'] == 1) ? "selected" : ""; // Set selected attribute if the semester is current
                echo "<option value='" . $row['id'] . "' $selected>" . ($row['id'] == 1 ? "1st Semester" : ($row['id'] == 2 ? "2nd Semester" : "Summer")) . "</option>";
              }
            } else {
              echo "<option value=''>No semesters found</option>";
            }

            mysqli_close($conn);
            ?>
          </select>

          <button type="submit" class="btn btn-primary">Update</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // JavaScript code here
  </script>
</body>

</html>
