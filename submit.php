<?php
// Database file path - using an absolute path
$db_file = '/workspaces/bdayreminder/wishes.db';
$success_message = "";
$error_message = "";

try {
    // 1. Connect to SQLite database (or create it if it doesn't exist)
    $pdo = new PDO('sqlite:' . $db_file);
    // Set error mode to exceptions for better error handling
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Create table if it doesn't exist
    // The 'id' column is an INTEGER PRIMARY KEY AUTOINCREMENT for an auto-incrementing ID.
    // Added 'submission_date' to track when the wish was submitted.
    $pdo->exec("CREATE TABLE IF NOT EXISTS wishlists (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        dob TEXT NOT NULL,
        wishlist TEXT NOT NULL,
        submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 3. Check if the request method is GET and if form data is submitted
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        // Check if all required fields are present in $_GET
        if (isset($_GET['name']) && isset($_GET['dob']) && isset($_GET['wishlist'])) {
            $name = trim($_GET['name']);
            $dob = trim($_GET['dob']);
            $wishlist_text = trim($_GET['wishlist']);

            // Basic validation: ensure fields are not empty after trimming
            // Your HTML form already has 'required', but server-side validation is good practice.
            if (!empty($name) && !empty($dob) && !empty($wishlist_text)) {
                // 4. Prepare and execute INSERT statement to prevent SQL injection
                $stmt = $pdo->prepare("INSERT INTO wishlists (name, dob, wishlist) VALUES (:name, :dob, :wishlist)");
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':dob', $dob, PDO::PARAM_STR);
                $stmt->bindParam(':wishlist', $wishlist_text, PDO::PARAM_STR);

                if ($stmt->execute()) {
                    $success_message = "Thank you, " . htmlspecialchars($name) . "! Your wishlist has been submitted successfully.";
                } else {
                    $error_message = "Error: Could not submit your wishlist. Please try again.";
                }
            } else {
                $error_message = "Error: All fields are required. Please fill out the form completely and submit again.";
            }
        } else {
            // This handles cases where submit.php is accessed via GET but not all expected parameters are present.
            if (!empty($_GET)) { // Some GET params are present, but not all required ones
                 $error_message = "Error: Missing required form data. Please ensure Name, Date of Birth, and Wishlist are filled.";
            } else {
                // No GET parameters at all (e.g., direct access to submit.php without form submission)
                $error_message = "No data submitted. Please use the form on the previous page.";
            }
        }
    } else {
        // If the request method is not GET
        $error_message = "Invalid request method. Please submit the form correctly.";
    }

} catch (PDOException $e) {
    // Handle database connection or query errors
    // In a production environment, you might log this error instead of displaying it directly.
    $error_message = "Database error: " . htmlspecialchars($e->getMessage()) . ". Please contact support if this issue persists.";
}

// Close the database connection
$pdo = null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submission Status</title>
    <style>
        body { font-family: 'Arial', sans-serif; background-color: #f0f2f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px; box-sizing: border-box; text-align: center; }
        .container { background-color: #ffffff; padding: 30px 40px; border-radius: 10px; box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1); width: 100%; max-width: 500px; }
        h2 { color: #333; margin-bottom: 20px; }
        .message { padding: 15px; margin-bottom: 20px; border-radius: 5px; font-size: 16px; border: 1px solid transparent; }
        .success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        a.button-link { display: inline-block; margin-top: 20px; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; font-size: 16px; transition: background-color 0.3s ease; }
        a.button-link:hover { background-color: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Submission Status</h2>
        <?php if (!empty($success_message)): ?>
            <div class="message success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="message error">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        <a href="index.html" class="button-link">Go Back to Form</a>
    </div>
</body>
</html>