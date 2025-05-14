<?php
$errors = [];
$oldValues = [];
$savedValues = [];

if (isset($_COOKIE['form_errors'])) {
    $errors = json_decode($_COOKIE['form_errors'], true);
    $oldValues = json_decode($_COOKIE['old_values'], true);
}

foreach ($_COOKIE as $name => $value) {
    if (strpos($name, 'saved_') === 0) {
        $field = substr($name, 6);
        $savedValues[$field] = $value;
    }
}

function getFieldValue($field, $default = '') {
    global $oldValues, $savedValues;

    if (isset($oldValues[$field])) {
        return $oldValues[$field];
    }

    if (isset($savedValues[$field])) {
        return $savedValues[$field];
    }

    return $default;
}

function isSelected($field, $value) {
    global $oldValues, $savedValues;

    $currentValues = [];
    if (isset($oldValues[$field])) {
        if ($field === 'languages') {
            $currentValues = explode(',', $oldValues[$field]);
        } else {
            return $oldValues[$field] === $value ? 'checked' : '';
        }
    } elseif (isset($savedValues[$field])) {
        if ($field === 'languages') {
            $currentValues = explode(',', $savedValues[$field]);
        } else {
            return $savedValues[$field] === $value ? 'checked' : '';
        }
    }

    return in_array($value, $currentValues) ? 'selected' : '';
}


function isChecked($field) {
    global $oldValues, $savedValues;

    if (isset($oldValues[$field])) {
        return $oldValues[$field] ? 'checked' : '';
    }

    if (isset($savedValues[$field])) {
        return $savedValues[$field] ? 'checked' : '';
    }

    return '';
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

    <?php if (isset($_GET['success'])): ?>
                <div class="success-message">Данные успешно сохранены!</div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="error-list">
                    <h3>Ошибки:</h3>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
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

    </form>
  </body>

</html>