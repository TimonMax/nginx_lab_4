<?php
session_start();

// получение post-поля
function post($name) {
    return isset($_POST[$name]) ? trim($_POST[$name]) : '';
}

$name = htmlspecialchars(post('name'), ENT_QUOTES | ENT_SUBSTITUTE);
$model = htmlspecialchars(post('model'), ENT_QUOTES | ENT_SUBSTITUTE);
$email = post('email'); // проверка отдельно
$email_sanitized = htmlspecialchars($email, ENT_QUOTES | ENT_SUBSTITUTE);
$service = htmlspecialchars(post('service'), ENT_QUOTES | ENT_SUBSTITUTE);
$warranty = isset($_POST['warranty']) ? 'Да' : 'Нет';
$term = htmlspecialchars(post('term'), ENT_QUOTES | ENT_SUBSTITUTE);

$errors = [];

if ($name === '') {
    $errors[] = 'Имя не может быть пустым';
}
if ($model === '') {
    $errors[] = 'Модель не может быть пустой';
}
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Некорректный email';
}

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old'] = [
        'name' => $name,
        'model' => $model,
        'email' => $email_sanitized,
        'service' => $service,
        'warranty' => $warranty,
        'term' => $term
    ];
    header('Location: index.php');
    exit();
}

// последняя заявка
$_SESSION['form_data'] = [
    'name' => $name,
    'model' => $model,
    'email' => $email_sanitized,
    'service' => $service,
    'warranty' => $warranty,
    'term' => $term,
    'time' => date('Y-m-d H:i:s')
];


function safe_field($s) {
    return str_replace(";", ",", $s);
}
$line = safe_field($name) . ";" . safe_field($model) . ";" . safe_field($email) . ";" . safe_field($service) . ";" . safe_field($warranty) . ";" . safe_field($term) . ";" . date('Y-m-d H:i:s') . "\n";

// Записываем в файл data.txt (в папке www)
$file = __DIR__ . '/data.txt';

// Попытка записать с блокировкой
file_put_contents($file, $line, FILE_APPEND | LOCK_EX);

$_SESSION['success'] = 'Данные успешно сохранены';

// Редирект на главную
header('Location: index.php');
exit();