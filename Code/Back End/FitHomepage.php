<?php
namespace Fitify;
include_once 'MoreDBUtil.php';
session_start();

/*echo '<pre>'; //use for debugging
print_r($_SESSION);
echo '</pre>'; */

if (empty($_SESSION)) //if somehow not logged in
{
    Header("Location: login.php");
}
$weeklyCalSql = "SELECT TotalCal FROM Simple_Cal_Log WHERE LogDate 
>= DATE_SUB(NOW(), INTERVAL 7 DAY) AND user_id = " . $_SESSION['user_id']; //perhaps make error handling to check if username in session has associated user_id in database
$weeklyCalDisplay; //when user-specific stuff is sorted, sort by user_id

$result = $conn->query($weeklyCalSql); //Execute query, need to do this when pulling from DB

if ($result->num_rows > 0) 
{
    $weeklyTotal = 0;
    while ($row = $result->fetch_assoc()) 
    {
        $weeklyTotal += $row["TotalCal"]; //sum up all calories
    }

    $weeklyCalDisplay = $weeklyTotal; //display total calories for week on webpage
} 
else 
{
    $weeklyCalDisplay = "No calories logged this week.";   
}
//end cal intake logic

//user's stats display logic
$currentWeightSql = "SELECT weight FROM weight_log 
WHERE created_at <= NOW() 
ORDER BY created_at DESC 
LIMIT 1"; //will have to modify this later to account for current user's userid

$result1 = $conn->query($currentWeightSql);
$currentWeightDisplay;

if ($result1->num_rows > 0) 
{
    $row = $result1->fetch_assoc();
    $currentWeight = $row['weight'];
    $currentWeightDisplay = $currentWeight;
} 
else 
{
    $currentWeightDisplay = "No weight logged yet";
}


//end current weight logic

$currentBMISql = "SELECT bmi FROM bmi_records 
WHERE created_at <= NOW() AND name = '" . $_SESSION['user'] . "' 
ORDER BY created_at DESC 
LIMIT 1";

$result2 = $conn->query($currentBMISql);
$currentBMIDisplay;

if ($result2->num_rows > 0) 
{
    $row = $result2->fetch_assoc();
    $currentBMI = $row['bmi'];
    $currentBMIDisplay = $currentBMI;
} 
else 
{
    $currentBMItDisplay = "No BMI logged yet";
}

//end php, begin html
?>

<!DOCTYPE html> 

<style> /* Additional rules for this page */
.TopofPage {
    text-align:center;
    justify-content: center;
    display: flex;
    font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif;
    border-style: groove;
    text-align: center;
    padding: 2%;    
    border: 1px solid black;
    margin: 0;
    border-width: 5px;
    background-image: url('DarkSwirl.png');
    background-size: cover;        /* Scales image to cover entire fieldset */
    background-repeat: no-repeat;  /* Prevents tiling */
    background-position: center;
    background-color: transparent;
}
.TopofPage a {
    background-color:transparent;
    background-image:none;
    color: white;
}
#Calories {
    line-height: 206%;
    font-size: 132%;
}
.Hello {
    background-image:none;
    background-color:transparent;
}
.Dash {
    background-image:url('DarkBG.jpg');
    border: .5px solid rgb(92, 91, 91);
    width: 50%;
    margin: 8% auto;
    border-width: 1.6px;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;    
}
.Dash p {
    color: rgb(201, 197, 7);
    text-shadow: .7px .7px .7px rgb(92, 91, 91);
    font-family: 'Orbitron', sans-serif;
    background-image:none;
}
</style>

<head>
    <link href="FitifyRules2.css" type="text/css" rel="stylesheet"/>

    <div class="TopofPage">
        <h1 id="Logo"> fitify </h1>
    
        <h1 id="Calories"> <a href="CalorieCounter.php"> Health & Nutrition </a> </h1> <!-- Perhaps combine HMI & Calorie Counter pages into one later. -->
        <h1 id="Workouts"> <a href="WorkoutTracker.php"> Workouts </a></h1>
        <h1 id="Milestones"> <a href="View_milestones.php"> Milestones </a></h1>
        <h1 id="Guidance"> <a href="MidTermDiscountClub.html"> Guidance</a> </h1>
        <h1 id="Achievements"> <a href="Accolades.php">Accolades</a> </h1>

    </div>

    <?php
    if ($_SERVER["REQUEST_METHOD"] === "POST") //if logout is clicked
    {
        session_destroy();
        header("Location: login.php"); //back to login page
    }
    ?> 

    <div class = "Hello"> 
        <h2> Hello, <?= $_SESSION['user']; ?> </h2>
        <form method = "POST">
            <input type="submit" value="Logout">
        </form>
    </div>

</head>

<body>
    <div class = "Dash">
        <p> This week's calorie intake: <?= $weeklyCalDisplay ?>cal</p> <br/>
        <p> Your Current weight is: <?= $currentWeightDisplay ?>lbs</p> <br/>
        <p> Your BMI is currently <?= $currentBMIDisplay?></p> <br/>
        <p> Your last logged workout was on <i> date </i> and you burned <i> amount </i> calories </p> <br/>
        <p> Your latest achievement was <i>placeholder</i> </p> <br/>
    </div>
</body>
<!--TODO-->
<!--Make an alert system; like reminding the user to enter their calories for the day. -->
<!--Make an achievements/badges system, maybe work that in with the milestone feature that doesnt exist yet.-->
<!--get this linked with the weight tracker bs and update that table. -->