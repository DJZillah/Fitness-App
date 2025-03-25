<?php
namespace Fitify;

$servername = "fitify-db.ctq460w22gbq.us-east-2.rds.amazonaws.com";
$username = "root"; //I believe in root supremacy
$password = "fitify123";
$database = "fitifyDB"; //case sensitive

//Create connection
$conn = new \mysqli($servername, $username, $password, $database); // \ ensures it uses global php class

//Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
//echo "Connected successfully"; 
//use echo for debugging 

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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $feet = intval($_POST["feet"]);
    $inches = intval($_POST["inches"]);
    $weight_lbs = floatval($_POST["weight"]);

    // Convert height to total inches
    $height_in = ($feet * 12) + $inches;

    list($bmi, $category) = calculateBMI($height_in, $weight_lbs);

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO bmi_records (name, height, weight, bmi, category) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sddds", $name, $height_in, $weight_lbs, $bmi, $category);
    
    if ($stmt->execute()) {
        echo "<p>Record saved successfully!</p>";
        echo "<p>BMI: " . number_format($bmi, 2) . " ($category)</p>";
    } else {
        echo "<p>Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>BMI Calculator</title>
</head>
<link rel="stylesheet" type="text/css" href="CCSS.css">
<body>
    <h2>BMI Calculator</h2>
    <form method="POST">
        <label for="name">Name:</label>
        <input type="text" name="name" required><br>

        <label for="feet">Height:</label>
        <input type="number" name="feet" required placeholder="Feet" min="0">
        <input type="number" name="inches" required placeholder="Inches" min="0" max="11"><br>

        <label for="weight">Weight (lbs):</label>
        <input type="text" name="weight" required><br>

        <button type="submit">Calculate BMI</button>
    </form>
</body>
</html>