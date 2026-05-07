<?php
require_once "config.php";
$error = "";
$success = "";

$lrn = $fname = $lname = $email = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $lrn = trim($_POST["lrn"]);
    $fname = trim($_POST["fname"]);
    $lname = trim($_POST["lname"]);
    $email = trim($_POST["email"]);
    $pass = $_POST["password"];
    $re_pass = $_POST["re_password"];

    if ($pass !== $re_pass) {
        $error = "Passwords do not match.";
    } else {
        // Updated Check: Check if LRN OR Gmail already exists
        $check = $mysqli->prepare("SELECT StudentID, Gmail FROM tb_StudentCredentials WHERE StudentID = ? OR Gmail = ?");
        $check->bind_param("ss", $lrn, $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['StudentID'] === $lrn) {
                $error = "This LRN/ID is already registered.";
            } else {
                $error = "This Gmail address is already in use.";
            }
        } else {
            $stmt = $mysqli->prepare("INSERT INTO tb_StudentCredentials (StudentID, FirstName, LastName, Gmail, Password) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $lrn, $fname, $lname, $email, $pass);
            if ($stmt->execute()) {
                $success = "Account created successfully!";
                $lrn = $fname = $lname = $email = "";
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Signup</title>
    <style>
        :root { --primary-blue: #003366; --bg-light: #f4f4f9; }
        body { font-family: 'Segoe UI', sans-serif; background-color: var(--bg-light); display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .login-card { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1); width: 100%; max-width: 400px; border-top: 5px solid #003366; }
        .input-group { text-align: left; margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: var(--primary-blue); }
        input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .btn-signup { width: 100%; padding: 12px; background-color: #003366; color: white; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; }
        .alert { padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; font-size: 0.9rem; }
        .alert-error { color: #721c24; background-color: #f8d7da; }
        .alert-success { color: #003366; background-color: #d4edda; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2 style="text-align:center; color:var(--primary-blue);">Student Signup</h2>
        
        <?php if($error): ?> <div class="alert alert-error"><?php echo $error; ?></div> <?php endif; ?>
        <?php if($success): ?> <div class="alert alert-success"><?php echo $success; ?></div> <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <label>Student LRN / ID</label>
                <input type="text" name="lrn" value="<?php echo htmlspecialchars($lrn); ?>" required>
            </div>
            <div style="display:flex; gap:10px;">
                <div class="input-group">
                    <label>First Name</label>
                    <input type="text" name="fname" value="<?php echo htmlspecialchars($fname); ?>" required>
                </div>
                <div class="input-group">
                    <label>Last Name</label>
                    <input type="text" name="lname" value="<?php echo htmlspecialchars($lname); ?>" required>
                </div>
            </div>
            <div class="input-group">
                <label>Gmail Address</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required placeholder="example@gmail.com">
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="input-group">
                <label>Re-type Password</label>
                <input type="password" name="re_password" required>
            </div>
            <button type="submit" class="btn-signup">Create Account</button>
            <p style="text-align:center; font-size:0.9rem; margin-top:15px;">
                Already registered? <a href="login.php" style="color:var(--primary-blue); text-decoration:none; font-weight:bold;">Login here</a>
            </p>
        </form>
    </div>
</body>
</html>