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
    <link rel="stylesheet" href="FitifyRules0.css">
</head>
<body>

<!-- Navigation Bar -->
<header class="TopofPage">
    <h1 id="Logo">Fitify</h1>
    <nav>
        <a href="FitHomepage.php">Home</a>
        <a href="#">About</a>
        <a href="#">Contact</a>
    </nav>
</header>

<!-- Page Content -->
<div class="container">

    <!-- Page Heading -->
    <h1>Workout Tracker</h1>

    <!-- Workout Form -->
    <fieldset>
        <legend>Add New Workout</legend>
        <form method="POST" action="">
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
    <h2>Your Workout Logs</h2>
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
                        <form method="POST" action="">
                            <input type="hidden" name="workout_id" value="<?= intval($row['workout_id']) ?>">
                            <button type="submit" name="delete">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

</div>

</body>
</html>

<?php $conn->close(); ?>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

body {
    background-color: #f3f4f6;
    color: #1f2937;
    line-height: 1.6;
    padding: 20px;
}

/* Typography */
h1, h2, h3, h4 {
    color: #111827;
    margin-bottom: 10px;
}

p, label, legend {
    font-size: 16px;
}

/* Page Containers */
.container {
    max-width: 900px;
    margin: 40px auto;
    padding: 30px;
    background-color: #ffffff;
    border-radius: 12px;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.05);
}

/* Navigation Bar */
.TopofPage {
    background-color: #1e3a8a;
    padding: 20px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    position: sticky;
    top: 0;
    z-index: 10;
}

.TopofPage h1#Logo {
    font-size: 28px;
    font-weight: bold;
    color: #ffffff;
    margin: 0;
    font-style: italic;
    text-shadow: 1px 1px 2px #000;
}

.TopofPage nav {
    display: flex;
    gap: 20px;
}

.TopofPage nav a {
    color: #e0f2fe;
    text-decoration: none;
    font-weight: 600;
    padding: 10px 16px;
    border-radius: 8px;
    transition: background-color 0.3s ease;
}

.TopofPage nav a:hover {
    background-color: #3b82f6;
    color: #ffffff;
}

/* Forms */
fieldset {
    border: 2px solid #cbd5e0;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    background-color: #ffffff;
}

legend {
    font-style: italic;
    font-size: 1.2em;
    background-color: #3b82f6;
    color: white;
    padding: 5px 12px;
    border-radius: 5px;
}

label {
    display: block;
    font-weight: 600;
    margin-top: 12px;
    margin-bottom: 6px;
}

input[type="text"], input[type="email"], input[type="password"], input[type="number"], select {
    width: 100%;
    padding: 10px;
    border: 1px solid #cbd5e0;
    border-radius: 6px;
    margin-bottom: 15px;
    background-color: #ffffff;
}

/* Buttons */
input[type="submit"], button {
    background-color: #3b82f6;
    color: white;
    font-size: 1em;
    font-weight: 600;
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

input[type="submit"]:hover, button:hover {
    background-color: #2563eb;
}

/* Greeting & Misc */
.Hello {
    text-align: center;
    margin: 40px 0;
}

.Hello h2 {
    font-size: 26px;
    font-weight: 600;
    color: #1e293b;
}

#Finalize {
    background-color: #6b7280;
    color: white;
    font-weight: bold;
    font-size: 1.1em;
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    position: fixed;
    bottom: 20%;
    left: 5%;
    transform: translateX(-50%);
}

/* Table Styling */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background-color: #ffffff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

th, td {
    padding: 12px 15px;
    text-align: center;
    border-bottom: 1px solid #e5e7eb;
}

th {
    background-color: #3b82f6;
    color: white;
    font-size: 16px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

td {
    font-size: 15px;
    color: #374151;
}

tr:hover {
    background-color: #f1f5f9;
}

/* Utility */
.back-button {
    display: inline-block;
    margin-bottom: 20px;
    background-color: #3b82f6;
    color: white;
    padding: 10px 20px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    transition: background-color 0.3s ease;
}

.back-button:hover {
    background-color: #2563eb;
}
</style>