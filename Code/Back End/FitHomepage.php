<?php 
namespace Fitify;
include_once 'MoreDBUtil.php';
session_start();
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Weekly Calories
$weeklyCalSql = "SELECT TotalCal FROM Simple_Cal_Log WHERE LogDate >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND user_id = $userId";
$weeklyTotal = 0;
$result = $conn->query($weeklyCalSql);
$weeklyCalDisplay = ($result->num_rows > 0) ? array_sum(array_column($result->fetch_all(MYSQLI_ASSOC), 'TotalCal')) . " cals" : "No calories logged this week.";

// Current Weight
$currentWeightSql = "SELECT weight FROM weight_log WHERE user_id = $userId ORDER BY created_at DESC LIMIT 1";
$result1 = $conn->query($currentWeightSql);
$currentWeightDisplay = ($result1->num_rows > 0) ? $result1->fetch_assoc()['weight'] . " lbs" : "No weight logged yet";

// Current BMI
$currentBMISql = "SELECT bmi FROM bmi_records WHERE user_id = $userId ORDER BY created_at DESC LIMIT 1";
$result2 = $conn->query($currentBMISql);
$currentBMIDisplay = ($result2->num_rows > 0) ? $result2->fetch_assoc()['bmi'] : "No BMI logged yet";

// YTD Workouts
$ytdWorkoutsSql = "SELECT COUNT(*) AS workout_count FROM workout_logs WHERE user_id = $userId AND log_date >= DATE_FORMAT(NOW(), '%Y-01-01')";
$result3 = $conn->query($ytdWorkoutsSql);
$ytdWorkoutDisplay = ($result3->num_rows > 0) ? $result3->fetch_assoc()['workout_count'] . " workouts logged" : "No workouts logged this year yet.";

// Starting Weight & Change
$startingWeightSql = "SELECT weight FROM weight_log WHERE user_id = $userId ORDER BY created_at ASC LIMIT 1";
$result4 = $conn->query($startingWeightSql);
if ($result4->num_rows > 0) {
    $startingWeight = $result4->fetch_assoc()['weight'];
    $currentWeightValue = is_numeric($currentWeightDisplay) ? floatval($currentWeightDisplay) : null;
    $weightChange = $currentWeightValue !== null ? $currentWeightValue - $startingWeight : null;
    $weightChangeDisplay = $weightChange !== null ? ($weightChange > 0 ? "+" : "") . number_format($weightChange, 1) . " lbs" : "N/A";
    $startingWeightDisplay = $startingWeight . " lbs";
} else {
    $startingWeightDisplay = "No starting weight logged";
    $weightChangeDisplay = "N/A";
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["logout"])) {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fitify Dashboard</title>
</head>
<body>
    <div class="container">
        <div class="Hello">
            <h2>Welcome back, <?= htmlspecialchars($_SESSION['user']); ?>!</h2>
            <p>Here's your current fitness snapshot:</p>
        </div>

        <hr>

        <div class="dashboard-section">
            <h3>📊 Weekly Summary</h3>
            <p><strong>Calorie Intake (Past 7 Days):</strong> <?= $weeklyCalDisplay ?></p>
            <p><strong>Current Weight:</strong> <?= $currentWeightDisplay ?></p>
            <p><strong>Starting Weight:</strong> <?= $startingWeightDisplay ?></p>
            <p><strong>Weight Change:</strong> <?= $weightChangeDisplay ?></p>
            <p><strong>Current BMI:</strong> <?= $currentBMIDisplay ?></p>
            <p><strong>Workouts Logged (Year-to-Date):</strong> <?= $ytdWorkoutDisplay ?></p>
        </div>

        <hr>

        <div class="dashboard-section">
            <h3>🛠 Explore Your Tools</h3>
            <p><a href="WorkoutTracker.php">Track a New Workout</a></p>
            <p><a href="CalorieCounter.php">Log Today's Meals</a></p>
            <p><a href="View_milestones.php">View Your Milestones</a></p>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
