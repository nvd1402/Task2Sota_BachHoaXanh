<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

requireAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: brands.php');
    exit;
}

$conn = connectDB();
$stmt = $conn->prepare("DELETE FROM brands WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();
closeDB($conn);

header('Location: brands.php?deleted=1');
exit;

