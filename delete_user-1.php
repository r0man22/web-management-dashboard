<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Проверка роли пользователя (1 - Администратор)
if ($_SESSION['role_id'] != 1) {
    echo "У вас нет доступа к этой странице.";
    exit;
}

// Удаление пользователя
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];

    // Проверка, чтобы не удалить администратора, который сейчас в системе
    if ($user_id == $_SESSION['user_id']) {
        echo "Вы не можете удалить свою собственную учетную запись.";
    } else {
        // Удаление всех оценок, связанных с пользователем
        $stmt = $pdo->prepare("DELETE FROM evaluations WHERE user_id = ?");
        $stmt->execute([$user_id]);

        // Удаление пользователя
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        echo "Пользователь удален!";
    }
}

// Удаление департамента
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_department'])) {
    $department_id = $_POST['department_id'];

    // Удаление всех сотрудников, связанных с департаментом
    $stmt = $pdo->prepare("DELETE FROM users WHERE department_id = ?");
    $stmt->execute([$department_id]);

    // Удаление всех оценок, связанных с департаментом
    $stmt = $pdo->prepare("DELETE FROM department_evaluations WHERE department_id = ?");
    $stmt->execute([$department_id]);

    // Удаление департамента
    $stmt = $pdo->prepare("DELETE FROM departments WHERE id = ?");
    $stmt->execute([$department_id]);
    echo "Департамент удален!";
}

if (isset($_POST['delete_top_department'])) {
    $top_department_id = $_POST['top_department_id'];

    try {
        // Проверка и удаление связей
        $stmt = $pdo->prepare("UPDATE departments SET top_department_id = NULL WHERE top_department_id = ?");
        $stmt->execute([$top_department_id]);

        // Удаление топ-департамента
        $stmt = $pdo->prepare("DELETE FROM top_departments WHERE id = ?");
        $stmt->execute([$top_department_id]);

        echo "Топ-Департамент успешно удален.";
    } catch (PDOException $e) {
        echo "Ошибка: " . $e->getMessage();
    }
}

// Получение данных для форм
$roles = $pdo->query("SELECT * FROM roles")->fetchAll();
$departments = $pdo->query("SELECT * FROM departments")->fetchAll();
$employees = $pdo->query("SELECT * FROM users WHERE role_id = 4")->fetchAll(); // Список работников
$date_ranges = $pdo->query("SELECT * FROM date_ranges")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            text-align: center;
        }
        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            color: #333;
        }
        form {
            margin-bottom: 20px;
        }
        input, select, button {
            margin-bottom: 10px;
            padding: 10px;
            font-size: 16px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <header>
        <h1>Панель администратора</h1>
    </header>
    <div class="container">
        <!-- Форма удаления пользователя -->
        <h2>Удалить пользователя</h2>
        <form method="post">
            <select name="user_id" required>
                <option value="" disabled selected>Выберите пользователя</option>
                <?php foreach ($employees as $employee): ?>
                    <option value="<?= $employee['id'] ?>"><?= $employee['name'] ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="delete_user">Удалить пользователя</button>
        </form>

        <!-- Форма удаления департамента -->
        <h2>Удалить департамент</h2>
        <form method="post">
            <select name="department_id" required>
                <option value="" disabled selected>Выберите департамент</option>
                <?php foreach ($departments as $department): ?>
                    <option value="<?= $department['id'] ?>"><?= $department['name'] ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="delete_department">Удалить департамент</button>
        </form>
		
		    <h2>Удалить Топ-Департамент</h2>
    <form action="delete_top_department.php" method="post">
        <select id="top_department_id" name="top_department_id" required>
            <?php
            // Подключение к базе данных
            $stmt = $pdo->query("SELECT id, name FROM top_departments");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<option value='{$row['id']}'>{$row['name']}</option>";
            }
            ?>
        </select>
        <button type="submit" name="delete_top_department">Удалить</button>
    </form>
    </div>
</body>
</html>
