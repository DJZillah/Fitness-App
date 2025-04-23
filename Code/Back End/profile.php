<?php
namespace Fitify;
session_start();
require_once __DIR__ . '/../Back End/MoreDBUtil.php';
require_once __DIR__ . '/../Front End/header.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["logout"])) {
    session_destroy();
    header("Location: ../Front End/login.php");
    exit();
}

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: ../Front End/login.php");
    exit;
}

// Handle updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $age = $_POST['age'] ?? null;
    $weight = $_POST['weight'] ?? null;
    $height = $_POST['height'] ?? null;

    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, age = ?, weight = ?, height = ? WHERE user_id = ?");
    $stmt->bind_param("ssiiii", $username, $email, $age, $weight, $height, $user_id);
    $stmt->execute();

    // Redirect to prevent form resubmission
    header("Location: profile.php");
    exit;
}

// Fetch user data
$stmt = $conn->prepare("SELECT username, email, age, weight, height FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Profile</title>
    <link rel="stylesheet" href="../Front End/styles.css">
</head>
<body>
    <div class="container">
        <h1>Your Profile</h1>

        <form method="POST" action="">
            <label>Username:
                <input type="text" name="username" value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>
            </label><br>

            <label>Email:
                <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
            </label><br>

            <label>Age:
                <input type="number" name="age" value="<?= htmlspecialchars($user['age'] ?? '') ?>">
            </label><br>

            <label>Weight (lbs):
                <input type="number" name="weight" step="0.1" value="<?= htmlspecialchars($user['weight'] ?? '') ?>">
            </label><br>

            <label>Height (in):
                <input type="number" name="height" step="0.1" value="<?= htmlspecialchars($user['height'] ?? '') ?>">
            </label><br>

            <button type="submit">Update Profile</button>
        </form>

        <form method="POST" style="margin-top: 20px;">
             <button type="submit" name="logout" class="logout-btn">Logout</button>
        </form>

    </div>

<?php include_once __DIR__ . '/../Front End/footer.php'; ?>
</body>
</html>
