<?php
include("../DBCONFIG.PHP");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["fileToDelete"]) && isset($_POST["fileId"])) {
    $fileToDelete = urldecode($_POST["fileToDelete"]);
    $fileId = $_POST["fileId"];

    if (unlink($fileToDelete)) {
        $sql = "DELETE FROM files WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $fileId);
        if ($stmt->execute()) {
            echo "File deleted successfully.";
        } else {
            echo "Error deleting file from the database.";
        }
        $stmt->close();
    } else {
        echo "Error deleting file from the server.";
    }
}
?>
