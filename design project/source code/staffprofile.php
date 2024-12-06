<?php
session_start();

// Check if staff is logged in
if (!isset($_SESSION['staff_email'])) {
    header("Location: stafflogin.php");
    exit();
}

// Use `email` to fetch staff data
$email = $_SESSION['staff_email'];

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "non_academic_credit1";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to retrieve staff details using email
$sql = "SELECT name, email, department, photo FROM staff WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Fetch staff details
if ($result->num_rows > 0) {
    $staff = $result->fetch_assoc();
} else {
    // If no staff data is found, redirect to login
    header("Location: stafflogin.php");
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
    <title>Staff Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: lightblue;
            margin: 0;
            padding: 20px;
        }
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }
        h1 {
            color: darkblue;
            margin-bottom: 20px;
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
            border-radius: 8px;
            width: 140px;
            height: 180px;
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

<div class="container">
    <div class="profile-header">
        <h2>Welcome, <?php echo htmlspecialchars($staff['name']); ?></h2>
        <?php if ($staff['photo']): ?>
            <img src="uploads/<?php echo htmlspecialchars($staff['photo']); ?>" alt="Profile Photo">
        <?php else: ?>
            <img src="uploads/default.jpg" alt="Profile Photo">
        <?php endif; ?>
    </div>

    <div class="profile-info">
        <p><strong>Name:</strong> <?php echo htmlspecialchars($staff['name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($staff['email']); ?></p>
        <p><strong>Department:</strong> <?php echo htmlspecialchars($staff['department']); ?></p>
        
        <button onclick="window.location.href='viewstudentcredits.php'">View Student Credits</button>
        <button onclick="window.location.href='awardcredits.php'">Award Credit Points</button>
        <button onclick="window.location.href='staffedit.php'">Edit Profile</button>
        <button onclick="window.location.href='index.html'">Logout</button>
    </div>
</div>

</body>
</html>
