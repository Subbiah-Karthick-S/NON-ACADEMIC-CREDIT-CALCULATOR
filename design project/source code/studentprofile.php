<?php
session_start();

// Check if the student is logged in using email
if (!isset($_SESSION['email'])) {
    // Redirect to login page if not logged in
    header("Location: studentlogin.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "non_academic_credit1";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the logged-in student's email from the session
$email = $_SESSION['email'];

// Prepare and execute query to fetch student data
$stmt = $conn->prepare("SELECT name, department, year, section, mentor, photo, register_no FROM students WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Check if student data exists
if ($result->num_rows > 0) {
    $student = $result->fetch_assoc();
} else {
    echo "No student found.";
    exit();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: lightblue;
            margin: 0;
        }
        h1 {
            text-align: center;
            color: red;
            text-transform: uppercase; /* Capitalizes the text */
            margin-top: 20px;
            margin-bottom: 30px;
        }
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }
        .profile-header {
            display: flex;
            justify-content: space-between;
            width: 100%;
            max-width: 1200px;
            align-items: center;
            margin-bottom: 20px;
        }
        .profile-header h2 {
            color: #333;
        }
        .profile-header img {
            border-radius: 8px; /* Rectangular frame */
            width: 150px;
            height: 200px;
            object-fit: cover;
            margin-left: 20px;
        }
        .profile-info {
            width: 100%;
            max-width: 600px;
            margin-top: 20px;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .profile-info p {
            font-size: 16px;
            margin: 10px 0;
        }
        button {
            padding: 10px;
            background-color: blue;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
        }
        button:hover {
            background-color: #004080;
        }
    </style>
</head>
<body>

<h1>NON-ACADEMIC CREDIT CALCULATOR</h1> <!-- Heading placed above the layout -->

<div class="container">
    <div class="profile-header">
        <h2>Welcome, <?php echo htmlspecialchars($student['name']); ?></h2>
        <!-- Repositioning the student's photo to the right side -->
        <?php if (!empty($student['photo'])): ?>
            <img src="uploads/<?php echo htmlspecialchars($student['photo']); ?>" alt="Profile Photo">
        <?php else: ?>
            <img src="uploads/default.jpg" alt="Profile Photo">
        <?php endif; ?>
    </div>

    <div class="profile-info">
        <p><strong>Name:</strong> <?php echo htmlspecialchars($student['name']); ?></p>
        <p><strong>Department:</strong> <?php echo htmlspecialchars($student['department']); ?></p>
        <p><strong>Year:</strong> <?php echo htmlspecialchars($student['year']); ?></p>
        <p><strong>Section:</strong> <?php echo htmlspecialchars($student['section']); ?></p>
        <p><strong>Mentor:</strong> <?php echo htmlspecialchars($student['mentor']); ?></p>
        <p><strong>Register No:</strong> <?php echo htmlspecialchars($student['register_no']); ?></p>
        
        <button onclick="window.location.href='uploadcertificate.php'">Upload Certificate</button>
        <button onclick="window.location.href='viewcreditpoints.php'">View Credit Points</button>
        <button onclick="window.location.href='studenteditprofile.php'">Edit Profile</button>
        <button onclick="window.location.href='index.html'">Logout</button>
    </div>
</div>

</body>
</html>
