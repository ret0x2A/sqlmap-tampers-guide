<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . "/../lib/config.php";
require_once __DIR__ . "/../lib/auth.php";
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Библиотека</title>
  <link rel="stylesheet" href="/public/css/main.css" />
</head>
<body>
  <nav>
    <div class="container">
      <a href="/index.php">Библиотека</a>
      <?php if (current_user()): ?>
        <a href="/profile.php">Мой профиль</a>
        <a href="/books.php">Мои книги</a>
        <a href="/logout.php">Выход</a>
      <?php else: ?>
        <a href="/register.php">Регистрация</a>
        <a href="/login.php">Вход</a>
      <?php endif; ?>
    </div>
  </nav>
  <div class="container">
