<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$request_id = $_POST['request_id'] ?? 0;

if (!$request_id) {
    $_SESSION['request_errors'] = ["Не указан ID заявки"];
    header('Location: ../user.php#requests');
    exit();
}

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    
    // Получаем email пользователя
    $user_sql = "SELECT email FROM users WHERE id = :user_id";
    $user_stmt = $pdo->prepare($user_sql);
    $user_stmt->execute([':user_id' => $_SESSION['user_id']]);
    $user_data = $user_stmt->fetch();
    
    if (!$user_data) {
        $_SESSION['request_errors'] = ["Пользователь не найден"];
        header('Location: ../user.php#requests');
        exit();
    }
    
    // Помечаем как прочитанное
    $update_sql = "UPDATE messages SET is_read = 1 
                   WHERE id = :id AND user_email = :email";
    $update_stmt = $pdo->prepare($update_sql);
    $update_stmt->execute([
        ':id' => $request_id,
        ':email' => $user_data['email']
    ]);
    
    $_SESSION['request_success'] = "Заявка отмечена как прочитанная";
    
} catch (PDOException $e) {
    $_SESSION['request_errors'] = ["Ошибка при обновлении статуса"];
}

header('Location: ../user.php#requests');
exit();
?>