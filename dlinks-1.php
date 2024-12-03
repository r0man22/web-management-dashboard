<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_stmt = $pdo->prepare("SELECT role_id, name FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch();
$role_id = $user['role_id'];
$user_name = $user['name'];

// Проверка роли пользователя (1 - Администратор, 2 - Руководитель департамента)
if ($_SESSION['role_id'] != 1) {
    echo "У вас нет доступа к этой странице.";
    exit;
}

if (isset($_POST['link_department'])) {
    $department_id = $_POST['department_id'];
    $top_department_id = $_POST['top_department_id'];

    try {
        $stmt = $pdo->prepare("UPDATE departments SET top_department_id = ? WHERE id = ?");
        $stmt->execute([$top_department_id, $department_id]);
        echo "Департамент успешно связан с Топ-Департаментом.";
    } catch (PDOException $e) {
        echo "Ошибка: " . $e->getMessage();
    }
}
?>


<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Связать Департамент с Топ-Департаментом</title>
</head>
<body>
    <h1>Связать Департамент с Топ-Департаментом</h1>
    <form method="post">
        <label for="department_id">Департамент:</label>
        <select id="department_id" name="department_id" required>
            <?php
            // Подключение к базе данных
            $stmt = $pdo->query("SELECT id, name FROM departments");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<option value='{$row['id']}'>{$row['name']}</option>";
            }
            ?>
        </select>

        <label for="top_department_id">Топ-Департамент:</label>
        <select id="top_department_id" name="top_department_id" required>
            <?php
            $stmt = $pdo->query("SELECT id, name FROM top_departments");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<option value='{$row['id']}'>{$row['name']}</option>";
            }
            ?>
        </select>
        <button type="submit" name="link_department">Связать</button>
    </form>

    <h1>Список связанных департаментов с топ-департаментами</h1>
    <table border="1">
        <tr>
            <th>Топ-Департамент</th>
            <th>Департамент</th>
        </tr>
        <?php
        // Получение связанных департаментов с топ-департаментами
        $sql = "SELECT td.name as top_name, d.name as dept_name
                FROM departments d
                JOIN top_departments td ON d.top_department_id = td.id";
        $stmt = $pdo->query($sql);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr><td>{$row['top_name']}</td><td>{$row['dept_name']}</td></tr>";
        }
        ?>
    </table>
</body>
</html>
