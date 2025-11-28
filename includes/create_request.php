<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $service_type = trim($_POST['service_type']);
    $description = trim($_POST['description']);
    $address = trim($_POST['address'] ?? '');
    $preferred_date = $_POST['preferred_date'] ?? '';
    $preferred_time = $_POST['preferred_time'] ?? '';

    if (empty($service_type) || empty($description)) {
        header('Location: ../user.php?error=empty_fields');
        exit();
    }

    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "INSERT INTO requests 
                (user_id, service_type, description, address, preferred_date, preferred_time) 
                VALUES 
                (:user_id, :service_type, :description, :address, :preferred_date, :preferred_time)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $user_id,
            ':service_type' => $service_type,
            ':description' => $description,
            ':address' => $address,
            ':preferred_date' => $preferred_date ?: null,
            ':preferred_time' => $preferred_time ?: null
        ]);

        header('Location: ../user.php?message=request_created');
        exit();

    } catch (PDOException $e) {
        header('Location: ../user.php?error=request_failed');
        exit();
    }
} else {
    header('Location: ../user.php');
    exit();
}
?>