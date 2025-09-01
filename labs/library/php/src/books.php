<?php
require_once __DIR__ . "/lib/csrf.php";
require_once __DIR__ . "/lib/auth.php";
require_login();
csrf_check();

$uid = current_user()['id'];
$name = current_user()['name'];
$errors = []; $ok = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
  $title = $_POST['title'] ?? '';
  $author = $_POST['author'] ?? '';
  $description = $_POST['description'] ?? '';
  $cover_path = null;

  if ($title === '' || $author === '') $errors[] = "Название и автор обязательны.";

  if (!empty($_FILES['cover']['name'])) {
    if ($_FILES['cover']['error'] === UPLOAD_ERR_OK) {
      $tmp = $_FILES['cover']['tmp_name'];
      $info = getimagesize($tmp);
      if ($info && in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF])) {
        $ext = image_type_to_extension($info[2], false);
        $newName = "/uploads/covers/". time() . "_" . bin2hex(random_bytes(6)) . "." . $ext;
        if (move_uploaded_file($tmp, __DIR__ . $newName)) {
          $cover_path = $newName;
        } else {
          $errors[] = "Не удалось сохранить файл обложки.";
        }
      } else {
        $errors[] = "Поддерживаются изображения JPG, PNG, GIF.";
      }
    } else {
      $errors[] = "Ошибка загрузки обложки.";
    }
  }

  if (!$errors) {
    // All input goes via prepared statements
    $stmt = $pdo->prepare("INSERT INTO books(owner_name, title, author, cover_path, description) VALUES (?,?,?,?,?)");
    $stmt->execute([$name, $title, $author, $cover_path, $description]);
    $ok = "Книга добавлена.";
  }
}

// Fetch books for the owner
$stmt = $pdo->prepare("SELECT * FROM books WHERE owner_name = ? ORDER BY created_at DESC");
$stmt->execute([$name]);
$books = $stmt->fetchAll();
require_once __DIR__ . "/views/header.php";
?>
<div class="card">
  <h2>Мои книги</h2>
  <?php foreach ($errors as $er) echo '<div class="alert alert-error">'.h($er).'</div>'; ?>
  <?php if ($ok) echo '<div class="alert alert-ok">'.h($ok).'</div>'; ?>

  <h3>Добавить книгу</h3>
  <form method="post" enctype="multipart/form-data">
    <?php csrf_field(); ?>
    <input type="hidden" name="action" value="create">
    <label>Название (можно что угодно)</label>
    <input name="title" required>
    <label>Автор</label>
    <input name="author" required>
    <label>Короткое описание</label>
    <textarea name="description" rows="3"></textarea>
    <label>Обложка (JPG/PNG/GIF)</label>
    <input type="file" name="cover" accept="image/*">
    <button type="submit">Добавить</button>
  </form>
</div>

<div class="card">
  <h3>Список</h3>
  <?php if (!$books): ?>
    <p>Пусто.</p>
  <?php else: ?>
    <ul>
      <?php foreach ($books as $b): ?>
        <li>
          <?php if ($b['cover_path']): ?><img class="thumb" src="<?= h($b['cover_path']) ?>" style="max-width:60px" alt="cover"><?php endif; ?>
          <strong><?= h($b['title']) ?></strong> — <?= h($b['author']) ?>
          <a class="btn-secondary" href="/books_edit.php?id=<?= $b['id'] ?>">Редактировать</a>
          <a class="btn-danger" href="/books_delete.php?id=<?= $b['id'] ?>&csrf=<?= csrf_token() ?>" onclick="return confirm('Удалить?')">Удалить</a>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>

<?php include __DIR__ . "/views/footer.php"; ?>
