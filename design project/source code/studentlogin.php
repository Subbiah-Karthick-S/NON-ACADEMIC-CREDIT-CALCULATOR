<?php
session_start();

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
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

    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare and execute the query
    $stmt = $conn->prepare("SELECT id, name, password FROM students WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    // If the email exists
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $name, $hashed_password);
        $stmt->fetch();

        // Verify the password
        if (password_verify($password, $hashed_password)) {
            // Store the student's information in session variables
            $_SESSION['student_id'] = $id;
            $_SESSION['email'] = $email;
            $_SESSION['name'] = $name;

            // Redirect to profile page
            header("Location: studentprofile.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No account found with this email.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: lightblue;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            flex-direction: column; /* To align the heading and form in column */
        }
        h1 {
            text-align: center;
            color: red;
            text-transform: uppercase; /* Capitalizes the text */
            margin-bottom: 20px;
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
            color: darkblue;
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
        input[type="email"], input[type="password"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 100%;
            box-sizing: border-box;
        }
        .password-container {
            position: relative;
        }
        .toggle-eye {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 20px;
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

<h1>NON-ACADEMIC CREDIT CALCULATOR</h1> <!-- Heading outside the container -->

<div class="container">
    <h2>Student Login</h2>

    <?php if (isset($error)): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <form action="" method="POST">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="Enter your email" required>
        
        <label for="password">Password</label>
        <div class="password-container">
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
            <span class="toggle-eye" id="toggle-eye">&#128065;</span> <!-- Eye icon for password visibility -->
        </div>
        
        <button type="submit">Login</button>
    </form>

    <div class="create-account">
        <p>Don't have an account? <a href="studentcreation.php">Create Account</a></p>
    </div>
</div>

<script>
    // JavaScript for toggling password visibility
    const toggleEye = document.getElementById("toggle-eye");
    const passwordField = document.getElementById("password");

    toggleEye.addEventListener("click", function() {
        // Toggle password visibility
        if (passwordField.type === "password") {
            passwordField.type = "text";
            toggleEye.innerHTML = "&#128064;"; // Open eye icon
        } else {
            passwordField.type = "password";
            toggleEye.innerHTML = "&#128065;"; // Closed eye icon
        }
    });
</script>

</body>
</html>
