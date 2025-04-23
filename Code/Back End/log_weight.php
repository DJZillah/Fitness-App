<?php
session_start();
include 'header.php';

// Connect to DB
$servername = "fitify-db.ctq460w22gbq.us-east-2.rds.amazonaws.com";
$username = "root";
$password = "fitify123";
$database = "fitifyDB";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$userId = $_SESSION['user_id'];
$message = "";

// Handle new or updated entry
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $weight = $_POST["weight"];
    if (is_numeric($weight) && $weight > 0) {
        if (isset($_POST["edit_id"])) {
            $editId = intval($_POST["edit_id"]);
            $stmt = $conn->prepare("UPDATE weight_log SET weight = ? WHERE log_id = ? AND user_id = ?");
            $stmt->bind_param("dii", $weight, $editId, $userId);
            $stmt->execute();
            $stmt->close();
            $message = "Weight updated successfully.";
        } else {
            $stmt = $conn->prepare("INSERT INTO weight_log (weight, user_id) VALUES (?, ?)");
            $stmt->bind_param("di", $weight, $userId);
            $stmt->execute();
            $stmt->close();
            $message = "New weight logged successfully.";
        }
        header("Location: " . $_SERVER['PHP_SELF'] . "?msg=" . urlencode($message));
        exit();
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $conn->query("DELETE FROM weight_log WHERE log_id = $deleteId AND user_id = $userId");
    $message = "Weight entry deleted successfully.";
    header("Location: " . $_SERVER['PHP_SELF'] . "?msg=" . urlencode($message));
    exit();
}

if (isset($_GET['msg'])) {
    $message = htmlspecialchars($_GET['msg']);
}

// Fetch record to edit
$editData = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $editResult = $conn->query("SELECT * FROM weight_log WHERE log_id = $editId AND user_id = $userId");
    if ($editResult->num_rows > 0) {
        $editData = $editResult->fetch_assoc();
    }
}

// Get all weight entries for user
$result = $conn->query("SELECT * FROM weight_log WHERE user_id = $userId ORDER BY created_at DESC");

// Get data for chart (ascending order)
$chartData = $conn->query("SELECT weight, created_at FROM weight_log WHERE user_id = $userId ORDER BY created_at ASC");
$weights = [];
$dates = [];

while ($row = $chartData->fetch_assoc()) {
    $weights[] = $row['weight'];
    $dates[] = $row['created_at'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Weight Tracker</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<!-- Page Heading -->
<h1 class="page-heading">Weight Tracker</h1>

<!-- Optional message box -->
<?php if (!empty($message)): ?>
    <div class="message"><?= $message ?></div>
<?php endif; ?>

<!-- Weight Entry Form -->
<form method="POST">
    <fieldset class="weight-form">
        <legend><?= $editData ? "Edit Entry" : "New Entry" ?></legend>
        <label for="weight">Weight (lbs):</label>
        <input type="number" step="0.1" name="weight" required value="<?= $editData ? $editData['weight'] : '' ?>">

        <?php if ($editData): ?>
            <input type="hidden" name="edit_id" value="<?= $editData['log_id'] ?>">
            <input type="submit" value="Update Weight">
            <a class="cancel-link" href="<?= $_SERVER['PHP_SELF'] ?>">Cancel</a>
        <?php else: ?>
            <input type="submit" value="Log Weight">
        <?php endif; ?>
    </fieldset>
</form>

<!-- Weight History Table -->
<h2 style="text-align: center;">Weight History</h2>
<table class="weight-table">
    <tr>
        <th>Log ID</th>
        <th>Weight</th>
        <th>Date</th>
        <th>Actions</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['log_id'] ?></td>
            <td><?= $row['weight'] ?> lbs</td>
            <td><?= $row['created_at'] ?></td>
            <td>
                <a href="?edit=<?= $row['log_id'] ?>">Edit</a> |
                <a href="?delete=<?= $row['log_id'] ?>" onclick="return confirm('Delete this entry?')">Delete</a>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

<!-- Chart -->
<h2 style="text-align: center;">Weight Progress Chart</h2>
<canvas id="weightChart" width="600" height="300" class="weight-chart"></canvas>

<script>
    const ctx = document.getElementById('weightChart').getContext('2d');
    const weightChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($dates) ?>,
            datasets: [{
                label: 'Weight (lbs)',
                data: <?= json_encode($weights) ?>,
                borderColor: 'red',
                borderWidth: 2,
                fill: true,
                tension: 0.5
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: false
                }
            }
        }
    });
</script>

<?php
$conn->close();
include 'footer.php';
?>
</body>
</html>
