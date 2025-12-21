<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

requireAdmin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: sizes.php');
    exit;
}

$conn = connectDB();
$stmt = $conn->prepare("DELETE FROM sizes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();
closeDB($conn);

header('Location: sizes.php?deleted=1');
exit;

