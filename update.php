<?php include 'header.php'; ?>
<?php include 'db.php'; ?>
<?php
session_start();
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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $user['password'];

    $sql = "UPDATE users SET firstname = ?, lastname = ?, email = ?, username = ?, password = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $firstname, $lastname, $email, $username, $password, $user_id);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Profile updated successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}
?>

<h2>Update Profile</h2>
<form action="update.php" method="post">
    <div class="form-group">
        <label for="firstname">First Name</label>
        <input type="text" class="form-control" id="firstname" name="firstname" value="<?php echo htmlspecialchars($user['firstname']); ?>" required>
    </div>
    <div class="form-group">
        <label for="lastname">Last Name</label>
        <input type="text" class="form-control" id="lastname" name="lastname" value="<?php echo htmlspecialchars($user['lastname']); ?>" required>
    </div>
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
    </div>
    <div class="form-group">
        <label for="username">Username</label>
        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
    </div>
    <div class="form-group">
        <label for="password">Password (leave blank to keep current password)</label>
        <input type="password" class="form-control" id="password" name="password">
    </div>
    <button type="submit" class="btn btn-primary">Update</button>
</form>

<?php include 'footer.php'; ?>
