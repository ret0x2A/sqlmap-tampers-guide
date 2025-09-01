<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

function csrf_token() {
  if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf'];
}
function csrf_field() {
  $t = csrf_token();
  echo '<input type="hidden" name="csrf" value="'.htmlspecialchars($t, ENT_QUOTES).'">';
}
function csrf_check() {
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'])) {
      http_response_code(400);
      echo "CSRF validation failed.";
      exit;
    }
  }
}
