<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Error</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container error-container">
        <h1>Oops! Something went wrong.</h1>
        <div class="error-message">
            <?php
if (isset($_SESSION['error_msg'])) {
    echo htmlspecialchars($_SESSION['error_msg']);
    unset($_SESSION['error_msg']); // Clear error after displaying
}
else {
    echo "An unexpected error occurred.";
}
?>
        </div>
        <a href="index.php" class="btn btn-primary">Return Home</a>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
