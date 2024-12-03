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

// Проверка роли пользователя
if ($role_id != 3) { // предполагается, что 3 — роль директора
    echo "У вас нет доступа к этой странице.";
    exit;
}

// Получение выбранного месяца и года
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

try {
    // Получение топ-департаментов, связанных с директором
    $stmt = $pdo->prepare("
        SELECT td.id, td.name 
        FROM top_departments td
        JOIN director_top_departments dtd ON td.id = dtd.top_department_id
        WHERE dtd.director_id = ?
    ");
    $stmt->execute([$user_id]);
    $top_departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Ошибка: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.png">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/lnr-icon.css">
    <link rel="stylesheet" href="assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>Departament Reytinqləri</title>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.7.1/css/buttons.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js"></script>
</head>
<body>
<div class="inner-wrapper">
    <header class="header">
        <div class="top-header-section">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-lg-3 col-md-3 col-sm-3 col-6">
                        <div class="logo my-3 my-sm-0">
                            <a href="#">
                                <img src="assets/img/logo.png" alt="logo" class="img-fluid" width="100">
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-9 col-md-9 col-sm-9 col-6 text-right">
                        <div class="user-info align-right dropdown d-inline-block">
                            <a href="javascript:void(0)" data-toggle="dropdown" class="menu-style dropdown-toggle">
                                <div class="user-avatar d-inline-block"><?php echo htmlspecialchars($user_name); ?></div>
                            </a>
                            <div class="dropdown-menu notification-dropdown-menu shadow-lg border-0 p-3 m-0 dropdown-menu-right">
                                <a class="dropdown-item p-2" href="login.php">
                                    <span class="media align-items-center"><span class="lnr lnr-power-switch mr-3"></span><span class="media-body text-truncate"><span class="text-truncate">Logout</span></span></span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <div class="page-wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-3 col-lg-4 col-md-12 theiaStickySidebar">
                    <aside class="sidebar sidebar-user">
                        <div class="card ctm-border-radius shadow-sm grow">
                            <div class="card-body py-4">
                                <div class="row">
                                    <div class="col-md-12 mr-auto text-left">
                                        <div class="custom-search input-group">
                                            <div class="custom-breadcrumb">
                                                <ol class="breadcrumb no-bg-color d-inline-block p-0 m-0 mb-2">
                                                    <li class="breadcrumb-item d-inline-block"><a href="#" class="text-dark">Home</a></li>
                                                    <li class="breadcrumb-item d-inline-block active">Manage</li>
                                                </ol>
                                                <h4 class="text-dark">Admin</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="sidebar-wrapper d-lg-block d-md-none d-none">
                            <div class="card ctm-border-radius shadow-sm grow border-none">
                                <div class="card-body">
                                    <div class="row no-gutters">
                                        <div class="col-6 align-items-center text-center">
                                            <a href="#" class="text-dark p-4 first-slider-btn ctm-border-right ctm-border-left ctm-border-top"><span class="lnr lnr-home pr-0 pb-lg-2 font-23"></span><span>Ümumi</span></a>
                                        </div>
                                        <div class="col-6 align-items-center shadow-none text-center">
                                            <a href="reports_director.php" class="text-dark p-4 second-slider-btn ctm-border-right ctm-border-top"><span class="lnr lnr-users pr-0 pb-lg-2 font-23"></span><span>İşçi Reytinqləri</span></a>
                                        </div>
                                        <div class="col-6 align-items-center shadow-none text-center">
                                            <a href="reports_director_departments.php" class="text-dark p-4 ctm-border-right ctm-border-left"><span class="lnr lnr-rocket pr-0 pb-lg-2 font-23"></span><span>Şöbə Reytinqləri</span></a>
                                        </div>
                                        <div class="col-6 align-items-center shadow-none text-center">
                                            <a href="top_department_evaluations.php" class="text-white active p-4 ctm-border-right ctm-border-left"><span class="lnr lnr-rocket pr-0 pb-lg-2 font-23"></span><span>Departament Reytinqləri</span></a>
                                        </div>
                                        <div class="col-6 align-items-center shadow-none text-center">
                                            <a href="re_evaluations.php" class="text-dark p-4 ctm-border-right ctm-border-left"><span class="lnr lnr-sync pr-0 pb-lg-2 font-23"></span><span>Yenidən Qiymətləndirmə</span></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>
                <div class="col-xl-9 col-lg-8 col-md-12">
                    <div class="card ctm-border-radius shadow-sm grow">
                        <div class="card-header">
                            <h4 class="card-title mb-0 d-inline-block">Departament Reytinqləri <?php echo htmlspecialchars("$month/$year"); ?></h4>
                        </div>
                        <div class="card-body">
                            <form method="get" action="top_department_evaluations.php">
                                <div class="row">
                                    <label for="month" class="form-control col-3">Ay:</label>
                                    <select id="month" name="month" class="form-control col-3">
                                        <?php for ($i = 1; $i <= 12; $i++) {
                                            $selected = ($i == $month) ? 'selected' : '';
                                            echo "<option value='$i' $selected>$i</option>";
                                        } ?>
                                    </select>

                                    <label for="year" class="form-control col-3">İl:</label>
                                    <input type="text" id="year" name="year" value="<?php echo htmlspecialchars($year); ?>" size="4" class="form-control col-3">

                                    <div class="col-12">
                                        <div class="submit-section text-center btn-add">
                                            <button class="btn btn-theme text-white ctm-border-radius button-1" type="submit">Filter</button>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <h4>Departament Reytinqləri</h4>
                            <table id="evaluationTable" class="display">
                                <thead>
                                    <tr>
                                        <th>Departament</th>
                                        <th>Ortalama Yekun Reytinq</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($top_departments as $top_department) {
                                        $top_department_id = $top_department['id'];
                                        $top_department_name = $top_department['name'];

                                        // Получение среднего рейтинга по топ-департаменту
                                        $stmt = $pdo->prepare("
                                            SELECT AVG((score_1 + score_2 + score_3 + score_4 + score_5 + score_6 + score_7) / 7) as average_score
                                            FROM department_evaluations e
                                            JOIN departments d ON e.department_id = d.id
                                            WHERE d.top_department_id = ?
                                              AND YEAR(e.created_at) = ?
                                              AND MONTH(e.created_at) = ?
                                        ");
                                        $stmt->execute([$top_department_id, $year, $month]);
                                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                                        $average_score = $result['average_score'] ?? 'No data';

                                        echo "<tr><td>" . htmlspecialchars($top_department_name) . "</td><td>" . ($average_score !== 'No data' ? number_format($average_score, 2) : 'No data') . "</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="sidebar-overlay" id="sidebar_overlay"></div>
<script>
$(document).ready(function() {
    $('#evaluationTable').DataTable({
        dom: 'Bfrtip',
        buttons: ['excelHtml5']
    });
});
</script>
<script src="assets/js/popper.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/plugins/theia-sticky-sidebar/ResizeSensor.js"></script>
<script src="assets/plugins/theia-sticky-sidebar/theia-sticky-sidebar.js"></script>
<script src="assets/js/script.js"></script>
</body>
</html>
