<?php
session_start(); // Start session to store login state if needed in future

$db_file_users = '/workspaces/bdayreminder/users.db';
$login_message = "";
$login_success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['phone']) && isset($_POST['password'])) {
        $phone_number = trim($_POST['phone']);
        $password = trim($_POST['password']);

        if (empty($phone_number) || empty($password)) {
            $login_message = "Phone number and password are required.";
        } elseif (!preg_match('/^\d{6}$/', $password)) {
            $login_message = "Password must be 6 digits.";
        } else {
            try {
                $pdo_users = new PDO('sqlite:' . $db_file_users);
                $pdo_users->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $stmt = $pdo_users->prepare("SELECT password_hash FROM users WHERE phone_number = :phone_number");
                $stmt->bindParam(':phone_number', $phone_number, PDO::PARAM_STR);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['password_hash'])) {
                    // Login successful
                    $_SESSION['user_phone'] = $phone_number; // Store phone in session
                    $login_message = "Login successful! Welcome, " . htmlspecialchars($phone_number) . ".";
                    $login_success = true;
                    // In a real app, you'd redirect to a dashboard: header('Location: dashboard.php'); exit;
                } else {
                    $login_message = "Invalid phone number or password.";
                }
            } catch (PDOException $e) {
                $login_message = "Database error: " . htmlspecialchars($e->getMessage());
            }
            $pdo_users = null;
        }
    } else {
        $login_message = "Please enter both phone number and password.";
    }
} else {
    // If accessed directly via GET, or not a POST request
    $login_message = "Please login using the form.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Status</title>
    <style>
        body { font-family: 'Arial', sans-serif; background-color: #f0f2f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px; box-sizing: border-box; text-align: center; }
        .container { background-color: #ffffff; padding: 30px 40px; border-radius: 10px; box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1); width: 100%; max-width: 500px; }
        h2 { color: #333; margin-bottom: 20px; }
        .message { padding: 15px; margin-bottom: 20px; border-radius: 5px; font-size: 16px; border: 1px solid transparent; }
        .success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        a { color: #007bff; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login Status</h2>
        <div class="message <?php echo $login_success ? 'success' : 'error'; ?>"><?php echo $login_message; ?></div>
        <?php if (!$login_success): ?>
            <p><a href="login_user.html">Try Login Again</a></p>
        <?php endif; ?>
        <p><a href="register_user.html">Register a new account</a></p>
    </div>
</body>
</html>