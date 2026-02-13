<?php
require_once "auth_session.php"; // Ensures user is logged in
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate inputs
    if (empty($_POST["comment"]) || empty($_POST["recipe_id"])) {
        // should handle error better but simple redirect for now
        header("location: index.php");
        exit;
    }

    $comment = trim($_POST["comment"]);
    $recipe_id = $_POST["recipe_id"];
    $user_id = $_SESSION["id"];

    $sql = "INSERT INTO comments (recipe_id, user_id, comment) VALUES (?, ?, ?)";

    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "iis", $recipe_id, $user_id, $comment);

        if (mysqli_stmt_execute($stmt)) {
            // Calculated new rating logic could go here, but omitted for simplicity
            header("location: view_recipe.php?id=" . $recipe_id);
            exit();
        }
        else {
            echo "Error submitting comment.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>
