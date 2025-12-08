<?php
require_once 'includes/config.php';

// Проверка прав администратора
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Обработка действий
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'mark_read':
                if (isset($_GET['id'])) {
                    $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE id = ?");
                    $stmt->execute([$_GET['id']]);
                }
                break;
                
            case 'delete':
                if (isset($_GET['id'])) {
                    $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
                    $stmt->execute([$_GET['id']]);
                }
                break;
                
            case 'respond':
                if (isset($_POST['message_id']) && isset($_POST['response'])) {
                    $stmt = $pdo->prepare("
                        UPDATE messages 
                        SET admin_response = ?, 
                            responded_at = NOW(), 
                            responded_by = ?,
                            is_read = 1
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $_POST['response'],
                        $_SESSION['user_id'],
                        $_POST['message_id']
                    ]);
                    
                    // Можно добавить отправку email с ответом
                }
                break;
        }
        
        header('Location: admin_messages.php');
        exit();
    }
    
    // Получение сообщений
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = 20;
    $offset = ($page - 1) * $limit;
    
    // Фильтры
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
    $where = '';
    $params = [];
    
    switch ($filter) {
        case 'unread':
            $where = 'WHERE is_read = 0';
            break;
        case 'read':
            $where = 'WHERE is_read = 1';
            break;
        case 'with_response':
            $where = 'WHERE admin_response IS NOT NULL';
            break;
        case 'without_response':
            $where = 'WHERE admin_response IS NULL';
            break;
    }
    
    // Общее количество
    $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM messages $where");
    $countStmt->execute($params);
    $totalMessages = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalMessages / $limit);
    
    // Получение сообщений
    $stmt = $pdo->prepare("
        SELECT m.*, 
               u.name as admin_name 
        FROM messages m
        LEFT JOIN users u ON m.responded_by = u.id
        $where 
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?
    ");
    
    $stmt->execute(array_merge($params, [$limit, $offset]));
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Ошибка базы данных: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление сообщениями - Админ панель</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin_massege.css">
</head>
<body>
    <!-- Подключаем хедер -->
    <?php include 'templates/header.php'; ?>
    
    <main class="admin-messages">
        <div class="admin-header">
            <h1>📨 Управление сообщениями</h1>
            <div class="admin-nav">
                <a href="admin.php">← Назад в админ панель</a>
            </div>
        </div>
        
        <!-- Статистика -->
        <?php if (!isset($error)): ?>
        <div class="stats">
            <div class="stat-card total">
                <div class="stat-number"><?php echo $totalMessages; ?></div>
                <div class="stat-label">Всего сообщений</div>
            </div>
            
            <?php 
            // Получаем дополнительные статистические данные
            try {
                $unreadStmt = $pdo->query("SELECT COUNT(*) as count FROM messages WHERE is_read = 0");
                $unreadCount = $unreadStmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                $respondedStmt = $pdo->query("SELECT COUNT(*) as count FROM messages WHERE admin_response IS NOT NULL");
                $respondedCount = $respondedStmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                $readStmt = $pdo->query("SELECT COUNT(*) as count FROM messages WHERE is_read = 1");
                $readCount = $readStmt->fetch(PDO::FETCH_ASSOC)['count'];
            } catch (Exception $e) {
                $unreadCount = $readCount = $respondedCount = 0;
            }
            ?>
            
            <div class="stat-card unread">
                <div class="stat-number"><?php echo $unreadCount; ?></div>
                <div class="stat-label">Непрочитанных</div>
            </div>
            
            <div class="stat-card read">
                <div class="stat-number"><?php echo $readCount; ?></div>
                <div class="stat-label">Прочитанных</div>
            </div>
            
            <div class="stat-card responded">
                <div class="stat-number"><?php echo $respondedCount; ?></div>
                <div class="stat-label">С ответом</div>
            </div>
        </div>
        
        <!-- Фильтры -->
        <div class="filters">
            <a href="?filter=all" class="filter-btn <?php echo $filter == 'all' ? 'active' : ''; ?>">
                Все сообщения
            </a>
            <a href="?filter=unread" class="filter-btn <?php echo $filter == 'unread' ? 'active' : ''; ?>">
                Непрочитанные
            </a>
            <a href="?filter=read" class="filter-btn <?php echo $filter == 'read' ? 'active' : ''; ?>">
                Прочитанные
            </a>
            <a href="?filter=with_response" class="filter-btn <?php echo $filter == 'with_response' ? 'active' : ''; ?>">
                С ответом
            </a>
            <a href="?filter=without_response" class="filter-btn <?php echo $filter == 'without_response' ? 'active' : ''; ?>">
                Без ответа
            </a>
        </div>
        
        <!-- Таблица сообщений -->
        <div class="messages-table-container">
            <table class="messages-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Отправитель</th>
                        <th>Тема</th>
                        <th>Дата</th>
                        <th>Статус</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($messages)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: #6c757d;">
                                Сообщений не найдено
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($messages as $message): ?>
                        <tr class="<?php echo $message['is_read'] ? '' : 'unread'; ?>">
                            <td><?php echo $message['id']; ?></td>
                            <td>
                                <div><?php echo htmlspecialchars($message['user_name']); ?></div>
                                <div class="message-meta"><?php echo htmlspecialchars($message['user_email']); ?></div>
                            </td>
                            <td>
                                <div class="message-subject"><?php echo htmlspecialchars($message['subject']); ?></div>
                                <div class="message-preview">
                                    <?php echo htmlspecialchars(substr($message['message'], 0, 100)); ?>...
                                </div>
                            </td>
                            <td>
                                <?php echo date('d.m.Y H:i', strtotime($message['created_at'])); ?>
                            </td>
                            <td>
                                <?php if (!$message['is_read']): ?>
                                    <span style="color: #e74c3c; font-weight: bold;">Новое</span>
                                <?php elseif ($message['admin_response']): ?>
                                    <span style="color: #28a745;">Ответ дан</span>
                                <?php else: ?>
                                    <span style="color: #3498db;">Прочитано</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="message-actions">
                                    <button class="action-btn view" 
                                            onclick="viewMessage(<?php echo $message['id']; ?>)">
                                        Просмотр
                                    </button>
                                    <?php if (!$message['is_read']): ?>
                                        <a href="?action=mark_read&id=<?php echo $message['id']; ?>&filter=<?php echo $filter; ?>&page=<?php echo $page; ?>"
                                           class="action-btn respond">
                                            Прочитано
                                        </a>
                                    <?php endif; ?>
                                    <a href="?action=delete&id=<?php echo $message['id']; ?>&filter=<?php echo $filter; ?>&page=<?php echo $page; ?>"
                                       class="action-btn delete"
                                       onclick="return confirm('Удалить это сообщение?')">
                                        Удалить
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Пагинация -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?filter=<?php echo $filter; ?>&page=<?php echo $i; ?>"
                   class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        
        <?php else: ?>
            <div style="color: #e74c3c; padding: 20px; background: #f8d7da; border-radius: 8px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
    </main>
    
    <!-- Модальное окно для просмотра сообщения -->
    <div class="modal" id="messageModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Просмотр сообщения</h3>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Контент будет загружен через AJAX -->
            </div>
        </div>
    </div>
    
    <script>
        // Функции для работы с модальным окном
        function viewMessage(messageId) {
            fetch('includes/get_message.php?id=' + messageId)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('modalBody').innerHTML = html;
                    document.getElementById('messageModal').style.display = 'flex';
                    document.body.style.overflow = 'hidden';
                })
                .catch(error => {
                    alert('Ошибка загрузки сообщения');
                });
        }
        
        function closeModal() {
            document.getElementById('messageModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        function sendResponse(messageId) {
            const responseText = document.getElementById('responseText').value;
            
            if (!responseText.trim()) {
                alert('Введите текст ответа');
                return;
            }
            
            const formData = new FormData();
            formData.append('message_id', messageId);
            formData.append('response', responseText);
            
            fetch('admin_messages.php?action=respond', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.ok) {
                    alert('Ответ успешно отправлен');
                    closeModal();
                    location.reload();
                } else {
                    alert('Ошибка при отправке ответа');
                }
            })
            .catch(error => {
                alert('Ошибка сети');
            });
        }
        
        // Закрытие модального окна при клике вне его
        document.getElementById('messageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        
        // Закрытие по Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>