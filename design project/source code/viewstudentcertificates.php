<?php
session_start();

// Check if the staff is logged in
if (!isset($_SESSION['staff_id'])) {
    header("Location: stafflogin.php");
    exit();
}

// Get the staff_id from the session
$staff_id = $_SESSION['staff_id'];

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

// Get the student's register_no from the GET request
if (isset($_GET['register_no'])) {
    $register_no = $_GET['register_no'];
} else {
    echo "No student selected.";
    exit();
}

// Fetch student's name and register_no from the students table
$student_sql = "SELECT name, register_no FROM students WHERE register_no = ?";
$student_stmt = $conn->prepare($student_sql);
$student_stmt->bind_param("s", $register_no);
$student_stmt->execute();
$student_result = $student_stmt->get_result();

// Check if student exists
if ($student_result->num_rows > 0) {
    $student = $student_result->fetch_assoc();
    $student_name = $student['name'];
    $student_register_no = $student['register_no'];
} else {
    echo "Student not found.";
    exit();
}

// Handle the form submission to update the credits
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['certificate_id'])) {
    $certificate_id = $_POST['certificate_id'];
    $credits_awarded = $_POST['credits_awarded'];

    // Update the credits for the selected certificate
    $update_sql = "UPDATE certificates SET credits_awarded = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ii", $credits_awarded, $certificate_id);
    if ($stmt->execute()) {
        echo "<p style='color:green;'>Credits updated successfully!</p>";
    } else {
        echo "<p style='color:red;'>Error updating credits.</p>";
    }
    $stmt->close();
}

// Fetch certificates for the student based on register_no
$sql = "SELECT * FROM certificates WHERE register_no = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $register_no);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validate Certificate</title>
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
        .student-info {
            font-size: 18px;
            margin-bottom: 20px;
            font-weight: bold;
            text-align: center; /* Centering the text */
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
        .back-btn {
            background-color: green;
            margin-top: 20px;
            text-align: center;
            display: block;
            width: 150px;
            margin: 20px auto;
        }
        .tick {
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Validate and Award Credits</h2>

    <!-- Student Info -->
    <div class="student-info">
        Student: <?php echo htmlspecialchars($student_name); ?> (Register Number: <?php echo htmlspecialchars($student_register_no); ?>)
    </div>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Certificate Name</th>
                    <th>Type</th>
                    <th>Upload Date</th>
                    <th>Current Credits</th>
                    <th>View Certificate</th>
                    <th>Modify Credits</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['file_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['certificate_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['upload_date']); ?></td>
                        <td>
                            <?php
                            echo htmlspecialchars($row['credits_awarded']);
                            if ($row['credits_awarded'] > 0) {
                                echo ' <span class="tick">✔</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <a href="uploads/<?php echo htmlspecialchars($row['file_name']); ?>" target="_blank">View</a>
                        </td>
                        <td>
                            <form method="POST" action="">
                                <input type="hidden" name="certificate_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                <input type="number" name="credits_awarded" min="0" max="10" value="<?php echo htmlspecialchars($row['credits_awarded']); ?>" <?php if ($row['credits_awarded'] > 0) echo 'readonly'; ?>>
                        </td>
                        <td>
                                <button type="submit" class="btn" <?php if ($row['credits_awarded'] > 0) echo 'disabled'; ?>>Give Credit</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No certificates found for this student.</p>
    <?php endif; ?>

    <!-- Back to Profile Button -->
    <div style="text-align: center;">
        <a href="staffprofile.php" class="btn back-btn">Back to Profile</a>
    </div>
</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
