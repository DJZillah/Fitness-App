<?php
session_start();
require 'MoreDBUtil.php'; 
$conn->select_db("fitifyDB");
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $age = $_POST['age'];  // Get age from the form
    $weight = $_POST['weight'];  // Get weight from the form
    $height = $_POST['height'];  // Get height from the form

    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? OR username = ?");
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $error = "Username or Email already exists!";
    } else {
        // Include age, weight, and height in the INSERT statement
        $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, age, weight, height) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssii", $username, $email, $password, $age, $weight, $height);  // Use appropriate data types

        if ($stmt->execute()) {
            $_SESSION['user'] = $username;
            $_SESSION['user_id'] = $stmt->insert_id;
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Error registering account!";
        }
    }
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fitify Registration</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Register</h1>
        <form action="register.php" method="post">
            <label for="username">Username:</label>
            <input type="text" name="username" required>
    
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="age">Age:</label>
            <input type="number" id="age" name="age" required>

            <label for="weight">Weight (lbs):</label>
            <input type="number" id="weight" name="weight" required>

            <label for="height">Height (in):</label>
            <input type="number" id="height" name="height" required>

            <input type="submit" value="Register">
        </form>
        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>
</body>
</html>

<style>
    body {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        background-color: rgb(88, 88, 234);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .container {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        width: 350px;
        text-align: center;
    }
    h1 {
        color: black;
    }
    label {
        display: block;
        margin-top: 10px;
        font-weight: bold;
    }
    input {
        width: 90%;
        padding: 8px;
        margin-top: 5px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }
    input[type="submit"] {
        background: black;
        color: yellow;
        margin-top: 15px;
        cursor: pointer;
    }
    input[type="submit"]:hover {
        background: yellow;
        color: black;
    }
    p a {
        color: blue;
        text-decoration: none;
    }
</style>

