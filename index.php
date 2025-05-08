<?php
require_once 'config/database.php';
session_start();

// Fetch all published articles
$stmt = $pdo->query("SELECT * FROM articles WHERE status = 'published' ORDER BY created_at DESC");
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query statistik
$total_posts = $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn();
$total_categories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_comments = $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn();

// Query recent posts
$recent_posts = $pdo->query("SELECT a.*, c.name as category, u.username as author 
    FROM articles a 
    LEFT JOIN categories c ON a.category_id = c.id 
    LEFT JOIN users u ON a.user_id = u.id 
    ORDER BY a.created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple CMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php if(isset($_SESSION['user_id'])): ?>
    <div class="container-fluid bg-dark py-4 mb-3">
        <div class="d-flex justify-content-center">
            <a href="admin/dashboard.php" class="btn btn-dark btn-lg px-5 fs-2 fw-bold border border-light shadow">DASHBOARD</a>
        </div>
    </div>
    <div class="container mb-4">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <table class="table table-bordered text-center mb-4">
                    <thead class="table-light">
                        <tr>
                            <th>Welcome</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Welcome, <strong><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></strong>!</td>
                        </tr>
                    </tbody>
                </table>
                <table class="table table-bordered text-center">
                    <thead class="table-light">
                        <tr>
                            <th colspan="2">User</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Username</td>
                            <td><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <a href="admin/edit_user.php" class="btn btn-secondary btn-sm">Edit Profile</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Simple CMS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <!--<li class="nav-item">
                            <a class="nav-link" href="admin/dashboard.php">Dashboard</a>
                        </li>-->
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
        <?php if(isset($_SESSION['user_id'])): ?>
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card bg-dark text-white shadow">
                        <div class="card-body text-center">
                            <h5 class="card-title">Total Posts</h5>
                            <h2><?= $total_posts ?></h2>
                            <a href="admin/dashboard.php" class="btn btn-outline-warning btn-sm mt-2">Lihat Posts</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-dark text-white shadow">
                        <div class="card-body text-center">
                            <h5 class="card-title">Total Categories</h5>
                            <h2><?= $total_categories ?></h2>
                            <a href="admin/categories.php" class="btn btn-outline-warning btn-sm mt-2">Lihat Categories</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-dark text-white shadow">
                        <div class="card-body text-center">
                            <h5 class="card-title">Total Users</h5>
                            <h2><?= $total_users ?></h2>
                            <a href="admin/users.php" class="btn btn-outline-warning btn-sm mt-2">Lihat Users</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-dark text-white shadow">
                        <div class="card-body text-center">
                            <h5 class="card-title">Total Comments</h5>
                            <h2><?= $total_comments ?></h2>
                            <a href="admin/comments.php" class="btn btn-outline-warning btn-sm mt-2">Lihat Comments</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card shadow mb-5">
                <div class="card-header d-flex justify-content-between align-items-center bg-dark text-white">
                    <span>Recent Posts</span>
                    <a href="admin/dashboard.php" class="btn btn-warning btn-sm">View All</a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-dark table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Author</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recent_posts as $post): ?>
                            <tr>
                                <td><?= htmlspecialchars($post['title']) ?></td>
                                <td><?= htmlspecialchars($post['category']) ?></td>
                                <td><?= htmlspecialchars($post['author']) ?></td>
                                <td><?= date('Y-m-d', strtotime($post['created_at'])) ?></td>
                                <td>
                                    <span class="badge bg-<?= $post['status'] == 'published' ? 'success' : 'warning' ?>">
                                        <?= ucfirst($post['status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach($articles as $article): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($article['title']); ?></h5>
                                <p class="card-text"><?php echo substr(htmlspecialchars($article['content']), 0, 150) . '...'; ?></p>
                                <a href="article.php?id=<?php echo $article['id']; ?>" class="btn btn-primary">Read More</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 