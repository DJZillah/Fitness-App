<?php
session_start();

$servername = "fitify-db.ctq460w22gbq.us-east-2.rds.amazonaws.com";
$username = "root";
$password = "fitify123";
$database = "fitifyDB";
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$userId = $_SESSION['user_id'];
$filter = $_GET['filter'] ?? 'all';
$filterQuery = "1=1";

if ($filter === 'week') {
    $filterQuery = "created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
} elseif ($filter === 'month') {
    $filterQuery = "MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())";
}

$chartQuery = $conn->query("SELECT weight, created_at FROM weight_log WHERE user_id = $userId AND $filterQuery ORDER BY created_at ASC");
$weights = [];
$dates = [];
while ($row = $chartQuery->fetch_assoc()) {
    $weights[] = $row['weight'];
    $dates[] = $row['created_at'];
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Weight Chart</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        #chart-container {
            width: 600px;
            height: 300px;
        }
        canvas {
            width: 100% !important;
            height: 100% !important;
        }
    </style>
</head>
<body>
<div id="chart-container">
    <canvas id="weightChart" width="600" height="300"></canvas>
</div>

<script>
    const ctx = document.getElementById('weightChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($dates) ?>,
            datasets: [{
                label: 'Weight (lbs)',
                data: <?= json_encode($weights) ?>,
                borderColor: 'blue',
                borderWidth: 2,
                fill: true,
                tension: 0.5
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: false,
            scales: {
                y: { beginAtZero: false }
            }
        }
    });
</script>
</body>
</html>