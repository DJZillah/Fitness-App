<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}


$username = isset($_SESSION["username"]) ? htmlspecialchars($_SESSION["username"]) : 'User';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Fitify</title>
    <link rel="stylesheet" href="FitifyRules.css">
</head>
<body>

<h2>Welcome, <?= $username ?>!</h2>
<p>This is your dashboard.</p>

<a href="logout.php">Logout</a>

</body>
</html>
