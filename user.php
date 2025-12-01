<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/check_auth.php';
checkAuth();

$user_id = $_SESSION['user_id'];

// Получаем данные пользователя
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    
    // Основная информация пользователя
    $user_sql = "SELECT u.email, u.role, up.first_name, up.last_name, up.middle_name, 
                        up.birth_date, up.address, up.phone 
                 FROM users u 
                 LEFT JOIN user_profiles up ON u.id = up.user_id 
                 WHERE u.id = :user_id";
    $user_stmt = $pdo->prepare($user_sql);
    $user_stmt->execute([':user_id' => $user_id]);
    $user_data = $user_stmt->fetch();
    
    // Заявки пользователя
    $requests_sql = "SELECT * FROM requests WHERE user_id = :user_id ORDER BY created_at DESC";
    $requests_stmt = $pdo->prepare($requests_sql);
    $requests_stmt->execute([':user_id' => $user_id]);
    $requests = $requests_stmt->fetchAll();
    
} catch (PDOException $e) {
    die("Ошибка базы данных: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/user.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Личный кабинет</h1>
            <nav>
                <a href="index.php">Главная</a>
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <a href="admin.php">Панель администратора</a>
                <?php endif; ?>
                <a href="includes/logout.php">Выйти</a>
            </nav>
        </header>

        <div class="user-content">
            <!-- Вкладки -->
            <div class="tabs">
                <button class="tab-button active" onclick="showTab('profile')">Профиль</button>
                <button class="tab-button" onclick="showTab('requests')">Мои заявки</button>
                <button class="tab-button" onclick="showTab('new-request')">Новая заявка</button>
            </div>

            <!-- Вкладка профиля -->
            <div id="profile" class="tab-content active">
                <h2>Личная информация</h2>
                <form action="includes/update_profile.php" method="POST">
                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" value="<?php echo $user_data['email']; ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>Фамилия:</label>
                        <input type="text" name="last_name" value="<?php echo $user_data['last_name'] ?? ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Имя:</label>
                        <input type="text" name="first_name" value="<?php echo $user_data['first_name'] ?? ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Отчество:</label>
                        <input type="text" name="middle_name" value="<?php echo $user_data['middle_name'] ?? ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Дата рождения:</label>
                        <input type="date" name="birth_date" value="<?php echo $user_data['birth_date'] ?? ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>Телефон:</label>
                        <input type="tel" name="phone" value="<?php echo $user_data['phone'] ?? ''; ?>">
                    </div>
                   <div class="form-group">
                        <label>Адрес *:</label>
                        <input type="text" id="address" name="address" 
                               value="<?php echo htmlspecialchars($user_data['address'] ?? ''); ?>" 
                               placeholder="Начните вводить адрес..." required>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Подъезд:</label>
                            <input type="text" id="entrance" name="entrance" 
                                value="<?php echo htmlspecialchars($user_data['entrance'] ?? ''); ?>" 
                                placeholder="№">
                        </div>
                        
                        <div class="form-group">
                            <label>Этаж:</label>
                            <input type="text" id="floor" name="floor" 
                                value="<?php echo htmlspecialchars($user_data['floor'] ?? ''); ?>" 
                                placeholder="№">
                        </div>
                        
                        <div class="form-group">
                            <label>Квартира *:</label>
                            <input type="text" id="apartment" name="apartment" 
                                value="<?php echo htmlspecialchars($user_data['apartment'] ?? ''); ?>" 
                                placeholder="№" required>
                        </div>
                    </div>
        <button type="submit" class="btn-primary">Сохранить изменения</button>
    </form>
</div>

            <!-- Вкладка заявок -->
            <div id="requests" class="tab-content">
                <h2>Мои заявки</h2>
                <?php if (empty($requests)): ?>
                    <p>У вас пока нет заявок</p>
                <?php else: ?>
                    <div class="requests-list">
                        <?php foreach ($requests as $request): ?>
                            <div class="request-item">
                                <h3><?php echo htmlspecialchars($request['service_type']); ?></h3>
                                <p><?php echo htmlspecialchars($request['description']); ?></p>
                                <div class="request-meta">
                                    <span class="status status-<?php echo $request['status']; ?>">
                                        <?php 
                                        $statuses = [
                                            'pending' => 'На рассмотрении',
                                            'in_progress' => 'В работе', 
                                            'completed' => 'Выполнена',
                                            'cancelled' => 'Отменена'
                                        ];
                                        echo $statuses[$request['status']];
                                        ?>
                                    </span>
                                    <span class="date"><?php echo date('d.m.Y H:i', strtotime($request['created_at'])); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Вкладка новой заявки -->
            <div id="new-request" class="tab-content">
                <h2>Оставить заявку</h2>
                <form action="includes/create_request.php" method="POST">
                    <div class="form-group">
                        <label>Тип услуги:</label>
                        <select name="service_type" required>
                            <option value="">Выберите услугу</option>
                            <option value="Ремонт">Ремонт</option>
                            <option value="Уборка">Уборка</option>
                            <option value="Вывоз мусора">Вывоз мусора</option>
                            <option value="Техническое обслуживание">Техническое обслуживание</option>
                            <option value="Консультация">Консультация</option>
                            <option value="Другое">Другое</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Описание проблемы/запроса:</label>
                        <textarea name="description" rows="5" required placeholder="Подробно опишите вашу проблему или запрос"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Адрес выполнения работ (если отличается от указанного в профиле):</label>
                        <textarea name="address" rows="3" placeholder="<?php echo $user_data['address'] ?? ''; ?>"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Предпочтительная дата:</label>
                        <input type="date" name="preferred_date" min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label>Предпочтительное время:</label>
                        <input type="time" name="preferred_time">
                    </div>
                    <button type="submit" class="btn-primary">Отправить заявку</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Скрыть все вкладки
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Показать выбранную вкладку
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }
    </script>

  <script src="js/address-autocomplete.js"></script>
</body>
</html>