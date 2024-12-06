<?php
session_start();

// Check if the staff is logged in using the staff's email
if (!isset($_SESSION['staff_email'])) {
    header("Location: stafflogin.php");
    exit();
}

// Retrieve staff email from session
$staff_email = $_SESSION['staff_email'];

// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "non_academic_credit1";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the staff name using their email
$staff_query = "SELECT name FROM staff WHERE email = ?";
$stmt = $conn->prepare($staff_query);
$stmt->bind_param("s", $staff_email);
$stmt->execute();
$staff_result = $stmt->get_result();

if ($staff_result->num_rows === 0) {
    echo "Staff member not found.";
    exit();
}

$staff_name = $staff_result->fetch_assoc()['name'];

// Fetch students assigned to this mentor (staff)
$sql = "SELECT 
            students.name AS student_name, 
            students.department AS student_department,
            students.year AS student_year,
            students.section AS student_section,
            students.register_no AS student_register_no,
            SUM(certificates.credits_awarded) AS total_credits
        FROM students
        LEFT JOIN certificates ON students.register_no = certificates.register_no
        INNER JOIN mentor_student ON students.register_no = mentor_student.student_register_no
        WHERE mentor_student.mentor_name = ?
        GROUP BY students.register_no";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $staff_name);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student Credits</title>
    <style>
        /* Styling */
        body {
            font-family: Arial, sans-serif;
            background-color: lightblue;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        td {
            text-align: center;
        }
        .container {
            max-width: 1000px;
            margin: auto;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Student Credits Overview</h2>
    
    <table>
        <thead>
            <tr>
                <th>S.No</th>
                <th>Student Name</th>
                <th>Department</th>
                <th>Year</th>
                <th>Section</th>
                <th>Register No</th>
                <th>Total Credits Awarded</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                $sno = 1;
                while ($row = $result->fetch_assoc()) {
                    $total_credits = $row['total_credits'] ?? 0;
                    echo "<tr>
                        <td>{$sno}</td>
                        <td>" . htmlspecialchars($row['student_name']) . "</td>
                        <td>" . htmlspecialchars($row['student_department']) . "</td>
                        <td>" . htmlspecialchars($row['student_year']) . "</td>
                        <td>" . htmlspecialchars($row['student_section']) . "</td>
                        <td>" . htmlspecialchars($row['student_register_no']) . "</td>
                        <td>" . htmlspecialchars($total_credits) . "</td>
                    </tr>";
                    $sno++;
                }
            } else {
                echo "<tr><td colspan='7'>No students found</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <button onclick="window.location.href='staffprofile.php'">Back to Staff Profile</button>
</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
