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

if ($role_id != 3) {
    echo "У вас нет доступа к этой странице.";
    exit;
}

$filter_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

// Измененный запрос для фильтрации департаментов, связанных с директором
$evaluation_query = "
    SELECT d.name as department_name,
           AVG(de.score_1) as avg_score_1,
           AVG(de.score_2) as avg_score_2,
           AVG(de.score_3) as avg_score_3,
           AVG(de.score_4) as avg_score_4,
           AVG(de.score_5) as avg_score_5,
           AVG(de.score_6) as avg_score_6,
           AVG(de.score_7) as avg_score_7
    FROM department_evaluations de
    JOIN departments d ON de.department_id = d.id
    JOIN director_top_departments dt ON dt.top_department_id = d.top_department_id
    WHERE DATE_FORMAT(de.created_at, '%Y-%m') = :filter_month
    AND dt.director_id = :director_id
    GROUP BY d.name
";

$evaluation_stmt = $pdo->prepare($evaluation_query);
$evaluation_stmt->execute(['filter_month' => $filter_month, 'director_id' => $user_id]);
$evaluations = $evaluation_stmt->fetchAll();
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
    <title>Şöbələrin ortalama reytinqləri</title>
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
    <div id="loader-wrapper">
        <div class="loader">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        </div>
    </div>
    <header class="header">
        <div class="top-header-section">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-lg-3 col-md-3 col-sm-3 col-6">
                        <div class="logo my-3 my-sm-0">
                            <a href="#">
                                <img src="assets/img/logo.png" alt="logo image" class="img-fluid" width="100">
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-9 col-md-9 col-sm-9 col-6 text-right">
                        <div class="user-block d-none d-lg-block">
                            <div class="row align-items-center">
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <div class="user-info align-right dropdown d-inline-block">
                                        <a href="javascript:void(0)" data-toggle="dropdown" class="menu-style dropdown-toggle">
                                            <div class="user-avatar d-inline-block">
                                                <?php echo $user_name; ?>
                                            </div>
                                        </a>
                                        <div class="dropdown-menu notification-dropdown-menu shadow-lg border-0 p-3 m-0 dropdown-menu-right">
                                            <a class="dropdown-item p-2" href="login.php">
                                                <span class="media align-items-center">
                                                    <span class="lnr lnr-power-switch mr-3"></span>
                                                    <span class="media-body text-truncate">
                                                        <span class="text-truncate">Çıxış</span>
                                                    </span>
                                                </span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-block d-lg-none">
                            <a href="javascript:void(0)">
                                <span class="lnr lnr-user d-block display-5 text-white" id="open_navSidebar"></span>
                            </a>
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
                                            <a href="#" class="text-dark p-4 first-slider-btn ctm-border-right ctm-border-left ctm-border-top">
                                                <span class="lnr lnr-home pr-0 pb-lg-2 font-23"></span><span>Ümumi</span>
                                            </a>
                                        </div>
                                        <div class="col-6 align-items-center shadow-none text-center">
                                            <a href="reports_director.php" class="text-dark p-4 second-slider-btn ctm-border-right ctm-border-top">
                                                <span class="lnr lnr-users pr-0 pb-lg-2 font-23"></span><span>İşçi Reytinqləri</span>
                                            </a>
                                        </div>
                                        <div class="col-6 align-items-center shadow-none text-center">
                                            <a href="reports_director_departments.php" class="text-white active p-4 ctm-border-right ctm-border-left">
                                                <span class="lnr lnr-rocket pr-0 pb-lg-2 font-23"></span><span>Şöbə Reytinqləri</span>
                                            </a>
                                        </div>
                                        <div class="col-6 align-items-center shadow-none text-center">
                                            <a href="top_department_evaluations.php" class="text-dark p-4 ctm-border-right ctm-border-left">
                                                <span class="lnr lnr-rocket pr-0 pb-lg-2 font-23"></span><span>Departament Reytinqləri</span>
                                            </a>
                                        </div>
                                        <div class="col-6 align-items-center shadow-none text-center">												
													<a href="re_evaluations.php" class="text-dark p-4 ctm-border-right ctm-border-left"><span class="lnr lnr-sync pr-0 pb-lg-2 font-23"></span><span class="">Yenidən Qiymətləndirmə</span></a>												
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
                            <h4 class="card-title mb-0 d-inline-block">Departament Rəhbəri üçün Şöbələrin Qiymətləndirmə Nəticələri</h4>
                        </div>
                    </div>
                    <div class="card ctm-border-radius shadow-sm grow">
                        <div class="card-body">
                            <div class="tab-content" id="v-pills-tabContent">
                                <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
                                    <div class="table-responsive">
                                        <div class="container">
                                            <form method="get" action="">
                                                <label for="month">Tarix seçin:</label>
                                                <input type="month" id="month" name="month" value="<?= htmlspecialchars($filter_month) ?>">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div class="submit-section text-center btn-add">
                                                            <button class="btn btn-theme text-white ctm-border-radius button-1" type="submit">Filteri Tətbiq et</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                            <h4>Şöbələrin ortalama reytinqləri</h4>
                                            <table id="evaluationTable" class="display">
                                                <thead>
                                                    <tr>
                                                        <th>Şöbə</th>
                                                        <th>Davamiyyət</th>
                                                        <th>Korporativ etika</th>
                                                        <th>Komanda işi</th>
                                                        <th>Təşəbbüsk.</th>
                                                        <th>Korrespondensiya</th>
                                                        <th>Proses və prosedurlara riayət</th>
                                                        <th>Hesabatlıq</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($evaluations as $evaluation): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($evaluation['department_name']) ?></td>
                                                            <td><?= htmlspecialchars(number_format($evaluation['avg_score_1'], 2)) ?></td>
                                                            <td><?= htmlspecialchars(number_format($evaluation['avg_score_2'], 2)) ?></td>
                                                            <td><?= htmlspecialchars(number_format($evaluation['avg_score_3'], 2)) ?></td>
                                                            <td><?= htmlspecialchars(number_format($evaluation['avg_score_4'], 2)) ?></td>
                                                            <td><?= htmlspecialchars(number_format($evaluation['avg_score_5'], 2)) ?></td>
                                                            <td><?= htmlspecialchars(number_format($evaluation['avg_score_6'], 2)) ?></td>
                                                            <td><?= htmlspecialchars(number_format($evaluation['avg_score_7'], 2)) ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
        buttons: [
            'excelHtml5'
        ]
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
