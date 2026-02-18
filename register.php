<?php
session_start();
require_once __DIR__ . '/api/db.php';

$name = $email = $password = $confirm_password = "";
$name_err = $email_err = $password_err = $confirm_password_err = $success_msg = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Name validation
    if(empty(trim($_POST["name"]))){
        $name_err = "Please enter your name.";
    } else {
        $name = trim($_POST["name"]);
    }

    // Email validation
    if(empty(trim($_POST["email"]))){
        $email_err = "Please enter an email.";
    } else {
        $email = trim($_POST["email"]);
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if($stmt->num_rows > 0){
            $email_err = "This email is already registered.";
        }
        $stmt->close();
    }

    // Password validation
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter a password.";
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "Password must be at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Confirm password
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Please confirm your password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if($password != $confirm_password){
            $confirm_password_err = "Passwords do not match.";
        }
    }

    // Insert
    if(empty($name_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err)){
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, 'user')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $name, $email, $hashed_password);
        if($stmt->execute()){
            header('Location: login.php');
            exit;
        } else {
            $success_msg = "Something went wrong. Please try again.";
        }
        $stmt->close();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width: 500px;">
    <h2>Register</h2>
    <?php if($success_msg) echo '<div class="alert alert-success">'.$success_msg.'</div>'; ?>
    <form action="" method="post">
        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>">
            <span class="text-danger"><?php echo $name_err; ?></span>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>">
            <span class="text-danger"><?php echo $email_err; ?></span>
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control">
            <span class="text-danger"><?php echo $password_err; ?></span>
        </div>
        <div class="mb-3">
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control">
            <span class="text-danger"><?php echo $confirm_password_err; ?></span>
        </div>
        <button type="submit" class="btn btn-primary w-100">Register</button>
        <p class="mt-2">Already have an account? <a href="login.php">Login here</a></p>
    </form>
</div>
</body>
</html>
