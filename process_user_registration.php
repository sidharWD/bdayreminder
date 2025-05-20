<?php
$db_file_users = '/workspaces/bdayreminder/users.db';
$success_message = "";
$error_message = "";

try {
    $pdo_users = new PDO('sqlite:' . $db_file_users);
    $pdo_users->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo_users->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        phone_number TEXT UNIQUE NOT NULL,
        password_hash TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['phone']) && isset($_POST['password'])) {
            $phone_number = trim($_POST['phone']);
            $password = trim($_POST['password']);

            // Validate phone number (basic: numeric, reasonable length)
            if (!preg_match('/^[0-9]{10,15}$/', $phone_number)) {
                $error_message = "Invalid phone number format. Please enter 10-15 digits.";
            }
            // Validate password (must be 6 digits)
            elseif (!preg_match('/^\d{6}$/', $password)) {
                $error_message = "Password must be exactly 6 digits.";
            } else {
                // Check if phone number already exists
                $stmt_check = $pdo_users->prepare("SELECT id FROM users WHERE phone_number = :phone_number");
                $stmt_check->bindParam(':phone_number', $phone_number, PDO::PARAM_STR);
                $stmt_check->execute();

                if ($stmt_check->fetchColumn()) {
                    $error_message = "This phone number is already registered. Please <a href='login_user.html'>login</a> or use a different number.";
                } else {
                    // Hash the password
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);

                    $stmt_insert = $pdo_users->prepare("INSERT INTO users (phone_number, password_hash) VALUES (:phone_number, :password_hash)");
                    $stmt_insert->bindParam(':phone_number', $phone_number, PDO::PARAM_STR);
                    $stmt_insert->bindParam(':password_hash', $password_hash, PDO::PARAM_STR);

                    if ($stmt_insert->execute()) {
                        $success_message = "Registration successful! You can now <a href='login_user.html'>login</a>.";
                    } else {
                        $error_message = "Error: Could not register your account. Please try again.";
                    }
                }
            }
        } else {
            $error_message = "Error: Phone number and password are required.";
        }
    } else {
        $error_message = "Invalid request method.";
    }

} catch (PDOException $e) {
    $error_message = "Database error: " . htmlspecialchars($e->getMessage());
}

$pdo_users = null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Status</title>
    <link rel="stylesheet" href="style.css"> <!-- You can link to a shared CSS or use the style block below -->
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
        <h2>Registration Status</h2>
        <?php if (!empty($success_message)): ?><div class="message success"><?php echo $success_message; ?></div><?php endif; ?>
        <?php if (!empty($error_message)): ?><div class="message error"><?php echo $error_message; ?></div><?php endif; ?>
        <?php if (empty($success_message)): ?><p><a href="register_user.html">Try Registration Again</a></p><?php endif; ?>
        <p><a href="login_user.html">Go to Login Page</a></p>
    </div>
</body>
</html>