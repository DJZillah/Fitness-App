<?php
session_start();
include 'MoreDBUtil.php'; // Include our database connection

// Check if the user is logged in (check if user_id is set in session)
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login page
    header("Location: login.php"); 
    exit();
}

$user_id = $_SESSION['user_id']; // User ID from the session

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the milestone data from the form
    $milestone = trim($_POST['milestone']);
    $user_id = $_SESSION['user_id']; // Assuming a user is logged in and their ID is in the session

    // Validate the input
    if (!empty($milestone)) {
        // Prepare and execute the SQL query to insert the milestone
        $stmt = $conn->prepare("INSERT INTO milestones (user_id, milestone_name) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $milestone);

        // Check if the milestone was added successfully
        if ($stmt->execute()) {
            // Successfully added, display a success message
            $message = "Milestone added successfully!";
        } else {
            // Error occurred while adding the milestone
            $message = "Error adding milestone: " . $conn->error;
        }

        $stmt->close();
    } else {
        // If the milestone field is empty
        $message = "Please enter a milestone.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Milestone</title>
    <link rel="stylesheet" href="FitifyRules.css">
</head>
<body>
    <div class="container">
        <h1>Add Milestone</h1>

        <?php
        // If there's a message (either success or error), display it
        if (isset($message)) {
            echo "<p class='message'>$message</p>";
        }
        ?>
        <form action="add_milestone.php" method="POST">
            <div>
                <label for="milestone">Milestone Achieved:</label>
                <input type="text" id="milestone" name="milestone" required>
            </div>
            <button type="submit">Add Milestone</button>
        </form>
        <br>
        <a href="view_milestones.php">View Your Milestones</a>
    </div>
</body>
</html>
