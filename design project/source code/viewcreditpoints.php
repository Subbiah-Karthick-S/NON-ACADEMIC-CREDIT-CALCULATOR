<?php 
session_start();

// Check if the student is logged in by checking if email exists in the session
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

// Get the student's email from the session
$email = $_SESSION['email'];

// Query to get the student's register_no from the email
$sql = "SELECT register_no FROM students WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($register_no);
$stmt->fetch();
$stmt->close();

// Query to get all certificates for the logged-in student using register_no
$sql = "SELECT * FROM certificates WHERE register_no = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $register_no);
$stmt->execute();
$result = $stmt->get_result();

$certificates = [];
$total_credits = 0; // Initialize total credits

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $certificates[] = $row;
        $total_credits += $row['credits_awarded']; // Sum credits awarded
    }
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credit Results</title>
    <style>
        /* Global styling */
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: lightblue;
        }

        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        /* Table styling */
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px 15px;
            text-align: center;
            font-size: 16px;
        }

        th {
            background-color: #4CAF50;
            color: white;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        td {
            color: #555;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .total-credits {
            text-align: right;
            font-weight: bold;
            font-size: 18px;
            color: #333;
            margin-top: 20px;
        }

        /* Button styling */
        button {
            padding: 6px 12px;
            border: none;
            background-color: #f44336;
            color: white;
            cursor: pointer;
            font-size: 14px;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #d32f2f;
        }

        /* Action column */
        .action-btn {
            display: inline-block;
            margin: 5px 0;
        }

        .view-link {
            color: #4CAF50;
            text-decoration: none;
            font-weight: bold;
            padding: 6px 12px;
            border: 1px solid #4CAF50;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .view-link:hover {
            background-color: #4CAF50;
            color: white;
        }

        /* Center the delete confirmation and table */
        .table-container {
            margin-top: 30px;
        }

        .confirmation-btn {
            padding: 8px 14px;
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .confirmation-btn:hover {
            background-color: #d32f2f;
        }
    </style>
    <script>
        // Function to confirm deletion
        function confirmDelete(certificateId) {
            if (confirm("Are you sure you want to delete this certificate?")) {
                // If user confirms, submit the form to delete the certificate
                document.getElementById("deleteForm" + certificateId).submit();
            }
        }
    </script>
</head>

<body>

    <div class="container">
        <h2>Credit Results</h2>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>S.NO</th>
                        <th>Certificate Name</th>
                        <th>Certificate Type</th>
                        <th>Credits Awarded</th>
                        <th>View Certificate</th>
                        <th>Action</th> <!-- New Action Column for Delete -->
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sno = 1;

                    if (!empty($certificates)) {
                        foreach ($certificates as $certificate) {
                            ?>

                            <tr>
                                <td><?php echo $sno++; ?></td>
                                <td><?php echo htmlspecialchars($certificate['file_name']); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($certificate['certificate_type'])); ?></td>
                                <td><?php echo htmlspecialchars($certificate['credits_awarded']); ?></td> <!-- Displaying Credits Awarded -->
                                <td>
                                    <a href="uploads/<?php echo htmlspecialchars($certificate['file_name']); ?>" target="_blank" class="view-link">View</a> <!-- View Certificate Link -->
                                </td>
                                <td>
                                    <form id="deleteForm<?php echo $certificate['id']; ?>" method="POST" action="delete_certificate.php" style="display: none;">
                                        <input type="hidden" name="certificate_id" value="<?php echo htmlspecialchars($certificate['id']); ?>">
                                    </form>
                                    <button type="button" onclick="confirmDelete(<?php echo $certificate['id']; ?>)" class="confirmation-btn">Delete</button> <!-- Delete Button -->
                                </td>
                            </tr>

                            <?php
                        }
                    } else {
                        echo '<tr><td colspan="6">No certificates uploaded yet.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="total-credits">
            Total Credits Awarded: <?php echo $total_credits; ?> <!-- Display Total Credits -->
        </div>
    </div>

</body>

</html>
