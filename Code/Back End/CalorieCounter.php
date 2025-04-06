<?php
namespace Fitify;
include_once 'MoreDBUtil.php';
session_start();

//affected table does not have user_id, will have to modify this later when logging in & shit is sorted
$totalCal = 0;
$message = '';
$logDate = date('Y-m-d H:i:s');

$fields = ['breakfast', 'lunch', 'dinner', 'misc']; //array of all text input ids

$validInputs = array_fill_keys($fields, false); //associative array where each value is given a key of false

if ($_SERVER["REQUEST_METHOD"] == "POST") { //when submit button clicked
    foreach ($fields as $field) { //iterate through $fields
        if (isset($_POST[$field]) && is_numeric($_POST[$field])) {
            $validInputs[$field] = true;
            $_SESSION["valid" . ucfirst($field)] = $_POST[$field]; //store valid values in session, ucfirst stores them as differently by capitalizing the first letter
            $message  = "";
        } else {
            $_SESSION["valid" . ucfirst($field)] = ""; //clear invalid values
            $message = "Please ensure only numbers are entered.";
        }
    }

    if ($validInputs) 
    {
        foreach ($fields as $field) { //fetch the associated entered values by the user and get the sum
            if (isset($_POST[$field]) && is_numeric($_POST[$field])) {
                $totalCal += $_POST[$field];
            }
        }

        //$_SESSION['user'] 
        //$_SESSION['user_id']

        $sql = "INSERT INTO Simple_Cal_Log (TotalCal, LogDate, user_id) 
        VALUES ($totalCal, '$logDate', " . $_SESSION['user_id'] . ")";
         //Simple_Cal_Log has auto-increment primary key "LogID"

        if ($conn->query($sql) === TRUE) {
            $message = "Yay";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
        $conn->close();
    }
} //end of if submit button scope

?> 

<!DOCTYPE HTML> <!--For some reason the namespace declaration won't work unless its the very first thing declared in a file. -->
<html>
    <head>
        <link href="FitifyRules.css" type="text/css" rel="stylesheet"/>
        <p id="Logo"> fitify </p>

    </head>
<h1> Your Calories </h1>
<p> <?= $message; ?> </p>

    
    <form action="CalorieCounter.php" method="post">
        <fieldset id="Entry">
            <legend>Today's Calories</legend>
            <?php foreach ($fields as $field): ?>
                <label> <?= ucfirst($field) ?>: 
                    <input type="text" size="7" maxlength="5" name="<?= $field ?>" 
                    value="<?= isset($_SESSION["valid" . ucfirst($field)]) ? $_SESSION["valid" . ucfirst($field)] : ''; ?>"/>
                </label><br/>
            <?php endforeach; ?>
        </fieldset>
    
        <fieldset id="Entry"> 
                <label><input type="submit" value="Add" name="add"></label>
        </fieldset>
    </form>


        <!--TODO -->
        <!-- Have a total for the day dynamically sum all Calorie Counts -->
        <!-- Make logo a link to home page that doesn't exist yet -->
        <!-- Combine this with BMI calculator-->


</html>