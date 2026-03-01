<?php
// www/process.php
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
file_put_contents($file, $line, FILE_APPEND | LOCK_EX);

// === НОВАЯ УПРОЩЁННАЯ ЧАСТЬ: API, UserInfo, cookie ===

// Попытка подключить автолоадер (обычный путь: ../vendor/autoload.php)
$autoloadRoot = __DIR__ . '/../vendor/autoload.php';
$autoloadLocal = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadRoot)) {
    require_once $autoloadRoot;
} elseif (file_exists($autoloadLocal)) {
    require_once $autoloadLocal;
} else {
    // автолоадер не найден — сохраним ошибку в сессию, но не прерываем работу
    $_SESSION['api_data'] = ['error' => 'vendor/autoload.php not found'];
}

// Подключаем ApiClient и UserInfo (если файлы есть)
if (file_exists(__DIR__ . '/ApiClient.php')) {
    require_once __DIR__ . '/ApiClient.php';
}
if (file_exists(__DIR__ . '/UserInfo.php')) {
    require_once __DIR__ . '/UserInfo.php';
}

// Вызов API (если класс ApiClient доступен)
$apiData = ['error' => 'ApiClient not available'];
if (class_exists('ApiClient')) {
    try {
        $api = new ApiClient(['timeout' => 5.0]);
        $apiData = $api->request('https://dummyjson.com/products/category/smartphones');
    } catch (\Throwable $e) {
        $apiData = ['error' => $e->getMessage()];
    }
}
$_SESSION['api_data'] = $apiData;

// Информация о пользователе (через UserInfo, если есть)
if (class_exists('UserInfo')) {
    $_SESSION['user_info'] = UserInfo::getInfo();
} else {
    $_SESSION['user_info'] = [
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'time' => date('Y-m-d H:i:s'),
    ];
}

// Устанавливаем куку с меткой времени (до редиректа)
setcookie('last_submission', date('Y-m-d H:i:s'), time() + 3600, "/");

// Сообщение об успехе и редирект
$_SESSION['success'] = 'Данные успешно сохранены';
header('Location: index.php');
exit();