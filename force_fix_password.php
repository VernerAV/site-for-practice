<?php
$host = 'localhost';
$dbname = 'vorotnikova';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    echo "🔧 Исправляем пароль...<br>";
    
    // Создаем правильный хеш для пароля 'adminis1213'
    $hashed_password = password_hash('adminis1213', PASSWORD_DEFAULT);
    
    echo "🔐 Новый хеш: " . $hashed_password . "<br>";
    
    // Обновляем пароль в БД
    $sql = "UPDATE users SET password = :password WHERE email = 'admin@strogino.ru'";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([':password' => $hashed_password]);
    
    if ($result) {
        echo "✅ Пароль успешно обновлен в БД!<br>";
        
        // Проверим что записалось
        $check_sql = "SELECT password FROM users WHERE email = 'admin@strogino.ru'";
        $check_stmt = $pdo->query($check_sql);
        $new_password = $check_stmt->fetchColumn();
        
        echo "🔍 Проверка: " . $new_password . "<br>";
        
        // Проверим что пароль работает
        if (password_verify('adminis1213', $new_password)) {
            echo "🎉 ВСЕ РАБОТАЕТ! Теперь можно входить с:<br>";
            echo "👤 Email: admin@strogino.ru<br>";
            echo "🔐 Пароль: adminis1213<br>";
        } else {
            echo "❌ Что-то пошло не так при проверке...";
        }
    } else {
        echo "❌ Не удалось обновить пароль";
    }
    
} catch (PDOException $e) {
    echo "❌ Ошибка: " . $e->getMessage();
}
?>