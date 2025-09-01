<?php
require_once __DIR__ . "/views/header.php";
require_login();
$uid = current_user()['id'];

// Fetch profile
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$uid]);
$user = $stmt->fetch();

$username = $user['username'];
$name = $user['name'];

// УЯЗВИМОСТЬ: прямое включение логина в SQL-запрос
$query = "SELECT * FROM books WHERE owner_name = '$name' ORDER BY created_at DESC";
$books = $pdo->query($query)->fetchAll();
?>
<div class="card">
  <h2>Мой профиль</h2>
  <div class="grid grid-2">
    <div>
      <p><strong>Логин:</strong> <?= h($user['username']) ?></p>
      <p><strong>Имя:</strong> <?= h($user['name']) ?></p>
      <p><strong>Описание:</strong><br><?= nl2br(h($user['bio'])) ?></p>
      <a class="btn" href="/edit_profile.php">Редактировать профиль</a>
    </div>
    <div>
      <?php if ($user['avatar_path']): ?>
        <img class="thumb" src="<?= h($user['avatar_path']) ?>" alt="avatar">
      <?php else: ?>
        <div class="alert">Аватар не загружен.</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="card">
  <h2>Мои книги</h2>
  <a class="btn" href="/books.php">Управление книгами</a>
  <?php if (!$books): ?>
    <p>Список пуст.</p>
  <?php else: ?>
    <ul>
      <?php foreach ($books as $b): ?>
        <li>
          <strong><?= h($b['title']) ?></strong> — <?= h($b['author']) ?>, владелец - <?=h($b['owner_name']) ?>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>
<?php include __DIR__ . "/views/footer.php"; ?>