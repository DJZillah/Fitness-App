<?php
namespace Fitify;
require 'MoreDBUtil.php';

$conn->select_db("fitifyDB");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['create'])) {
        // Create a new workout log
        $user_id = intval($_POST['user_id']);
        $workout_type = $conn->real_escape_string($_POST['workout_type']);
        $duration_minutes = intval($_POST['duration_minutes']);
        $calories_burned = intval($_POST['calories_burned']);
        $log_date = $_POST['log_date'];

        $stmt = $conn->prepare("INSERT INTO workout_logs (user_id, workout_type, duration_minutes, calories_burned, log_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isdds", $user_id, $workout_type, $duration_minutes, $calories_burned, $log_date);
        
        if ($stmt->execute()) {
            echo "New workout log created successfully.";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }

    if (isset($_POST['update'])) {
        // Update an existing workout log
        $workout_id = intval($_POST['workout_id']);
        $workout_type = $conn->real_escape_string($_POST['workout_type']);
        $duration_minutes = intval($_POST['duration_minutes']);
        $calories_burned = intval($_POST['calories_burned']);
        $log_date = $_POST['log_date'];

        $stmt = $conn->prepare("UPDATE workout_logs SET workout_type=?, duration_minutes=?, calories_burned=?, log_date=? WHERE workout_id=?");
        $stmt->bind_param("sddsi", $workout_type, $duration_minutes, $calories_burned, $log_date, $workout_id);
        
        if ($stmt->execute()) {
            echo "Workout log updated successfully.";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }

    if (isset($_POST['delete'])) {
        // Delete a workout log
        $workout_id = intval($_POST['workout_id']);

        $stmt = $conn->prepare("DELETE FROM workout_logs WHERE workout_id=?");
        $stmt->bind_param("i", $workout_id);
        
        if ($stmt->execute()) {
            echo "Workout log deleted successfully.";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch all workout logs for the user
$user_id = 1; // Replace with session data
$sql = "SELECT * FROM workout_logs WHERE user_id=? ORDER BY log_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

// Fetch all exercises from the exercises table
$exercise_sql = "SELECT exercise_name FROM exercises";
$exercise_result = $conn->query($exercise_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workout Tracker</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        button { padding: 8px 12px; background-color: #4CAF50; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #45a049; }
    </style>
</head>
<body>

<h1>Workout Tracker</h1>
<h2>Add New Workout</h2>
<form method="POST" action="">
    <input type="hidden" name="user_id" value="1">
    <label for="workout_type">Exercise:</label><br>
    <select name="workout_type" required>
        <?php while ($row = $exercise_result->fetch_assoc()) { ?>
            <option value="<?= htmlspecialchars($row['exercise_name']) ?>">
                <?= htmlspecialchars($row['exercise_name']) ?>
            </option>
        <?php } ?>
    </select><br><br>
    <label for="duration_minutes">Duration (minutes):</label><br>
    <input type="number" name="duration_minutes" required><br><br>
    <label for="calories_burned">Calories Burned:</label><br>
    <input type="number" name="calories_burned" required><br><br>
    <label for="log_date">Date:</label><br>
    <input type="date" name="log_date" required><br><br>
    <button type="submit" name="create">Add Workout</button>
</form>
<h2>Your Workout Logs</h2>
<table>
    <tr>
        <th>Date</th>
        <th>Workout Type</th>
        <th>Duration (Minutes)</th>
        <th>Calories Burned</th>
        <th>Actions</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?= htmlspecialchars($row['log_date']) ?></td>
            <td><?= htmlspecialchars($row['workout_type']) ?></td>
            <td><?= intval($row['duration_minutes']) ?></td>
            <td><?= intval($row['calories_burned']) ?></td>
            <td>
                <form method="POST" action="" style="display:inline-block;">
                    <input type="hidden" name="workout_id" value="<?= intval($row['workout_id']) ?>">
                    <button type="submit" name="delete">Delete</button>
                </form>
                <form method="POST" action="" style="display:inline-block;">
                    <input type="hidden" name="workout_id" value="<?= intval($row['workout_id']) ?>">
                    <select name="workout_type">
                        <option value="<?= htmlspecialchars($row['workout_type']) ?>" selected>
                            <?= htmlspecialchars($row['workout_type']) ?>
                        </option>
                        <?php while ($exercise_row = $exercise_result->fetch_assoc()) { ?>
                            <option value="<?= htmlspecialchars($exercise_row['exercise_name']) ?>">
                                <?= htmlspecialchars($exercise_row['exercise_name']) ?>
                            </option>
                        <?php } ?>
                    </select>
                    <input type="number" name="duration_minutes" value="<?= intval($row['duration_minutes']) ?>">
                    <input type="number" name="calories_burned" value="<?= intval($row['calories_burned']) ?>">
                    <input type="date" name="log_date" value="<?= htmlspecialchars($row['log_date']) ?>">
                    <button type="submit" name="update">Update</button>
                </form>
            </td>
        </tr>
    <?php } ?>
</table>
</body>
</html>
<?php $conn->close(); ?>
