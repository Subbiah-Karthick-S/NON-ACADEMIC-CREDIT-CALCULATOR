<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "non_academic_credit1"; // Updated database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, department, password, photo FROM staff WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $staff = $result->fetch_assoc();

        if (password_verify($password, $staff['password'])) {
            $_SESSION['staff_email'] = $email;
            $_SESSION['staff_id'] = $staff['id'];
            $_SESSION['staff_name'] = $staff['name'];
            $_SESSION['staff_department'] = $staff['department'];
            $_SESSION['staff_photo'] = $staff['photo'];

            header("Location: staffprofile.php");
            exit();
        } else {
            $error_message = "Invalid password.";
        }
    } else {
        $error_message = "No staff found with that email.";
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
    <title>Staff Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: lightblue;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            box-sizing: border-box;
        }
        h2 {
            text-align: center;
            color: blue;
            margin-bottom: 20px;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        label {
            font-weight: bold;
        }
        input[type="text"], input[type="password"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 100%;
            box-sizing: border-box;
        }
        .password-container {
            position: relative;
        }
        .eye-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
        }
        button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border: none;
            border-radius: 4px;
            background-color: blue;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #004080;
        }
        .create-account {
            margin-top: 20px;
            text-align: center;
        }
        .create-account a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: bold;
        }
        .create-account a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Staff Login</h2>
        <?php if (!empty($error_message)): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <form action="stafflogin.php" method="POST">
            <label for="email">Email:</label>
            <input type="text" id="email" name="email" placeholder="Enter your email" required>
            <label for="password">Password:</label>
            <div class="password-container">
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
                <span class="eye-icon" onclick="togglePassword()">👁️</span>
            </div>
            <button type="submit">Login</button>
        </form>
        <div class="create-account">
            <p>Don't have an account? <a href="staffcreation.php">Create Account</a></p>
        </div>
    </div>
    <script>
        function togglePassword() {
            const passwordField = document.getElementById("password");
            const eyeIcon = document.querySelector(".eye-icon");
            if (passwordField.type === "password") {
                passwordField.type = "text";
                eyeIcon.textContent = "🙈";
            } else {
                passwordField.type = "password";
                eyeIcon.textContent = "👁️";
            }
        }
    </script>
</body>
</html>
