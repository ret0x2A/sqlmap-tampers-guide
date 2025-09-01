<?php
require_once __DIR__ . "/config.php";

function current_user() {
  return $_SESSION['user'] ?? null;
}
function require_login() {
  if (!current_user()) { header("Location: /login.php"); exit; }
}
function attempt_login($pdo, $username, $password) {
  $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
  $stmt->execute([$username]);
  $u = $stmt->fetch();
  if ($u && md5($password) === $u['password_hash']) {
    $_SESSION['user'] = ['id' => $u['id'], 'username' => $u['username'], 'name' => $u['name']];
    return true;
  }
  return false;
}
function logout() { unset($_SESSION['user']); session_destroy(); }
