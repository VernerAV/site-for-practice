<?php
require_once 'config.php';

header('Content-Type: application/json');

$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$suggestions = [];

if (strlen($query) >= 2) {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Подсказки из заголовков новостей
        $sqlNews = "SELECT DISTINCT title FROM news 
                   WHERE title LIKE :query 
                   ORDER BY created_at DESC 
                   LIMIT 5";
        
        $stmtNews = $pdo->prepare($sqlNews);
        $stmtNews->execute([':query' => $query . '%']);
        $news = $stmtNews->fetchAll(PDO::FETCH_COLUMN);
        
        // Подсказки из названий услуг
        $sqlServices = "SELECT DISTINCT service_name FROM services 
                       WHERE service_name LIKE :query 
                       ORDER BY service_name 
                       LIMIT 5";
        
        $stmtServices = $pdo->prepare($sqlServices);
        $stmtServices->execute([':query' => $query . '%']);
        $services = $stmtServices->fetchAll(PDO::FETCH_COLUMN);
        
        $suggestions = array_merge($news, $services);
        
    } catch (PDOException $e) {
        // В случае ошибки возвращаем пустой массив
    }
}

echo json_encode(array_slice($suggestions, 0, 8));
?>