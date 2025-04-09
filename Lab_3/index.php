<?php
// Устанавливаем соединение с базой данных
$host = 'localhost';
$dbname = 'u68678';
$username = 'u68678';
$password = '6091345';
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8";

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к БД: " . $e->getMessage());
}

// Проверяем, был ли успешный submit
if (isset($_GET['success']) && $_GET['success'] == 1) {
    echo '<div class="success-message" style="padding: 15px; margin: 20px 0; border: 1px solid #4CAF50; background-color: #DFF2BF; color: #4CAF50; border-radius: 4px;">
            Форма успешно отправлена! Спасибо за вашу заявку.
          </div>';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];
    if (!empty($errors)) {
        echo "<h2>Ошибки:</h2><ul>";
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo "</ul>";
        exit;
    }
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO applications (full_name, phone, email, birthdate, gender, biography, agreement)
                      VALUES (:name, :phone, :email, :birthdate, :gender, :bio, :contract)");
        $stmt->execute([
            ':name' => $_POST['name'],
            ':phone' => $_POST['phone'],
            ':email' => $_POST['email'],
            ':birthdate' => $_POST['birthdate'],
            ':gender' => $_POST['gender'],
            ':bio' => $_POST['bio'],
            ':contract' => isset($_POST['contract_accepted']) ? 1 : 0
        ]);
        $applicationId = $pdo->lastInsertId();
        
        $validLanguages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala'];
        $selectedLanguages = array_intersect($_POST['languages'] ?? [], $validLanguages);

        if (!empty($selectedLanguages)) {
            $placeholders = rtrim(str_repeat('?,', count($selectedLanguages)), ',');
            $stmt = $pdo->prepare("SELECT id, name FROM languages WHERE name IN ($placeholders)");
            $stmt->execute($selectedLanguages);
            $existingLanguages = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            $missingLanguages = array_diff($selectedLanguages, array_keys($existingLanguages));
            if (!empty($missingLanguages)) {
                $stmt = $pdo->prepare("INSERT IGNORE INTO languages (name) VALUES (?)");
                foreach ($missingLanguages as $lang) {
                    $stmt->execute([$lang]);
                    if ($stmt->rowCount() > 0) {
                        $existingLanguages[$lang] = $pdo->lastInsertId();
                    } else {
                        $stmtSelect = $pdo->prepare("SELECT id FROM languages WHERE name = ?");
                        $stmtSelect->execute([$lang]);
                        $existingLanguages[$lang] = $stmtSelect->fetchColumn();
                    }
                }
            }

            $stmt = $pdo->prepare("INSERT IGNORE INTO application_languages (application_id, language_id) VALUES (?, ?)");
            foreach ($existingLanguages as $langId) {
                $stmt->execute([$applicationId, $langId]);
            }
        }
        $pdo->commit();
        header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
        exit;
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        die("Ошибка при сохранении данных: " . $e->getMessage());
    }
}
?>