<?php
namespace Fitify;
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

require_once __DIR__ . '/../Back End/MoreDBUtil.php';

$user_id = $_SESSION['user_id'];
$events = [];

$sql = "SELECT LogDate, TotalCal FROM Simple_Cal_Log WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $date = date('Y-m-d', strtotime($row['LogDate']));
    $events[] = [
        'title' => $row['TotalCal'] . ' Calories',
        'start' => $date,
        'allDay' => true
    ];
}

echo json_encode($events);
?>
