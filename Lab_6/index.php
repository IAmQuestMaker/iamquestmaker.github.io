<?php
require 'db.php';

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admins (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            login VARCHAR(50) UNIQUE NOT NULL,
            pass_hash VARCHAR(255) NOT NULL
        ) ENGINE=InnoDB
    ");

    $stmt = $pdo->query("SELECT COUNT(*) FROM admins");
    if ($stmt->fetchColumn() == 0) {
        $pass_hash = password_hash('123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO admins (login, pass_hash) VALUES (?, ?)")
            ->execute(['admin', $pass_hash]);
    }
} catch (PDOException $e) {
    die("Ошибка инициализации БД: " . $e->getMessage());
}

if (empty($_SERVER['PHP_AUTH_USER'])) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="Admin Panel"');
    die('Требуется авторизация');
}

try {
    $stmt = $pdo->prepare("SELECT pass_hash FROM admins WHERE login = ?");
    $stmt->execute([$_SERVER['PHP_AUTH_USER']]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($_SERVER['PHP_AUTH_PW'], $admin['pass_hash'])) {
        header('HTTP/1.1 401 Unauthorized');
        header('WWW-Authenticate: Basic realm="Admin Panel"');
        die('Неверные логин или пароль');
    }
} catch (PDOException $e) {
    die("Ошибка аутентификации: " . $e->getMessage());
}

$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

