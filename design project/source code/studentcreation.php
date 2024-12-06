<?php
// Server and database connection setup
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

// Initialize messages
$success_message = '';
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate password match
    if ($_POST['password'] !== $_POST['confirmPassword']) {
        $error_message = "Passwords do not match!";
    } else {
        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO students (name, email, department, year, section, mentor, register_no, password, photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssss", $name, $email, $department, $year, $section, $mentor, $register_no, $hashed_password, $photo);

        // Set parameters and execute
        $name = $_POST['name'];
        $email = $_POST['email'];
        $department = $_POST['department'];
        $year = $_POST['year'];
        $section = $_POST['section'];
        $mentor = $_POST['mentor'];
        $register_no = $_POST['register_no'];
        $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $photo = $_FILES['photo']['name'];

        // Upload photo
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($photo);
        move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file);

        if ($stmt->execute()) {
            $success_message = "New student account created successfully.";
        } else {
            $error_message = "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Fetch mentor names from the staff table before closing the connection
$mentorQuery = "SELECT name FROM staff";
$mentorResult = $conn->query($mentorQuery);

$mentors = [];
if ($mentorResult->num_rows > 0) {
    while ($row = $mentorResult->fetch_assoc()) {
        $mentors[] = $row['name'];
    }
} else {
    $mentors[] = "Nil";  // Add "Nil" if no mentors are available
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Student Account</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        h2 {
            text-align: center;
        }
        input[type="text"], input[type="email"], input[type="password"], select, input[type="file"] {
            width: 100%;
            padding: 8px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            position: relative;
        }
        .password-wrapper {
            position: relative;
            width: 100%;
        }
        .password-wrapper input {
            width: 100%;
            padding-right: 40px;
        }
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 10px;
            cursor: pointer;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .message {
            text-align: center;
            margin: 10px 0;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
        .back-to-login {
            text-align: center;
            margin-top: 20px;
        }
        .back-to-login a {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .back-to-login a:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Create Student Account</h2>
        <?php if ($success_message): ?>
            <p class="message success"><?php echo $success_message; ?></p>
            <!-- Display "Back to Login" button only on success -->
            <div class="back-to-login">
                <a href="studentlogin.php">Back to Login</a>
            </div>
        <?php elseif ($error_message): ?>
            <p class="message error"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <?php if (!$success_message): ?>
            <form action="" method="POST" enctype="multipart/form-data">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" required>

                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>

                <label for="department">Department</label>
                <input type="text" id="department" name="department" required>

                <label for="year">Year</label>
                <input type="text" id="year" name="year" required>

                <label for="section">Section</label>
                <input type="text" id="section" name="section" required>

                <label for="mentor">Mentor</label>
                <select id="mentor" name="mentor" required>
                    <?php foreach ($mentors as $mentor): ?>
                        <option value="<?php echo $mentor; ?>"><?php echo $mentor; ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="register_no">Register No</label>
                <input type="text" id="register_no" name="register_no" required>

                <label for="password">Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" required>
                    <span class="toggle-password" onclick="togglePassword('password')">👁️</span>
                </div>

                <label for="confirmPassword">Confirm Password</label>
                <div class="password-wrapper">
                    <input type="password" id="confirmPassword" name="confirmPassword" required>
                    <span class="toggle-password" onclick="togglePassword('confirmPassword')">👁️</span>
                </div>

                <label for="photo">Upload Photo</label>
                <input type="file" id="photo" name="photo" accept="image/*" required>

                <button type="submit">Create Account</button>
            </form>
        <?php endif; ?>
    </div>

    <script>
        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const type = passwordField.getAttribute("type") === "password" ? "text" : "password";
            passwordField.setAttribute("type", type);
        }
    </script>
</body>
</html>
