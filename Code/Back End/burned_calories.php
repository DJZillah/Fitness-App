<?php
session_start();

//connect to the database
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

//used to calculate calories burned based on weight
$activityMETs = [
    "Running" => 9.8,
    "Walking" => 3.5,
    "Cycling" => 7.5,
    "Swimming" => 8.3,
    "Yoga" => 2.5,
    "Weightlifting" => 6.0
];

//get user weight
$userQuery = $conn->query("SELECT weight FROM users WHERE user_id = $userId");
$userWeight = 0;
if ($userQuery && $userQuery->num_rows > 0) {
    $userData = $userQuery->fetch_assoc();
    $userWeight = floatval($userData['weight']);
}

//handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $activity = isset($_POST["activity_name"]) ? trim($_POST["activity_name"]) : "";
    $duration = isset($_POST["duration_minutes"]) ? intval($_POST["duration_minutes"]) : 0;

    $met = $activityMETs[$activity] ?? 0;
    $calories = ($met * 3.5 * $userWeight / 200) * $duration;

    if ($duration > 0 && !empty($activity) && $met > 0 && $userWeight > 0) {
        if (isset($_POST["edit_id"])) {
            $editId = intval($_POST["edit_id"]);
            $stmt = $conn->prepare("UPDATE burned_calories_log SET calories_burned = ?, activity_name = ?, duration_minutes = ? WHERE log_id = ? AND user_id = ?");
            $stmt->bind_param("dsiii", $calories, $activity, $duration, $editId, $userId);
            $stmt->execute();
            $stmt->close();
            $message = "Burned calories updated successfully.";
        } else {
            $stmt = $conn->prepare("INSERT INTO burned_calories_log (user_id, calories_burned, activity_name, duration_minutes) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("idsi", $userId, $calories, $activity, $duration);
            $stmt->execute();
            $stmt->close();
            $message = "New burned calories logged successfully.";
        }

        header("Location: " . $_SERVER['PHP_SELF'] . "?msg=" . urlencode($message));
        exit();
    } else {
        $message = "Please enter a valid activity, duration, and ensure your weight is recorded.";
    }
}

//handle delete
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    if ($deleteId > 0) {
        $conn->query("DELETE FROM burned_calories_log WHERE log_id = $deleteId AND user_id = $userId");
        $message = "Entry deleted successfully.";
        header("Location: " . $_SERVER['PHP_SELF'] . "?msg=" . urlencode($message));
        exit();
    }
}

if (isset($_GET['msg'])) {
    $message = htmlspecialchars($_GET['msg']);
}

//fetch data to edit if requested
$editData = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $editResult = $conn->query("SELECT * FROM burned_calories_log WHERE log_id = $editId AND user_id = $userId");
    if ($editResult->num_rows > 0) {
        $editData = $editResult->fetch_assoc();
    }
}

$result = $conn->query("SELECT * FROM burned_calories_log WHERE user_id = $userId ORDER BY log_date DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Burned Calories Tracker</title>
    <style>
        * {
            background-color: rgb(88, 88, 234);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: black;
        }
        h1 {
            background-color: rgb(214, 231, 24);
            border: 1px solid black;
            border-width: 3px;
            color: white;
            margin: auto;
            width: 40%;
            text-align: center;
            line-height: 70px;
            font-weight: bold;
            font-style: italic;
        }
        fieldset {
            border: 2px solid black;
            padding: 15px;
            border-radius: 10px;
            width: 40%;
            margin: 20px auto;
            background-color: white;
        }
        legend {
            font-style: italic;
            font-size: 1.2em;
            background-color: rgb(214, 231, 24);
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
        }
        label {
            display: block;
            font-weight: bold;
            margin-top: 10px;
        }
        input[type="number"],
        select {
            display: block;
            width: 90%;
            margin: auto;
            padding: 8px;
            border: 1px solid black;
            border-radius: 5px;
            background-color: white;
        }
        input[type="submit"] {
            background-color: black;
            color: yellow;
            font-size: 1.1em;
            font-weight: bold;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 15px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        input[type="submit"]:hover {
            background-color: yellow;
            color: black;
            border: 2px solid black;
        }
        .cancel-link {
            display: block;
            text-align: center;
            margin-top: 10px;
            font-weight: bold;
        }
        .message {
            text-align: center;
            font-weight: bold;
            margin-top: 15px;
        }
        table {
            background-color: white;
            margin-top: 20px;
            border-collapse: collapse;
            width: 90%;
            margin-left: auto;
            margin-right: auto;
        }
        th, td {
            padding: 10px;
            border: 1px solid black;
            text-align: center;
        }
    </style>
</head>
<body>

<h1><?= $editData ? "Edit Burned Calories" : "Log Burned Calories" ?></h1>

<?php if (!empty($message)): ?>
    <div class="message"><?= $message ?></div>
<?php endif; ?>

<form method="POST">
    <fieldset>
        <legend><?= $editData ? "Edit Entry" : "New Entry" ?></legend>

        <label for="activity_name">Activity Type:</label>
        <select name="activity_name" required>
            <option value="">--Select Activity--</option>
            <?php foreach ($activityMETs as $activityName => $rate): ?>
                <option value="<?= $activityName ?>" <?= ($editData && $editData['activity_name'] === $activityName) ? "selected" : "" ?>>
                    <?= $activityName ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="duration_minutes">Duration (minutes):</label>
        <input type="number" name="duration_minutes" required
               value="<?= $editData ? $editData['duration_minutes'] : '' ?>">

        <?php if ($editData): ?>
            <input type="hidden" name="edit_id" value="<?= $editData['log_id'] ?>">
            <input type="submit" value="Update Entry">
            <a class="cancel-link" href="<?= $_SERVER['PHP_SELF'] ?>">Cancel</a>
        <?php else: ?>
            <input type="submit" value="Log Burned Calories">
        <?php endif; ?>
    </fieldset>
</form>

<h2 style="text-align: center;">Burned Calories History</h2>
<table>
    <tr>
        <th>Log ID</th>
        <th>Activity</th>
        <th>Duration (min)</th>
        <th>Calories Burned</th>
        <th>Date</th>
        <th>Actions</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['log_id'] ?></td>
            <td><?= $row['activity_name'] ?></td>
            <td><?= $row['duration_minutes'] ?? '-' ?></td>
            <td><?= round($row['calories_burned'], 2) ?></td>
            <td><?= $row['log_date'] ?></td>
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
