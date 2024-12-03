<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Получение информации о пользователе
$user_id = $_SESSION['user_id'];
$user_stmt = $pdo->prepare("SELECT department_id, role_id, name FROM users WHERE id = ?");
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch();
$department_id = $user['department_id'];
$role_id = $user['role_id'];
$user_name = $user['name'];

// Проверка доступа
if ($role_id != 2) {
    echo "У вас нет доступа к этой странице.";
    exit;
}

// Получение текущего диапазона дат
$today = date('d');
$date_range_stmt = $pdo->prepare("SELECT * FROM date_ranges WHERE ? BETWEEN start_day_1 AND end_day_1 OR ? BETWEEN start_day_2 AND end_day_2");
$date_range_stmt->execute([$today, $today]);
$current_date_range = $date_range_stmt->fetch();

if (!$current_date_range) {
    echo "Оценки можно ставить только в установленные дни месяца.";
    exit;
}

// Получение id текущего диапазона дат
$date_range_id = $current_date_range['id'];

// Получение информации о статусе оценки для текущего диапазона дат
$evaluation_check_stmt = $pdo->prepare("SELECT * FROM date_range_evaluations WHERE user_id = ? AND date_range_id = ?");
$evaluation_check_stmt->execute([$user_id, $date_range_id]);
$evaluation_status = $evaluation_check_stmt->fetch();

// Получение оценок департаментов и сотрудников для текущего диапазона дат
// Оценки департаментов
$department_evaluations_stmt = $pdo->prepare("SELECT * FROM department_evaluations WHERE evaluator_id = ? AND department_id != ?");
$department_evaluations_stmt->execute([$user_id, $department_id]);
$department_evaluations = $department_evaluations_stmt->fetchAll();

// Оценки сотрудников
$employee_evaluations_stmt = $pdo->prepare("SELECT * FROM evaluations WHERE evaluator_id = ?");
$employee_evaluations_stmt->execute([$user_id]);
$employee_evaluations = $employee_evaluations_stmt->fetchAll();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/img/favicon.png">
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    
    <!-- Linearicon Font -->
    <link rel="stylesheet" href="assets/css/lnr-icon.css">
            
    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="assets/css/font-awesome.min.css">
            
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">

    <!-- DataTables Buttons CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
    
    <title>Manage Page</title>
