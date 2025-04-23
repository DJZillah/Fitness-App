<?php
namespace Fitify;
include_once 'MoreDBUtil.php';
session_start();
include 'header.php';

if (empty($_SESSION)) 
{
    header("Location: login.php");
} 
$message = '';

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

if (isset($_POST['calc'])) // if calculate BMI submitted
{
    $feet = intval($_POST["feet"]);
    $inches = intval($_POST["inches"]);
    $weight_lbs = floatval($_POST["weight"]);
    $name = $_SESSION['user']; // username
    $user_id = $_SESSION['user_id']; 

    // Convert height to total inches
    $height_in = ($feet * 12) + $inches;

    list($bmi, $category) = calculateBMI($height_in, $weight_lbs);

    // Insert into database with user_id added
    $stmt = $conn->prepare("INSERT INTO bmi_records (user_id, name, height, weight, bmi, category) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isddds", $user_id, $name, $height_in, $weight_lbs, $bmi, $category);

    if ($stmt->execute()) {
        $message = "Your BMI is " . number_format($bmi, 2) . ", which makes you " . ($category) . "."; 
    } else {
        echo "<p>Error: " . $stmt->error . "</p>";
        $message = "Error logging BMI.";
    }
    $stmt->close();
    $conn->close();
}
 //idk im not gonna touch this 
//below starts calorie intake tracker logic
$totalCal = 0;
$logDate = date('Y-m-d H:i:s');

$fields = ['breakfast', 'lunch', 'dinner', 'misc']; //array of all text input ids

$validInputs = array_fill_keys($fields, false); //associative array where each value is given a key of false

if (isset($_POST['add'])) 
{ //when enter calories button is clicked
    foreach ($fields as $field) 
    { //iterate through $fields
        if (isset($_POST[$field]) && is_numeric($_POST[$field])) 
        {
            $validInputs[$field] = true;
        } 
        else 
        {
            $message = "Please ensure only numbers are entered.";
        }
    }
    if ($validInputs) 
    {
        foreach ($fields as $field) { //fetch the associated entered values by the user and get the sum
            if (isset($_POST[$field]) && is_numeric($_POST[$field])) 
            {
                $totalCal += $_POST[$field];
            }
        }
        $sql = "INSERT INTO Simple_Cal_Log (TotalCal, LogDate, user_id) 
        VALUES ($totalCal, '$logDate', " . $_SESSION['user_id'] . ")"; //for some reason user_id wants to be concatenated
         //Simple_Cal_Log has auto-increment primary key "LogID"

        if ($conn->query($sql) === TRUE) {
            $message = "Total calories: " . $totalCal; //maybe prevent the user 
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
        $conn->close();
    }
} //end of if add button scope
?> 

    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workout Tracker</title>
</head>
<body>
<div class="form-container">
    <h1>Your Calories &amp; BMI</h1>
    <?php if ($message): ?>
      <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <!-- Calorie Entry Form -->
    <form action="CalorieCounter.php" method="post">
      <fieldset>
        <legend>Today's Calories</legend>
        <?php foreach (['breakfast','lunch','dinner','misc'] as $meal): ?>
          <label for="<?= $meal ?>">
            <?= ucfirst($meal) ?>:
            <input
              type="number"
              id="<?= $meal ?>"
              name="<?= $meal ?>"
              min="0"
              step="1"
              style="width:4em;"
            />
          </label>
        <?php endforeach; ?>
        <input type="submit" name="add" value="Add Calories">
      </fieldset>
    </form>
    <hr>
    <!-- BMI Calculator -->
    <form method="POST" id="BMI">
      <fieldset>
        <legend>BMI Calculator</legend>

        <label for="feet">Height:</label>
        <div style="display:flex; gap:0.5em; margin-bottom:1em;">
          <input type="number" name="feet" id="feet" required placeholder="ft" min="0">
          <input type="number" name="inches" required placeholder="in" min="0" max="11">
        </div>
        <label for="weight">Weight (lbs):</label>
        <input type="number" name="weight" id="weight" required placeholder="e.g. 150">
        <input type="submit" name="calc" value="Calculate BMI">
      </fieldset>
    </form>
    <a href="FitHomepage.php" class="back-button">Back to Dashboard</a>
  </div>
    </body>
</html>
    <?php include 'footer.php';?>
