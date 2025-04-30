<?php
namespace Fitify;
session_start();
require_once __DIR__ . '/../Back End/MoreDBUtil.php';
include_once __DIR__ . '/../Front End/header.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit;
}

// Get user info (age, weight, height only)
$stmt = $conn->prepare("SELECT age, weight, height FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$calorieGoal = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $sex = $_POST["sex"];
    $activity = $_POST["activity"];
    $goal = $_POST["goal"];

    $age = (int)$user['age'];
    $weight_lbs = (float)$user['weight'];
    $height_in = (float)$user['height'];

    // Convert to metric
    $weight = $weight_lbs * 0.453592;
    $height = $height_in * 2.54;

    // Calculate BMR
    if ($sex === "male") {
        $bmr = 10 * $weight + 6.25 * $height - 5 * $age + 5;
    } else {
        $bmr = 10 * $weight + 6.25 * $height - 5 * $age - 161;
    }

    // TDEE multiplier
    $multipliers = [
        "sedentary" => 1.2,
        "light" => 1.375,
        "moderate" => 1.55,
        "very" => 1.725,
        "extreme" => 1.9
    ];
    $tdee = $bmr * $multipliers[$activity];

    // Adjust for goal
    if ($goal === "lose") {
        $calorieGoal = round($tdee - 500);
        $goalType = 'weight loss';
    } elseif ($goal === "gain") {
        $calorieGoal = round($tdee + 300);
        $goalType = 'muscle gain';
    } else {
        $calorieGoal = round($tdee);
        $goalType = 'endurance'; // fallback, you can rename if needed
    }

    // Insert calorie goal into fitness_goals
    $today = date('Y-m-d');
    $stmt = $conn->prepare("INSERT INTO fitness_goals (user_id, goal_type, calorie_goal, start_date, status) VALUES (?, ?, ?, ?, 'in progress')");
    $stmt->bind_param("isis", $user_id, $goalType, $calorieGoal, $today);
    $stmt->execute();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Set Calorie Goal</title>
    <link rel="stylesheet" href="../Front End/styles.css">
</head>
<body>
    <div class="container">
    <a href="../Back End/CalorieCounter.php">Back to Calories page</a>

        <h1>Your Current Calorie Goal</h1>
        <?php
        // Get the latest goal for this user
        $stmt = $conn->prepare("SELECT calorie_goal, goal_type FROM fitness_goals WHERE user_id = ? ORDER BY start_date DESC LIMIT 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $latestGoal = $result->fetch_assoc();

        if ($latestGoal): ?>
            <p><strong><?= $latestGoal['calorie_goal'] ?> Calories/day</strong> — <?= ucwords($latestGoal['goal_type']) ?></p>
        <?php else: ?>
            <p>You haven't set a calorie goal yet.</p>
        <?php endif; ?>

        <h1>Set Your Calorie Goal</h1>
        <form method="POST">
            <label for="sex">Sex:</label>
            <select name="sex" required>
                <option value="male">Male</option>
                <option value="female">Female</option>
            </select><br><br>

            <label for="activity">Activity Level:</label>
            <select name="activity" required>
                <option value="sedentary">Sedentary (x1.2)</option>
                <option value="light">Lightly Active (x1.375)</option>
                <option value="moderate">Moderately Active (x1.55)</option>
                <option value="very">Very Active (x1.725)</option>
                <option value="extreme">Extremely Active (x1.9)</option>
            </select><br><br>

            <label for="goal">Your Goal:</label>
            <select name="goal" required>
                <option value="lose">Lose Weight</option>
                <option value="maintain">Maintain Weight</option>
                <option value="gain">Gain Muscle/Weight</option>
            </select><br><br>

            <button type="submit">Calculate & Save</button>
        </form>

        <?php if ($calorieGoal): ?>
            <div class="result">
                <h3>Your New Target Daily Calorie Intake:</h3>
                <p><strong><?= $calorieGoal ?> Calories/day</strong></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
