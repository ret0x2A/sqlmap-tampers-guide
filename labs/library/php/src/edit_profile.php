<?php
require_once __DIR__ . "/views/header.php";
require_once __DIR__ . "/lib/csrf.php";
require_login();
csrf_check();

$uid = current_user()['id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$uid]);
$user = $stmt->fetch();

$ok = null; $errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $bio = trim($_POST['bio'] ?? '');
  $password = $_POST['password'] ?? '';
  $avatar_path = $user['avatar_path'];

  // Handle avatar upload
  if (!empty($_FILES['avatar']['name'])) {
    if ($_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
      $tmp = $_FILES['avatar']['tmp_name'];
      $info = getimagesize($tmp);
      if ($info && in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF])) {
        $ext = image_type_to_extension($info[2], false);
        $newName = "/uploads/avatars/". time() . "_" . bin2hex(random_bytes(6)) . "." . $ext;
        if (move_uploaded_file($tmp, __DIR__ . $newName)) {
          $avatar_path = $newName;
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
    if ($password !== '') {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $pdo->prepare("UPDATE users SET name=?, bio=?, avatar_path=?, password_hash=? WHERE id=?");
      $stmt->execute([$name, $bio, $avatar_path, $hash, $uid]);
    } else {
      $stmt = $pdo->prepare("UPDATE users SET name=?, bio=?, avatar_path=? WHERE id=?");
      $stmt->execute([$name, $bio, $avatar_path, $uid]);
    }
    $ok = "Профиль обновлён.";
    $_SESSION['user']['name'] = $name;
  }
}

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }

    return $randomString;
}

?>
<div class="card">
  <h2>Редактирование профиля</h2>
  <?php foreach ($errors as $er) echo '<div class="alert alert-error">'.h($er).'</div>'; ?>
  <?php if ($ok) echo '<div class="alert alert-ok">'.h($ok).'</div>'; ?>

  <form method="post" enctype="multipart/form-data">
    <?php csrf_field(); ?>
    <label>Логин</label>
    <input value="<?= h($user['username']) ?>" disabled>
    <label>Имя</label>
    <input name="name" value="<?= h($user['name']) ?>" required>
    <label>Описание</label>
    <textarea name="bio" rows="4"><?= h($user['bio']) ?></textarea>
    <label>Новый пароль (необязательно)</label>
    <input type="password" name="password">
    <label>Аватар (JPG/PNG/GIF)</label>
    <input type="file" name="avatar" accept="image/*">
    <button type="submit">Сохранить</button>
  </form>
</div>
<?php include __DIR__ . "/views/footer.php"; ?>
