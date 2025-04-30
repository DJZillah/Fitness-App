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

if (!$id) {
    echo json_encode(['status'=>'error','message'=>'Invalid input']);
    exit;
}

$sql = "DELETE FROM scheduled_workouts WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $_SESSION['user_id']);

if ($stmt->execute()) {
    echo json_encode(['status'=>'success']);
} else {
    echo json_encode(['status'=>'error','message'=>$stmt->error]);
}