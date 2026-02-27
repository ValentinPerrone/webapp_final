<?php
require_once "auth_session.php";
require_once "config.php";


session_start(); // Helper loaded by header usually, but good to have explicit if logic needs it before

// 1. Handling Search & Sort inputs
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$sort_order = isset($_GET['sort']) ? $_GET['sort'] : 'rating_desc';

// Base Query
$sql = "SELECT r.*, u.username FROM recipes r JOIN users u ON r.user_id = u.id";
$params = [];
$types = "";

// Filtering
if (!empty($search_query)) {
    // Search in Title, Category, or Ingredient Name
    // Note: Ingredient search requires a JOIN or subquery, let's use EXISTS for cleaner main query result
    $sql .= " WHERE (r.title LIKE ? OR r.category LIKE ? OR EXISTS (
        SELECT 1 FROM ingredients i WHERE i.recipe_id = r.id AND i.name LIKE ?
    ))";
    $wildcard = "%" . $search_query . "%";
    $params[] = $wildcard;
    $params[] = $wildcard;
    $params[] = $wildcard;
    $types .= "sss";
}

// Sorting
switch ($sort_order) {
    case 'date_desc':
        $sql .= " ORDER BY r.created_at DESC";
        break;
    case 'date_asc':
        $sql .= " ORDER BY r.created_at ASC";
        break;
    case 'rating_asc':
        $sql .= " ORDER BY r.rating ASC, r.created_at DESC";
        break;
    case 'rating_desc':
    default:
        $sql .= " ORDER BY r.rating DESC, r.created_at DESC";
        break;
}

$stmt = mysqli_prepare($link, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

?>

<?php include 'header.php'; ?>

<div class="container">
    <div class="search-bar">
        <form action="index.php" method="GET" style="display:flex; gap:1rem; width:100%; align-items: center;">
            <input type="text" name="q" placeholder="Search recipes, categories, ingredients..." value="<?php echo htmlspecialchars($search_query); ?>">
            <select name="sort" onchange="this.form.submit()">
                <option value="rating_desc" <?php echo($sort_order == 'rating_desc') ? 'selected' : ''; ?>>Highest Rated</option>
                <option value="rating_asc" <?php echo($sort_order == 'rating_asc') ? 'selected' : ''; ?>>Lowest Rated</option>
                <option value="date_desc" <?php echo($sort_order == 'date_desc') ? 'selected' : ''; ?>>Newest First</option>
                <option value="date_asc" <?php echo($sort_order == 'date_asc') ? 'selected' : ''; ?>>Oldest First</option>
            </select>
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if (!empty($search_query)): ?>
                <a href="index.php" class="btn btn-secondary">Clear</a>
            <?php
endif; ?>
        </form>
    </div>

    <h1>Latest Recipes</h1>
    
    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="recipe-grid">
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <div class="card">
                    <div class="card-header">
                        <div class="card-title"><?php echo htmlspecialchars($row['title']); ?></div>
                    </div>
                    <div class="card-body">
                        <div class="card-meta">
                            <span><i class="role-badge" style="background:#ddd; color:#333; font-size: 0.7rem;"><?php echo htmlspecialchars($row['category']); ?></i></span>
                            <span class="rating">★ <?php echo $row['rating']; ?></span>
                        </div>
                        <p><?php echo htmlspecialchars(substr($row['description'], 0, 100)) . '...'; ?></p>
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:1rem;">
                            <small>By <?php echo htmlspecialchars($row['username']); ?></small>
                            <a href="view_recipe.php?id=<?php echo $row['id']; ?>" class="btn btn-secondary" style="padding:0.5rem 1rem; font-size:0.8rem;">View Recipe</a>
                        </div>
                    </div>
                </div>
            <?php
    endwhile; ?>
        </div>
    <?php
else: ?>
        <p>No recipes found matching your criteria.</p>
    <?php
endif; ?>
</div>

<?php include 'footer.php'; ?>
<?php mysqli_close($link); ?>
