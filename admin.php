<?php
session_start();
require_once 'includes/check_auth.php';
checkAuth();

if (!isAdmin()) {
    header('Location: user.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора - ГБУ "Жилищник Района Строгино"</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <header class="admin-header">
        <div class="admin-container">
            <nav class="admin-nav">
                <h1>Панель администратора</h1>
                <div class="admin-nav-links">
                    <span>Добро пожаловать, <?php echo $_SESSION['user_email']; ?></span>
                    <a href="user.php">Личный кабинет</a>
                    <a href="logout.php">Выйти</a>
                </div>
            </nav>
        </div>
    </header>

    <div class="admin-container">
        <div class="admin-content">
            <aside class="admin-sidebar">
                <ul>
                    <li><a href="#" class="active" onclick="showSection('news')">Управление новостями</a></li>
                    <li><a href="#" onclick="showSection('prices')">Управление ценами</a></li>
                    <li><a href="#" onclick="showSection('users')">Управление пользователями</a></li>
                </ul>
            </aside>

            <main class="admin-main">
                <!-- Секция новостей -->
                <div id="news" class="section active">
                    <h2>Управление новостями</h2>
                    <button class="btn btn-primary" onclick="showNewsForm()">Добавить новость</button>
                    
                    <!-- Форма добавления/редактирования новости -->
                    <div id="news-form" style="display: none; margin-top: 20px;">
                        <form action="includes/save_news.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="news_id" id="news_id">
                            <div class="form-group">
                                <label>Заголовок</label>
                                <input type="text" name="title" id="news_title" required>
                            </div>
                            <div class="form-group">
                                <label>Описание</label>
                                <textarea name="description" id="news_description" rows="5" required></textarea>
                            </div>
                            <div class="form-group">
                                <label>Изображение</label>
                                <input type="file" name="image" id="news_image" accept="image/*">
                            </div>
                            <button type="submit" class="btn btn-success">Сохранить</button>
                            <button type="button" class="btn" onclick="hideNewsForm()">Отмена</button>
                        </form>
                    </div>

                    <!-- Список новостей -->
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Заголовок</th>
                                <th>Дата</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php include 'includes/get_news.php'; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Секция цен -->
                <div id="prices" class="section">
                    <h2>Управление ценами на услуги</h2>
                    <button class="btn btn-primary" onclick="showPriceForm()">Добавить услугу</button>
                    
                    <!-- Форма добавления/редактирования цены -->
                    <div id="price-form" style="display: none; margin-top: 20px;">
                        <form action="includes/save_price.php" method="POST">
                            <input type="hidden" name="price_id" id="price_id">
                            <div class="form-group">
                                <label>Название услуги</label>
                                <input type="text" name="service_name" id="service_name" required>
                            </div>
                            <div class="form-group">
                                <label>Описание</label>
                                <textarea name="description" id="price_description" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Цена (руб.)</label>
                                <input type="number" name="price" id="service_price" step="0.01" required>
                            </div>
                            <div class="form-group">
                                <label>Единица измерения</label>
                                <input type="text" name="unit" id="service_unit" placeholder="шт., м², час и т.д.">
                            </div>
                            <button type="submit" class="btn btn-success">Сохранить</button>
                            <button type="button" class="btn" onclick="hidePriceForm()">Отмена</button>
                        </form>
                    </div>

                    <!-- Список цен -->
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Услуга</th>
                                <th>Цена</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php include 'includes/get_prices.php'; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Секция пользователей -->
                <div id="users" class="section">
                    <h2>Управление пользователями</h2>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Email</th>
                                <th>Роль</th>
                                <th>Дата регистрации</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php include 'includes/get_users.php'; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <script>
        function showSection(sectionName) {
            // Скрыть все секции
            document.querySelectorAll('.section').forEach(section => {
                section.classList.remove('active');
            });
            
            // Показать выбранную секцию
            document.getElementById(sectionName).classList.add('active');
            
            // Обновить активную ссылку в меню
            document.querySelectorAll('.admin-sidebar a').forEach(link => {
                link.classList.remove('active');
            });
            event.target.classList.add('active');
        }

        function showNewsForm() {
            document.getElementById('news-form').style.display = 'block';
            // Сбросить форму
            document.getElementById('news_id').value = '';
            document.getElementById('news_title').value = '';
            document.getElementById('news_description').value = '';
        }

        function hideNewsForm() {
            document.getElementById('news-form').style.display = 'none';
        }

        function showPriceForm() {
            document.getElementById('price-form').style.display = 'block';
            // Сбросить форму
            document.getElementById('price_id').value = '';
            document.getElementById('service_name').value = '';
            document.getElementById('price_description').value = '';
            document.getElementById('service_price').value = '';
            document.getElementById('service_unit').value = '';
        }

        function hidePriceForm() {
            document.getElementById('price-form').style.display = 'none';
        }

        function editNews(id, title, description) {
            document.getElementById('news_id').value = id;
            document.getElementById('news_title').value = title;
            document.getElementById('news_description').value = description;
            document.getElementById('news-form').style.display = 'block';
        }

        function editPrice(id, name, description, price, unit) {
            document.getElementById('price_id').value = id;
            document.getElementById('service_name').value = name;
            document.getElementById('price_description').value = description;
            document.getElementById('service_price').value = price;
            document.getElementById('service_unit').value = unit;
            document.getElementById('price-form').style.display = 'block';
        }
    </script>
</body>
</html>