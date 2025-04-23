<?php
namespace Fitify;
require_once __DIR__ . '/../Back End/MoreDBUtil.php';
session_start();

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
            header("Location: ../Back End/FitHomepage.php");
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
    <link rel="stylesheet" href="FitifyRulesNew.css">
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

