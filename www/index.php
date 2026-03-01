<?php
session_start();

// Копируем сообщения в локальные переменные и очищаем их из сессии (как я понял, надо чтобы 
// всё хранилось в файле, а не сессии),
// чтобы после перезагрузки страницы сообщения не показывались снова
$errors = $_SESSION['errors'] ?? null;
$success = $_SESSION['success'] ?? null;
$form_data = $_SESSION['form_data'] ?? null;
unset($_SESSION['errors'], $_SESSION['success']);
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <title>Главная — Заявка на ремонт</title>
  <style>
    body{font-family:Arial,Helvetica,sans-serif;padding:18px}
    .errors{color:#900;background:#fee;padding:10px;border-radius:6px}
    .success{color:#060;background:#efe;padding:10px;border-radius:6px}
    .box{border:1px solid #ddd;padding:10px;border-radius:6px;background:#fafafa;margin:12px 0}
  </style>
</head>
<body>
  <h1>Заявка на ремонт техники</h1>

  <!-- Ошибки -->
  <?php if ($errors): ?>
    <div class="errors box">
      <strong>Ошибки:</strong>
      <ul>
        <?php foreach ($errors as $e): ?>
          <li><?= htmlspecialchars($e, ENT_QUOTES|ENT_SUBSTITUTE) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <!-- Сообщение об успехе -->
  <?php if ($success): ?>
    <div class="success box"><?= htmlspecialchars($success, ENT_QUOTES|ENT_SUBSTITUTE) ?></div>
  <?php endif; ?>

  <!-- Данные из сессии (последняя отправка) -->
  <?php if ($form_data): ?>
    <div class="box">
      <h3>Последняя заявка (из сессии):</h3>
      <ul>
        <li><b>Имя:</b> <?= htmlspecialchars($form_data['name'] ?? '', ENT_QUOTES|ENT_SUBSTITUTE) ?></li>
        <li><b>Модель:</b> <?= htmlspecialchars($form_data['model'] ?? '', ENT_QUOTES|ENT_SUBSTITUTE) ?></li>
        <li><b>Email:</b> <?= htmlspecialchars($form_data['email'] ?? '—', ENT_QUOTES|ENT_SUBSTITUTE) ?></li>
        <li><b>Услуга:</b> <?= htmlspecialchars($form_data['service'] ?? '', ENT_QUOTES|ENT_SUBSTITUTE) ?></li>
        <li><b>Гарантия:</b> <?= htmlspecialchars($form_data['warranty'] ?? '', ENT_QUOTES|ENT_SUBSTITUTE) ?></li>
        <li><b>Срок:</b> <?= htmlspecialchars($form_data['term'] ?? '', ENT_QUOTES|ENT_SUBSTITUTE) ?></li>
        <li><b>Время:</b> <?= htmlspecialchars($form_data['time'] ?? '', ENT_QUOTES|ENT_SUBSTITUTE) ?></li>
      </ul>
    </div>
  <?php else: ?>
    <p>Данных пока нет.</p>
  <?php endif; ?>

  <p><a href="form.html">Заполнить форму</a> | <a href="view.php">Посмотреть все данные</a></p>

  <?php include 'form.html'; ?>
</body>
</html>