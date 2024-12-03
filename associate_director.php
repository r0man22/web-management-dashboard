<?php
if (isset($_POST['associate_director'])) {
    $director_id = (int)$_POST['director_id'];
    $top_departments = $_POST['top_departments'];

    // Удаляем старые ассоциации директора с топ-департаментами
    $stmt = $pdo->prepare("DELETE FROM director_top_departments WHERE director_id = ?");
    $stmt->execute([$director_id]);

    // Вставляем новые ассоциации
    foreach ($top_departments as $top_department_id) {
        $stmt = $pdo->prepare("INSERT INTO director_top_departments (director_id, top_department_id) VALUES (?, ?)");
        $stmt->execute([$director_id, (int)$top_department_id]);
    }

    echo "Ассоциации директора с топ-департаментами обновлены!";
}
