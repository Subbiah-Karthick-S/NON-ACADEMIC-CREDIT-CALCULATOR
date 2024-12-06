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
        $stmt = $conn->prepare("INSERT INTO staff (name, email, department, password, photo) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $department, $hashed_password, $photo);

        // Set parameters and execute
        $name = $_POST['name'];
        $email = $_POST['email'];
        $department = $_POST['department'];
        $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $photo = $_FILES['photo']['name'];

        // Directory to store uploads
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Create the directory if it doesn't exist
        }

        $target_file = $target_dir . basename($photo);

        // Check for upload errors
        if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            $error_message = "File upload error. Code: " . $_FILES['photo']['error'];
        } else {
            // Attempt to move the uploaded file
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
                // File moved successfully, proceed to insert data
                if ($stmt->execute()) {
                    $success_message = "New staff account created successfully.";
                } else {
                    $error_message = "Error: " . $stmt->error;
                }
            } else {
                $error_message = "Failed to move the uploaded file.";
            }
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
    <title>Create Staff Account</title>
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
        input[type="text"], input[type="email"], input[type="password"], input[type="file"] {
            width: 100%;
            padding: 8px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
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
    </style>
</head>
<body>
    <div class="container">
        <h2>Create Staff Account</h2>
        <?php if ($success_message): ?>
            <p class="message success"><?php echo $success_message; ?></p>
            <div class="back-to-login">
                <a href="stafflogin.php">Back to Login</a>
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
