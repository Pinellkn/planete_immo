<?php
require_once '../includes/config.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'login']);
    exit;
}

$maison_id = (int)($_POST['maison_id'] ?? 0);
if (!$maison_id) {
    echo json_encode(['status' => 'error']);
    exit;
}

$db = getDB();
$uid = $_SESSION['user_id'];

$check = $db->prepare("SELECT id FROM favoris WHERE user_id=? AND maison_id=?");
$check->execute([$uid, $maison_id]);
$existing = $check->fetch();

if ($existing) {
    $db->prepare("DELETE FROM favoris WHERE user_id=? AND maison_id=?")->execute([$uid, $maison_id]);
    echo json_encode(['status' => 'removed']);
} else {
    $db->prepare("INSERT INTO favoris (user_id, maison_id) VALUES (?, ?)")->execute([$uid, $maison_id]);
    echo json_encode(['status' => 'added']);
}
