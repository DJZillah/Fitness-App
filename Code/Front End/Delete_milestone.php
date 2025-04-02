<?php
session_start();
include 'MoreDBUtil.php'; // Include our database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to the login page
    header("Location: login.php");
    exit();
}

// Check if the delete button was clicked
if (isset($_POST['delete_milestone'])) {
    $milestone_id = (int) $_POST['milestone_id']; // Get the milestone ID from the form
    $user_id = $_SESSION['user_id']; // Get user ID from session

    // Prepare the delete SQL statement
    $stmt = $conn->prepare("DELETE FROM milestones WHERE id = ? AND user_id = ?");
    
    if ($stmt === false) {
        die('MySQL prepare failed: ' . $conn->error);
    }

    $stmt->bind_param("ii", $milestone_id, $user_id);

    if ($stmt->execute()) {
        // Redirect to the Add Milestone page after successful deletion
        header("Location: add_milestone.php");
        exit();
    } else {
        echo "Error deleting milestone: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}
?>

