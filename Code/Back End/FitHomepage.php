<?php 
namespace Fitify;
include_once 'MoreDBUtil.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch weekly calorie intake
$userId = $_SESSION['user_id'];
$weeklyCalSql = "SELECT TotalCal FROM Simple_Cal_Log WHERE LogDate >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND user_id = $userId";
$weeklyTotal = 0;
$result = $conn->query($weeklyCalSql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $weeklyTotal += $row["TotalCal"];
    }
    $weeklyCalDisplay = $weeklyTotal . " cals";
} else {
    $weeklyCalDisplay = "No calories logged this week.";
}

// Fetch current weight
$currentWeightSql = "SELECT weight FROM weight_log WHERE user_id = $userId ORDER BY created_at DESC LIMIT 1";
$result1 = $conn->query($currentWeightSql);
if ($result1->num_rows > 0) {
    $row = $result1->fetch_assoc();
    $currentWeightDisplay = $row['weight'] . " lbs";
} else {
    $currentWeightDisplay = "No weight logged yet";
}

// Fetch current BMI
$currentBMISql = "SELECT bmi FROM bmi_records WHERE user_id = $userId ORDER BY created_at DESC LIMIT 1";
$result2 = $conn->query($currentBMISql);
if ($result2->num_rows > 0) {
    $row = $result2->fetch_assoc();
    $currentBMIDisplay = $row['bmi'];
} else {
    $currentBMIDisplay = "No BMI logged yet";
}

// Fetch Year-to-Date workout count
$ytdWorkoutsSql = "SELECT COUNT(*) AS workout_count FROM workout_logs WHERE user_id = $userId AND log_date >= DATE_FORMAT(NOW(), '%Y-01-01')";
$result3 = $conn->query($ytdWorkoutsSql);
if ($result3->num_rows > 0) {
    $row = $result3->fetch_assoc();
    $ytdWorkoutDisplay = $row['workout_count'] . " workouts logged";
} else {
    $ytdWorkoutDisplay = "No workouts logged this year yet.";
}

// Fetch starting weight
$startingWeightSql = "SELECT weight FROM weight_log WHERE user_id = $userId ORDER BY created_at ASC LIMIT 1";
$result4 = $conn->query($startingWeightSql);
if ($result4->num_rows > 0) {
    $row = $result4->fetch_assoc();
    $startingWeight = $row['weight'];
    $weightChange = $currentWeightDisplay !== "No weight logged yet" ? floatval($currentWeightDisplay) - floatval($startingWeight) : null;
    $weightChangeDisplay = $weightChange !== null ? ($weightChange > 0 ? "+" : "") . number_format($weightChange, 1) . " lbs" : "N/A";
    $startingWeightDisplay = $startingWeight . " lbs";
} else {
    $startingWeightDisplay = "No starting weight logged";
    $weightChangeDisplay = "N/A";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fitify Dashboard</title>
    <link rel="stylesheet" href="FitifyRules0.css">
</head>
<body>

    <header class="TopofPage">
        <h1 id="Logo">Fitify</h1>
        <nav>
            <a href="CalorieCounter.php">Nutrition</a>
            <a href="workouts.php">Workouts</a>
            <a href="log_weight.php">Log Weight</a>
            <a href="View_milestones.php">Milestones</a>
            <a href="MidTermDiscountClub.html">Guidance</a>
            <a href="MidTermFuturePage.html">More</a>
            <form method="POST" style="display:inline;">
                <input type="submit" name="logout" value="Logout">
            </form>
        </nav>
    </header>

    <?php
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["logout"])) {
        session_destroy();
        header("Location: login.php");
        exit();
    }
    ?>

    <div class="container">
        <div class="Hello">
            <h2>Welcome back, <?= htmlspecialchars($_SESSION['user']); ?>!</h2>
            <p>Here's your current fitness snapshot:</p>
        </div>

        <hr>

        <div class="dashboard-section">
           <h3>?? Weekly Summary</h3>
             <p><strong>Calorie Intake (Past 7 Days):</strong> <?= $weeklyCalDisplay ?></p>
             <p><strong>Current Weight:</strong> <?= $currentWeightDisplay ?></p>
             <p><strong>Starting Weight:</strong> <?= $startingWeightDisplay ?></p>
             <p><strong>Weight Change:</strong> <?= $weightChangeDisplay ?></p>
             <p><strong>Current BMI:</strong> <?= $currentBMIDisplay ?></p>
             <p><strong>Workouts Logged (Year-to-Date):</strong> <?= $ytdWorkoutDisplay ?></p>
        </div>


        <hr>

        <div class="dashboard-section">
            <h3>?? Explore Your Tools</h3>
            <p><a href="WorkoutTracker.php">Track a New Workout</a></p>
            <p><a href="CalorieCounter.php">Log Today's Meals</a></p>
            <p><a href="View_milestones.php">View Your Milestones</a></p>
        </div>
    </div>

</body>
</html>
<style>
/* General Styles */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f3f4f6;
    color: #1f2937;
    margin: 0;
    padding: 0;
}

h1, h2, h3, h4 {
    color: #111827;
    margin-bottom: 10px;
}

p {
    font-size: 16px;
    line-height: 1.6;
    margin-bottom: 15px;
}

/* Container Styling */
.container {
    max-width: 1000px;
    margin: 40px auto;
    padding: 40px;
    background-color: #ffffff;
    border-radius: 16px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
    text-align: left;
}

/* Navigation Bar */
.TopofPage {
    background-color: #1e3a8a;
    padding: 20px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    position: sticky;
    top: 0;
    z-index: 10;
}

.TopofPage h1#Logo {
    font-size: 28px;
    font-weight: bold;
    color: #ffffff;
    margin: 0;
}

.TopofPage nav {
    display: flex;
    gap: 20px;
}

.TopofPage nav a {
    color: #e0f2fe;
    text-decoration: none;
    font-weight: 600;
    padding: 10px 16px;
    border-radius: 8px;
    transition: background-color 0.3s ease;
}

.TopofPage nav a:hover {
    background-color: #3b82f6;
    color: #ffffff;
}

/* Form & Button Styling */
input[type="submit"] {
    padding: 12px 24px;
    background-color: #3b82f6;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    font-size: 16px;
    margin-top: 20px;
}

input[type="submit"]:hover {
    background-color: #2563eb;
}

/* Greeting Section */
.Hello {
    text-align: center;
    margin: 40px 0;
}

.Hello h2 {
    font-size: 26px;
    font-weight: 600;
    color: #1e293b;
}
</style>
