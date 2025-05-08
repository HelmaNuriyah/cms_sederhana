<?php
require_once 'config/database.php';
session_start();

// Fetch all published articles
$stmt = $pdo->query("SELECT * FROM articles WHERE status = 'published' ORDER BY created_at DESC");
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                <table class="table table-bordered text-center">
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
            <div class="d-flex flex-column align-items-center mb-4">
                <a href="admin/create_article.php" class="btn btn-primary btn-lg mb-3">Create New Article</a>
            </div>
            <div class="table-responsive mb-5">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($articles as $article): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($article['title']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $article['status'] == 'published' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($article['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('Y-m-d H:i', strtotime($article['created_at'])); ?></td>
                                <td>
                                    <a href="admin/edit_article.php?id=<?php echo $article['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <a href="admin/delete_article.php?id=<?php echo $article['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this article?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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