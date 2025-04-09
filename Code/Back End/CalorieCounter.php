<?php
namespace Fitify;
include_once 'MoreDBUtil.php';
session_start();

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

if (isset($_POST['calc'])) //if calculate BMI submitted
{
    $feet = intval($_POST["feet"]);
    $inches = intval($_POST["inches"]);
    $weight_lbs = floatval($_POST["weight"]);
    $name = $_SESSION['user'];

    // Convert height to total inches
    $height_in = ($feet * 12) + $inches;

    list($bmi, $category) = calculateBMI($height_in, $weight_lbs);

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO bmi_records (name, height, weight, bmi, category) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sddds", $name, $height_in, $weight_lbs, $bmi, $category);
    
    if ($stmt->execute()) {
        $message = "Your BMI is " . number_format($bmi, 2) .", which makes you " . ($category) . "."; 
    } else {
        echo "<p>Error: " . $stmt->error . "</p>";
        $message = "error?";
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

<!DOCTYPE HTML> <!--For some reason the namespace declaration won't work unless its the very first thing declared in a file. -->
<html>
    <head>
        <link href="FitifyRules2.css" type="text/css" rel="stylesheet"/>
        <p id="Logo"> <a href="FitHomepage.php"> fitify </a></p>

        <style>
        #Logo { /*Logo is just text */
        width: auto; 
        height: auto; 
        position: fixed;
        bottom: 72%;
        left: 12px; 
        transform: translateY(-50%);
        z-index: 9999; 
        font-size: 58px;
        text-shadow: 1px 1px 2px black;
        font-weight: bold;
        font-style: italic;
    }
    a {
        color:white;
        text-decoration:none;
    } 
        </style>
    </head>
<h1> Your Calories </h1>
<p> <?= $message; ?> </p>

    
    <form action="CalorieCounter.php" method="post">
        <fieldset>
            <legend>Today's Calories</legend>
            <?php foreach ($fields as $field): ?>
                <div class = "form-row">
                    <label> <?= ucfirst($field) ?>: 
                        <input type="text" size="3" maxlength="5" name="<?= $field ?>" 
                        value="<?= isset($_SESSION["valid" . ucfirst($field)]) ? $_SESSION["valid" . ucfirst($field)] : ''; ?>"/>
                    </label><br/>
                </div>
            <?php endforeach; ?>
            <label><input type="submit" value="Add" name="add"></label> <!--lowercase one for logic-->
        </fieldset>

    </form> <br/>

    <h2>BMI Calculator</h2>
    <form method="POST" id="BMI">

        <label for="feet">Height:</label>
        <input type="number" name="feet" required placeholder="Feet" min="0">
        <input type="number" name="inches" required placeholder="Inches" min="0" max="11"><br>

        <label for="weight">Weight (lbs):</label>
        <input type="text" name="weight" required><br>

        <input type="submit" name="calc" value="Calculate BMI">
    </form>
</html>