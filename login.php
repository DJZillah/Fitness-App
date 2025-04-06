<?php
namespace Fitify;
session_start();
require 'MoreDBUtil.php'; 

// Select the correct database
$conn->select_db("fitifyDB"); 

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, username, password_hash FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $username, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) { 
            $_SESSION['user'] = $username;
            $_SESSION['user_id'] = $user_id; //get userID from session and check if its in database later
            header("Location: FitHomepage.php");
            exit();
        } else {
            $error = "Invalid email or password!";
        }
    } else {
        $error = "Invalid email or password!";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="FitifyRules.css">
</head>
<body>
    <div class="container">
        <h1>Login</h1>
        <div id="Logo">Fitify</div>

        <form method="POST">
            <fieldset>
                <p style="color:red;"><?= htmlspecialchars($error) ?></p>

                <label for="email">Email:</label>
                <input type="email" name="email" required>

                <label for="password">Password:</label>
                <input type="password" name="password" required>

                <input type="submit" value="Login">
            </fieldset>
        </form>

        <p>Don't have an account? <a href="register.php">Create one</a></p>
    </div>
</body>
</html>

<style>     /* needed to work with my own styling */
    body {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    background-color: rgb(88, 88, 234); /* Outer background */
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
}
h1 {
    color: black !important;
    background: rgb(214, 231, 24) !important;
}
/* White container */
.container {
    background: white !important;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
    width: 350px;
    text-align: center;
}

/* Ensures form elements stay inside the white box */
form {
    background: white !important;
    padding: 20px;
    border-radius: 10px;
}

fieldset {
    background: white !important;
    border: 2px solid #ccc;
    padding: 15px;
    border-radius: 8px;
}

/* Override purple background */
label, p, a {
    color: black !important;
    background: transparent !important;
    display: block;
}

input {
    width: 90%;
    padding: 8px;
    margin-top: 5px;
    border: 1px solid #ccc;
    border-radius: 5px;
    background: white !important;
    color: black !important;
}

/* Submit button styling */
input[type="submit"] {
    background: black !important;
    color: yellow !important;
    margin-top: 15px;
    cursor: pointer;
    width: 100%;
    padding: 10px;
    font-size: 16px;
    border: none;
    border-radius: 5px;
}

input[type="submit"]:hover {
    background: yellow !important;
    color: black !important;
}

p, label, input, a {
    background-color: transparent !important;
}


</style>
