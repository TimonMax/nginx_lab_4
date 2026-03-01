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
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <style>
    body{font-family:Arial,Helvetica,sans-serif;padding:18px;max-width:1000px;margin:0 auto}
    .errors{color:#900;background:#fee;padding:10px;border-radius:6px}
    .success{color:#060;background:#efe;padding:10px;border-radius:6px}
    .box{border:1px solid #ddd;padding:10px;border-radius:6px;background:#fafafa;margin:12px 0}
    pre{white-space:pre-wrap;word-wrap:break-word}
    a { color: #0366d6; text-decoration:none; }
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

  <!-- Данные из API -->
  <?php if (isset($_SESSION['api_data'])): ?>
    <div class="box">
      <h3>Данные из API (категория: smartphones)</h3>
      <?php $api_data = $_SESSION['api_data']; ?>
      <?php if ($api_data === null): ?>
        <p>Данных из API нет.</p>
      <?php elseif (isset($api_data['error'])): ?>
        <p style="color:red">Ошибка API: <?= htmlspecialchars($api_data['error'], ENT_QUOTES|ENT_SUBSTITUTE) ?></p>
        <pre><?= htmlspecialchars(print_r($api_data, true), ENT_QUOTES|ENT_SUBSTITUTE) ?></pre>
      <?php else: ?>
        <p><b>Всего товаров:</b> <?= htmlspecialchars($api_data['total'] ?? '—', ENT_QUOTES|ENT_SUBSTITUTE) ?></p>
        <?php if (!empty($api_data['products']) && is_array($api_data['products'])): ?>
          <ul>
            <?php foreach (array_slice($api_data['products'], 0, 5) as $p): ?>
              <li>
                <b><?= htmlspecialchars($p['title'] ?? '', ENT_QUOTES|ENT_SUBSTITUTE) ?></b>
                Бренд: <?= htmlspecialchars($p['brand'] ?? '', ENT_QUOTES|ENT_SUBSTITUTE) ?>,
                Цена: <?= htmlspecialchars($p['price'] ?? '', ENT_QUOTES|ENT_SUBSTITUTE) ?>,
                Рейтинг: <?= htmlspecialchars($p['rating'] ?? '', ENT_QUOTES|ENT_SUBSTITUTE) ?>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <pre><?= htmlspecialchars(print_r($api_data, true), ENT_QUOTES|ENT_SUBSTITUTE) ?></pre>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <!-- Информация о пользователе -->
  <?php if (!empty($_SESSION['user_info'])): ?>
    <div class="box">
      <h3>Информация о пользователе</h3>
      <?php $ui = $_SESSION['user_info']; ?>
      <?php foreach ($ui as $k => $v): ?>
        <?= htmlspecialchars((string)$k, ENT_QUOTES|ENT_SUBSTITUTE) ?>: <?= htmlspecialchars((string)$v, ENT_QUOTES|ENT_SUBSTITUTE) ?><br>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <!-- Cookie -->
  <?php if (!empty($_COOKIE['last_submission'])): ?>
    <div class="box"><b>Последняя отправка (cookie):</b> <?= htmlspecialchars($_COOKIE['last_submission'], ENT_QUOTES|ENT_SUBSTITUTE) ?></div>
  <?php endif; ?>

  <p><a href="form.html">Заполнить форму</a> | <a href="view.php">Посмотреть все данные</a></p>

  <?php include 'form.html'; ?>
</body>
</html>