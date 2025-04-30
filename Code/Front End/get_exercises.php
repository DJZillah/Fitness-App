<?php
// get_exercises.php
namespace Fitify;
header('Content-Type: application/json; charset=utf-8');
error_reporting(0);
session_start();

include 'MoreDBUtil.php';

$sql = "SELECT id, exercise_name AS name
        FROM exercises
        ORDER BY exercise_name";
$stmt = $conn->prepare($sql);
$stmt->execute();
$res = $stmt->get_result();

echo json_encode($res->fetch_all(MYSQLI_ASSOC));
