<?php
require_once 'config/database.php';
session_start();

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$article_id = $_GET['id'];

// Fetch article with category and author info
$stmt = $pdo->prepare("
    SELECT a.*, c.name as category_name, u.username as author_name 
    FROM articles a 
    LEFT JOIN categories c ON a.category_id = c.id 
    LEFT JOIN users u ON a.user_id = u.id 
    WHERE a.id = ? AND a.status = 'published'
");
$stmt->execute([$article_id]);
$article = $stmt->fetch();

if (!$article) {
    header("Location: index.php");
    exit();
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $comment = $_POST['comment'];
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    $stmt = $pdo->prepare("INSERT INTO comments (article_id, user_id, name, email, comment, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    if ($stmt->execute([$article_id, $user_id, $name, $email, $comment])) {
        $success = "Comment submitted successfully and waiting for approval.";
    } else {
        $error = "Failed to submit comment. Please try again.";
    }
}

// Fetch approved comments
$stmt = $pdo->prepare("
    SELECT c.*, u.username 
    FROM comments c 
    LEFT JOIN users u ON c.user_id = u.id 
    WHERE c.article_id = ? AND c.status = 'approved' 
    ORDER BY c.created_at DESC
");
$stmt->execute([$article_id]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title']); ?> - Simple CMS</title>
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
                <article>
                    <h1><?php echo htmlspecialchars($article['title']); ?></h1>
                    <div class="text-muted mb-4">
                        By <?php echo htmlspecialchars($article['author_name']); ?> | 
                        <?php if ($article['category_name']): ?>
                            Category: <?php echo htmlspecialchars($article['category_name']); ?> |
                        <?php endif; ?>
                        <?php echo date('F j, Y', strtotime($article['created_at'])); ?>
                    </div>
                    
                    <?php if ($article['image']): ?>
                        <img src="uploads/<?php echo htmlspecialchars($article['image']); ?>" 
                             class="img-fluid mb-4" 
                             alt="<?php echo htmlspecialchars($article['title']); ?>">
                    <?php endif; ?>

                    <div class="content">
                        <?php echo $article['content']; ?>
                    </div>
                </article>

                <hr class="my-5">

                <section class="comments">
                    <h3>Comments (<?php echo count($comments); ?>)</h3>
                    
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" class="mb-5">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="comment" class="form-label">Comment</label>
                            <textarea class="form-control" id="comment" name="comment" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit Comment</button>
                    </form>

                    <?php foreach($comments as $comment): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <?php echo htmlspecialchars($comment['name']); ?>
                                    <?php if ($comment['username']): ?>
                                        <small class="text-muted">(<?php echo htmlspecialchars($comment['username']); ?>)</small>
                                    <?php endif; ?>
                                </h5>
                                <p class="card-text"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                                <small class="text-muted">
                                    <?php echo date('F j, Y g:i a', strtotime($comment['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </section>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Search</h5>
                    </div>
                    <div class="card-body">
                        <form action="search.php" method="GET">
                            <div class="input-group">
                                <input type="text" class="form-control" name="q" placeholder="Search articles...">
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