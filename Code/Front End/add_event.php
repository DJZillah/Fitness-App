<?php
namespace Fitify;
include 'MoreDBUtil.php';
session_start();
$user_id = $_SESSION['user_id'];

$data = json_decode(file_get_contents('php://input'), true);
$sql = "INSERT INTO scheduled_workouts (user_id, exercise_id, scheduled_date) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $user_id, $data['exercise_id'], $data['date']);
$stmt->execute();

echo json_encode(['status'=>'success','id'=>$stmt->insert_id]);
