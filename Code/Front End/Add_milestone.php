<?php
require_once __DIR__ . '/../Back End/MoreDBUtil.php';  // Include our database connection
session_start();
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $exercise_id = $_POST['exercise_id'];
    $max_weight = $_POST['max_weight'];
    $reps = $_POST['reps'];

    if (!empty($exercise_id) && !empty($max_weight) && !empty($reps)) {
        $stmt = $conn->prepare("INSERT INTO milestones (user_id, exercise_id, max_weight, reps) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiii", $user_id, $exercise_id, $max_weight, $reps);

        if ($stmt->execute()) {
            $message = "Milestone added successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $message = "Please fill in all fields.";
    }
}

// Pull exercise list
$exercise_list = $conn->query("SELECT id, exercise_name FROM exercises");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Milestone</title>
</head>
<body>
<?php if (!empty($message)): ?>
    <div class="message"><?= $message ?></div>
<?php endif; ?>
    <div class="form-container">
        <h1>Add Milestone</h1>

        <?php if ($message): ?>
            <p><strong><?= htmlspecialchars($message) ?></strong></p>
        <?php endif; ?>

        <form action="add_milestone.php" method="POST">
            <label for="exercise_id">Exercise:</label>
            <select name="exercise_id" id="exercise_id" required>
                <option value="">-- Select Exercise --</option>
                <?php while ($row = $exercise_list->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['exercise_name']) ?></option>
                <?php endwhile; ?>
            </select>

            <label for="max_weight">Max Weight (lbs):</label>
            <input type="number" name="max_weight" id="max_weight" required>

            <label for="reps">Reps:</label>
            <input type="number" name="reps" id="reps" required>

            <input type="submit" value="Add Milestone">
        </form>

        <br>
        <a href="FitHomepage.php" class="back-button">Back to Dashboard</a>
        <a href="view_milestones.php">View Your Milestones</a>
    </div>
</body>
</html>
<?php include_once __DIR__ . '/../Front End/footer.php'; ?>
