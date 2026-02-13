<?php
require_once "auth_session.php";
require_once "config.php";

if ($_SESSION["role"] !== 'cook') {
    header("location: index.php");
    exit;
}

$title = $description = $category = $rating = $ingredients_text = "";
$id = 0;

// Check existence of id parameter before processing further
if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $id = trim($_GET["id"]);

    // Prepare a select statement
    $sql = "SELECT * FROM recipes WHERE id = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $param_id);
        $param_id = $id;

        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) == 1) {
                $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                $title = $row["title"];
                $description = $row["description"];
                $category = $row["category"];
                $rating = $row["rating"];

                // Fetch existing ingredients
                $sql_ing = "SELECT * FROM ingredients WHERE recipe_id = ?";
                if ($stmt_ing = mysqli_prepare($link, $sql_ing)) {
                    mysqli_stmt_bind_param($stmt_ing, "i", $id);
                    mysqli_stmt_execute($stmt_ing);
                    $res_ing = mysqli_stmt_get_result($stmt_ing);
                    $ing_lines = [];
                    while ($ing = mysqli_fetch_assoc($res_ing)) {
                        $ing_lines[] = $ing['name'] . ": " . $ing['quantity'];
                    }
                    $ingredients_text = implode("\n", $ing_lines);
                }

            }
            else {
                header("location: error.php");
                exit();
            }
        }
        else {
            echo "Oops! Something went wrong.";
        }
        mysqli_stmt_close($stmt);
    }
}
elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST["id"];
    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $category = trim($_POST["category"]);
    $rating = trim($_POST["rating"]);
    $ingredients_text = trim($_POST["ingredients"]);

    if (!empty($title) && !empty($description) && !empty($category)) {
        mysqli_begin_transaction($link);
        try {
            // Update Recipe
            $sql = "UPDATE recipes SET title=?, description=?, category=?, rating=? WHERE id=?";
            $stmt = mysqli_prepare($link, $sql);
            mysqli_stmt_bind_param($stmt, "sssdi", $title, $description, $category, $rating, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            // Update Ingredients (Delete all and Re-insert)
            $sql_del = "DELETE FROM ingredients WHERE recipe_id=?";
            $stmt_del = mysqli_prepare($link, $sql_del);
            mysqli_stmt_bind_param($stmt_del, "i", $id);
            mysqli_stmt_execute($stmt_del);
            mysqli_stmt_close($stmt_del);

            // Insert new ingredients
            $lines = explode("\n", $ingredients_text);
            $sql_ing = "INSERT INTO ingredients (recipe_id, name, quantity) VALUES (?, ?, ?)";
            $stmt_ing = mysqli_prepare($link, $sql_ing);

            foreach ($lines as $line) {
                $parts = explode(":", $line);
                $name = trim($parts[0]);
                $qty = isset($parts[1]) ? trim($parts[1]) : "As needed"; // Default if no quantity

                if (!empty($name)) {
                    mysqli_stmt_bind_param($stmt_ing, "iss", $id, $name, $qty);
                    mysqli_stmt_execute($stmt_ing);
                }
            }
            mysqli_stmt_close($stmt_ing);

            mysqli_commit($link);
            header("location: view_recipe.php?id=" . $id);
            exit();

        }
        catch (Exception $e) {
            mysqli_rollback($link);
            echo "Error: " . $e->getMessage();
        }
    }
}
?>

<?php include 'header.php'; ?>

<div class="container container-narrow">
    <h2>Edit Recipe</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <input type="hidden" name="id" value="<?php echo $id; ?>"/>
        <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($title); ?>">
        </div>
        <div class="form-group">
            <label>Category</label>
            <select name="category">
                <option value="Main Course" <?php if ($category == 'Main Course')
    echo 'selected'; ?>>Main Course</option>
                <option value="Breakfast" <?php if ($category == 'Breakfast')
    echo 'selected'; ?>>Breakfast</option>
                <option value="Dessert" <?php if ($category == 'Dessert')
    echo 'selected'; ?>>Dessert</option>
                <option value="Salad" <?php if ($category == 'Salad')
    echo 'selected'; ?>>Salad</option>
                <option value="Vegetarian" <?php if ($category == 'Vegetarian')
    echo 'selected'; ?>>Vegetarian</option>
                <option value="Dinner" <?php if ($category == 'Dinner')
    echo 'selected'; ?>>Dinner</option>
            </select>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="5"><?php echo htmlspecialchars($description); ?></textarea>
        </div>
        <div class="form-group">
            <label>Ingredients (One per line, format "Name: Quantity")</label>
            <textarea name="ingredients" rows="5"><?php echo htmlspecialchars($ingredients_text); ?></textarea>
        </div>
        <div class="form-group">
             <label>Rating (Admin Override)</label>
             <input type="number" step="0.1" name="rating" value="<?php echo htmlspecialchars($rating); ?>">
        </div>
        <input type="submit" class="btn btn-primary" value="Save Changes">
        <a href="view_recipe.php?id=<?php echo $id; ?>" class="btn btn-secondary">Cancel</a>
    </form>
</div>
<?php include 'footer.php'; ?>
