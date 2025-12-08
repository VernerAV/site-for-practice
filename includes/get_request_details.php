<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Не авторизован']);
    exit();
}

$request_id = $_GET['id'] ?? 0;
if (!$request_id) {
    echo json_encode(['success' => false, 'error' => 'Не указан ID заявки']);
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
        echo json_encode(['success' => false, 'error' => 'Пользователь не найден']);
        exit();
    }
    
    // Получаем заявку (только если она принадлежит пользователю)
    $request_sql = "SELECT * FROM messages 
                    WHERE id = :id AND user_email = :email";
    $request_stmt = $pdo->prepare($request_sql);
    $request_stmt->execute([
        ':id' => $request_id,
        ':email' => $user_data['email']
    ]);
    
    $request = $request_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$request) {
        echo json_encode(['success' => false, 'error' => 'Заявка не найдена']);
        exit();
    }
    
    // Форматируем даты
    $request['created_at'] = date('d.m.Y H:i', strtotime($request['created_at']));
    if ($request['responded_at']) {
        $request['responded_at'] = date('d.m.Y H:i', strtotime($request['responded_at']));
    }
    
    // Экранируем HTML
    $request['message'] = nl2br(htmlspecialchars($request['message']));
    if ($request['admin_response']) {
        $request['admin_response'] = nl2br(htmlspecialchars($request['admin_response']));
    }
    
    echo json_encode(['success' => true, ...$request]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Ошибка базы данных']);
}
?>