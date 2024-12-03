<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_stmt = $pdo->prepare("SELECT role_id, name, department_id FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch();
$role_id = $user['role_id'];
$user_name = $user['name'];
$user_department_id = $user['department_id'];

// Проверка роли пользователя (1 - Администратор, 2 - Руководитель департамента, 3 - Директор, 5 - Руководитель топ-департамента)
if ($role_id != 5) {
    echo "У вас нет доступа к этой странице.";
    exit;
}

// Получение выбранного месяца и года из запроса, если они заданы
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

try {
    // Получение всех департаментов, которые входят в топ-департамент текущего пользователя
    $stmt = $pdo->prepare("
        SELECT d.id, d.name 
        FROM departments d
        JOIN top_departments td ON d.top_department_id = td.id
        WHERE td.id IN (
            SELECT top_department_id 
            FROM departments 
            WHERE id = ?
        )
    ");
    $stmt->execute([$user_department_id]);
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h1>Оценки Департаментов в вашем Топ-Департаменте за $month/$year</h1>";
    echo "<form method='get' action='top_department_head_report_departments.php'>";
    echo "<label for='month'>Месяц:</label>";
    echo "<select id='month' name='month'>";
    for ($i = 1; $i <= 12; $i++) {
        $selected = ($i == $month) ? 'selected' : '';
        echo "<option value='$i' $selected>$i</option>";
    }
    echo "</select>";

    echo "<label for='year'>Год:</label>";
    echo "<input type='text' id='year' name='year' value='$year' size='4'>";

    echo "<button type='submit'>Фильтровать</button>";
    echo "</form>";

    echo "<table border='1'>";
    echo "<tr><th>Департамент</th><th>Средняя Оценка</th></tr>";

    foreach ($departments as $department) {
        $department_id = $department['id'];
        $department_name = $department['name'];

        // Вычисление средней оценки для текущего департамента за выбранный месяц и год
        $stmt = $pdo->prepare("
            SELECT AVG((score_1 + score_2 + score_3 + score_4 + score_5 + score_6 + score_7) / 7) as average_score
            FROM department_evaluations
            WHERE department_id = ?
              AND YEAR(created_at) = ?
              AND MONTH(created_at) = ?
        ");
        $stmt->execute([$department_id, $year, $month]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $average_score = $result['average_score'] ?? 'Нет данных';

        echo "<tr><td>{$department_name}</td><td>" . number_format($average_score, 2) . "</td></tr>";
    }

    echo "</table>";
} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}
?>
