<?php
require_once __DIR__ . "/views/header.php";
$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT id, username, name, bio, avatar_path, created_at FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) { http_response_code(404); echo "<div class='card'><h2>Пользователь не найден</h2></div>"; include __DIR__ . "/views/footer.php"; exit; }

$stmt = $pdo->prepare("SELECT * FROM books WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user['id']]);
$books = $stmt->fetchAll();
?>
<div class="card">
  <h2>Профиль: <?= h($user['name']) ?> (<?= h($user['username']) ?>)</h2>
  <div class="grid grid-2">
    <div>
      <p><?= nl2br(h($user['bio'])) ?></p>
      <p><small class="mono">ID: <?= $user['id'] ?></small></p>
    </div>
    <div>
      <?php if ($user['avatar_path']): ?>
        <img class="thumb" src="<?= h($user['avatar_path']) ?>" alt="avatar">
      <?php endif; ?>
    </div>
  </div>
</div>
<div class="card">
  <h3>Книги</h3>
  <?php if (!$books): ?>
    <p>Нет книг.</p>
  <?php else: ?>
    <ul>
      <?php foreach ($books as $b): ?>
        <li>
          <?php if ($b['cover_path']): ?><img class="thumb" src="<?= h($b['cover_path']) ?>" style="max-width:60px" alt="cover"><?php endif; ?>
          <strong><?= h($b['title']) ?></strong> — <?= h($b['author']) ?><br>
          <small><?= nl2br(h($b['description'])) ?></small>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>
<?php include __DIR__ . "/views/footer.php"; ?>
