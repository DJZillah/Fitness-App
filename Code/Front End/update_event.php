<?php
namespace Fitify;
header('Content-Type: application/json; charset=utf-8');
error_reporting(0);
session_start();
if (empty($_SESSION['user_id'])) {
    echo json_encode(['status'=>'error','message'=>'Not authenticated']);
    exit;
}

include 'MoreDBUtil.php';

$data = json_decode(file_get_contents('php://input'), true);
$id = intval($data['id'] ?? 0);
$exercise_id = intval($data['exercise_id'] ?? 0);
$date = $data['date'] ?? '';

if (!$id || !$exercise_id || !$date) {
    echo json_encode(['status'=>'error','message'=>'Invalid input']);
    exit;
}

$sql = "UPDATE scheduled_workouts SET exercise_id = ?, scheduled_date = ? WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isii", $exercise_id, $date, $id, $_SESSION['user_id']);

if ($stmt->execute()) {
    echo json_encode(['status'=>'success']);
} else {
    echo json_encode(['status'=>'error','message'=>$stmt->error]);
}