<?php
include("../DBCONFIG.PHP");
include("../LoginControl.php");
include("../BASICLOGININFO.PHP");

date_default_timezone_set('Asia/Manila');
session_start();


if (!isset($_SESSION['adminId'])) {
  // Redirect to the desired page
  header("Location: ../default.php"); // Change 'login.php' to the desired page
  exit; // Terminate script execution after redirection
}

$response = array('status' => 'error', 'message' => 'File upload failed');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if file already exists
    if (file_exists($target_file)) {
        $response['message'] = "File already exists.";
        $uploadOk = 0;
    }

    // Check file size (limit to 5MB)
    if ($_FILES["fileToUpload"]["size"] > 5000000) {
        $response['message'] = "File is too large.";
        $uploadOk = 0;
    }

    if ($uploadOk == 0) {
        $response['message'] = "Error: File was not uploaded.";
    } else {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
           $upload_time = date('Y-m-d H:i:s'); // Get the current timestamp
           $stmt = $conn->prepare("INSERT INTO files (filename, upload_time) VALUES (?, ?)");
           $stmt->bind_param("ss", $target_file, $upload_time);

            if ($stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = "File has been uploaded and saved to the database.";
            } else {
                $response['message'] = "File has been uploaded but failed to save to the database.";
            }

            $stmt->close();
        } else {
            $response['message'] = "There was an error uploading your file.";
        }
    }
}

echo json_encode($response);
$conn->close();
?>
