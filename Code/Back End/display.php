<?php 
namespace Fitify;
require 'MoreDBUtil.php';

$conn->select_db("fitifyDB");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT user_id, username, email, age, weight, height FROM users";
$result = $conn->query($sql);

// Handle user deletion
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_user_id'])) {
    $delete_user_id = $_POST['delete_user_id'];

    // Prepare the DELETE statement
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $delete_user_id);

    if ($stmt->execute()) {
        echo "User deleted successfully.";
    } else {
        echo "Error deleting user: " . $conn->error;
    }

    $stmt->close();
}
?>
<!-- This displays what's in the users table -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Data</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">

    <h2>Stored Users</h2>
    <table class="table">
        <thead>
            <tr>
                <th>User ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Age</th>
                <th>Weight</th>
                <th>Height</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row["user_id"] ?></td>
                    <td><?= $row["username"] ?></td>
                    <td><?= $row["email"] ?></td>
                    <td><?= $row["age"] ?></td>
                    <td><?= $row["weight"] ?> lbs</td>
                    <td><?= $row["height"] ?> in</td>
                    <td>
                        <!-- Edit button links to edit.php with the user_id -->
                        <a href="edit.php?user_id=<?= $row['user_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                        
                        <!-- Delete button triggers the form submission -->
                        <form action="display.php" method="POST" style="display:inline;">
                            <input type="hidden" name="delete_user_id" value="<?= $row['user_id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</body>
</html>

<?php
$conn->close();
?>
