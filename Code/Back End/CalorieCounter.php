<?php
namespace Fitify;
include_once dirname(__DIR__) . '/Back End/MoreDBUtil.php';
session_start();
include_once dirname(__DIR__) . '/Front End/header.php';

if (empty($_SESSION)) {
    header("Location: ../login.php");
    exit();
}

// Database Connection
$conn = new \mysqli("fitify-db.ctq460w22gbq.us-east-2.rds.amazonaws.com", "root", "fitify123", "fitifyDB");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Function to calculate BMI and category (Imperial)
function calculateBMI($height_in, $weight_lbs) {
    $bmi = ($weight_lbs * 703) / ($height_in * $height_in);
    $category = "";

    if ($bmi < 18.5) {
        $category = "Underweight";
    } elseif ($bmi >= 18.5 && $bmi < 24.9) {
        $category = "Normal weight";
    } elseif ($bmi >= 25 && $bmi < 29.9) {
        $category = "Overweight";
    } else {
        $category = "Obese";
    }

    return [$bmi, $category];
}

$message = "";

// Handle BMI Calculation
if (isset($_POST['calc'])) {
    $feet = intval($_POST["feet"]);
    $inches = intval($_POST["inches"]);
    $weight_lbs = floatval($_POST["weight"]);
    $name = $_SESSION['user'];
    $user_id = $_SESSION['user_id'];
    
    // Convert height to total inches
    $height_in = ($feet * 12) + $inches;

    list($bmi, $category) = calculateBMI($height_in, $weight_lbs);

    // Insert into database with user_id added
    $stmt = $conn->prepare("INSERT INTO bmi_records (user_id, name, height, weight, bmi, category) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isddds", $user_id, $name, $height_in, $weight_lbs, $bmi, $category);

    if ($stmt->execute()) {
        $_SESSION['bmi_message'] = "Your BMI is " . number_format($bmi, 2) . ", which makes you " . $category . ".";
    } else {
        $_SESSION['bmi_message'] = "Error logging BMI.";
    }
    $stmt->close();

    // Prevent resubmission on refresh
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Calories Logic
$totalCal = 0;
$logDate = date('Y-m-d H:i:s');
$fields = ['breakfast', 'lunch', 'dinner', 'misc'];
$validInputs = array_fill_keys($fields, false);

if (isset($_POST['add'])) {
    foreach ($fields as $field) {
        if (isset($_POST[$field]) && is_numeric($_POST[$field])) {
            $validInputs[$field] = true;
        } else {
            $message = "Please ensure only numbers are entered.";
        }
    }

    if (array_filter($validInputs)) {
        foreach ($fields as $field) {
            if (isset($_POST[$field]) && is_numeric($_POST[$field])) {
                $totalCal += $_POST[$field];
            }
        }

        // Insert calories log
        $sql = "INSERT INTO Simple_Cal_Log (TotalCal, LogDate, user_id) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isi", $totalCal, $logDate, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $message = "Total calories: " . $totalCal;
        }

        $stmt->close();
    }
}

// Fetch the latest calorie goal
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT calorie_goal, goal_type FROM fitness_goals WHERE user_id = ? ORDER BY start_date DESC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$latestGoal = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Calories & BMI</title>
    <link rel="stylesheet" href="../Front End/FitifyRules.css">
</head>
<body>

<div class="form-container">

<?php if ($latestGoal): ?>
    <p><strong>Your Current Goal:</strong> <?= $latestGoal['calorie_goal'] ?> Calories/day — <?= ucwords($latestGoal['goal_type']) ?></p>
<?php else: ?>
    <p><strong>No calorie goal set yet.</strong></p>
<?php endif; ?>

<h1>Your Calories &amp; BMI</h1>

<?php if (!empty($message)): ?>
    <p class="message"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<a href="../Back End/setCalorieGoal.php">Set Calorie Goal</a>

<!-- Calorie Entry Form -->
<form action="CalorieCounter.php" method="post">
    <fieldset>
        <legend>Today's Calories</legend>
        <?php foreach (['breakfast','lunch','dinner','misc'] as $meal): ?>
            <label for="<?= $meal ?>"><?= ucfirst($meal) ?>:
                <input type="number" id="<?= $meal ?>" name="<?= $meal ?>" min="0" step="1" style="width:4em;">
            </label>
        <?php endforeach; ?>
        <input type="submit" name="add" value="Add Calories">
    </fieldset>
</form>

<hr>

<!-- BMI Calculator -->
<form method="POST">
    <fieldset>
        <legend>BMI Calculator</legend>
        <label for="feet">Height:</label>
        <div style="display:flex; gap:0.5em; margin-bottom:1em;">
            <input type="number" name="feet" required placeholder="ft" min="0">
            <input type="number" name="inches" required placeholder="in" min="0" max="11">
        </div>
        <label for="weight">Weight (lbs):</label>
        <input type="number" name="weight" required placeholder="e.g. 150">
        <input type="submit" name="calc" value="Calculate BMI">
    </fieldset>
</form>

<?php if (!empty($_SESSION['bmi_message'])): ?>
    <p class="message"><?= htmlspecialchars($_SESSION['bmi_message']) ?></p>
    <?php unset($_SESSION['bmi_message']); ?>
<?php endif; ?>

<!-- BMI Chart -->
<h2 class="section-heading">BMI Progress Chart</h2>
<iframe src="../Front End/Charts/bmi_chart.php" width="100%" height="360" style="border:none;"></iframe>

<a href="../Front End/FitHomepage.php" class="back-button">Back to Dashboard</a>
</div>

<?php include_once dirname(__DIR__) . '/Front End/footer.php'; ?>
</body>
</html>
