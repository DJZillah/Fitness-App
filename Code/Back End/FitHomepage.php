<?php
namespace Fitify;
include_once 'MoreDBUtil.php';
//session_start();

$weeklyCalSql = "SELECT TotalCal FROM Simple_Cal_Log WHERE LogDate 
>= DATE_SUB(NOW(), INTERVAL 7 DAY)"; //come back to this when user login bs is sorted
$weeklyCalDisplay;

$result = $conn->query($weeklyCalSql); //Execute query

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
WHERE created_at <= NOW() 
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
?>

<!DOCTYPE html> 
<head>
    <link href="FitifyRules.css" type="text/css" rel="stylesheet"/>

    <div class="TopofPage">
        <h1 id="Logo"> fitify </h1>
    
        <h1 id="Calories"> <a href=CalorieCounter.php> Calories </a> </h1>
        <h1 id="Workouts"> <a href="MidTermProduct2.html"> Workouts </a></h1>
        <h1 id="Milestones"> <a href=MidTermProduct1.html> Milestones </a></h1>
        <h1 id="Guidance"> <a href="MidTermDiscountClub.html"> Guidance</a> </h1>
        <h1 id="Placeholder"> <a href="MidTermFuturePage.html">Placeholder</a> </h1>

    </div>

</head>

<body>
    <h3> Hello! </h3> </br> <!-- Put username here when that is a thing later -->
    <p> This week's calorie intake: <?= $weeklyCalDisplay ?></p>
    <p> Your Current weight is: <?= $currentWeightDisplay ?></p>
    <p> Your BMI is currently: <?= $currentBMIDisplay?></p>



</body>