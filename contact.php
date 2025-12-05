<?php
require_once 'includes/config.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $first_name = trim($_POST['first_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Валидация
    if (empty($first_name)) {
        $errors[] = 'Введите ваше имя';
    } elseif (strlen($first_name) < 2) {
        $errors[] = 'Имя должно содержать не менее 2 символов';
    }
    
    if (empty($email)) {
        $errors[] = 'Введите ваш email';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Введите корректный email адрес';
    }
    
    if (empty($subject)) {
        $errors[] = 'Введите тему сообщения';
    } elseif (strlen($subject) < 3) {
        $errors[] = 'Тема должна содержать не менее 3 символов';
    }
    
    if (empty($message)) {
        $errors[] = 'Введите текст сообщения';
    } elseif (strlen($message) < 10) {
        $errors[] = 'Сообщение должно содержать не менее 10 символов';
    }
    
    // Проверка на спам
    $honeypot = $_POST['website'] ?? '';
    if (!empty($honeypot)) {
        // Это бот, молча завершаем
        $success = true; // Показываем успех, но не сохраняем
    } else {
        // Проверка частоты отправки (не более 3 сообщений в час с одного IP)
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $ip = $_SERVER['REMOTE_ADDR'];
            $hourAgo = date('Y-m-d H:i:s', strtotime('-1 hour'));
            
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM messages WHERE ip_address = ? AND created_at > ?");
            $stmt->execute([$ip, $hourAgo]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] >= 3) {
                $errors[] = 'Слишком много сообщений. Пожалуйста, попробуйте позже.';
            }
        } catch (PDOException $e) {
            // Пропускаем проверку при ошибке БД
        }
    }
    
    // Если нет ошибок, сохраняем сообщение
    if (empty($errors) && empty($honeypot)) {
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $ip = $_SERVER['REMOTE_ADDR'];
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            $stmt = $pdo->prepare("
                INSERT INTO messages (first_name, user_email, subject, message, ip_address, user_agent)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([$first_name, $email, $subject, $message, $ip, $userAgent]);
            
            // Отправляем email уведомление (опционально)
            sendEmailNotification($first_name, $email, $subject, $message);
            
            $success = true;
            
            // Очищаем форму
            $first_name = $email = $subject = $message = '';
            
        } catch (PDOException $e) {
            $errors[] = 'Ошибка при сохранении сообщения. Пожалуйста, попробуйте позже.';
        }
    }
}

