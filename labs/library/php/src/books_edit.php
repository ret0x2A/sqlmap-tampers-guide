<?php
require_once __DIR__ . "/views/header.php";
require_once __DIR__ . "/lib/csrf.php";
require_login();
csrf_check();

$uid = current_user()['id'];
$id = intval($_GET['id'] ?? 0);
// Only owner can edit
$stmt = $pdo->prepare("SELECT * FROM books WHERE id=? AND user_id=?");
$stmt->execute([$id, $uid]);
$book = $stmt->fetch();
if (!$book) { http_response_code(404); echo "<div class='card'><h2>Книга не найдена</h2></div>"; include __DIR__ . "/views/footer.php"; exit; }

$errors = []; $ok = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = $_POST['title'] ?? '';
  $author = $_POST['author'] ?? '';
  $description = $_POST['description'] ?? '';
  $cover_path = $book['cover_path'];

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
          $errors[] = "Не удалось сохранить файл.";
        }
      } else {
        $errors[] = "Поддерживаются изображения JPG, PNG, GIF.";
      }
    } else {
      $errors[] = "Ошибка загрузки файла.";
    }
  }

  if (!$errors) {
    $stmt = $pdo->prepare("UPDATE books SET title=?, author=?, cover_path=?, description=? WHERE id=? AND user_id=?");
    $stmt->execute([$title, $author, $cover_path, $description, $id, $uid]);
    $ok = "Сохранено.";
    // Refresh
    $stmt = $pdo->prepare("SELECT * FROM books WHERE id=? AND user_id=?");
    $stmt->execute([$id, $uid]);
    $book = $stmt->fetch();
  }
}
?>
<div class="card">
  <h2>Редактировать книгу</h2>
  <?php foreach ($errors as $er) echo '<div class="alert alert-error">'.h($er).'</div>'; ?>
  <?php if ($ok) echo '<div class="alert alert-ok">'.h($ok).'</div>'; ?>
  <form method="post" enctype="multipart/form-data">
    <?php csrf_field(); ?>
    <label>Название (можно что угодно)</label>
    <input name="title" value="<?= h($book['title']) ?>" required>
    <label>Автор</label>
    <input name="author" value="<?= h($book['author']) ?>" required>
    <label>Короткое описание</label>
    <textarea name="description" rows="3"><?= h($book['description']) ?></textarea>
    <label>Обложка</label>
    <input type="file" name="cover" accept="image/*">
    <button type="submit">Сохранить</button>
  </form>
</div>
<?php include __DIR__ . "/views/footer.php"; ?>
