<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gourmet Haven</title>
    <!-- Importing Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <a href="index.php" class="logo">Gourmet Haven</a>
        <nav class="nav-links">
            <a href="index.php">Browse Recipes</a>
            
            <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                <?php if ($_SESSION["role"] === 'cook'): ?>
                    <a href="create_recipe.php" class="btn btn-primary">Publish Recipe</a>
                <?php
    endif; ?>
                
                <div class="user-info">
                    <span><?php echo htmlspecialchars($_SESSION["username"]); ?></span>
                    <span class="role-badge"><?php echo htmlspecialchars($_SESSION["role"]); ?></span>
                </div>
                <a href="logout.php" class="btn btn-secondary">Logout</a>
            <?php
else: ?>
                <a href="login.php">Login</a>
                <a href="register.php" class="btn btn-primary">Sign Up</a>
            <?php
endif; ?>
        </nav>
    </header>
    <main>
