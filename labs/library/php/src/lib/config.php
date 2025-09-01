<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$host = getenv('DB_HOST') ?: 'db';
$db   = getenv('DB_NAME') ?: 'library';
$user = getenv('DB_USER') ?: 'user';
$pass = getenv('DB_PASS') ?: 'pass';
$APP_ENV = getenv('APP_ENV') ?: 'prod';
$UPLOAD_MAX_MB = intval(getenv('UPLOAD_MAX_MB') ?: '5');

try {
  $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
} catch (PDOException $e) {
  http_response_code(500);
  echo "DB error: " . htmlspecialchars($e->getMessage());
  exit;
}

function h($v) { return htmlspecialchars($v ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
