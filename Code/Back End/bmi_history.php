<?php
namespace Fitify;
include_once __DIR__ . '/MoreDBUtil.php';
session_start();
include_once __DIR__ . '/../Front End/header.php';

if (empty($_SESSION)) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch BMI history for the user
$bmiHistorySql = "SELECT height, weight, bmi, category, created_at 
                  FROM bmi_records 
                  WHERE user_id = $userId 
                  ORDER BY created_at DESC";
$result = $conn->query($bmiHistorySql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BMI History</title>
</head>
<body>
    <div class="container">
        <h2>Your BMI History</h2>

        <?php if ($result->num_rows > 0): ?>
            <table class="bmi-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Height (in)</th>
                        <th>Weight (lbs)</th>
                        <th>BMI</th>
                        <th>Category</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars(date("Y-m-d", strtotime($row['created_at']))) ?></td>
                            <td><?= htmlspecialchars($row['height']) ?></td>
                            <td><?= htmlspecialchars($row['weight']) ?></td>
                            <td><?= number_format($row['bmi'], 2) ?></td>
                            <td><?= htmlspecialchars($row['category']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No BMI records found.</p>
        <?php endif; ?>

    </div>
    <?php include_once __DIR__ . '/../Front End/footer.php'; ?>
</body>
</html>
