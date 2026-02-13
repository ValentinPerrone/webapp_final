<?php
require_once "config.php";
session_start();

if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    // Prepare a select statement
    $sql = "SELECT r.*, u.username FROM recipes r JOIN users u ON r.user_id = u.id WHERE r.id = ?";

    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $param_id);
        $param_id = trim($_GET["id"]);

        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) == 1) {
                $row = mysqli_fetch_assoc($result);
                $title = $row["title"];
                $description = $row["description"];
                $category = $row["category"];
                $rating = $row["rating"];
                $author = $row["username"];
                $created_at = $row["created_at"];
                $recipe_id = $row["id"];
            }
            else {
                header("location: error.php");
                exit();
            }
        }
        else {
            echo "Oops! Something went wrong. Please try again later.";
        }
        mysqli_stmt_close($stmt);
    }

    // Fetch Ingredients
    $ingredients = [];
    $sql_ing = "SELECT * FROM ingredients WHERE recipe_id = ?";
    if ($stmt_ing = mysqli_prepare($link, $sql_ing)) {
        mysqli_stmt_bind_param($stmt_ing, "i", $param_id);
        mysqli_stmt_execute($stmt_ing);
        $res_ing = mysqli_stmt_get_result($stmt_ing);
        while ($ing = mysqli_fetch_assoc($res_ing)) {
            $ingredients[] = $ing;
        }
        mysqli_stmt_close($stmt_ing);
    }

    // Fetch Comments
    $comments = [];
    $sql_com = "SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.recipe_id = ? ORDER BY c.created_at DESC";
    if ($stmt_com = mysqli_prepare($link, $sql_com)) {
        mysqli_stmt_bind_param($stmt_com, "i", $param_id);
        mysqli_stmt_execute($stmt_com);
        $res_com = mysqli_stmt_get_result($stmt_com);
        while ($com = mysqli_fetch_assoc($res_com)) {
            $comments[] = $com;
        }
        mysqli_stmt_close($stmt_com);
    }
}
else {
    header("location: error.php");
    exit();
}
?>

<?php include 'header.php'; ?>

<div class="container">
    <div style="background:white; padding:2rem; border-radius:16px; box-shadow:var(--card-shadow);">
        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
            <div>
                <h1 style="margin-bottom:0.5rem;"><?php echo htmlspecialchars($title); ?></h1>
                <p style="color:#666; margin-top:0;">
                    By <strong><?php echo htmlspecialchars($author); ?></strong> | 
                    In <span class="role-badge" style="background:#666;"><?php echo htmlspecialchars($category); ?></span> | 
                    Rating: <span class="rating"><?php echo $rating; ?>/5.0</span>
                </p>
            </div>
            
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'cook'): ?>
                <div style="display:flex; gap:0.5rem;">
                    <a href="edit_recipe.php?id=<?php echo $recipe_id; ?>" class="btn btn-secondary">Edit</a>
                    <a href="delete_recipe.php?id=<?php echo $recipe_id; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this recipe?');">Delete</a>
                </div>
            <?php
endif; ?>
        </div>

        <hr style="border:0; border-top:1px solid #eee; margin:2rem 0;">

        <div style="display:grid; grid-template-columns: 1fr 2fr; gap:3rem;">
            <div>
                <h3>Ingredients</h3>
                <ul style="list-style:none; padding:0;">
                    <?php foreach ($ingredients as $ing): ?>
                        <li style="padding:0.5rem 0; border-bottom:1px solid #f0f0f0;">
                            <strong><?php echo htmlspecialchars($ing['name']); ?>:</strong> <?php echo htmlspecialchars($ing['quantity']); ?>
                        </li>
                    <?php
endforeach; ?>
                </ul>
            </div>
            <div>
                <h3>Instructions</h3>
                <p style="white-space: pre-wrap;"><?php echo htmlspecialchars($description); ?></p>
            </div>
        </div>
    </div>

    <!-- Comments Section -->
    <div class="comment-section">
        <h3>Comments (<?php echo count($comments); ?>)</h3>
        
        <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
            <form action="submit_comment.php" method="post" style="margin-bottom:2rem;">
                <input type="hidden" name="recipe_id" value="<?php echo $recipe_id; ?>">
                <div class="form-group">
                    <textarea name="comment" rows="3" placeholder="Leave a comment (and rate functionality coming soon)..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Post Comment</button>
            </form>
        <?php
else: ?>
            <p><a href="login.php" style="color:var(--primary-color);">Login</a> to leave a comment.</p>
        <?php
endif; ?>

        <?php foreach ($comments as $comment): ?>
            <div class="comment">
                <div class="comment-header">
                    <span class="comment-author"><?php echo htmlspecialchars($comment['username']); ?></span>
                    <div>
                        <span class="comment-date"><?php echo $comment['created_at']; ?></span>
                        <?php if (isset($_SESSION['id']) && $_SESSION['id'] == $comment['user_id']): ?>
                            <a href="delete_comment.php?id=<?php echo $comment['id']; ?>&recipe_id=<?php echo $recipe_id; ?>" 
                               style="color:#e63946; font-size:0.8rem; margin-left:10px; text-decoration:none;"
                               onclick="return confirm('Delete your comment?');">Delete</a>
                        <?php
    endif; ?>
                    </div>
                </div>
                <div><?php echo htmlspecialchars($comment['comment']); ?></div>
            </div>
        <?php
endforeach; ?>

        <?php if (empty($comments)): ?>
            <p style="color:#888; font-style:italic;">No comments yet. Be the first!</p>
        <?php
endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
<?php mysqli_close($link); ?>
