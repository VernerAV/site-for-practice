<?php
// includes/get_message.php - Получение сообщения по ID
session_start();
require_once 'config.php';

// Проверка прав
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die('Доступ запрещен');
}

if (!isset($_GET['id'])) {
    die('ID сообщения не указан');
}

$messageId = intval($_GET['id']);

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Получаем сообщение
    $stmt = $pdo->prepare("
        SELECT m.*, u.name as admin_name 
        FROM messages m
        LEFT JOIN users u ON m.responded_by = u.id
        WHERE m.id = ?
    ");
    $stmt->execute([$messageId]);
    $message = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$message) {
        die('Сообщение не найдено');
    }
    
    // Помечаем как прочитанное
    $updateStmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE id = ?");
    $updateStmt->execute([$messageId]);
    
    ?>
    <div class="message-details">
        <div class="message-field">
            <div class="field-label">Отправитель:</div>
            <div class="field-value">
                <strong><?php echo htmlspecialchars($message['user_name']); ?></strong>
                (<a href="mailto:<?php echo htmlspecialchars($message['user_email']); ?>">
                    <?php echo htmlspecialchars($message['user_email']); ?>
                </a>)
            </div>
        </div>
        
        <div class="message-field">
            <div class="field-label">Тема:</div>
            <div class="field-value"><strong><?php echo htmlspecialchars($message['subject']); ?></strong></div>
        </div>
        
        <div class="message-field">
            <div class="field-label">Дата отправки:</div>
            <div class="field-value">
                <?php echo date('d.m.Y H:i', strtotime($message['created_at'])); ?>
            </div>
        </div>
        
        <div class="message-field">
            <div class="field-label">IP адрес:</div>
            <div class="field-value"><?php echo htmlspecialchars($message['ip_address']); ?></div>
        </div>
        
        <div class="message-field">
            <div class="field-label">Сообщение:</div>
            <div class="field-value" style="white-space: pre-wrap; background: white; padding: 15px; border-radius: 4px; border: 1px solid #eee;">
                <?php echo nl2br(htmlspecialchars($message['message'])); ?>
            </div>
        </div>
    </div>
    
    <?php if ($message['admin_response']): ?>
        <div class="message-details" style="background: #e8f5e9;">
            <h4>📝 Ответ администратора:</h4>
            <div class="message-field">
                <div class="field-label">Ответил:</div>
                <div class="field-value"><?php echo htmlspecialchars($message['admin_name'] ?? 'Администратор'); ?></div>
            </div>
            
            <div class="message-field">
                <div class="field-label">Дата ответа:</div>
                <div class="field-value">
                    <?php echo date('d.m.Y H:i', strtotime($message['responded_at'])); ?>
                </div>
            </div>
            
            <div class="message-field">
                <div class="field-label">Текст ответа:</div>
                <div class="field-value" style="white-space: pre-wrap; background: white; padding: 15px; border-radius: 4px; border: 1px solid #ddd;">
                    <?php echo nl2br(htmlspecialchars($message['admin_response'])); ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="response-form">
            <h4>✍️ Ответить на сообщение:</h4>
            <textarea id="responseText" placeholder="Введите текст ответа..."></textarea>
            <button onclick="sendResponse(<?php echo $messageId; ?>)">Отправить ответ</button>
        </div>
    <?php endif; ?>
    
    <?php
} catch (PDOException $e) {
    echo '<div style="color: #e74c3c;">Ошибка базы данных</div>';
}
?>