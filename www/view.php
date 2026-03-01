<?php
// view.php
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <title>Все данные — Заявки</title>
  <style>
    body { font-family: Arial, sans-serif; padding:20px; }
    table { border-collapse: collapse; width:100%; max-width:900px; }
    th, td { border:1px solid #ddd; padding:8px; }
    th { background:#f0f0f0; }
  </style>
</head>
<body>
  <h2>Все сохранённые данные</h2>

  <?php
  $file = __DIR__ . '/data.txt';
  if (!file_exists($file)) {
      echo "<p>Данных пока нет.</p>";
  } else {
      $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
      if (empty($lines)) {
          echo "<p>Данных пока нет.</p>";
      } else {
          echo "<table><tr><th>#</th><th>Имя</th><th>Модель</th><th>Email</th><th>Услуга</th><th>Гарантия</th><th>Срок</th><th>Время</th></tr>";
          $i = 1;
          foreach ($lines as $ln) {
              $parts = explode(";", $ln);
              // если нет частей, подставляем пустые
              $name = htmlspecialchars($parts[0] ?? '', ENT_QUOTES | ENT_SUBSTITUTE);
              $model = htmlspecialchars($parts[1] ?? '', ENT_QUOTES | ENT_SUBSTITUTE);
              $email = htmlspecialchars($parts[2] ?? '', ENT_QUOTES | ENT_SUBSTITUTE);
              $service = htmlspecialchars($parts[3] ?? '', ENT_QUOTES | ENT_SUBSTITUTE);
              $warranty = htmlspecialchars($parts[4] ?? '', ENT_QUOTES | ENT_SUBSTITUTE);
              $term = htmlspecialchars($parts[5] ?? '', ENT_QUOTES | ENT_SUBSTITUTE);
              $time = htmlspecialchars($parts[6] ?? '', ENT_QUOTES | ENT_SUBSTITUTE);

              echo "<tr><td>{$i}</td><td>{$name}</td><td>{$model}</td><td>{$email}</td><td>{$service}</td><td>{$warranty}</td><td>{$term}</td><td>{$time}</td></tr>";
              $i++;
          }
          echo "</table>";
      }
  }
  ?>

  <p><a href="index.php">На главную</a></p>
</body>
</html>