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

// Проверка роли пользователя (5 - Руководитель топ-департамента)
if ($_SESSION['role_id'] != 5) {
    echo "У вас нет доступа к этой странице.";
    exit;
}

// Получение выбранного месяца и года из запроса, если они заданы
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');



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
		
		<!-- Datetimepicker CSS -->
		<link rel="stylesheet" href="assets/css/bootstrap-datetimepicker.min.css">
		
		<!-- Select2 CSS -->
		<link rel="stylesheet" href="assets/plugins/select2/select2.min.css">
		
		<!-- Custom CSS -->
		<link rel="stylesheet" href="assets/css/style.css">
		
		<title>Reviews Page</title>
			
		<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!--[if lt IE 9]>
		<script src="assets/js/html5shiv.min.js"></script>
		<script src="assets/js/respond.min.js"></script>
		<![endif]-->
	  
	</head>
	<body>
	
		<!-- Inner wrapper -->
		<div class="inner-wrapper">
				
			<!-- Loader -->
			<div id="loader-wrapper">
				
				<div class="loader">
				  <div class="dot"></div>
				  <div class="dot"></div>
				  <div class="dot"></div>
				  <div class="dot"></div>
				  <div class="dot"></div>
				</div>
			</div>

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
											<a href="javascript:void(0)" data-toggle="dropdown" class=" menu-style dropdown-toggle">
												<div class="user-avatar d-inline-block">
													<?php echo $user_name; ?>
												</div>
											</a>
												
												<!-- Notifications -->
												<div class="dropdown-menu notification-dropdown-menu shadow-lg border-0 p-3 m-0 dropdown-menu-right">
													
													<!--<a class="dropdown-item p-2" href="settings.html">
														<span class="media align-items-center">
															<span class="lnr lnr-cog mr-3"></span>
															<span class="media-body text-truncate">
																<span class="text-truncate">Settings</span>
															</span>
														</span>
													</a>-->
													<a class="dropdown-item p-2" href="login.php">
														<span class="media align-items-center">
															<span class="lnr lnr-power-switch mr-3"></span>
															<span class="media-body text-truncate">
																<span class="text-truncate">Çıxış</span>
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
								<div class="d-block d-lg-none">
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
													<h4 class="text-dark"><?php echo $user_name; ?></h4>
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
													<a href="#" class="text-dark p-4 first-slider-btn ctm-border-right ctm-border-left ctm-border-top"><span class="lnr lnr-home pr-0 pb-lg-2 font-23"></span><span class="">Ümumi</span></a>												
												</div>
																					
												
												<div class="col-6 align-items-center shadow-none text-center">												
													<a href="top_department_head_report.php" class="text-white active p-4 ctm-border-right ctm-border-left"><span class="lnr lnr-rocket pr-0 pb-lg-2 font-23"></span><span class="">Hesabatlar</span></a>												
												</div>
												 <!-- <div class="col-6 align-items-center shadow-none text-center">												
													<a href="dlinks.php" class="text-white active p-4 ctm-border-right"><span class="lnr lnr-sync pr-0 pb-lg-2 font-23"></span><span class="">Dep-Şöbə</span></a>												
												</div> -->
												<!-- <div class="col-6 align-items-center shadow-none text-center">											
													<a href="delete_user.php" class="text-dark p-4 last-slider-btn1 ctm-border-right ctm-border-left"><span class="lnr lnr-cog pr-0 pb-lg-2 font-23"></span><span class="">Sil</span></a>												
												</div> -->
												<!--<div class="col-6 align-items-center shadow-none text-center">											
													<a href="employment.html" class="text-dark p-4 last-slider-btn ctm-border-right"><span class="lnr lnr-user pr-0 pb-lg-2 font-23"></span><span class="">Profile</span></a>												
												</div> -->
											</div>
										</div>
									</div>
								</div>
								
								<!-- /Sidebar -->
																
							</aside>
						</div>
				
						<div class="col-xl-9 col-lg-8 col-md-12">
							<form>
								<div class="card ctm-border-radius shadow-sm grow">
									<div class="card-header">
										<h4 class="card-title mb-0 d-inline-block">Departamentlərin qiymətləndirmə nəticələri </h4><br>
                                        <?php
                                        try {
                                            // Получение всех топ-департаментов, к которым относится текущий пользователь
                                            $stmt = $pdo->prepare("
                                                SELECT td.id, td.name 
                                                FROM top_departments td
                                                JOIN departments d ON td.id = d.top_department_id
                                                JOIN users u ON d.id = u.department_id
                                                WHERE u.id = ?
                                            ");
                                            $stmt->execute([$user_id]);
                                            $top_departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                        
                                            // echo "<h5>Оценки Топ-Департаментов за $month/$year</h5>";
                                            // echo "<form method='get' action='top_department_head_report.php'>";
                                            // echo "<label for='month' >Месяц:</label>";
                                            // echo "<select id='month' class='form-control' name='month'>";
                                            // for ($i = 1; $i <= 12; $i++) {
                                            //     $selected = ($i == $month) ? 'selected' : '';
                                            //     echo "<option value='$i' $selected>$i</option>";
                                            // }
                                            // echo "</select>";
                                        
                                            // echo "<label for='year' >Год:</label>";
                                            // echo "<input type='text' class='form-control' id='year' name='year' value='$year' size='4'>";
                                        
                                            // echo "<button class='btn btn-theme text-white ctm-border-radius button-1' type='submit'>Фильтровать</button>";
                                            // echo "</form>";
                                            echo "<h5>Departament Reytinqləri $month/$year</h5>";
                                            echo "<form method='get' action='top_department_head_report.php'>";
                                            echo "<div style='display: flex; align-items: center; gap: 10px;'>";
                                            echo "<div>";
                                            echo "<label for='month'>Ay:</label>";
                                            echo "<select id='month' class='form-control' name='month'>";
                                            for ($i = 1; $i <= 12; $i++) {
                                                $selected = ($i == $month) ? 'selected' : '';
                                                echo "<option value='$i' $selected>$i</option>";
                                            }
                                            echo "</select>";
                                            echo "</div>";
                                            echo "<div>";
                                            echo "<label for='year'>İl:</label>";
                                            echo "<input type='text' class='form-control' id='year' name='year' value='$year' size='4'>";
                                            echo "</div>";
                                            echo "</div>";
                                            echo "<button class='btn btn-theme text-white ctm-border-radius button-1' type='submit'>Filter</button>";
                                            echo "</form>";

                                        
                                            // Отображение оценок топ-департаментов
                                            echo "<h4>Departament Reytinqləri</h4>";
                                            echo "<table class='table table-bordered'>";
                                            echo "<tr><th>Departament</th><th>Ortalama Reytinq</th></tr>";
                                        
                                            foreach ($top_departments as $top_department) {
                                                $top_department_id = $top_department['id'];
                                                $top_department_name = $top_department['name'];
                                        
                                                // Вычисление средней оценки для текущего топ-департамента за выбранный месяц и год
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
                                                $average_score = $result['average_score'] ?? 'Нет данных';
                                        
                                                echo "<tr><td>{$top_department_name}</td><td>" . number_format($average_score, 2) . "</td></tr>";
                                            }
                                        
                                            echo "</table>";
                                        
                                            // Отображение таблицы с работниками
                                            echo "<h4>Departament İşçiləri reytinqi $month/$year</h4>";
                                            echo "<table class='table table-bordered'>";
                                            echo "<tr><th>İşçi</th><th>Şöbə</th><th>Ortalama Reytinq</th></tr>";
                                        
                                            foreach ($top_departments as $top_department) {
                                                $top_department_id = $top_department['id'];
                                        
                                                // Получение работников и их средних оценок
                                                $stmt = $pdo->prepare("
                                                    SELECT u.name as employee_name, d.name as department_name, AVG((ev.score_1 + ev.score_2 + ev.score_3 + ev.score_4 + ev.score_5 + ev.score_6 + ev.score_7 + ev.score_8 + ev.score_9) / 9) as average_score
                                                    FROM users u
                                                    JOIN evaluations ev ON u.id = ev.user_id
                                                    JOIN departments d ON u.department_id = d.id
                                                    WHERE d.top_department_id = ?
                                                      AND YEAR(ev.created_at) = ?
                                                      AND MONTH(ev.created_at) = ?
                                                    GROUP BY u.id, u.name, d.name
                                                ");
                                                $stmt->execute([$top_department_id, $year, $month]);
                                                $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                        
                                                foreach ($employees as $employee) {
                                                    echo "<tr><td>{$employee['employee_name']}</td><td>{$employee['department_name']}</td><td>" . number_format($employee['average_score'], 2) . "</td></tr>";
                                                }
                                            }
                                        
                                            echo "</table>";
                                        
                                            // Отображение таблицы с оценками департаментов
                                            echo "<h4>Şöbə Reytinqləri $month/$year</h4>";
                                            echo "<table class='table table-bordered'>";
                                            echo "<tr><th>Şöbə</th><th>Ortalama Reytinq</th></tr>";
                                        
                                            foreach ($top_departments as $top_department) {
                                                $top_department_id = $top_department['id'];
                                        
                                                // Получение департаментов и их средних оценок
                                                $stmt = $pdo->prepare("
                                                    SELECT d.name as department_name, AVG((e.score_1 + e.score_2 + e.score_3 + e.score_4 + e.score_5 + e.score_6 + e.score_7) / 7) as average_score
                                                    FROM department_evaluations e
                                                    JOIN departments d ON e.department_id = d.id
                                                    WHERE d.top_department_id = ?
                                                      AND YEAR(e.created_at) = ?
                                                      AND MONTH(e.created_at) = ?
                                                    GROUP BY d.id, d.name
                                                ");
                                                $stmt->execute([$top_department_id, $year, $month]);
                                                $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                        
                                                foreach ($departments as $department) {
                                                    echo "<tr><td>{$department['department_name']}</td><td>" . number_format($department['average_score'], 2) . "</td></tr>";
                                                }
                                            }
                                        
                                            echo "</table>";
                                        } catch (PDOException $e) {
                                            echo "Ошибка: " . $e->getMessage();
                                        }
                                        


?>
						</div>
					</div>
				</div>
			</div>
			<!--/Content-->
			
		</div>
		<!-- Inner Wrapper -->
		
		<div class="sidebar-overlay" id="sidebar_overlay"></div>
		
				
		<!-- jQuery -->
		<script src="assets/js/jquery-3.2.1.min.js"></script>
		
		<!-- Bootstrap Core JS -->
		<script src="assets/js/popper.min.js"></script>
		<script src="assets/js/bootstrap.min.js"></script>
				
		<!-- Datetimepicker JS -->
		<script src="assets/plugins/select2/moment.min.js"></script>
		<script src="assets/js/bootstrap-datetimepicker.min.js"></script>
		
		<!-- Select2 JS -->
		<script src="assets/plugins/select2/select2.min.js"></script>
		
		<!-- Sticky sidebar JS -->
		<script src="assets/plugins/theia-sticky-sidebar/ResizeSensor.js"></script>		
		<script src="assets/plugins/theia-sticky-sidebar/theia-sticky-sidebar.js"></script>		
			
		<!-- Custom Js -->
		<script src="assets/js/script.js"></script>
		
	</body>
</html>

