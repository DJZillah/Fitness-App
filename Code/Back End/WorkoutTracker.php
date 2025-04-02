<?php
namespace Fitify;
require 'MoreDBUtil.php';
session_start();

$conn->select_db("fitifyDB");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure user is logged in
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
        
        if ($stmt->execute()) {
            echo "New workout log created successfully.";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }

    // Handle workout update
    if (isset($_POST['update'])) {
        $workout_id = intval($_POST['workout_id']);
        $workout_type = $conn->real_escape_string($_POST['workout_type']);
        $duration_minutes = intval($_POST['duration_minutes']);
        $calories_burned = intval($_POST['calories_burned']);
        $weight_used = intval($_POST['weight_used']);
        $sets = intval($_POST['sets']);
        $reps = intval($_POST['reps']);

        $stmt = $conn->prepare("UPDATE workout_logs SET workout_type=?, duration_minutes=?, calories_burned=?, weight_used=?, sets=?, reps=? WHERE workout_id=? AND user_id=?");
        $stmt->bind_param("sddiiii", $workout_type, $duration_minutes, $calories_burned, $weight_used, $sets, $reps, $workout_id, $user_id);
        
        if ($stmt->execute()) {
            echo "Workout log updated successfully.";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }

    // Handle workout deletion
    if (isset($_POST['delete'])) {
        $workout_id = intval($_POST['workout_id']);

        $stmt = $conn->prepare("DELETE FROM workout_logs WHERE workout_id=? AND user_id=?");
        $stmt->bind_param("ii", $workout_id, $user_id);
        
        if ($stmt->execute()) {
            echo "Workout log deleted successfully.";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch all workout logs for the user
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
    <link rel="stylesheet" href="FitifyRules.css">
</head>
<body>

<!-- Back to Homepage Button -->
<a href="dashboard.php">
    <button>Back to Homepage</button>
</a>

<h1>Workout Tracker</h1>
<h2>Add New Workout</h2>
<form method="POST" action="">
    <label for="workout_type">Exercise:</label>
    <select name="workout_type" required>
        <?php while ($row = $exercise_result->fetch_assoc()) { ?>
            <option value="<?= htmlspecialchars($row['exercise_name']) ?>">
                <?= htmlspecialchars($row['exercise_name']) ?>
            </option>
        <?php } ?>
    </select><br>
    <label for="duration_minutes">Duration (minutes):</label>
    <input type="number" name="duration_minutes" required><br>
    <label for="calories_burned">Calories Burned:</label>
    <input type="number" name="calories_burned" required><br>
    <label for="weight_used">Weight Used:</label>
    <input type="number" name="weight_used" required><br>
    <label for="sets">Sets:</label>
    <input type="number" name="sets" required><br>
    <label for="reps">Reps:</label>
    <input type="number" name="reps" required><br>
    <button type="submit" name="create">Add Workout</button>
</form>

<h2>Your Workout Logs</h2>
<table>
    <tr>
        <th>Date</th>
        <th>Workout Type</th>
        <th>Duration</th>
        <th>Calories Burned</th>
        <th>Weight Used</th>
        <th>Sets</th>
        <th>Reps</th>
        <th>Actions</th>
    </tr>
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
                <form method="POST" action="">
                    <input type="hidden" name="workout_id" value="<?= intval($row['workout_id']) ?>">
                    <button type="submit" name="delete">Delete</button>
                </form>
            </td>
        </tr>
    <?php } ?>
</table>

</body>
</html>
<?php $conn->close(); ?>

<style>
    /* General Styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    color: #333;
    text-align: center;
    padding: 20px;
}

h1, h2 {
    color: #222;
}

/* Container Styling */
.container {
    max-width: 800px;
    margin: auto;
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
}

/* Form Styling */
form {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
}

label {
    font-weight: bold;
}

input, select {
    padding: 8px;
    width: 80%;
    border-radius: 5px;
    border: 1px solid #ccc;
}

button {
    background-color: #28a745;
    color: white;
    border: none;
    padding: 10px 15px;
    cursor: pointer;
    border-radius: 5px;
    transition: 0.3s;
}

button:hover {
    background-color: #218838;
}

/* Table Styling */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

th, td {
    padding: 10px;
    border-bottom: 1px solid #ddd;
}

th {
    background-color: #28a745;
    color: white;
}

/* Back Button */
.back-button {
    display: inline-block;
    margin-bottom: 20px;
    background-color: #007bff;
}

.back-button:hover {
    background-color: #0056b3;
}
</style>