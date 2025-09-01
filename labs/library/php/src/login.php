<?php
require_once __DIR__ . "/lib/csrf.php";
require_once __DIR__ . "/lib/auth.php"; 
csrf_check();

$err = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';
  if (!attempt_login($pdo, $username, $password)) {
    $err = "Неверные логин или пароль.";
  } else {
    header("Location: /profile.php"); exit;
  }
}
require_once __DIR__ . "/views/header.php";
?>
<div class="card">
  <h2>Вход</h2>
  <?php if ($err) echo '<div class="alert alert-error">'.h($err).'</div>'; ?>
  <form method="post" action="/login.php" novalidate>
    <?php csrf_field(); ?>
    <label>Логин</label>
    <input name="username" required>
    <label>Пароль</label>
    <input type="password" name="password" required>
    <button type="submit">Войти</button>
  </form>
</div>
<?php include __DIR__ . "/views/footer.php"; ?>
