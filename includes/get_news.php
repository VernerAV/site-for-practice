<?php
require_once 'config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = "SELECT * FROM news ORDER BY created_at DESC";
    $stmt = $pdo->query($sql);
    $news = $stmt->fetchAll();
    
    if (empty($news)) {
        echo '<tr><td colspan="4" style="text-align: center;">Новостей пока нет</td></tr>';
    } else {
        foreach ($news as $item) {
            echo '
            <tr>
                <td>' . htmlspecialchars($item['id']) . '</td>
                <td>' . htmlspecialchars($item['title']) . '</td>
                <td>' . date('d.m.Y H:i', strtotime($item['created_at'])) . '</td>
                <td>
                    <button class="btn btn-primary" onclick="editNews(' . $item['id'] . ', \'' . addslashes($item['title']) . '\', \'' . addslashes($item['description']) . '\')">Редактировать</button>
                    <a href="includes/delete_news.php?id=' . $item['id'] . '" class="btn btn-danger" onclick="return confirm(\'Удалить эту новость?\')">Удалить</a>
                </td>
            </tr>';
        }
    }
    
} catch (PDOException $e) {
    echo '<tr><td colspan="4" style="text-align: center; color: red;">Ошибка загрузки новостей</td></tr>';
}
?>