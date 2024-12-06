<?php
session_start();

// Check if the staff is logged in
if (!isset($_SESSION['staff_id'])) {
    header("Location: stafflogin.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "non_academic_credit1";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if certificate_id is set
if (isset($_POST['certificate_id'])) {
    $certificate_id = $_POST['certificate_id'];

    // Fetch the certificate to get the file name
    $sql = "SELECT file_name FROM certificates WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $certificate_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $certificate = $result->fetch_assoc();
        $file_name = $certificate['file_name'];

        // Delete the certificate from the database
        $delete_sql = "DELETE FROM certificates WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $certificate_id);
        
        if ($delete_stmt->execute()) {
            // Optionally delete the file from the server
            $file_path = 'uploads/' . $file_name;
            if (file_exists($file_path)) {
                unlink($file_path); // Deletes the file
            }
            header("Location: viewcreditpoints.php?message=Certificate deleted successfully.");
            exit();
        } else {
            echo "Error deleting certificate.";
        }
        $delete_stmt->close();
    } else {
        echo "Certificate not found.";
    }

    $stmt->close();
}

$conn->close();
?>
