<?php
namespace Fitify;
require 'MoreDBUtil.php';
session_start();
include 'header.php';

$conn->select_db("fitifyDB");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    die("Error: User not logged in.");
}

// Handle workout creation
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['create'])) {
        $workout_type = $conn->real_escape_string($_POST['workout_type']);
        $duration_minutes = intval($_POST['duration_minutes']);
        $calories_burned = intval($_POST['calories_burned']);
        $weight_used = intval($_POST['weight_used']);
        $sets = intval($_POST['sets']);
        $reps = intval($_POST['reps']);

        $stmt = $conn->prepare("INSERT INTO workout_logs (user_id, workout_type, duration_minutes, calories_burned, weight_used, sets, reps) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isddiii", $user_id, $workout_type, $duration_minutes, $calories_burned, $weight_used, $sets, $reps);
        $stmt->execute();
        $stmt->close();
    }

    if (isset($_POST['delete'])) {
        $workout_id = intval($_POST['workout_id']);
        $stmt = $conn->prepare("DELETE FROM workout_logs WHERE workout_id=? AND user_id=?");
        $stmt->bind_param("ii", $workout_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch all workout logs
$sql = "SELECT * FROM workout_logs WHERE user_id=? ORDER BY log_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

// Fetch exercises
$exercise_sql = "SELECT exercise_name FROM exercises";
$exercise_result = $conn->query($exercise_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Workout Tracker</title>
</head>
<body>

<!-- Main Content -->
<div class="container">

    <!-- Page Heading -->
    <h1 class="page-heading">Workout Tracker</h1>

    <!-- Workout Form -->
    <fieldset>
        <legend>Add New Workout</legend>
        <form method="POST">
            <label for="workout_type">Exercise:</label>
            <select name="workout_type" required>
                <?php while ($row = $exercise_result->fetch_assoc()) { ?>
                    <option value="<?= htmlspecialchars($row['exercise_name']) ?>">
                        <?= htmlspecialchars($row['exercise_name']) ?>
                    </option>
                <?php } ?>
            </select>

            <label for="duration_minutes">Duration (minutes):</label>
            <input type="number" name="duration_minutes" required>

            <label for="calories_burned">Calories Burned:</label>
            <input type="number" name="calories_burned" required>

            <label for="weight_used">Weight Used:</label>
            <input type="number" name="weight_used" required>

            <label for="sets">Sets:</label>
            <input type="number" name="sets" required>

            <label for="reps">Reps:</label>
            <input type="number" name="reps" required>

            <button type="submit" name="create">Add Workout</button>
        </form>
    </fieldset>

    <!-- Workout Table -->
    <h2 class="section-heading">Your Workout Logs</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Workout Type</th>
                <th>Duration</th>
                <th>Calories</th>
                <th>Weight</th>
                <th>Sets</th>
                <th>Reps</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= htmlspecialchars($row['log_date']) ?></td>
                    <td><?= htmlspecialchars($row['workout_type']) ?></td>
                    <td><?= intval($row['duration_minutes']) ?></td>
                    <td><?= intval($row['calories_burned']) ?></td>
                    <td><?= intval($row['weight_used']) ?></td>
                    <td><?= intval($row['sets']) ?></td>
                    <td><?= intval($row['reps']) ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="workout_id" value="<?= intval($row['workout_id']) ?>">
                            <button type="submit" name="delete">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php
$conn->close();
include 'footer.php';
?>
</body>
</html>
