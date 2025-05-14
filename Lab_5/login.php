<?php
session_start();
require 'db.php';

if (!empty($_SESSION['login'])) {
    header('Location: index.php');
    exit();
}

$messages = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = $_POST['login'] ?? '';
    $pass = $_POST['pass'] ?? '';

    try {
        $stmt = $pdo->prepare("SELECT * FROM applications WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch();

        if ($user && password_verify($pass, $user['pass_hash'])) {
            $_SESSION['login'] = $user['login'];
            $_SESSION['uid'] = $user['id'];

            header('Location: index.php');
            exit();
        } else {
            $messages[] = '<div>Неверный логин или пароль</div>';
        }
    } catch (PDOException $e) {
        $messages[] = '<div>Ошибка входа: '.htmlspecialchars($e->getMessage()).'</div>';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Вход</title>
</head>
<body>
    <div>
        <?php if (!empty($messages)): ?>
            <div>
                <?php foreach ($messages as $message): ?>
                    <?= $message ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div>
                <label for="login">Логин</label>
                <input type="text" id="login" name="login" required>
            </div>
            <div>
                <label for="pass">Пароль</label>
                <input type="password" id="pass" name="pass" required>
            </div>
            <button type="submit">Войти</button>
        </form>
    </div>
</body>
</html>