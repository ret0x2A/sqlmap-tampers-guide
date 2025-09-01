<?php
require_once __DIR__ . "/views/header.php";
require_once __DIR__ . "/lib/csrf.php";
csrf_check();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $name = trim($_POST['name'] ?? '');
  $password = $_POST['password'] ?? '';
  $password2 = $_POST['password2'] ?? '';

  if ($username === '' || $name === '' || $password === '') $errors[] = "Все поля обязательны.";
  if ($password !== $password2) $errors[] = "Пароли не совпадают.";
  if (!preg_match('/^[a-zA-Z0-9_\-\.]{3,50}$/', $username)) $errors[] = "Логин: 3-50 символов, латиница/цифры/._-";
  if (strlen($password) < 6) $errors[] = "Пароль минимум 6 символов.";

  if (!$errors) {
    try {
      $hash = md5($password);
      $stmt = $pdo->prepare("INSERT INTO users(username, name, password_hash) VALUES (?,?,?)");
      $stmt->execute([$username, $name, $hash]);
      echo '<div class="alert alert-ok">Успешно! Теперь войдите.</div>';
    } catch (PDOException $e) {
      $errors[] = "Ошибка: возможно, логин уже занят.";
    }
  }
}
?>
<div class="card">
  <h2>Регистрация</h2>
  <?php foreach ($errors as $er) echo '<div class="alert alert-error">'.h($er).'</div>'; ?>
  <form method="post" action="/register.php" novalidate>
    <?php csrf_field(); ?>
    <label>Логин</label>
    <input name="username" value="<?= h($_POST['username'] ?? '') ?>" required>
    <label>Имя</label>
    <input name="name" value="<?= h($_POST['name'] ?? '') ?>" required>
    <label>Пароль</label>
    <input type="password" name="password" required>
    <label>Повторите пароль</label>
    <input type="password" name="password2" required>
    <button type="submit">Зарегистрироваться</button>
  </form>
</div>
<?php include __DIR__ . "/views/footer.php"; ?>