// Функция отправки email уведомления
function sendEmailNotification($first_name, $email, $subject, $message) {
    $to = 'gbu-strogino@mail.ru'; // Email администратора
    $headers = "From: no-reply@gbu-strogino.ru\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/plain; charset=utf-8\r\n";
    
    $emailSubject = "Новое сообщение с сайта: " . $subject;
    $emailBody = "Имя: $first_name\n";
    $emailBody .= "Email: $email\n";
    $emailBody .= "Тема: $subject\n\n";
    $emailBody .= "Сообщение:\n$message\n\n";
    $emailBody .= "---\n";
    $emailBody .= "Это сообщение отправлено с формы обратной связи на сайте gbu-strogino.ru";
    
    @mail($to, $emailSubject, $emailBody, $headers);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Контакты и обратная связь</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/contact.css">
</head>
<body>
    <!-- Подключаем хедер -->
    <?php include 'templates/header.php'; ?>
    
        <!-- Форма обратной связи -->
        <section class="contact-form-section">
            <h2>Обратная связь</h2>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <strong>✅ Ваше сообщение успешно отправлено!</strong>
                    <p>Мы получили ваше сообщение и ответим вам в ближайшее время.</p>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <strong>⚠️ Пожалуйста, исправьте следующие ошибки:</strong>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="contactForm">
                <!-- Хонейпот поле для защиты от спама -->
                <div class="honeypot">
                    <label for="website">Если вы человек, оставьте это поле пустым:</label>
                    <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                </div>
                
                <div class="form-group">
                    <label for="name" class="form-label">
                        Ваше имя <span class="required">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           class="form-control" 
                           value="<?php echo htmlspecialchars($first_name ?? ''); ?>"
                           required
                           placeholder="Введите ваше имя">
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">
                        Ваш email <span class="required">*</span>
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-control" 
                           value="<?php echo htmlspecialchars($email ?? ''); ?>"
                           required
                           placeholder="Введите ваш email">
                </div>
                
                <div class="form-group">
                    <label for="subject" class="form-label">
                        Тема сообщения <span class="required">*</span>
                    </label>
                    <input type="text" 
                           id="subject" 
                           name="subject" 
                           class="form-control" 
                           value="<?php echo htmlspecialchars($subject ?? ''); ?>"
                           required
                           placeholder="Введите тему сообщения">
                </div>
                
                <div class="form-group">
                    <label for="message" class="form-label">
                        Сообщение <span class="required">*</span>
                    </label>
                    <textarea id="message" 
                              name="message" 
                              class="form-control" 
                              required
                              placeholder="Опишите ваш вопрос или проблему"><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                </div>
                
                <button type="submit" class="btn-submit" id="submitBtn">
                    Отправить сообщение
                </button>
                
                <div class="privacy-note">
                    Отправляя сообщение, вы соглашаетесь с 
                    <a href="privacy.php" target="_blank">политикой конфиденциальности</a>
                </div>
            </form>
        </section>
    </main>
    
    <!-- Подключаем футер -->
    <?php include 'templates/footer.php'; ?>
    
    <script>
        // Валидация формы на клиенте
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('contactForm');
            const submitBtn = document.getElementById('submitBtn');
            
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Проверка обязательных полей
                    const name = document.getElementById('name').value.trim();
                    const email = document.getElementById('email').value.trim();
                    const subject = document.getElementById('subject').value.trim();
                    const message = document.getElementById('message').value.trim();
                    
                    let errors = [];
                    
                    if (name.length < 2) {
                        errors.push('Имя должно содержать не менее 2 символов');
                        document.getElementById('name').style.borderColor = '#e74c3c';
                    } else {
                        document.getElementById('name').style.borderColor = '';
                    }
                    
                    if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                        errors.push('Введите корректный email адрес');
                        document.getElementById('email').style.borderColor = '#e74c3c';
                    } else {
                        document.getElementById('email').style.borderColor = '';
                    }
                    
                    if (subject.length < 3) {
                        errors.push('Тема должна содержать не менее 3 символов');
                        document.getElementById('subject').style.borderColor = '#e74c3c';
                    } else {
                        document.getElementById('subject').style.borderColor = '';
                    }
                    
                    if (message.length < 10) {
                        errors.push('Сообщение должно содержать не менее 10 символов');
                        document.getElementById('message').style.borderColor = '#e74c3c';
                    } else {
                        document.getElementById('message').style.borderColor = '';
                    }
                    
                    if (errors.length > 0) {
                        e.preventDefault();
                        alert('Пожалуйста, исправьте следующие ошибки:\n\n' + errors.join('\n'));
                        return false;
                    }
                    
                    // Блокируем кнопку отправки
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.textContent = 'Отправка...';
                    }
                    
                    return true;
                });
                
                // Автосохранение в localStorage
                const inputs = form.querySelectorAll('input, textarea');
                inputs.forEach(input => {
                    // Восстанавливаем сохраненные значения
                    const savedValue = localStorage.getItem('contact_' + input.id);
                    if (savedValue && !input.value) {
                        input.value = savedValue;
                    }
                    
                    // Сохраняем изменения
                    input.addEventListener('input', function() {
                        localStorage.setItem('contact_' + this.id, this.value);
                    });
                });
                
                // Очистка localStorage после успешной отправки
                if (window.location.search.includes('success')) {
                    inputs.forEach(input => {
                        localStorage.removeItem('contact_' + input.id);
                    });
                }
                
                // Автозаполнение имени и email если пользователь авторизован
                <?php if (isset($_SESSION['first_name']) || isset($_SESSION['user_email'])): ?>
                    const nameField = document.getElementById('name');
                    const emailField = document.getElementById('email');
                    
                    <?php if (isset($_SESSION['first_name']) && !empty($_SESSION['first_name'])): ?>
                        if (nameField && !nameField.value) {
                            nameField.value = '<?php echo addslashes($_SESSION["first_name"]); ?>';
                        }
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['user_email']) && !empty($_SESSION['user_email'])): ?>
                        if (emailField && !emailField.value) {
                            emailField.value = '<?php echo addslashes($_SESSION["user_email"]); ?>';
                        }
                    <?php endif; ?>
                <?php endif; ?>
            }
            
            // Подсчет символов в сообщении
            const messageField = document.getElementById('message');
            if (messageField) {
                const charCounter = document.createElement('div');
                charCounter.style.fontSize = '12px';
                charCounter.style.color = '#6c757d';
                charCounter.style.marginTop = '5px';
                charCounter.textContent = 'Символов: 0';
                
                messageField.parentNode.appendChild(charCounter);
                
                messageField.addEventListener('input', function() {
                    const length = this.value.length;
                    charCounter.textContent = 'Символов: ' + length;
                    
                    if (length < 10) {
                        charCounter.style.color = '#e74c3c';
                    } else if (length < 1000) {
                        charCounter.style.color = '#28a745';
                    } else {
                        charCounter.style.color = '#ffc107';
                    }
                });
                
                // Триггерим событие для начального подсчета
                messageField.dispatchEvent(new Event('input'));
            }
        });
    </script>
</body>
</html>