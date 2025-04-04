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

//fetch all weight entries for displaying in the table
$result = $conn->query("SELECT * FROM weight_log ORDER BY created_at DESC");

//fetch data for the chart in ascending order to show progress over time
$chartData = $conn->query("SELECT weight, created_at FROM weight_log ORDER BY created_at ASC");
$weights = [];
$dates = [];

while ($row = $chartData->fetch_assoc()) {
    $weights[] = $row['weight'];         
    $dates[] = $row['created_at'];       
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Weight Tracker</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!--chart.js library-->
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

        input[type="text"],
        input[type="number"] {
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

        canvas {
            display: block;
            margin: 20px auto;
            background-color: white;
            border-radius: 10px;
            padding: 10px;
        }
    </style>
</head>
<body>

<!--form title dynamically switches between edit and add modes -->
<h1><?= $editData ? "Edit Weigh-In" : "Log New Weigh-In" ?></h1>

<!--form for submitting weight-->
<form method="POST">
    <fieldset>
        <legend><?= $editData ? "Edit Entry" : "New Entry" ?></legend>
        <label for="weight">Weight (lbs):</label>
        <input type="number" step="0.1" name="weight" required
               value="<?= $editData ? $editData['weight'] : '' ?>">
        <?php if ($editData): ?>
            <input type="hidden" name="edit_id" value="<?= $editData['log_id'] ?>">
            <input type="submit" value="Update Weight">
            <a class="cancel-link" href="<?= $_SERVER['PHP_SELF'] ?>">Cancel</a>
        <?php else: ?>
            <input type="submit" value="Log Weight">
        <?php endif; ?>
    </fieldset>
</form>

<!--weight history table-->
<h2 style="text-align: center;">Weight History</h2>
<table>
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
                <!--edit or delete entries-->
                <a href="?edit=<?= $row['log_id'] ?>">Edit</a> |
                <a href="?delete=<?= $row['log_id'] ?>" onclick="return confirm('Delete this entry?')">Delete</a>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

<!--chart display for weight progression-->
<h2 style="text-align: center;">Weight Progress Chart</h2>
<canvas id="weightChart" width="600" height="300"></canvas>

<script>
    //chart.js
    const ctx = document.getElementById('weightChart').getContext('2d');
    const weightChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($dates) ?>, //x axis: dates
            datasets: [{
                label: 'Weight (lbs)',
                data: <?= json_encode($weights) ?>, //y axis: weights
                borderColor: 'red',
                borderWidth: 2,
                fill: true,
                tension: 0.5
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: false //start y axis from lowest value
                }
            }
        }
    });
</script>

</body>
</html>

<?php $conn->close(); ?>
