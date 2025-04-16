<?php
session_start();
include 'MoreDBUtil.php';
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Build query with JOIN to get exercise name
$sql = "
SELECT m.id, e.exercise_name, m.reps, m.max_weight, m.achieved_at
FROM milestones m
JOIN exercises e ON m.exercise_id = e.id
WHERE m.user_id = ?
ORDER BY m.achieved_at DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Query error: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Milestones</title>
    <link rel="stylesheet" href="FitifyRulesDraft.css">
</head>
<body>
    <div class="content-container">
        <h1>Your Milestones</h1>

        <div class="milestones-list">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="milestone-item">
                        <p><strong>Exercise:</strong> <?= htmlspecialchars($row['exercise_name']) ?></p>
                        <p><strong>Reps:</strong> <?= $row['reps'] ?></p>
                        <p><strong>Max Weight:</strong> <?= $row['max_weight'] ?> lbs</p>
                        <p><small><em>Achieved on <?= date("F j, Y", strtotime($row['achieved_at'])) ?></em></small></p>

                        <form action="delete_milestone.php" method="POST">
                            <input type="hidden" name="milestone_id" value="<?= $row['id'] ?>">
                            <button type="submit" name="delete_milestone" onclick="return confirm('Delete this milestone?');">Delete</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No milestones found.</p>
            <?php endif; ?>
        </div>

        <a href="add_milestone.php">Add New Milestone</a>
    </div>
</body>
</html>
<?php
$stmt->close();
include 'footer.php';
?>
