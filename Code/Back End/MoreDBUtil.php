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
?>