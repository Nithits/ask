<?php
// admin/action/delete_quote.php
require_once __DIR__ . '/../../config/db.php';
session_start();

if (!isset($_SESSION['admin_logged_in'])) { exit('Unauthorized'); }

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM quotations WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: ../dashboard.php?status=deleted");
    } catch (PDOException $e) {
        header("Location: ../dashboard.php?status=error");
    }
}