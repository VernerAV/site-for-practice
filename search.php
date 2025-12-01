<?php
require_once 'includes/config.php';

$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$results = [];
$totalResults = 0;

if (!empty($query) && strlen($query) >= 2) {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Поиск в новостях
        $sqlNews = "SELECT 
                    id, 
                    title, 
                    description, 
                    image_path,
                    created_at,
                    'news' as type,
                    MATCH(title, description) AGAINST(:query IN BOOLEAN MODE) as relevance
                FROM news 
                WHERE MATCH(title, description) AGAINST(:query IN BOOLEAN MODE)
                OR title LIKE :likeQuery 
                OR description LIKE :likeQuery
                ORDER BY relevance DESC, created_at DESC
                LIMIT 20";
        
        $stmtNews = $pdo->prepare($sqlNews);
        $likeQuery = "%" . $query . "%";
        $stmtNews->execute([
            ':query' => $query . '*',
            ':likeQuery' => $likeQuery
        ]);
        $newsResults = $stmtNews->fetchAll();
        
        // Поиск в услугах
        $sqlServices = "SELECT 
                        id, 
                        service_name as title, 
                        description, 
                        price,
                        unit,
                        'service' as type,
                        MATCH(service_name, description) AGAINST(:query IN BOOLEAN MODE) as relevance
                    FROM services 
                    WHERE MATCH(service_name, description) AGAINST(:query IN BOOLEAN MODE)
                    OR service_name LIKE :likeQuery 
                    OR description LIKE :likeQuery
                    ORDER BY relevance DESC, service_name
                    LIMIT 20";
        
        $stmtServices = $pdo->prepare($sqlServices);
        $stmtServices->execute([
            ':query' => $query . '*',
            ':likeQuery' => $likeQuery
        ]);
        $serviceResults = $stmtServices->fetchAll();
        
        // Объединяем результаты
        $results = [
            'news' => $newsResults,
            'services' => $serviceResults
        ];
        
        $totalResults = count($newsResults) + count($serviceResults);
        
    } catch (PDOException $e) {
        $error = "Ошибка поиска: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Результаты поиска - ГБУ "Жилищник Района Строгино"</title>
    <link rel="stylesheet" href="css/search.css">

</head>
<body>
    <?php include 'templates/header.php'; ?>
    
    <div class="search-results">
        <div class="search-header">
            <h1>Поиск по сайту</h1>
            
            <form class="search-form" action="search.php" method="get">
                <input type="text" name="query" value="<?php echo htmlspecialchars($query); ?>" 
                       placeholder="Введите поисковый запрос..." required>
                <button type="submit">Найти</button>
            </form>
            
            <?php if (!empty($query)): ?>
                <div class="result-count">
                    По запросу "<strong><?php echo htmlspecialchars($query); ?></strong>" 
                    найдено результатов: <strong><?php echo $totalResults; ?></strong>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (empty($query)): ?>
            <div class="no-results">
                <h3>Введите поисковый запрос</h3>
                <p>Начните поиск, введя ключевые слова в поле выше.</p>
            </div>
            
        <?php elseif (strlen($query) < 2): ?>
            <div class="no-results">
                <h3>Слишком короткий запрос</h3>
                <p>Введите минимум 2 символа для поиска.</p>
            </div>
            
        <?php elseif ($totalResults == 0): ?>
            <div class="no-results">
                <h3>Ничего не найдено</h3>
                <p>Попробуйте изменить поисковый запрос или проверьте орфографию.</p>
                
                <div class="suggestions">
                    <p>Возможно, вас заинтересует:</p>
                    <?php
                    $suggestions = ['ремонт', 'уборка', 'новости', 'услуги', 'цены'];
                    foreach ($suggestions as $suggestion):
                    ?>
                        <span class="suggestion" onclick="document.querySelector('input[name=query]').value='<?php echo $suggestion; ?>'; document.querySelector('form').submit();">
                            <?php echo $suggestion; ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
            
        <?php else: ?>
            <!-- Результаты новостей -->
            <?php if (!empty($results['news'])): ?>
                <h2>Новости (<?php echo count($results['news']); ?>)</h2>
                <?php foreach ($results['news'] as $item): ?>
                    <div class="result-item">
                        <span class="result-type">Новость</span>
                        <h3>
                            <a href="news.php?id=<?php echo $item['id']; ?>">
                                <?php echo highlightWords($item['title'], $query); ?>
                            </a>
                        </h3>
                        <div class="excerpt">
                            <?php echo highlightWords(getExcerpt($item['description'], 200), $query); ?>
                        </div>
                        <div class="meta">
                            Дата: <?php echo date('d.m.Y', strtotime($item['created_at'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <!-- Результаты услуг -->
            <?php if (!empty($results['services'])): ?>
                <h2>Услуги (<?php echo count($results['services']); ?>)</h2>
                <?php foreach ($results['services'] as $item): ?>
                    <div class="result-item">
                        <span class="result-type">Услуга</span>
                        <h3>
                            <a href="services.php#service-<?php echo $item['id']; ?>">
                                <?php echo highlightWords($item['title'], $query); ?>
                            </a>
                        </h3>
                        <div class="excerpt">
                            <?php echo highlightWords(getExcerpt($item['description'], 200), $query); ?>
                        </div>
                        <div class="meta">
                            Цена: <?php echo $item['price']; ?> руб. <?php echo $item['unit'] ? '/ ' . $item['unit'] : ''; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <?php include 'templates/footer.php'; ?>
    
    <script>
        // AJAX подсказки при вводе
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('input[name="query"]');
            let timeoutId;
            
            searchInput.addEventListener('input', function() {
                clearTimeout(timeoutId);
                const query = this.value.trim();
                
                if (query.length >= 2) {
                    timeoutId = setTimeout(function() {
                        fetchSuggestions(query);
                    }, 300);
                }
            });
        });
        
        function fetchSuggestions(query) {
            fetch('includes/search_suggestions.php?query=' + encodeURIComponent(query))
                .then(response => response.json())
                .then(data => {
                    // Обработка подсказок (можно добавить dropdown)
                    console.log('Suggestions:', data);
                })
                .catch(error => console.error('Error:', error));
        }
        
        // Подсветка на странице
        function highlightText() {
            const query = "<?php echo addslashes($query); ?>";
            if (!query || query.length < 2) return;
            
            const elements = document.querySelectorAll('.result-item h3, .result-item .excerpt');
            const regex = new RegExp(query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'gi');
            
            elements.forEach(element => {
                const html = element.innerHTML;
                const highlighted = html.replace(regex, match => 
                    `<span class="highlight">${match}</span>`
                );
                element.innerHTML = highlighted;
            });
        }
        
        highlightText();
    </script>
</body>
</html>

<?php
// Вспомогательные функции
function highlightWords($text, $words) {
    if (empty($words)) return $text;
    
    $wordList = explode(' ', $words);
    foreach ($wordList as $word) {
        if (strlen($word) > 2) {
            $text = preg_replace('/(' . preg_quote($word, '/') . ')/iu', 
                                '<span class="highlight">$1</span>', 
                                $text);
        }
    }
    return $text;
}

function getExcerpt($text, $length = 200) {
    $text = strip_tags($text);
    if (strlen($text) <= $length) {
        return $text;
    }
    
    $excerpt = substr($text, 0, $length);
    $lastSpace = strrpos($excerpt, ' ');
    if ($lastSpace !== false) {
        $excerpt = substr($excerpt, 0, $lastSpace);
    }
    
    return $excerpt . '...';
}
?>