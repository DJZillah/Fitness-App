<?php
namespace Fitify;
include_once 'MoreDBUtil.php';
//include_once 'FitifyRules.css';


$totalCal = 0;
function validate($var) 
{
    return is_numeric($var); //simple input validation that checks if is number 
}

if ($_SERVER["REQUEST_METHOD"] == "POST") { //patience, all this activates upon form submission (submit button.)

    $breakfastCal = $_POST["breakfast"];
    $lunchCal = $_POST["lunch"];
    $dinnerCal = $_POST["dinner"];
    $miscCal = $_POST["misc"];

    $validFast = false; //start of input validation
    $validFast = false;
    $validDinner = false;
    $validMisc = false;

    if (validate($breakfastCal))
    {
        $validFast = true;
    }   
    if (validate($lunchCal)) 
    {
        $validLunch = true;
    }
    if (validate($dinnerCal)) 
    {
        $validDinner = true;
    }
    if (validate($miscCal))
    {
        $validMisc = true;
    }

}

?> 

<!DOCTYPE HTML> <!--For some reason the namespace declaration won't work unless its the very first thing declared in a file. -->
<html>
    <head>
        <link href="FitifyRules.css" type="text/css" rel="stylesheet"/>
        <p id="Logo"> fitify </p>

    </head>
<h1> Your Calorie Counter </h1>

    
        <form action="CalorieCounter.php?Submit=true" method="post"> 
            <fieldset id = "Entry">
                <legend> Today's Calories </legend>
                <label> Breakfast: <input type="text" size="7" maxlength="5" name="breakfast" value="<?php echo isset($_SESSION['validBreak']) ? $_SESSION['validBreak'] : ''; ?>"/> <br/>
                <label> Lunch: <input type="text" size="10" maxlength="5" name="lunch" value="<?php echo isset($_SESSION['validLunch']) ? $_SESSION['validLunch'] : ''; ?>"/> <br/>
                
                <label> Dinner: <input type="text" size="10" maxlength="5" name="dinner" value="<?php echo isset($_SESSION['validDinner']) ? $_SESSION['validDinner'] : ''; ?>">  <br/>
                <label> Miscellaneous: <input type="text" size="3.2" maxlength="4" name="misc" value="<?php echo isset($_SESSION['validMisc']) ? $_SESSION['validMisc'] : ''; ?>"> </label> <br /> 
            </fieldset>
            <label><input type="submit" value="Add" name ="add"> </label>
        </form>
        <!--TODO -->
        <!-- Have a total for the day dynamically sum all Calorie Counts -->
        <!-- Make number validation -->
        <!-- Keep a current date and time, and figure out how/if that would interact with the database --> 
        <!-- Make logo a link to home page that doesn't exist yet -->


</html>