<?php

//connect to the database
$servername = "fitify-db.ctq460w22gbq.us-east-2.rds.amazonaws.com";
$username = "root";
$password = "fitify123";
$database = "fitifyDB";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $weight = $_POST["weight"];

    if (is_numeric($weight)) {
        //if editing an existing entry
        if (isset($_POST["edit_id"])) {
            $editId = intval($_POST["edit_id"]);
            $stmt = $conn->prepare("UPDATE weight_log SET weight = ? WHERE log_id = ?");
            $stmt->bind_param("di", $weight, $editId);
            $stmt->execute();
            $stmt->close();
        } else {
            //normal insert for new weight log
            $stmt = $conn->prepare("INSERT INTO weight_log (weight) VALUES (?)");
            $stmt->bind_param("d", $weight);
            $stmt->execute();
            $stmt->close();
        }

        //redirect to avoid form resubmission on page refresh
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

//handle delete
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $conn->query("DELETE FROM weight_log WHERE log_id = $deleteId");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

//fetch data to edit if requested
$editData = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $editResult = $conn->query("SELECT * FROM weight_log WHERE log_id = $editId");
    if ($editResult->num_rows > 0) {
        $editData = $editResult->fetch_assoc();
    }
}

//fetch all weight entries
$result = $conn->query("SELECT * FROM weight_log ORDER BY created_at DESC");

?>

<!DOCTYPE html>
<html>
<head>
    <title>Weight Tracker</title>
</head>
<body>

<h2><?= $editData ? "Edit Weigh-In" : "Log New Weigh-In" ?></h2>

<form method="POST">
    <input type="number" step="0.1" name="weight" placeholder="Weight (lbs)" required
           value="<?= $editData ? $editData['weight'] : '' ?>">
    <?php if ($editData): ?>
        <input type="hidden" name="edit_id" value="<?= $editData['log_id'] ?>">
        <input type="submit" value="Update Weight">
        <a href="<?= $_SERVER['PHP_SELF'] ?>">Cancel</a>
    <?php else: ?>
        <input type="submit" value="Log Weight">
    <?php endif; ?>
</form>

<h2>Weight History</h2>

<table cellpadding="5">
    <tr>
        <th>Log ID</th>
        <th>Weight</th>
        <th>Date</th>
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

</body>
</html>

<?php $conn->close(); ?>