<?php
require_once "auth_session.php";
require_once "config.php";

if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {

    $comment_id = trim($_GET["id"]);
    $user_id = $_SESSION["id"];

    // Check if the comment belongs to the user
    $check_sql = "SELECT user_id, recipe_id FROM comments WHERE id = ?";
    if ($stmt = mysqli_prepare($link, $check_sql)) {
        mysqli_stmt_bind_param($stmt, "i", $comment_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result);
            if ($row['user_id'] == $user_id) {
                // Authorized to delete
                $delete_sql = "DELETE FROM comments WHERE id = ?";
                if ($del_stmt = mysqli_prepare($link, $delete_sql)) {
                    mysqli_stmt_bind_param($del_stmt, "i", $comment_id);
                    mysqli_stmt_execute($del_stmt);
                    header("location: view_recipe.php?id=" . $row['recipe_id']);
                }
            }
            else {
                echo "You do not have permission to delete this comment.";
            }
        }
        mysqli_stmt_close($stmt);
    }
}
?>