</head>
<body>
    <!-- Inner wrapper -->
    <div class="inner-wrapper">
        <!-- Header -->
        <header class="header">
            <!-- Top Header Section -->
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
                                        <!-- user info-->
                                        <div class="user-info align-right dropdown d-inline-block">
                                            <a href="javascript:void(0)" data-toggle="dropdown" class="menu-style dropdown-toggle">
                                                <div class="user-avatar d-inline-block">
                                                    <?php echo $user_name; ?>
                                                </div>
                                            </a>
                                            <!-- Notifications -->
                                            <div class="dropdown-menu notification-dropdown-menu shadow-lg border-0 p-3 m-0 dropdown-menu-right">        
                                                <a class="dropdown-item p-2" href="login.php">
                                                    <span class="media align-items-center">
                                                        <span class="lnr lnr-power-switch mr-3"></span>
                                                        <span class="media-body text-truncate">
                                                            <span class="text-truncate">Logout</span>
                                                        </span>
                                                    </span>
                                                </a>
                                            </div>
                                            <!-- Notifications -->
                                        </div>
                                        <!-- /User info-->
                                    </div>
                                </div>
                            </div>
                            <div class="d-none d-lg-none">
                                <a href="javascript:void(0)">
                                    <span class="lnr lnr-user d-block display-5 text-white" id="open_navSidebar"></span>
                                </a>                        
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /Top Header Section -->
        </header>
        <!-- /Header -->
        <!-- Content -->
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
                            <!-- Sidebar -->
                            <div class="sidebar-wrapper d-lg-block d-md-none d-none">
                                <div class="card ctm-border-radius shadow-sm grow border-none">
                                    <div class="card-body">
                                        <div class="row no-gutters">
                                            <div class="col-6 align-items-center text-center">
                                                <a href="#" class="text-dark p-4 first-slider-btn ctm-border-right ctm-border-left ctm-border-top"><span class="lnr lnr-home pr-0 pb-lg-2 font-23"></span><span>Ümumi</span></a>                                                
                                            </div>
                                            <div class="col-6 align-items-center shadow-none text-center">                                            
                                                <a href="departmenthead_dashboard.php" class="text-dark p-4 second-slider-btn ctm-border-right ctm-border-top"><span class="lnr lnr-sync pr-0 pb-lg-2 font-23"></span><span>Qiymətləndirmə</span></a>                                                
                                            </div>
                                            <div class="col-6 align-items-center shadow-none text-center">                                                
                                                <a href="department_head_report.php" class="text-white active p-4 ctm-border-right ctm-border-left"><span class="lnr lnr-rocket pr-0 pb-lg-2 font-23"></span><span>Nəticələr</span></a>                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /Sidebar -->
                        </aside>
                    </div>
                    
                    <div class="col-xl-9 col-lg-8  col-md-12">
                        <div class="card ctm-border-radius shadow-sm grow">
                            <div class="card-header">
                                <h4 class="card-title mb-0 d-inline-block">Şöbə Müdirinin Qiymətləndirilməsi Nəticələri</h4>
                            </div>
                            <div class="card-body"></div>
                        </div>
                        
                        <div class="quicklink-sidebar-menu ctm-border-radius shadow-sm grow bg-white card">
                            <div class="card-body">
                                <div class="list-group list-group-horizontal-lg" id="v-pills-tab" role="tablist">
                                    <a class=" active list-group-item" id="v-pills-home-tab" data-toggle="pill" href="#v-pills-home" role="tab" aria-controls="v-pills-home" aria-selected="true">Forma 1</a>
                                    <a class="list-group-item" id="v-pills-profile-tab" data-toggle="pill" href="#v-pills-profile" role="tab" aria-controls="v-pills-profile" aria-selected="false">Forma 2</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card ctm-border-radius shadow-sm grow">
                            <div class="card-body">
                                <div class="tab-content" id="v-pills-tabContent">
                                    <!-- Tab1-->
                                    <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
                                        <div class="table-responsive">
                                            <div class="container">
                                                <div class="report-section">
                                                    <h4>Şöbənin Qiymətləndirilməsi Nəticələri</h4>
                                                    <?php if (empty($department_evaluations)): ?>
                                                        <p>Heç bir şöbə qiymətləndirməsi tapılmadı.</p>
                                                    <?php else: ?>
                                                        <table id="department-evaluations" class="display">
                                                            <thead>
                                                                <tr>
                                                                    <th>Şöbənin Adı</th>
                                                                    <th>Davamiyyət</th>
                                                                    <th>Korporativ etika</th>
                                                                    <th>Komanda işi</th>
                                                                    <th>Təşəbbüskarlıq</th>
                                                                    <th>Korrespondensiya</th>
                                                                    <th>Ortalama 5</th>
                                                                    <th>Proses və prosedurlara riayət</th>
                                                                    <th>Hesabatlıq</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($department_evaluations as $evaluation): ?>
                                                                    <?php
                                                                        $department_stmt = $pdo->prepare("SELECT name FROM departments WHERE id = ?");
                                                                        $department_stmt->execute([$evaluation['department_id']]);
                                                                        $department = $department_stmt->fetch();
                                                                    ?>
                                                                    <tr>
                                                                        <td><?= htmlspecialchars($department['name']) ?></td>
                                                                        <td><?= htmlspecialchars($evaluation['score_1']) ?></td>
                                                                        <td><?= htmlspecialchars($evaluation['score_2']) ?></td>
                                                                        <td><?= htmlspecialchars($evaluation['score_3']) ?></td>
                                                                        <td><?= htmlspecialchars($evaluation['score_4']) ?></td>
                                                                        <td><?= htmlspecialchars($evaluation['score_5']) ?></td>
                                                                        <td><?= htmlspecialchars(($evaluation['score_1']+$evaluation['score_2']+$evaluation['score_3']+$evaluation['score_4']+$evaluation['score_5'])/5) ?></td>
                                                                        <td><?= htmlspecialchars($evaluation['score_6']) ?></td>
                                                                        <td><?= htmlspecialchars($evaluation['score_7']) ?></td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /Tab1-->
                                    
                                    <!-- Tab2-->
                                    <div class="tab-pane fade" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">
                                        <div class="table-responsive">
                                            <div class="report-section">
                                                <h4>İşçilərin Qiymətləndirilməsi Nəticələri</h4>
                                                <?php if (empty($employee_evaluations)): ?>
                                                    <p>Heç bir işçi qiymətləndirməsi tapılmadı.</p>
                                                <?php else: ?>
                                                    <table id="employee-evaluations" class="display">
                                                        <thead>
                                                            <tr>
                                                                <th>İşçinin Adı Soyadı Ata adı</th>
                                                                <th>Davamiyyət</th>
                                                                <th>Korporativ etika</th>
                                                                <th>Geyim qaydalarına riayət</th>
                                                                <th>Təşəbbüskarlıq</th>
                                                                <th>Komanda işi</th>
                                                                <th>Çeviklik</th>
                                                                <th>Problemi həll etmə bacarığı</th>
                                                                <th>Korrespondensiya</th>
                                                                <th>Hesabatlıq</th>
                                                                <th>Ortalama</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($employee_evaluations as $evaluation): ?>
                                                                <?php
                                                                    $employee_stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
                                                                    $employee_stmt->execute([$evaluation['user_id']]);
                                                                    $employee = $employee_stmt->fetch();
                                                                ?>
                                                                <tr>
                                                                    <td><?= htmlspecialchars($employee['name']) ?></td>
                                                                    <td><?= htmlspecialchars($evaluation['score_1']) ?></td>
                                                                    <td><?= htmlspecialchars($evaluation['score_2']) ?></td>
                                                                    <td><?= htmlspecialchars($evaluation['score_3']) ?></td>
                                                                    <td><?= htmlspecialchars($evaluation['score_4']) ?></td>
                                                                    <td><?= htmlspecialchars($evaluation['score_5']) ?></td>
                                                                    <td><?= htmlspecialchars($evaluation['score_6']) ?></td>
                                                                    <td><?= htmlspecialchars($evaluation['score_7']) ?></td>
                                                                    <td><?= htmlspecialchars($evaluation['score_8']) ?></td>
                                                                    <td><?= htmlspecialchars($evaluation['score_9']) ?></td>
                                                                    <td><?= htmlspecialchars(($evaluation['score_1']+$evaluation['score_2']+$evaluation['score_3']+$evaluation['score_4']+$evaluation['score_5']+$evaluation['score_6']+$evaluation['score_7']+$evaluation['score_8']+$evaluation['score_9'])/9) ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- /Tab2 -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Content-->
    </div>
    <!-- Inner Wrapper -->
    
    <div class="sidebar-overlay" id="sidebar_overlay"></div>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    
    <!-- Bootstrap Core JS -->
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>    
    
    <!-- Sticky sidebar JS -->
    <script src="assets/plugins/theia-sticky-sidebar/ResizeSensor.js"></script>        
    <script src="assets/plugins/theia-sticky-sidebar/theia-sticky-sidebar.js"></script>        
    
    <!-- DataTables JS -->
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

    <!-- DataTables Buttons JS -->
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>

    <!-- Initializing DataTables and Buttons -->
    <script>
    $(document).ready(function() {
        $('#department-evaluations').DataTable({
            dom: 'Bfrtip',
            buttons: [
                'excelHtml5'
            ]
        });
        
        $('#employee-evaluations').DataTable({
            dom: 'Bfrtip',
            buttons: [
                'excelHtml5'
            ]
        });
    });
    </script>
    
    <!-- Custom Js -->
    <script src="assets/js/script.js"></script>
</body>
</html>