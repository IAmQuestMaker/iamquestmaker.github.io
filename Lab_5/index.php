<?php
session_start();
require 'db.php';

$messages = [];
$errors = [];
$values = [];

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!empty($_COOKIE['save'])) {
        setcookie('save', '', time() - 3600);
        $messages[] = 'Результат сохранён.';

        if (!empty($_COOKIE['login']) && !empty($_COOKIE['pass'])) {
            $messages[] = sprintf(
                'Вы можете <a href="login.php">войти</a> с логином <strong>%s</strong> и паролем <strong>%s</strong> для изменения данных.',
                htmlspecialchars($_COOKIE['login']),
                htmlspecialchars($_COOKIE['pass'])
            );
        }
    }

    $field_names = ['name', 'phone', 'email', 'birthdate', 'gender', 'languages', 'bio', 'agreement'];
    foreach ($field_names as $field) {
        $errors[$field] = !empty($_COOKIE[$field.'_error']) ? $_COOKIE[$field.'_error'] : '';
        if (!empty($errors[$field])) {
            setcookie($field.'_error', '', time() - 3600);
        }
        $values[$field] = empty($_COOKIE[$field.'_value']) ? '' : $_COOKIE[$field.'_value'];
    }

    if (!empty($_SESSION['login'])) {
        try {
            $stmt = $pdo->prepare("SELECT a.*, GROUP_CONCAT(l.name) as languages
                FROM applications a
                LEFT JOIN application_languages al ON a.id = al.application_id
                LEFT JOIN languages l ON al.language_id = l.id
                WHERE a.login = ?
                GROUP BY a.id");
            $stmt->execute([$_SESSION['login']]);
            $user_data = $stmt->fetch();

            if ($user_data) {
                $values = array_merge($values, $user_data);
                $values['languages'] = $user_data['languages'] ? explode(',', $user_data['languages']) : [];
            }
        } catch (PDOException $e) {
            $messages[] = '<div class="alert alert-danger">Ошибка загрузки данных: '.htmlspecialchars($e->getMessage()).'</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru-RU">

  <head>
    <meta charset="utf-8"/>
    <title>Мой проект</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <link rel="stylesheet"
      href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script
      src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
  </head>

  <body style="background-color: bisque;">

    <header>
      <div class="row">
      <img class="p-2" id="logo" src="https://i.pinimg.com/736x/45/21/26/452126a1b2faf622db865962465c41e6.jpg" alt="strange_thing">
      <p>Мой проект</p>
      </div>
    </header>

    <?php if (!empty($messages)): ?>
           <div>
               <?php foreach ($messages as $message): ?>
                   <div><?= $message ?></div>
               <?php endforeach; ?>
           </div>
       <?php endif; ?>

       <?php
       $has_errors = false;
       foreach ($errors as $error) {
           if (!empty($error)) {
               $has_errors = true;
               break;
           }
       }
       ?>

       <?php if ($has_errors): ?>
           <div>
               <h4>Обнаружены ошибки:</h4>
               <ul>
                   <?php foreach ($errors as $field => $error): ?>
                       <?php if (!empty($error)): ?>
                           <li><?= htmlspecialchars($error) ?></li>
                       <?php endif; ?>
                   <?php endforeach; ?>
               </ul>
           </div>
       <?php endif; ?>
    <form action="sub.php" method="POST" id="form">

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

          <input type="submit" name="save" />Сохранить

          <?php if (!empty($_SESSION['login'])): ?>
                <a href="logout.php">Выйти</a>
            <?php endif; ?>
    </form>
  </body>

</html>