<?php
namespace Fitify;
include_once dirname(__DIR__, 2) . '/Back End/MoreDBUtil.php';
session_start();

if (empty($_SESSION)) {
    header("Location: ../login.php");
    exit();
}

$conn = new \mysqli("fitify-db.ctq460w22gbq.us-east-2.rds.amazonaws.com", "root", "fitify123", "fitifyDB");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BMI Chart</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation"></script>
    <link rel="stylesheet" href="../FitifyRules.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
        }
        #chart-container {
            width: 90%;
            max-width: 700px;
            height: 400px;
        }
        .legend {
            margin-top: 10px;
            display: flex;
            justify-content: center;
            gap: 1em;
            flex-wrap: wrap;
        }
        .legend span {
            display: flex;
            align-items: center;
            gap: 0.5em;
        }
        .color-box {
            width: 15px;
            height: 15px;
            display: inline-block;
            border: 1px solid #ccc;
        }
        .underweight { background-color: orange; }
        .normal { background-color: green; }
        .overweight { background-color: yellow; }
        .obese { background-color: red; }
    </style>
</head>
<body>
    <div id="chart-container">
        <canvas id="bmiChart"></canvas>
    </div>

    <div class="legend">
        <span><div class="color-box underweight"></div>Underweight</span>
        <span><div class="color-box normal"></div>Normal</span>
        <span><div class="color-box overweight"></div>Overweight</span>
        <span><div class="color-box obese"></div>Obese</span>
    </div>

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
                    tension: 0.3,
                    pointBackgroundColor: <?= json_encode(array_map(function($val) {
                        if ($val < 18.5) return 'orange';
                        if ($val < 25) return 'green';
                        if ($val < 30) return 'yellow';
                        return 'red';
                    }, $bmiValues)) ?>
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    annotation: {
                        annotations: {
                            underweight: {
                                type: 'box',
                                yMin: 0,
                                yMax: 18.5,
                                backgroundColor: 'rgba(255, 165, 0, 0.1)',
                                borderWidth: 0
                            },
                            normal: {
                                type: 'box',
                                yMin: 18.5,
                                yMax: 24.9,
                                backgroundColor: 'rgba(0, 255, 0, 0.1)',
                                borderWidth: 0
                            },
                            overweight: {
                                type: 'box',
                                yMin: 25,
                                yMax: 29.9,
                                backgroundColor: 'rgba(255, 255, 0, 0.1)',
                                borderWidth: 0
                            },
                            obese: {
                                type: 'box',
                                yMin: 30,
                                yMax: 50,
                                backgroundColor: 'rgba(255, 0, 0, 0.1)',
                                borderWidth: 0
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'BMI'
                        },
                        min: 10,
                        max: 50
                    }
                }
            }
        });
    </script>
</body>
</html>
