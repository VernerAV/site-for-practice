<?php
require_once 'includes/check_auth.php';
checkAuth();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Личный кабинет</title>
</head>
<body>
    <h1>Добро пожаловать, <?php echo $_SESSION['user_email']; ?>!</h1>
    
    <?php if (isAdmin()): ?>
        <p>Вы вошли как <strong>администратор</strong></p>
        <a href="admin.php">Панель администратора</a>
    <?php else: ?>
        <p>Вы вошли как <strong>пользователь</strong></p>
    <?php endif; ?>
    
    <a href="includes/logout.php">Выйти</a>
</body>
</html>