<?php include 'header.php'; ?>
<?php include 'db.php'; ?>
<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<h2>Profile</h2>
<div class="card">
    <div class="card-body">
        <h5 class="card-title"><?php echo htmlspecialchars($user['username']); ?></h5>
        <p class="card-text">Email: <?php echo htmlspecialchars($user['email']); ?></p>
        <p class="card-text">First Name: <?php echo htmlspecialchars($user['firstname']); ?></p>
        <p class="card-text">Last Name: <?php echo htmlspecialchars($user['lastname']); ?></p>
        <a href="update.php" class="btn btn-primary">Update Profile</a>
        <form action="delete.php" method="post" class="d-inline">
            <button type="submit" class="btn btn-danger">Delete Account</button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
