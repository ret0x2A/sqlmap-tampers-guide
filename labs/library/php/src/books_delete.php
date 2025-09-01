<?php
require_once __DIR__ . "/lib/config.php";
require_once __DIR__ . "/lib/auth.php";
require_once __DIR__ . "/lib/csrf.php";
require_login();

if (!hash_equals($_GET['csrf'] ?? '', $_SESSION['csrf'] ?? '')) {
  http_response_code(400);
  echo "CSRF validation failed."; exit;
}

$uid = current_user()['id'];
$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("DELETE FROM books WHERE id=? AND user_id=?");
$stmt->execute([$id, $uid]);
header("Location: /books.php"); exit;
