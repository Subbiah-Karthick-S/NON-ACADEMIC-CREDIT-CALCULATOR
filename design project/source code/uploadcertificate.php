<?php
session_start();

// Check if the student is logged in by checking if the email exists in the session
if (!isset($_SESSION['email'])) {
    header("Location: studentlogin.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "non_academic_credit1";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Enable detailed error reporting for debugging
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Get the student's email from session
    $email = $_SESSION['email'];
    $certificate_type = $_POST['certificateType'];
    $certificate_count = (int)$_POST['certificateCount'];

    // Get the register_no associated with the logged-in student
    $stmt = $conn->prepare("SELECT register_no FROM students WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($register_no);
    $stmt->fetch();
    $stmt->close();

    if (!$register_no) {
        echo "<script>alert('Student not found. Please log in again.'); window.location.href = 'studentlogin.php';</script>";
        exit();
    }

    // Directory to store uploads
    $targetDir = "uploads/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);  // Ensure the directory exists and is writable
    }

    $uploadSuccess = false; // To track if at least one file is successfully uploaded
    $errors = [];

    // Check if any certificates were uploaded
    if ($certificate_count > 0) {
        for ($i = 1; $i <= $certificate_count; $i++) {
            $fileKey = 'certificateUpload' . $i;

            if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES[$fileKey];
                $fileName = basename($file['name']);
                $targetFilePath = $targetDir . $fileName;
                $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

                // Allowed file types
                $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png'];

                if (in_array($fileType, $allowedTypes)) {
                    // Check if the file was successfully uploaded
                    if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
                        // Prepare SQL query to insert certificate data for each file
                        $stmt = $conn->prepare("INSERT INTO certificates (register_no, certificate_type, file_name) 
                                                VALUES (?, ?, ?)");
                        if ($stmt) {
                            $stmt->bind_param("sss", $register_no, $certificate_type, $fileName);

                            if ($stmt->execute()) {
                                $uploadSuccess = true; // At least one certificate successfully uploaded
                            } else {
                                $errors[] = "Database error for certificate $i: " . $stmt->error;
                            }
                            $stmt->close();
                        } else {
                            $errors[] = "SQL prepare error for certificate $i: " . $conn->error;
                        }
                    } else {
                        $errors[] = "Error moving uploaded file for certificate $i.";
                    }
                } else {
                    $errors[] = "Invalid file type for certificate $i. Only PDF, JPG, JPEG, and PNG are allowed.";
                }
            } else {
                $errors[] = "No valid file uploaded for certificate $i or an error occurred.";
            }
        }
    } else {
        $errors[] = "No certificates were selected for upload.";
    }

    if ($uploadSuccess) {
        echo "<script>alert('Certificates uploaded successfully!'); window.location.href = 'studentprofile.php';</script>";
    } else {
        $errorMessages = implode("\\n", $errors);
        echo "<script>alert('Errors encountered:\\n$errorMessages'); window.location.href = 'uploadcertificate.php';</script>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Certificates</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: lightblue; /* Set the background color to light blue */
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin: 10px 0 5px;
        }
        select, input[type="file"], input[type="number"], button {
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #28a745;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Upload Your Certificates</h2>

    <form action="uploadcertificate.php" method="POST" enctype="multipart/form-data">
        <label for="certificateType">Select Certificate Type:</label>
        <select name="certificateType" id="certificateType" required>
            <option value="" disabled selected>Select your certificate type</option>
            <option value="global">Global Certificate</option>
            <option value="nptel">NPTEL Certificate</option>
            <option value="workshop">Workshop Certificate</option>
            <option value="symposium">Symposium Certificate</option>
            <option value="others">Others</option>
        </select>

        <label for="certificateCount">Number of Certificates:</label>
        <input type="number" id="certificateCount" name="certificateCount" min="1" max="5" required>

        <div id="uploadFields">
            <!-- Certificate upload inputs will be appended here -->
        </div>

        <button type="button" id="addUploadFields">Add Upload Fields</button>
        <button type="submit">Upload Certificates</button>
    </form>
</div>

<script>
    document.getElementById('addUploadFields').addEventListener('click', function () {
        const count = document.getElementById('certificateCount').value;
        const uploadFields = document.getElementById('uploadFields');
        uploadFields.innerHTML = ''; // Clear previous fields

        for (let i = 1; i <= count; i++) {
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.name = 'certificateUpload' + i;
            fileInput.required = true;
            fileInput.accept = '.pdf,.jpg,.jpeg,.png';
            uploadFields.appendChild(fileInput);
        }
    });
</script>
</body>
</html>
