<?php
/**
 * 登录页
 * 不带品牌 logo/名称，纯账号密码框（按 /规范 第 2、4 节）
 * 认证方式与现有内部工具保持一致：标准账号 + .env 中的原始密码
 */
define('APP_ENTRY', true);
require __DIR__ . '/db.php';
if (token_check()) { header('Location: index.php?p=dashboard'); exit; }
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';
    $ok = false;
    foreach ($CONFIG['users'] as $row) {
        if (hash_equals($row['username'], $u) && hash_equals($row['password'], $p)) {
            $ok = true; break;
        }
    }
    if ($ok) {
        token_create($u);
        header('Location: index.php?p=dashboard');
        exit;
    }
    $err = '账号或密码错误';
}
?>
<!doctype html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>登录 · <?= htmlspecialchars($CONFIG['app_name']) ?></title>
<link rel="stylesheet" href="assets/app.css?v=1">
</head>
<body class="login-page">

<div class="stars" aria-hidden="true">
  <?php for ($i=0; $i<80; $i++): ?>
    <span style="--x:<?= rand(0,100) ?>%;--y:<?= rand(0,100) ?>%;--d:<?= rand(2,8) ?>s;--o:<?= round(rand(20,80)/100,2) ?>"></span>
  <?php endfor; ?>
  <span class="meteor m1"></span>
  <span class="meteor m2"></span>
  <span class="meteor m3"></span>
</div>

<form class="login-card" method="post" autocomplete="off">
  <h1 class="login-title">欢迎回来</h1>
  <p class="login-sub">请输入您的账号继续</p>

  <?php if ($err): ?>
    <div class="alert alert-error"><?= htmlspecialchars($err) ?></div>
  <?php endif; ?>

  <label class="float-field">
    <input type="text" name="username" required placeholder=" " autocomplete="username">
    <span>账号</span>
  </label>

  <label class="float-field">
    <input type="password" name="password" required placeholder=" " autocomplete="current-password">
    <span>密码</span>
  </label>

  <button class="btn btn-primary btn-block btn-lg" type="submit">登 录</button>
</form>

</body>
</html>
