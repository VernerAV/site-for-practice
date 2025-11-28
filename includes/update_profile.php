<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $birth_date = $_POST['birth_date'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    
    // Получаем данные адреса
    $street = trim($_POST['street'] ?? '');
    $house = trim($_POST['house'] ?? '');
    $building = trim($_POST['building'] ?? '');
    $entrance = trim($_POST['entrance'] ?? '');
    $apartment = trim($_POST['apartment'] ?? '');

    // Формируем полный адрес
    $address = $address_input;
    if (!empty($entrance)) $address .= ', под.' . $entrance;
    if (!empty($floor)) $address .= ', эт.' . $floor;
    if (!empty($apartment)) $address .= ', кв.' . $apartment;
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Проверяем существует ли профиль
        $check_sql = "SELECT id FROM user_profiles WHERE user_id = :user_id";
        $check_stmt = $pdo->prepare($check_sql);
        $check_stmt->execute([':user_id' => $user_id]);
        $profile_exists = $check_stmt->fetch();

        if ($profile_exists) {
            // Обновляем существующий профиль
            $sql = "UPDATE user_profiles SET 
                    first_name = :first_name,
                    last_name = :last_name, 
                    middle_name = :middle_name,
                    birth_date = :birth_date,
                    phone = :phone,
                    address = :address
                    WHERE user_id = :user_id";
        } else {
            // Создаем новый профиль
            $sql = "INSERT INTO user_profiles 
                    (user_id, first_name, last_name, middle_name, birth_date, phone, address) 
                    VALUES 
                    (:user_id, :first_name, :last_name, :middle_name, :birth_date, :phone, :address)";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $user_id,
            ':first_name' => $first_name,
            ':last_name' => $last_name,
            ':middle_name' => $middle_name,
            ':birth_date' => $birth_date ?: null,
            ':phone' => $phone,
            ':address' => $address
        ]);

        header('Location: ../user.php?message=profile_updated');
        exit();

    } catch (PDOException $e) {
        header('Location: ../user.php?error=update_failed');
        exit();
    }
} else {
    header('Location: ../user.php');
    exit();
}
?>