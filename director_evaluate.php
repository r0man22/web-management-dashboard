<?php
require 'config.php';
session_start();

// Проверка, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Получение информации о пользователе
$user_id = $_SESSION['user_id'];
$user_stmt = $pdo->prepare("SELECT role_id, name FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch();
$role_id = $user['role_id'];
$user_name = $user['name'];

// Проверка доступа
if ($role_id != 3) {
    echo "У вас нет доступа к этой странице.";
    exit;
}

// Фильтрация по месяцу и департаменту
$filter_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$filter_department = isset($_GET['department_id']) ? $_GET['department_id'] : '';

// Получение списка департаментов для фильтрации
$departments = $pdo->query("SELECT id, name FROM departments")->fetchAll();

// Если был отправлен POST-запрос, обрабатываем изменения для конкретного сотрудника
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['employee_id'])) {
    $employee_id = $_POST['employee_id'];
    $adjustments = $_POST['adjustments'] ?? null;
    $filter_month = $_POST['filter_month'] ?? null;
    $filter_department = $_POST['filter_department'] ?? ''; // Добавлено для перенаправления

    // Проверка корректности данных
    if ($employee_id && $adjustments && $filter_month) {
        // Функция для расчета новой оценки с учетом процента изменения
        function calculate_new_score($existing_scores, $target_percentage) {
            $existing_scores_array = explode(',', $existing_scores);
            $existing_sum = array_sum($existing_scores_array);
            $existing_count = count($existing_scores_array);

            // Рассчитываем текущее среднее значение
            $current_average = $existing_sum / $existing_count;

            // Рассчитываем новую среднюю оценку с учетом процента изменения
            $target_average = $current_average * (1 + $target_percentage / 100);

            // Новое значение, которое нужно добавить для достижения этой средней оценки
            $new_score = ($target_average * ($existing_count + 1)) - $existing_sum;

            return round($new_score, 2);
        }

        // Получение текущих оценок сотрудника для указанного месяца
        $get_employee_evaluations = $pdo->prepare("
            SELECT GROUP_CONCAT(score_1 ORDER BY created_at DESC) as score_1_list,
                   GROUP_CONCAT(score_2 ORDER BY created_at DESC) as score_2_list,
                   GROUP_CONCAT(score_3 ORDER BY created_at DESC) as score_3_list,
                   GROUP_CONCAT(score_4 ORDER BY created_at DESC) as score_4_list,
                   GROUP_CONCAT(score_5 ORDER BY created_at DESC) as score_5_list,
                   GROUP_CONCAT(score_6 ORDER BY created_at DESC) as score_6_list,
                   GROUP_CONCAT(score_7 ORDER BY created_at DESC) as score_7_list,
                   GROUP_CONCAT(score_8 ORDER BY created_at DESC) as score_8_list,
                   GROUP_CONCAT(score_9 ORDER BY created_at DESC) as score_9_list
            FROM evaluations
            WHERE user_id = ? AND DATE_FORMAT(created_at, '%Y-%m') = ?
        ");
        $get_employee_evaluations->execute([$employee_id, $filter_month]);
        $employee_evaluations = $get_employee_evaluations->fetch();

        $changes_made = false;
        for ($i = 1; $i <= 9; $i++) {
            if (isset($adjustments["score_$i"]) && $adjustments["score_$i"] != 0) { // Если ползунок был изменен
                $target_percentage = $adjustments["score_$i"]; // Процент изменения

                // Получаем текущие оценки для конкретного параметра
                $existing_scores_list = $employee_evaluations["score_{$i}_list"];

                // Рассчитываем новую оценку
                $new_score = calculate_new_score($existing_scores_list, $target_percentage);

                // Вставляем новую оценку в базу данных
                $stmt = $pdo->prepare("
                    INSERT INTO evaluations (user_id, evaluator_id, score_$i, created_at)
                    VALUES (?, ?, ?, NOW())
                ");
                $stmt->execute([$employee_id, $user_id, $new_score]);

                $changes_made = true;
            }
        }

        // Вывод сообщения о результате (можно использовать сессии или другие методы для уведомления)
        if ($changes_made) {
            // Используем сессию для отображения сообщения после редиректа
            $_SESSION['message'] = "Изменения для сотрудника с ID $employee_id успешно сохранены!";
        } else {
            $_SESSION['message'] = "Изменений не внесено.";
        }

        // Редирект обратно на ту же страницу после сохранения изменений
        header("Location: director_evaluate.php?month=$filter_month&department_id=$filter_department");
        exit;
    }
}

// Получение оценок сотрудников за выбранный месяц и департамент
$evaluation_query = "
    SELECT u.id as user_id, u.name as employee_name,
           AVG(e.score_1) as avg_score_1,
           AVG(e.score_2) as avg_score_2,
           AVG(e.score_3) as avg_score_3,
           AVG(e.score_4) as avg_score_4,
           AVG(e.score_5) as avg_score_5,
           AVG(e.score_6) as avg_score_6,
           AVG(e.score_7) as avg_score_7,
           AVG(e.score_8) as avg_score_8,
           AVG(e.score_9) as avg_score_9
    FROM evaluations e
    JOIN users u ON e.user_id = u.id
    WHERE DATE_FORMAT(e.created_at, '%Y-%m') = :filter_month
";

if ($filter_department) {
    $evaluation_query .= " AND u.department_id = :filter_department";
}

$evaluation_query .= " GROUP BY u.id, u.name";

$evaluation_stmt = $pdo->prepare($evaluation_query);

$params = ['filter_month' => $filter_month];
if ($filter_department) {
    $params['filter_department'] = $filter_department;
}

$evaluation_stmt->execute($params);
$evaluations = $evaluation_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оценки сотрудников</title>
    <style>
        .slider-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .employee-form {
            margin-bottom: 20px;
            border: 1px solid #ccc;
            padding: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th, td {
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Оценки сотрудников за месяц: <?php echo htmlspecialchars($filter_month); ?></h1>

    <?php
    // Вывод сообщения из сессии
    if (isset($_SESSION['message'])) {
        echo '<p>' . htmlspecialchars($_SESSION['message']) . '</p>';
        unset($_SESSION['message']);
    }
    ?>

    <form method="get" action="">
        <label for="month">Выберите месяц:</label>
        <input type="month" name="month" id="month" value="<?php echo htmlspecialchars($filter_month); ?>">

        <label for="department_id">Выберите департамент:</label>
        <select name="department_id" id="department_id">
            <option value="">Все департаменты</option>
            <?php foreach ($departments as $department): ?>
                <option value="<?php echo htmlspecialchars($department['id']); ?>" <?php echo ($filter_department == $department['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($department['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Применить фильтр</button>
    </form>

    <?php foreach ($evaluations as $evaluation): ?>
        <form method="post" action="director_evaluate.php" class="employee-form">
            <h3><?php echo htmlspecialchars($evaluation['employee_name']); ?></h3>
            <input type="hidden" name="employee_id" value="<?php echo htmlspecialchars($evaluation['user_id']); ?>">
            <input type="hidden" name="filter_month" value="<?php echo htmlspecialchars($filter_month); ?>">
            <input type="hidden" name="filter_department" value="<?php echo htmlspecialchars($filter_department); ?>">

            <table border="1">
                <thead>
                    <tr>
                        <?php for ($i = 1; $i <= 9; $i++): ?>
                            <th>Средняя оценка <?php echo $i; ?></th>
                            <th>Изменить оценку <?php echo $i; ?></th>
                        <?php endfor; ?>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <?php for ($i = 1; $i <= 9; $i++): ?>
                            <td><?php echo round($evaluation["avg_score_$i"], 2); ?></td>
                            <td>
                                <div class="slider-container">
                                    <input type="range" 
                                           name="adjustments[score_<?php echo $i; ?>]" 
                                           min="-60" max="60" 
                                           value="0" step="1" 
                                           oninput="updateScore(this)">
                                    <span class="score-display">0%</span>
                                </div>
                            </td>
                        <?php endfor; ?>
                    </tr>
                </tbody>
            </table>
            <button type="submit">Сохранить изменения для <?php echo htmlspecialchars($evaluation['employee_name']); ?></button>
        </form>
    <?php endforeach; ?>

    <script>
    function updateScore(slider) {
        var adjustment = slider.value;
        var output = slider.nextElementSibling; // Предполагаем, что <span> следует сразу после <input>
        if (output) {
            output.textContent = adjustment + "%";
        }
    }
    </script>
</body>
</html>