try {
    if ($action === 'delete' && $id) {
        $pdo->prepare("DELETE FROM applications WHERE id = ?")->execute([$id]);
        header("Location: index.php");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
        $stmt = $pdo->prepare("UPDATE applications SET
            name = ?, phone = ?, email = ?, birthdate = ?,
            gender = ?, bio = ?, contract_accepted = ?
            WHERE id = ?");

        $stmt->execute([
            $_POST['name'],
            $_POST['phone'],
            $_POST['email'],
            $_POST['birthdate'],
            $_POST['gender'],
            $_POST['bio'],
            isset($_POST['contract_accepted']) ? 1 : 0,
            $_POST['id']
        ]);

        $pdo->prepare("DELETE FROM application_languages WHERE application_id = ?")
            ->execute([$_POST['id']]);

        $lang_stmt = $pdo->prepare("INSERT INTO application_languages
            (application_id, language_id) SELECT ?, id FROM languages WHERE name = ?");

        foreach ($_POST['languages'] as $lang) {
            $lang_stmt->execute([$_POST['id'], $lang]);
        }

        header("Location: index.php");
        exit();
    }
} catch (PDOException $e) {
    die("Ошибка обработки действия: " . $e->getMessage());
}

try {
    $applications = $pdo->query("
        SELECT a.*, GROUP_CONCAT(l.name) as languages
        FROM applications a
        LEFT JOIN application_languages al ON a.id = al.application_id
        LEFT JOIN languages l ON al.language_id = l.id
        GROUP BY a.id
    ")->fetchAll();

    $stats = $pdo->query("
        SELECT l.name, COUNT(al.application_id) as count
        FROM languages l
        LEFT JOIN application_languages al ON l.id = al.language_id
        GROUP BY l.id
        ORDER BY count DESC
    ")->fetchAll();

    $all_languages = $pdo->query("SELECT name FROM languages")->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    die("Ошибка получения данных: " . $e->getMessage());
}

$edit_data = null;
if ($action === 'edit' && $id) {
    foreach ($applications as $app) {
        if ($app['id'] == $id) {
            $edit_data = $app;
            $edit_data['languages'] = explode(',', $app['languages']);
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление заявками</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        h1, h2, h3 {
            color: #1e40af;
            margin-bottom: 1rem;
        }

        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border: none;
            margin-bottom: 20px;
        }

        .card-header {
            background-color: #f1f5f9;
            border-bottom: 1px solid #e2e8f0;
            padding: 12px 20px;
            font-weight: 600;
            border-radius: 8px 8px 0 0 !important;
        }

        .card-body {
            padding: 20px;
        }

        .table {
            width: 100%;
        }

        .table th {
            background-color: #f1f5f9;
            padding: 12px;
            font-weight: 600;
        }

        .table td {
            padding: 12px;
            border-top: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .btn {
            border-radius: 6px;
            padding: 8px 16px;
            font-weight: 500;
        }

        .btn-primary {
            background-color: #3b82f6;
            border: none;
        }

        .btn-primary:hover {
            background-color: #2563eb;
        }

        .btn-danger {
            background-color: #ef4444;
            border: none;
        }

        .btn-warning {
            background-color: #f59e0b;
            border: none;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 50px;
            font-weight: 500;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .stat-card h3 {
            font-size: 1.2rem;
            margin-bottom: 5px;
            color: #3b82f6;
        }

        .stat-card p {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
            color: #1e293b;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-control, .form-select {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px;
        }

        .form-control:focus, .form-select:focus {
            border-color: #93c5fd;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .alert {
            border-radius: 6px;
            padding: 12px 15px;
        }
    </style>
</head>
<body>
    <div>
        <h1>Управление заявками</h1>

        <div>
            <?php foreach ($stats as $stat): ?>
                <div>
                    <h3><?= htmlspecialchars($stat['name']) ?></h3>
                    <p><?= $stat['count'] ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($edit_data): ?>
            <div>
                <div>
                    Редактирование заявки #<?= $edit_data['id'] ?>
                </div>
                <div>
                    <form method="POST">
                        <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">

                        <div>
                            <div>
                                <div>
                                    <label>
            ФИО:<br>
            <input type="text" placeholder="Иванов Иван Иванович" name="name" id = "name" required
                           value="<?php echo htmlspecialchars(getFieldValue('name')); ?>"
                           class="<?php echo isset($errors['name']) ? 'error-field' : ''; ?>">
                    <?php if (isset($errors['name'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['name']); ?></div>
                    <?php endif; ?>
          </label><br>

          <label>
            Телефон:<br>
            <input type="tel" placeholder="+7(999)123-45-67" name="phone" id="phone" required
                           value="<?php echo htmlspecialchars(getFieldValue('phone')); ?>"
                           class="<?php echo isset($errors['phone']) ? 'error-field' : ''; ?>">
                    <?php if (isset($errors['phone'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['phone']); ?></div>
                    <?php endif; ?>
          </label><br>

          <label>
            Почта (email):<br>
            <input type="email" placeholder="Введите вашу почту" name="email" id="email" required
                           value="<?php echo htmlspecialchars(getFieldValue('email')); ?>"
                           class="<?php echo isset($errors['email']) ? 'error-field' : ''; ?>">
                    <?php if (isset($errors['email'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['email']); ?></div>
                    <?php endif; ?>
          </label><br>

          <label">
            Дата рождения:<br>
            <input value="2000-01-01" type="date" name="birthdate" id="birthdate" required
                           value="<?php echo htmlspecialchars(getFieldValue('birthdate')); ?>"
                           class="<?php echo isset($errors['birthdate']) ? 'error-field' : ''; ?>">
                    <?php if (isset($errors['birthdate'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['birthdate']); ?></div>
                    <?php endif; ?>
          </label><br>
          
          <label>
             Биография:<br>
            <input type="text" id="bio" name="bio"  placeholder="Введите текст" required
                              class="<?php echo isset($errors['bio']) ? 'error-field' : ''; ?>"><?php
                              echo htmlspecialchars(getFieldValue('bio')); ?></textarea>
                    <?php if (isset($errors['bio'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['bio']); ?></div>
                    <?php endif; ?>
          </label>
          
          <div><br>
            Пол:<br>
          <label><input type="radio" checked="checked" value="male" id="male" name="gender" required
                               <?php echo isSelected('gender', 'male'); ?>
                               class="<?php echo isset($errors['gender']) ? 'error-field' : ''; ?>">>
            Мужской</label>
          <label><input type="radio" value="female" id="female" name="gender"
                               <?php echo isSelected('gender', 'female'); ?>
                               class="<?php echo isset($errors['gender']) ? 'error-field' : ''; ?>">>
            Женский</label><br>
            <?php if (isset($errors['gender'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['gender']); ?></div>
                    <?php endif; ?>

          </div><br>

          <label>
            Любимый язык программирования:<br>
            <select  id="languages" name="languages[]" multiple="multiple" required class="<?php echo isset($errors['languages']) ? 'error-field' : ''; ?>" size="5">
                        <?php
                        $allLanguages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala'];
                        foreach ($allLanguages as $lang): ?>
                            <option value="<?php echo htmlspecialchars($lang); ?>"
                                <?php echo isSelected('languages', $lang); ?>>
                                <?php echo htmlspecialchars($lang); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['languages'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['languages']); ?></div>
                    <?php endif; ?>
          </label><br>

          <div><br>
            Пол:<br>
          <label><input type="radio" checked="checked" value="male" id="male" name="gender" required
                               <?php echo isSelected('gender', 'male'); ?>
                               class="<?php echo isset($errors['gender']) ? 'error-field' : ''; ?>">>
            Мужской</label>
          <label><input type="radio" value="female" id="female" name="gender"
                               <?php echo isSelected('gender', 'female'); ?>
                               class="<?php echo isset($errors['gender']) ? 'error-field' : ''; ?>">>
            Женский</label><br>
            <?php if (isset($errors['gender'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['gender']); ?></div>
                    <?php endif; ?>

          </div><br>

          <label ><input type="checkbox" name="contract_accepted" id="contract_accepted" value="1" required
                           <?php echo isChecked('contract_accepted'); ?>
                           class="<?php echo isset($errors['contract_accepted']) ? 'error-field' : ''; ?>">
                    С контрактом ознакомлен(а)
                    <?php if (isset($errors['contract_accepted'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['contract_accepted']); ?></div>
                    <?php endif; ?>
                      </label>
                            <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                            <a href="index.php" class="btn btn-outline-secondary">Отмена</a>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Список заявок</h2>
                <span class="badge bg-primary">Всего: <?= count($applications) ?></span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>ФИО</th>
                                <th>Email</th>
                                <th>Телефон</th>
                                <th>Языки</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applications as $app): ?>
                                <tr>
                                    <td><?= $app['id'] ?></td>
                                    <td><?= htmlspecialchars($app['name']) ?></td>
                                    <td><?= htmlspecialchars($app['email']) ?></td>
                                    <td><?= htmlspecialchars($app['phone']) ?></td>
                                    <td>
                                        <?php
                                            $langs = explode(',', $app['languages']);
                                            foreach ($langs as $lang) {
                                                echo '<span class="badge bg-light text-dark me-1">'.htmlspecialchars($lang).'</span>';
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="index.php?action=edit&id=<?= $app['id'] ?>" class="btn btn-sm btn-warning">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="index.php?action=delete&id=<?= $app['id'] ?>" class="btn btn-sm btn-danger"
                                               onclick="return confirm('Вы уверены, что хотите удалить эту заявку?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>