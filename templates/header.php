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
            <img src="img/icon.png" alt="icon">
            <h1>ГБУ "Жилищник Района Строгино"</h1>
        </div>
        <!-- Поиск -->
        <div id="search">
            <form action="search.php" method="get">
                <input type="text" name="query" id="searchInput" placeholder="">
                <button type="submit">
                <img src="img/search.png" alt="Поиск">
                </button>
            </form>
        </div>
    <div class="enter"><a href="enter.php">Вход/регистрация</a></div>
   
</div>

<nav class="main-menu">
    <ul>
        <li><a href="news.php">Новости</a></li>
        <li><a href="price.php">Платные услуги</a></li>
        <li><a href="about.php">О нас</a></li>
        <li><a href="user.php">Личный кабинет</a></li>
    </ul>

    <div class="contact-info">
        <p>Телефон: 8(495) 758-38-22</p>
        <p>Эл. почта: gbu-strogino@mail.ru</p>
    </div>
</nav>
        
</body>
</html>



