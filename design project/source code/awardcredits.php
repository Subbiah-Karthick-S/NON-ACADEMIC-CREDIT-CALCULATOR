<?php
session_start();

// Check if the staff is logged in using the staff's email
if (!isset($_SESSION['staff_email'])) {
    // Redirect to login page if not logged in
    header("Location: stafflogin.php");
    exit();
}

// Retrieve staff email from session
$staff_email = $_SESSION['staff_email'];

// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "non_academic_credit1"; // Database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the name of the mentor based on the logged-in staff's email
$sql_mentor_name = "SELECT name FROM staff WHERE email = ?";
$stmt_mentor_name = $conn->prepare($sql_mentor_name);
$stmt_mentor_name->bind_param("s", $staff_email);
$stmt_mentor_name->execute();
$result_mentor_name = $stmt_mentor_name->get_result();
$mentor_name = "";

if ($result_mentor_name->num_rows > 0) {
    $mentor_name = $result_mentor_name->fetch_assoc()['name'];
} else {
    echo "Staff not found.";
    exit();
}

// Fetch all students mentored by the logged-in staff (based on mentor_student relationship)
$sql = "SELECT s.register_no, s.name, s.department 
        FROM students s
        INNER JOIN mentor_student m ON s.register_no = m.student_register_no
        WHERE m.mentor_name = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $mentor_name);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Award Credits</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: lightblue;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: blue;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        .btn {
            padding: 10px;
            color: white;
            background-color: blue;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Award Credits to Students</h2>
    <table>
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Register Number</th>
                <th>Department</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['register_no']); ?></td>
                        <td><?php echo htmlspecialchars($row['department']); ?></td>
                        <td>
                            <form action="viewstudentcertificates.php" method="GET">
                                <input type="hidden" name="register_no" value="<?php echo htmlspecialchars($row['register_no']); ?>">
                                <input type="hidden" name="staff_email" value="<?php echo htmlspecialchars($staff_email); ?>">
                                <button type="submit" class="btn">Validate</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4">No students found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>

<?php
$conn->close();
?>
