<?php
require_once '../config/database.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Check if article ID is provided
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$article_id = $_GET['id'];

// Delete the article
$stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
if ($stmt->execute([$article_id])) {
    header("Location: dashboard.php");
    exit();
} else {
    die("Failed to delete article");
}
?> 