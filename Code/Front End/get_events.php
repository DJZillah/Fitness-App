<?php
namespace Fitify;
header('Content-Type: application/json; charset=utf-8');
error_reporting(0);
session_start();
if (empty($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

include 'MoreDBUtil.php';

$sql = "
    SELECT
    sw.id            AS id,
    e.exercise_name  AS title,
    sw.scheduled_date AS start,
    e.id              AS exercise_id
    FROM scheduled_workouts sw
    JOIN exercises e ON e.id = sw.exercise_id
    WHERE sw.user_id = ?
    ORDER BY sw.scheduled_date";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$res = $stmt->get_result();

echo json_encode($res->fetch_all(MYSQLI_ASSOC));
