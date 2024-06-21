<?php
include("../DBCONFIG.PHP");
include("../LoginControl.php");
include("../BASICLOGININFO.PHP");

date_default_timezone_set('Asia/Manila');
$current_datetime = date('Y-m-d H:i:s');

session_start();
  if (!isset($_SESSION['adminId'])) {
  // Redirect to the desired page
  header("Location: ../default.php"); // Change 'login.php' to the desired page
  exit; // Terminate script execution after redirection
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Upload</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Add jQuery -->
</head>
<body>
    <?php INCLUDE('navbarAdmin.php'); ?>
    <div class="px-3">
        <h2 class="p-5 mt-5" style="text-align: center;">UPLOAD FORMS</h2>

        <div class="card p-3 mt-3">
            <form id="uploadForm" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="fileToUpload" class="form-label">Select file to upload:</label>
                    <input type="file" class="form-control" name="fileToUpload" id="fileToUpload">
                </div>
                <button class="btn btn-success btn-sm" type="submit">Upload File</button>
            </form>
        </div>
        <hr>
        <h4>UPLOADED FORMS</h4>
        <table class="table table-responsive table-striped">
            <thead>
                <tr>
                    <th>Document Name</th>
                    <th>Form Created</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT id, filename, upload_time FROM files";
                $result = $conn->query($sql);
                
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td><a href='uploads/" . basename($row["filename"]) . "' target='_blank'>" . basename($row["filename"]) . "</a></td>";
                        echo "<td>" . $row["upload_time"] . "</td>";
                        echo "<td class='text-end'>";
                        echo "<button class='btn btn-danger btn-sm delete-btn' data-file='" . urlencode($row["filename"]) . "' data-file-id='" . $row["id"] . "'>Delete</button>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>No files found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <script>
        $(document).ready(function() {
            $('#uploadForm').submit(function(e) {
                e.preventDefault();
                var formData = new FormData(this);

                $.ajax({
                    url: 'upload_file.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        var jsonResponse = JSON.parse(response);
                        if (jsonResponse.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                toast: true,
                                title: 'Upload Success',
                                text: jsonResponse.message
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.reload();
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                toast: true,
                                title: 'Upload Error',
                                text: jsonResponse.message
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            toast: true,
                            title: 'Upload Error',
                            text: 'An error occurred during the upload: ' + error
                        });
                    }
                });
            });

            $('.delete-btn').click(function() {
                var fileToDelete = $(this).data('file');
                var fileId = $(this).data('file-id');
                confirmDelete(fileToDelete, fileId);
            });

            function confirmDelete(fileToDelete, fileId) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Once deleted, you will not be able to recover this file!',
                    icon: 'warning',
                    toast: true,
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        deleteFile(fileToDelete, fileId);
                    }
                });
            }

            function deleteFile(fileToDelete, fileId) {
                $.ajax({
                    type: 'POST',
                    url: 'delete_file.php',
                    data: { fileToDelete: fileToDelete, fileId: fileId },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Delete',
                            toast: true,
                            text: response
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.reload();
                            }
                        });
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Delete',
                            toast: true,
                            text: 'Error deleting file: ' + error
                        });
                    }
                });
            }
        });
    </script>
    <?php
    $conn->close(); 
    ?>
</body>
</html>
