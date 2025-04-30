<?php
namespace Fitify;
include_once 'MoreDBUtil.php';
session_start();

if (empty($_SESSION)) {
    header("Location: login.php");
    exit();
}

$conn = new \mysqli("fitify-db.ctq460w22gbq.us-east-2.rds.amazonaws.com", "root", "fitify123", "fitifyDB");
$userId = $_SESSION['user_id'];
$bmiData = $conn->query("SELECT bmi, created_at FROM bmi_records WHERE user_id = $userId ORDER BY created_at ASC");
$bmiValues = [];
$bmiDates = [];
while ($row = $bmiData->fetch_assoc()) {
    $bmiValues[] = round($row['bmi'], 2);
    $bmiDates[] = $row['created_at'];
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@1.1.0"></script>
    <style>
        body { margin: 0; }
        canvas { display: block; max-width: 100%; }
    </style>
</head>
<body>
<canvas id="bmiChart"></canvas>
<script>
const bmiCtx = document.getElementById('bmiChart').getContext('2d');
new Chart(bmiCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode($bmiDates) ?>,
        datasets: [{
            label: 'BMI',
            data: <?= json_encode($bmiValues) ?>,
            borderColor: 'blue',
            borderWidth: 2,
            fill: false,
            tension: 0.5,
            pointBackgroundColor: <?= json_encode(array_map(function($val) {
                if ($val < 18.5) return 'orange';
                if ($val < 25) return 'green';
                if ($val < 30) return 'yellow';
                return 'red';
            }, $bmiValues)) ?>
        }]
    },
    options: {
        plugins: {
            annotation: {
                annotations: {
                    underweight: {
                        type: 'box',
                        yMin: 0,
                        yMax: 18.5,
                        backgroundColor: 'rgba(255, 165, 0, 0.1)',
                        label: {
                            content: 'Underweight',
                            enabled: true,
                            position: 'start'
                        }
                    },
                    normal: {
                        type: 'box',
                        yMin: 18.5,
                        yMax: 24.9,
                        backgroundColor: 'rgba(0, 255, 0, 0.1)',
                        label: {
                            content: 'Normal weight',
                            enabled: true,
                            position: 'start'
                        }
                    },
                    overweight: {
                        type: 'box',
                        yMin: 25,
                        yMax: 29.9,
                        backgroundColor: 'rgba(255, 255, 0, 0.1)',
                        label: {
                            content: 'Overweight',
                            enabled: true,
                            position: 'start'
                        }
                    },
                    obese: {
                        type: 'box',
                        yMin: 30,
                        yMax: 50,
                        backgroundColor: 'rgba(255, 0, 0, 0.1)',
                        label: {
                            content: 'Obese',
                            enabled: true,
                            position: 'start'
                        }
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: false,
                suggestedMax: 40
            }
        }
    }
});
</script>
</body>
</html>
