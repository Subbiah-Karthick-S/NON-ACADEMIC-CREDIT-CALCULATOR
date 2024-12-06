<?php
session_start();

// Check if the staff is logged in
if (!isset($_SESSION['staff_email'])) {
    header("Location: stafflogin.php");
    exit();
}

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

// Retrieve staff email from the session
$staff_email = $_SESSION['staff_email'];

// Prepare and execute query to fetch current staff data using email
$stmt = $conn->prepare("SELECT name, email, department, photo, password FROM staff WHERE email = ?");
$stmt->bind_param("s", $staff_email);
$stmt->execute();
$result = $stmt->get_result();

// Check if staff data exists
if ($result->num_rows > 0) {
    $staff = $result->fetch_assoc();
} else {
    echo "No staff found.";
    exit();
}

// Handle form submission for updating staff data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get updated data from form submission
    $updated_name = $_POST['name'];
    $updated_email = $_POST['email'];
    $updated_department = $_POST['department'];

    // Handle password update if new password is provided and confirmed
    if (!empty($_POST['new_password']) && $_POST['new_password'] === $_POST['confirm_password']) {
        $updated_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);  // Hash the new password
    } else {
        // If no new password is provided, keep the existing password
        $updated_password = $staff['password'];
    }

    // Handle photo upload or removal
    if (isset($_POST['remove_photo']) && $_POST['remove_photo'] === '1') {
        // Remove the photo
        $updated_photo = null;
    } elseif (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["photo"]["name"]);
        move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file);
        $updated_photo = basename($_FILES["photo"]["name"]);
    } else {
        // If no new photo is uploaded, keep the existing photo
        $updated_photo = $staff['photo'];
    }

    // Prepare and execute update query to modify staff data based on email
    $update_stmt = $conn->prepare("UPDATE staff SET name = ?, email = ?, department = ?, password = ?, photo = ? WHERE email = ?");
    $update_stmt->bind_param("ssssss", $updated_name, $updated_email, $updated_department, $updated_password, $updated_photo, $staff_email);

    if ($update_stmt->execute()) {
        // Update session variables
        $_SESSION['staff_name'] = $updated_name;
        $_SESSION['staff_email'] = $updated_email;
        $_SESSION['staff_department'] = $updated_department;
        $_SESSION['staff_photo'] = $updated_photo;

        echo "Profile updated successfully!";
        // Optionally, redirect to staff profile page
        header("Location: staffprofile.php");
        exit();
    } else {
        echo "Error updating profile: " . $conn->error;
    }

    $update_stmt->close();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Staff Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: lightblue;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: blue;
            font-weight: bold;
        }
        label {
            font-size: 18px;
            color: darkblue;
        }
        input[type="text"], input[type="email"], input[type="password"], input[type="file"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .password-container {
            position: relative;
            margin-bottom: 20px;
        }
        .eye-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #aaa;
        }
        .image-preview {
            width: 150px;
            height: 150px;
            border: 2px dashed #ccc;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 20px auto;
            overflow: hidden;
            background-color: #f9f9f9;
        }
        .image-preview img {
            max-width: 100%;
            max-height: 100%;
        }
        .placeholder-text {
            color: #aaa;
            font-size: 14px;
            text-align: center;
        }
        button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            background-color: blue;
            color: white;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Edit Staff Profile</h2>
    <div class="image-preview">
        <?php if (!empty($staff['photo'])): ?>
            <img src="uploads/<?php echo htmlspecialchars($staff['photo']); ?>" alt="Profile Photo">
        <?php else: ?>
            <div class="placeholder-text">No Photo Available</div>
        <?php endif; ?>
    </div>
    <form method="POST" action="" enctype="multipart/form-data">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($staff['name']); ?>" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($staff['email']); ?>" required>

        <label for="department">Department:</label>
        <input type="text" id="department" name="department" value="<?php echo htmlspecialchars($staff['department']); ?>" required>

        <label for="new_password">New Password (Optional):</label>
        <div class="password-container">
            <input type="password" id="new_password" name="new_password" placeholder="Enter new password">
            <span id="eye-icon-new" class="eye-icon" onclick="togglePassword('new_password')">&#128065;</span>
        </div>

        <label for="confirm_password">Confirm Password:</label>
        <div class="password-container">
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
            <span id="eye-icon-confirm" class="eye-icon" onclick="togglePassword('confirm_password')">&#128065;</span>
        </div>

        <label for="photo">Upload New Photo (Optional):</label>
        <input type="file" id="photo" name="photo" accept="image/*">

        <label>
            <input type="checkbox" name="remove_photo" value="1">
            Remove Current Photo
        </label>

        <button type="submit">Save Changes</button>
    </form>
</div>

<script>
    function togglePassword(id) {
        var passwordField = document.getElementById(id);
        var eyeIcon = document.getElementById('eye-icon-' + id);

        if (passwordField.type === "password") {
            passwordField.type = "text";
            eyeIcon.style.color = "black";
        } else {
            passwordField.type = "password";
            eyeIcon.style.color = "#aaa";
        }
    }
</script>

</body>
</html>
