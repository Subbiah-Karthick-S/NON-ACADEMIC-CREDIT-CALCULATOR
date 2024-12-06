<?php
session_start();

// Check if the student is logged in
if (!isset($_SESSION['student_id'])) {
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

// Get the logged-in student's registration number from the session
$register_no = $_SESSION['register_no'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Fetch the student's current password from the database
    $stmt = $conn->prepare("SELECT password FROM students WHERE register_no = ?");
    $stmt->bind_param("s", $register_no);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();

    // Verify the current password
    if ($student && password_verify($current_password, $student['password'])) {
        // Check if new password and confirm password match
        if ($new_password === $confirm_password) {
            // Hash the new password
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Update the password in the database
            $update_stmt = $conn->prepare("UPDATE students SET password = ? WHERE register_no = ?");
            $update_stmt->bind_param("ss", $hashed_new_password, $register_no);

            if ($update_stmt->execute()) {
                echo "<script>alert('Password updated successfully'); window.location.href='studentprofile.php';</script>";
            } else {
                echo "Error updating password: " . $update_stmt->error;
            }
            $update_stmt->close();
        } else {
            $error_message = "New password and confirm password do not match.";
        }
    } else {
        $error_message = "Current password is incorrect.";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Password</title>
    <!-- Include Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f8ff; /* Light blue color */
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
            max-width: 400px;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        label {
            margin-bottom: 5px;
        }
        .password-container {
            position: relative;
            margin-bottom: 15px;
        }
        input[type="password"], input[type="text"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 100%;
        }
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
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
            width: 100%;
        }
        button:hover {
            background-color: #45a049;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 10px;
        }
    </style>
    <script>
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            } else {
                passwordInput.type = "password";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Update Password</h1>
        <?php if (isset($error_message)): ?>
            <div class="error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form action="" method="POST">
            <div class="password-container">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" required>
                <i id="toggleCurrentPassword" class="fas fa-eye-slash toggle-password" onclick="togglePassword('current_password', 'toggleCurrentPassword')"></i>
            </div>

            <div class="password-container">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required>
                <i id="toggleNewPassword" class="fas fa-eye-slash toggle-password" onclick="togglePassword('new_password', 'toggleNewPassword')"></i>
            </div>

            <div class="password-container">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
                <i id="toggleConfirmPassword" class="fas fa-eye-slash toggle-password" onclick="togglePassword('confirm_password', 'toggleConfirmPassword')"></i>
            </div>

            <button type="submit">Save</button>
        </form>
    </div>
</body>
</html>
