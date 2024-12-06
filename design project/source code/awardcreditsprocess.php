<?php
session_start();

if (!isset($_SESSION['staff_id'])) {
    header("Location: stafflogin.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "non_academic_credit1";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$certificate_id = $_POST['certificate_id'];
$credits = $_POST['credits'];

// Update the credits awarded for the certificate
$sql = "UPDATE certificates SET credits_awarded = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $credits, $certificate_id);

if ($stmt->execute()) {
    echo "Credits successfully awarded!";
} else {
    echo "Error awarding credits: " . $conn->error;
}

$stmt->close();
$conn->close();

// Redirect back to the student's certificates page
header("Location: awardcredits.php");
exit();
?>
