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

// Fetch current student data using email
$stmt = $conn->prepare("SELECT name, department, year, section, mentor, photo, register_no, password FROM students WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

// Fetch mentor names from the staff table
$mentorQuery = "SELECT name FROM staff";
$mentorResult = $conn->query($mentorQuery);

$mentors = [];
if ($mentorResult->num_rows > 0) {
    while ($row = $mentorResult->fetch_assoc()) {
        $mentors[] = $row['name'];
    }
} else {
    $mentors[] = "Nil"; // Add "Nil" if no mentors are available
}

// If the form is submitted, update the student's profile
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $department = $_POST['department'];
    $year = $_POST['year'];
    $section = $_POST['section'];
    $mentor = $_POST['mentor'];
    $new_register_no = $_POST['register_no'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Handle photo removal if selected
    $remove_photo = isset($_POST['remove_photo']) ? true : false;

    // Handle new photo upload
    $photo = $_FILES['photo']['name'];
    if (!empty($photo)) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($photo);
        move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file);
    } elseif ($remove_photo) {
        $photo = null; // Remove photo from the profile
    } else {
        // Keep the old photo if no new one is uploaded and not removed
        $photo = $student['photo'];
    }

    // Validate if new password and confirm password match
    if (!empty($new_password) && $new_password !== $confirm_password) {
        echo "<script>alert('Password and Confirm Password do not match.');</script>";
    } else {
        // Hash the new password if it's provided
        if (!empty($new_password)) {
            $new_password_hashed = password_hash($new_password, PASSWORD_BCRYPT);
        } else {
            // Keep the old password if no new password is provided
            $new_password_hashed = $student['password'];
        }

        // Update student data in the students table
        $stmt = $conn->prepare("UPDATE students SET name = ?, department = ?, year = ?, section = ?, mentor = ?, photo = ?, register_no = ?, password = ? WHERE email = ?");
        $stmt->bind_param("sssssssss", $name, $department, $year, $section, $mentor, $photo, $new_register_no, $new_password_hashed, $email);

        if ($stmt->execute()) {
            // If the profile update is successful, update the mentor-student relationship
            // First, delete the existing relationship for this student
            $delete_stmt = $conn->prepare("DELETE FROM mentor_student WHERE student_register_no = ?");
            $delete_stmt->bind_param("s", $student['register_no']);
            $delete_stmt->execute();
            $delete_stmt->close();

            // Now, insert the new mentor-student relationship
            $insert_stmt = $conn->prepare("INSERT INTO mentor_student (student_register_no, mentor_name) VALUES (?, ?)");
            $insert_stmt->bind_param("ss", $new_register_no, $mentor);
            $insert_stmt->execute();
            $insert_stmt->close();

            echo "<script>alert('Profile updated successfully'); window.location.href='studentprofile.php';</script>";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: lightblue; /* Light blue color */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-bottom: 5px;
        }
        input[type="text"], input[type="file"], input[type="password"] {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .photo-frame {
            width: 51mm; /* Set the width of the photo frame */
            height: 51mm; /* Set the height of the photo frame */
            border: 3px solid #007bff; /* Frame color */
            border-radius: 5px; /* Slightly round the corners */
            overflow: hidden; /* Ensure the image stays within the frame */
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 15px; /* Space below the photo frame */
        }
        img {
            width: 100%; /* Make image fill the container */
            height: 100%; /* Make image fill the container */
            object-fit: cover; /* Cover the entire container */
        }
        button {
            background-color: #4CAF50; /* Green */
            color: white;
            border: none;
            padding: 10px 15px;
            text-align: center;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 4px;
        }
        button:hover {
            background-color: #45a049;
        }
        .eye-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Profile</h1>
        
        <div class="photo-frame">
            <?php if (!empty($student['photo'])): ?>
                <img src="uploads/<?php echo htmlspecialchars($student['photo']); ?>" alt="Profile Photo">
            <?php endif; ?>
        </div>

        <form action="" method="POST" enctype="multipart/form-data">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($student['name']); ?>" required>

            <label for="department">Department</label>
            <input type="text" id="department" name="department" value="<?php echo htmlspecialchars($student['department']); ?>" required>

            <label for="year">Year</label>
            <input type="text" id="year" name="year" value="<?php echo htmlspecialchars($student['year']); ?>" required>

            <label for="section">Section</label>
            <input type="text" id="section" name="section" value="<?php echo htmlspecialchars($student['section']); ?>" required>

            <label for="mentor">Mentor</label>
            <select id="mentor" name="mentor" required>
                <?php foreach ($mentors as $mentorOption): ?>
                    <option value="<?php echo $mentorOption; ?>" <?php echo ($student['mentor'] === $mentorOption) ? 'selected' : ''; ?>>
                        <?php echo $mentorOption; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="register_no">Register Number</label>
            <input type="text" id="register_no" name="register_no" value="<?php echo htmlspecialchars($student['register_no']); ?>" required>

            <label for="photo">Upload New Photo (Optional)</label>
            <input type="file" id="photo" name="photo" accept="image/*">

            <label>
                <input type="checkbox" name="remove_photo"> Remove current photo
            </label>

            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password">

            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password">

            <button type="submit">Update Profile</button>
        </form>
    </div>
</body>
</html>
