<?php
require_once 'config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = "SELECT * FROM services ORDER BY category, sort_order, service_name";
    $stmt = $pdo->query($sql);
    $prices = $stmt->fetchAll();
    
    if (empty($prices)) {
        echo '<tr><td colspan="4" style="text-align: center;">Услуг пока нет</td></tr>';
    } else {
        foreach ($prices as $item) {
            echo '
            <tr>
                <td>' . htmlspecialchars($item['id']) . '</td>
                <td>' . htmlspecialchars($item['service_name']) . '</td>
                <td>' . htmlspecialchars($item['price']) . ' руб.' . ($item['unit'] ? ' / ' . htmlspecialchars($item['unit']) : '') . '</td>
                <td>
                    <button class="btn btn-primary" onclick="editPrice(' . $item['id'] . ', \'' . addslashes($item['service_name']) . '\', \'' . addslashes($item['description']) . '\', ' . $item['price'] . ', \'' . addslashes($item['unit']) . '\')">Редактировать</button>
                    <a href="includes/delete_price.php?id=' . $item['id'] . '" class="btn btn-danger" onclick="return confirm(\'Удалить эту услугу?\')">Удалить</a>
                </td>
            </tr>';
        }
    }
    
} catch (PDOException $e) {
    echo '<tr><td colspan="4" style="text-align: center; color: red;">Ошибка загрузки услуг</td></tr>';
}
?>