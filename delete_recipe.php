<?php
require_once "auth_session.php";
require_once "config.php";

// Check role
if ($_SESSION["role"] !== 'cook') {
    header("location: index.php");
    exit;
}

if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $sql = "DELETE FROM recipes WHERE id = ?";

    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $param_id);
        $param_id = trim($_GET["id"]);

        if (mysqli_stmt_execute($stmt)) {
            header("location: index.php");
            exit();
        }
        else {
            echo "Oops! Something went wrong.";
        }
        mysqli_stmt_close($stmt);
    }
}
mysqli_close($link);
?>
