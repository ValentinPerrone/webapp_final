<?php
require_once "auth_session.php";
require_once "config.php";

// Check role
if ($_SESSION["role"] !== 'cook') {
    header("location: index.php");
    exit;
}

$title = $description = $category = $ingredients_text = "";
$title_err = $description_err = $category_err = $ingredients_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate Title
    if (empty(trim($_POST["title"]))) {
        $title_err = "Please enter a title.";
    }
    else {
        $title = trim($_POST["title"]);
    }

    // Validate Description
    if (empty(trim($_POST["description"]))) {
        $description_err = "Please enter a description.";
    }
    else {
        $description = trim($_POST["description"]);
    }

    // Validate Category
    if (empty(trim($_POST["category"]))) {
        $category_err = "Please select a category.";
    }
    else {
        $category = trim($_POST["category"]);
    }

    // Validate Ingredients
    if (empty(trim($_POST["ingredients"]))) {
        $ingredients_err = "Please enter at least one ingredient.";
    }
    else {
        $ingredients_text = trim($_POST["ingredients"]);
    }

    if (empty($title_err) && empty($description_err) && empty($category_err) && empty($ingredients_err)) {
        // Start transaction
        mysqli_begin_transaction($link);

        try {
            // Insert Recipe
            $sql = "INSERT INTO recipes (user_id, title, description, category, rating) VALUES (?, ?, ?, ?, 0.0)"; // Initial rating 0
            $stmt = mysqli_prepare($link, $sql);
            mysqli_stmt_bind_param($stmt, "isss", $_SESSION["id"], $title, $description, $category);
            mysqli_stmt_execute($stmt);
            $recipe_id = mysqli_insert_id($link);
            mysqli_stmt_close($stmt);

            // Insert Ingredients
            // Format expected: "Name: Quantity" per line
            $lines = explode("\n", $ingredients_text);
            $sql_ing = "INSERT INTO ingredients (recipe_id, name, quantity) VALUES (?, ?, ?)";
            $stmt_ing = mysqli_prepare($link, $sql_ing);

            foreach ($lines as $line) {
                $parts = explode(":", $line);
                $name = trim($parts[0]);
                $qty = isset($parts[1]) ? trim($parts[1]) : "As needed";

                if (!empty($name)) {
                    mysqli_stmt_bind_param($stmt_ing, "iss", $recipe_id, $name, $qty);
                    mysqli_stmt_execute($stmt_ing);
                }
            }
            mysqli_stmt_close($stmt_ing);

            mysqli_commit($link);
            header("location: index.php");
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
    <h2>Publish New Recipe</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="form-group">
            <label>Recipe Title</label>
            <input type="text" name="title" class="<?php echo(!empty($title_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $title; ?>">
            <span class="invalid-feedback"><?php echo $title_err; ?></span>
        </div>
        
        <div class="form-group">
            <label>Category</label>
            <select name="category">
                <option value="Main Course">Main Course</option>
                <option value="Breakfast">Breakfast</option>
                <option value="Dessert">Dessert</option>
                <option value="Salad">Salad</option>
                <option value="Vegetarian">Vegetarian</option>
                <option value="Dinner">Dinner</option>
            </select>
            <span class="invalid-feedback"><?php echo $category_err; ?></span>
        </div>

        <div class="form-group">
            <label>Description & Instructions</label>
            <textarea name="description" rows="5" class="<?php echo(!empty($description_err)) ? 'is-invalid' : ''; ?>"><?php echo $description; ?></textarea>
            <span class="invalid-feedback"><?php echo $description_err; ?></span>
        </div>

        <div class="form-group">
            <label>Ingredients (One per line, format "Name: Quantity")</label>
            <textarea name="ingredients" rows="5" placeholder="Acc: Flour: 200g&#10;Sugar: 100g" class="<?php echo(!empty($ingredients_err)) ? 'is-invalid' : ''; ?>"><?php echo $ingredients_text; ?></textarea>
            <span class="invalid-feedback"><?php echo $ingredients_err; ?></span>
        </div>

        <div class="form-group">
            <input type="submit" class="btn btn-primary" value="Publish Recipe">
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>
