<?php
session_start();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <!-- Подключение CSS -->
    <link rel="stylesheet" href="css/header.css">
    <!-- Скрипт анимация -->
     <script src="js/search.js" defer></script>
</head>
<body>
    <div class="header">
        <div class="icon">
            <img src="img/icons/icon.png" alt="icon">
            <h1>ГБУ "Жилищник Района Строгино"</h1>
        </div>
        <!-- Поиск -->
<div id="search">
    <form action="search.php" method="get">
        <input type="text" name="query" id="searchInput" placeholder="Поиск новостей, услуг..." 
               autocomplete="off">
        <button type="submit">
            <img src="img/icons/search.png" alt="Поиск">
        </button>
    </form>
    <div class="search-suggestions" id="searchSuggestions"></div>
</div>

<script>
// AJAX подсказки для поиска в шапке
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const suggestionsBox = document.getElementById('searchSuggestions');
    let timeoutId;
    
    searchInput.addEventListener('input', function() {
        clearTimeout(timeoutId);
        const query = this.value.trim();
        
        if (query.length >= 2) {
            timeoutId = setTimeout(() => {
                fetchSuggestions(query);
            }, 300);
        } else {
            suggestionsBox.style.display = 'none';
        }
    });
    
    function fetchSuggestions(query) {
        fetch('includes/search_suggestions.php?query=' + encodeURIComponent(query))
            .then(response => response.json())
            .then(suggestions => {
                if (suggestions.length > 0) {
                    suggestionsBox.innerHTML = suggestions.map(suggestion => 
                        `<div class="search-suggestion-item" onclick="selectSuggestion('${suggestion.replace("'", "\\'")}')">
                            ${suggestion}
                        </div>`
                    ).join('');
                    suggestionsBox.style.display = 'block';
                } else {
                    suggestionsBox.style.display = 'none';
                }
            });
    }
    
    // Закрытие подсказок при клике вне поля
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
            suggestionsBox.style.display = 'none';
        }
    });
});

function selectSuggestion(text) {
    document.getElementById('searchInput').value = text;
    document.getElementById('searchSuggestions').style.display = 'none';
    document.querySelector('#search form').submit();
}
</script>
        
        <div class="enter">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="includes/logout.php">Выйти</a>
            <?php else: ?>
                <a href="login.php">Вход/регистрация</a>
            <?php endif; ?>
        </div>
    </div>

    <nav class="main-menu">
        <ul>
            <li><a href="index.php">Главная</a></li>
            <li><a href="news.php">Новости</a></li>
            <li><a href="price.php">Платные услуги</a></li>
            <li><a href="about.php">О нас</a></li>
            <li>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <a href="admin.php">Панель администратора</a>
                <?php else: ?>
                    <a href="user.php">Личный кабинет</a>
                <?php endif; ?>
            </li>
        </ul>

        <div class="contact-info">
            <p>Телефон: 8(495) 758-38-22</p>
            <p>Эл. почта: gbu-strogino@mail.ru</p>
        </div>
    </nav>
        
</body>
</html>