<?php
session_start();
include 'MoreDBUtil.php';

$user_id = $_SESSION['user_id'];

$filter_date = isset($_GET['filter_date']) ? $_GET['filter_date'] : 'all';
$date_condition = '';

if ($filter_date == '7days') {
    $date_condition = "AND achieved_at >= CURDATE() - INTERVAL 7 DAY";
} elseif ($filter_date == '30days') {
    $date_condition = "AND achieved_at >= CURDATE() - INTERVAL 30 DAY";
} elseif ($filter_date == 'this_month') {
    $date_condition = "AND MONTH(achieved_at) = MONTH(CURRENT_DATE()) AND YEAR(achieved_at) = YEAR(CURRENT_DATE())";
}

$stmt = $conn->prepare("SELECT id, milestone_name FROM milestones WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($milestone_id, $milestone_name);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Milestones</title>
    <!-- Link to the external CSS stylesheet -->
    <link rel="stylesheet" href="FitifyRules.css">
</head>
<body>
    <h1>View Milestones</h1>
    <!-- Displaying milestones -->
    <div class="milestones-list">
        <?php
        // Check if the user has any milestones
            while ($stmt->fetch()) {
                echo "<div class='milestone-item'>";
                echo "<p>" . htmlspecialchars($milestone_name) . "</p>";
                // Provide a delete button with confirmation
                echo "<form action='delete_milestone.php' method='POST'>
                        <input type='hidden' name='milestone_id' value='$milestone_id'>
                        <button type='submit' name='delete_milestone' onclick='return confirm(\"Are you sure you want to delete this milestone?\");'>Delete</button>
                      </form>";
                echo "</div>";
            }
        ?>
    </div>
    <!-- Link to add a new milestone -->
    <a href="add_milestone.php">Add New Milestone</a>
</body>
</html>
<?php
$stmt->close();
?>