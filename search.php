<?php
require_once 'config/database.php';
session_start();

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$articles = [];

if ($search) {
    $stmt = $pdo->prepare("
        SELECT a.*, c.name as category_name, u.username as author_name 
        FROM articles a 
        LEFT JOIN categories c ON a.category_id = c.id 
        LEFT JOIN users u ON a.user_id = u.id 
        WHERE a.status = 'published' 
        AND (a.title LIKE ? OR a.content LIKE ?)
        ORDER BY a.created_at DESC
    ");
    $search_term = "%{$search}%";
    $stmt->execute([$search_term, $search_term]);
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Simple CMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Simple CMS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <h2>Search Results for "<?php echo htmlspecialchars($search); ?>"</h2>
                
                <?php if (empty($articles)): ?>
                    <div class="alert alert-info">
                        No articles found matching your search criteria.
                    </div>
                <?php else: ?>
                    <?php foreach($articles as $article): ?>
                        <div class="card mb-4">
                            <?php if ($article['image']): ?>
                                <img src="uploads/<?php echo htmlspecialchars($article['image']); ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($article['title']); ?>">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a href="article.php?id=<?php echo $article['id']; ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($article['title']); ?>
                                    </a>
                                </h5>
                                <p class="card-text">
                                    <?php echo substr(strip_tags($article['content']), 0, 200) . '...'; ?>
                                </p>
                                <div class="text-muted">
                                    By <?php echo htmlspecialchars($article['author_name']); ?> | 
                                    <?php if ($article['category_name']): ?>
                                        Category: <?php echo htmlspecialchars($article['category_name']); ?> |
                                    <?php endif; ?>
                                    <?php echo date('F j, Y', strtotime($article['created_at'])); ?>
                                </div>
                                <a href="article.php?id=<?php echo $article['id']; ?>" class="btn btn-primary mt-2">Read More</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Search</h5>
                    </div>
                    <div class="card-body">
                        <form action="search.php" method="GET">
                            <div class="input-group">
                                <input type="text" class="form-control" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search articles...">
                                <button class="btn btn-primary" type="submit">Search</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Categories</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $stmt = $pdo->query("
                            SELECT c.*, COUNT(a.id) as article_count 
                            FROM categories c 
                            LEFT JOIN articles a ON c.id = a.category_id AND a.status = 'published'
                            GROUP BY c.id 
                            ORDER BY c.name
                        ");
                        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        <ul class="list-unstyled">
                            <?php foreach($categories as $category): ?>
                                <li class="mb-2">
                                    <a href="category.php?slug=<?php echo $category['slug']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                        <span class="badge bg-secondary"><?php echo $category['article_count']; ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 