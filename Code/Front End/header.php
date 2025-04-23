<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= isset($title) ? htmlspecialchars($title) : 'Fitify' ?></title>
<link rel="stylesheet" href="../Front End/FitifyRulesNew.css">
</head>
<body>

<header class="TopofPage">
    <h1 id="Logo">Fitify</h1>
    <nav>
        <a href="../Back End/FitHomepage.php">Home</a>
        <a href="../Back End/CalorieCounter.php">Nutrition</a>
        <a href="../Back End/WorkoutTracker.php">Workouts</a>
        <a href="../Back End/log_weight.php">Log Weight</a>
        <a href="../Front End/Add_milestone.php">Milestones</a>
        <a href="MidTermDiscountClub.html">Guidance</a>

        <!-- More dropdown -->
        <div class="dropdown">
            <button class="dropbtn">More ▾</button>
            <div class="dropdown-content">
                <a href="MidTermFuturePage.html">More Tools</a>
                <label class="theme-switch">
                    <input type="checkbox" id="themeToggle">
                    <span class="slider"></span>
                    <span style="margin-left: 8px;">Dark Mode</span>
                </label>
            </div>
        </div>

       <!-- Profile button -->
<nav>
    <form method="GET" style="display:inline;">
        <input type="submit" value="Profile" formaction="profile.php">
    </form>
</nav>

</header>

<?php
// Logout handler
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["logout"])) {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>
