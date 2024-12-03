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
    echo "Bu səhifəyə giriş üçün icazəniz yoxdur";
    exit;
}

if (isset($_POST['add_user'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $role_id = (int)$_POST['role_id'];
    $department_id = isset($_POST['department_id']) ? (int)$_POST['department_id'] : null;
    $top_department_id = isset($_POST['top_department_id']) ? (int)$_POST['top_department_id'] : null;
    
    // Оставляем формат даты 'Y-m-d', как он приходит из input type="date"
    $hire_date = $_POST['hire_date'];
    
    $position = trim($_POST['position']);

    // Проверка уникальности email
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo "Этот email уже зарегистрирован!";
        exit;
    }

    // Проверка корректности role_id
    $valid_roles = [1, 2, 3, 4, 5];
    if (!in_array($role_id, $valid_roles)) {
        echo "Недопустимая роль!";
        exit;
    }

    // Вставка данных в базу
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role_id, department_id, top_department_id, hire_date, position) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $email, $password, $role_id, $department_id, $top_department_id, $hire_date, $position]);
    echo "Пользователь добавлен!";
}


if (isset($_POST['add_department'])) {
    $department_name = $_POST['department_name'];

    $stmt = $pdo->prepare("INSERT INTO departments (name) VALUES (?)");
    $stmt->execute([$department_name]);
    echo "Департамент добавлен!";
}

if (isset($_POST['add_top_department'])) {
    $name = $_POST['name'];

    try {
        $stmt = $pdo->prepare("INSERT INTO top_departments (name) VALUES (?)");
        $stmt->execute([$name]);
        echo "Топ-Департамент добавлен!";
    } catch (PDOException $e) {
        echo "Ошибка: " . $e->getMessage();
    }
}

if (isset($_POST['set_date_ranges'])) {
    $start_day_1 = $_POST['start_day_1'];
    $end_day_1 = $_POST['end_day_1'];
    $start_day_2 = $_POST['start_day_2'];
    $end_day_2 = $_POST['end_day_2'];

    // Проверяем, существует ли хотя бы одна запись в таблице date_ranges
    $stmt = $pdo->query("SELECT COUNT(*) FROM date_ranges");
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        // Если запись существует, обновляем её
        $stmt = $pdo->prepare("UPDATE date_ranges SET start_day_1 = ?, end_day_1 = ?, start_day_2 = ?, end_day_2 = ?");
        $stmt->execute([$start_day_1, $end_day_1, $start_day_2, $end_day_2]);
    } else {
        // Если записи нет, создаем новую
        $stmt = $pdo->prepare("INSERT INTO date_ranges (start_day_1, end_day_1, start_day_2, end_day_2) VALUES (?, ?, ?, ?)");
        $stmt->execute([$start_day_1, $end_day_1, $start_day_2, $end_day_2]);
    }

    echo "Диапазоны дат обновлены!";
}



if (isset($_POST['evaluate_employee'])) {
    $user_id = $_POST['user_id'];
    $score_1 = $_POST['score_1'];
    $score_2 = $_POST['score_2'];
    $score_3 = $_POST['score_3'];
    $score_4 = $_POST['score_4'];
    $score_5 = $_POST['score_5'];
    $score_6 = $_POST['score_6'];
    $score_7 = $_POST['score_7'];
    $score_8 = $_POST['score_8'];
    $score_9 = $_POST['score_9'];
    $score_10 = $_POST['score_10'];

    // Проверка даты
    $today = date('j');
    $date_ranges = $pdo->query("SELECT * FROM date_ranges")->fetch();
    $allowed = false;

    // Проверка диапазонов дат
    if (($today >= $date_ranges['start_day_1'] && $today <= $date_ranges['end_day_1']) ||
        ($today >= $date_ranges['start_day_2'] && $today <= $date_ranges['end_day_2'])) {
        $allowed = true;
    }

    if ($allowed) {
        // Проверка, была ли уже дана оценка в текущем диапазоне дат
        $stmt_check = $pdo->prepare("SELECT * FROM evaluations WHERE user_id = ? AND DATE(created_at) = CURDATE()");
        $stmt_check->execute([$user_id]);
        $existing_evaluation = $stmt_check->fetch();

        if ($existing_evaluation) {
            echo "Вы уже оценили этого сотрудника сегодня.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO evaluations (user_id, evaluator_id, score_1, score_2, score_3, score_4, score_5, score_6, score_7, score_8, score_9, score_10) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute(array_merge([$user_id, $_SESSION['user_id']], array_values($scores)));
            echo "Оценка сотрудника добавлена!";
        }
    } else {
        echo "Оценки можно ставить только в установленные дни месяца.";
    }
}

if (isset($_POST['evaluate_department'])) {
    $department_id = $_POST['department_id'];
    $score_1 = $_POST['score_1'];
    $score_2 = $_POST['score_2'];
    $score_3 = $_POST['score_3'];
    $score_4 = $_POST['score_4'];
    $score_5 = $_POST['score_5'];
    $score_6 = $_POST['score_6'];
    $score_7 = $_POST['score_7'];

    // Проверка даты
    $today = date('j');
    $date_ranges = $pdo->query("SELECT * FROM date_ranges")->fetch();
    $allowed = false;

    // Проверка диапазонов дат
    if (($today >= $date_ranges['start_day_1'] && $today <= $date_ranges['end_day_1']) ||
        ($today >= $date_ranges['start_day_2'] && $today <= $date_ranges['end_day_2'])) {
        $allowed = true;
    }

    if ($allowed) {
        $stmt = $pdo->prepare("INSERT INTO department_evaluations (department_id, evaluator_id, score_1, score_2, score_3, score_4, score_5, score_6, score_7) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$department_id, $_SESSION['user_id'], $score_1, $score_2, $score_3, $score_4, $score_5, $score_6, $score_7]);
        echo "Оценка департамента добавлена!";
    } else {
        echo "Оценки можно ставить только в установленные дни месяца.";
    }
}

if (isset($_POST['associate_director'])) {
    $director_id = (int)$_POST['director_id'];
    $top_departments = $_POST['top_departments'];

    // Удаляем старые ассоциации директора с топ-департаментами
    $stmt = $pdo->prepare("DELETE FROM director_top_departments WHERE director_id = ?");
    $stmt->execute([$director_id]);

    // Добавляем новые ассоциации
    foreach ($top_departments as $top_department_id) {
        $stmt = $pdo->prepare("INSERT INTO director_top_departments (director_id, top_department_id) VALUES (?, ?)");
        $stmt->execute([$director_id, (int)$top_department_id]);
    }

    echo "Ассоциации директора с топ-департаментами успешно обновлены!";
}

// Код для обработки удаления ассоциации
if (isset($_POST['delete_association'])) {
    $director_id = (int)$_POST['director_id'];

    // Удаляем все ассоциации директора с топ-департаментами
    $stmt = $pdo->prepare("DELETE FROM director_top_departments WHERE director_id = ?");
    $stmt->execute([$director_id]);

    echo "Ассоциации директора с топ-департаментами успешно удалены!";
}

// Получение списка директоров и топ-департаментов для отображения в форме
$directors = $pdo->query("SELECT id, name FROM users WHERE role_id = 3")->fetchAll();  // Директора

// Создаем карту директоров
$directors_map = [];
foreach ($directors as $director) {
    $directors_map[$director['id']] = $director['name'];
}

// Получение текущих ассоциаций директоров и топ-департаментов
$stmt = $pdo->query("
    SELECT dtd.director_id, td.name AS top_department_name
    FROM director_top_departments dtd
    JOIN top_departments td ON dtd.top_department_id = td.id
");
$associations = $stmt->fetchAll(PDO::FETCH_ASSOC);

$existing_associations = [];
foreach ($associations as $association) {
    $existing_associations[$association['director_id']][] = $association['top_department_name'];
}



// Получение пользователей
$users = $pdo->query("SELECT * FROM users")->fetchAll();

// Получение департаментов
$departments = $pdo->query("SELECT * FROM departments")->fetchAll();
$top_departments = $pdo->query("SELECT * FROM top_departments")->fetchAll();

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
		
		<title>Manage Page</title>
		
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
										<!-- <div class="user-notification-block align-right d-inline-block">
											<div class="top-nav-search">
												<form>
													<input type="text" class="form-control" placeholder="Search here">
													<button class="btn" type="submit"><i class="fa fa-search"></i></button>
												</form>
											</div>
										</div> -->
										
										<!-- User notification-->
										<!-- <div class="user-notification-block align-right d-inline-block">
											<ul class="list-inline m-0">
												<li class="list-inline-item dropdown" data-toggle="tooltip" data-placement="top" title="" data-original-title="Apply Leave">
													<a href="leave.php" class="lnr lnr-briefcase position-relative font-23 dropdown-toggle menu-style text-white align-middle" data-toggle="dropdown">
													</a>
												</li>
											</ul>
										</div> -->
										
										<!-- /User notification-->
										
										<!-- user info-->
										<div class="user-info align-right dropdown d-inline-block">
											<a href="javascript:void(0)" data-toggle="dropdown" class=" menu-style dropdown-toggle">
												<div class="user-avatar d-inline-block">
													<?php echo $user_name; ?>
												</div>
											</a>
											
											<!-- Notifications -->
											<div class="dropdown-menu notification-dropdown-menu shadow-lg border-0 p-3 m-0 dropdown-menu-right">
													<!-- <a class="dropdown-item p-2" href="employment.html">
														<span class="media align-items-center">
															<span class="lnr lnr-user mr-3"></span>
															<span class="media-body text-truncate">
																<span class="text-truncate">Profile</span>
															</span>
														</span>
													</a> -->
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
																<span class="text-truncate"><a href="logout.php">Выйти</a></span>
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
								
								<!-- Offcanvas menu -->
								<div class="offcanvas-menu" id="offcanvas_menu">
										<span class="lnr lnr-cross float-left display-6 position-absolute t-1 l-1 text-white" id="close_navSidebar"></span>
										<div class="user-info align-center bg-theme text-center">
											<a href="javascript:void(0)" class="d-block menu-style text-white">
												<div class="user-avatar d-inline-block mr-3">
													<img src="assets/img/profiles/img-6.jpg" alt="user avatar" class="rounded-circle img-fluid" width="55">
												</div>
											</a>
										</div>
										<div class="user-notification-block align-center">
											<div class="top-nav-search">
												<form>
													<input type="text" class="form-control" placeholder="Search here">
													<button class="btn" type="submit"><i class="fa fa-search"></i></button>
												</form>
											</div>
										</div>
										<hr>
										<div class="user-menu-items px-3 m-0">
											<a class="px-0 pb-2 pt-0" href="#">
												<span class="media align-items-center">
													<span class="lnr lnr-home mr-3"></span>
													<span class="media-body text-truncate text-left">
														<span class="text-truncate text-left">Dashboard</span>
													</span>
												</span>
											</a>
											<a class="p-2" href="employees.php">
												<span class="media align-items-center">
													<span class="lnr lnr-users mr-3"></span>
													<span class="media-body text-truncate text-left">
														<span class="text-truncate text-left">Employees</span>
													</span>
												</span>
											</a>
											<a class="p-2" href="company.php">
												<span class="media align-items-center">
													<span class="lnr lnr-apartment mr-3"></span>
													<span class="media-body text-truncate text-left">
														<span class="text-truncate text-left">Company</span>
													</span>
												</span>
											</a>
											<a class="p-2" href="calendar.php">
												<span class="media align-items-center">
													<span class="lnr lnr-calendar-full mr-3"></span>
													<span class="media-body text-truncate text-left">
														<span class="text-truncate text-left">Calendar</span>
													</span>
												</span>
											</a>
											<a class="p-2" href="leave.php">
												<span class="media align-items-center">
													<span class="lnr lnr-briefcase mr-3"></span>
													<span class="media-body text-truncate text-left">
														<span class="text-truncate text-left">Leave</span>
													</span>
												</span>
											</a>
											<a class="p-2" href="reviews.php">
												<span class="media align-items-center">
													<span class="lnr lnr-star mr-3"></span>
													<span class="media-body text-truncate text-left">
														<span class="text-truncate text-left">Reviews</span>
													</span>
												</span>
											</a>
											<a class="p-2" href="reports.php">
												<span class="media align-items-center">
													<span class="lnr lnr-rocket mr-3"></span>
													<span class="media-body text-truncate text-left">
														<span class="text-truncate text-left">Reports</span>
													</span>
												</span>
											</a>
											<a class="p-2" href="manage.php">
												<span class="media align-items-center">
													<span class="lnr lnr-sync mr-3"></span>
													<span class="media-body text-truncate text-left">
														<span class="text-truncate text-left">Manage</span>
													</span>
												</span>
											</a>
											
											<a class="p-2" href="settings.html">
												<span class="media align-items-center">
													<span class="lnr lnr-cog mr-3"></span>
													<span class="media-body text-truncate text-left">
														<span class="text-truncate text-left">Settings</span>
													</span>
												</span>
											</a>
											<a class="p-2" href="employment.html">
												<span class="media align-items-center">
													<span class="lnr lnr-user mr-3"></span>
													<span class="media-body text-truncate text-left">
														<span class="text-truncate text-left">Profile</span>
													</span>
												</span>
											</a>
											<a class="p-2" href="login.php">
												<span class="media align-items-center">
													<span class="lnr lnr-power-switch mr-3"></span>
													<span class="media-body text-truncate text-left">
														<span class="text-truncate text-left">Logout</span>
													</span>
												</span>
											</a>
										</div>
									</div>
								<!-- /Offcanvas menu -->
								
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
													<a href="dashboard.php" class="text-white active p-4 first-slider-btn ctm-border-right ctm-border-left ctm-border-top"><span class="lnr lnr-home pr-0 pb-lg-2 font-23"></span><span class="">Ümumi</span></a>												
												</div>
												<!-- <div class="col-6 align-items-center shadow-none text-center">											
													<a href="employees.php" class="text-dark p-4 second-slider-btn ctm-border-right ctm-border-top"><span class="lnr lnr-users pr-0 pb-lg-2 font-23"></span><span class="">Employees</span></a>												
												</div>
												<div class="col-6 align-items-center shadow-none text-center">												
													<a href="company.php" class="text-dark p-4 ctm-border-right ctm-border-left"><span class="lnr lnr-apartment pr-0 pb-lg-2 font-23"></span><span class="">Company</span></a>												
												</div> -->
												<!-- <div class="col-6 align-items-center shadow-none text-center">												
													<a href="calendar.php" class="text-dark p-4 ctm-border-right"><span class="lnr lnr-calendar-full pr-0 pb-lg-2 font-23"></span><span class="">Calendar</span></a>												
												</div>
												<div class="col-6 align-items-center shadow-none text-center">											
													<a href="leave.php" class="text-dark p-4 ctm-border-right ctm-border-left"><span class="lnr lnr-briefcase pr-0 pb-lg-2 font-23"></span><span class="">Leave</span></a>											
												</div>
												<div class="col-6 align-items-center shadow-none text-center">											
													<a href="reviews.php" class="text-dark p-4 ctm-border-right"><span class="lnr lnr-star pr-0 pb-lg-2 font-23"></span><span class="">Reviews</span></a>												
												</div> -->
												<div class="col-6 align-items-center shadow-none text-center">												
													<a href="reports.php" class="text-dark p-4 ctm-border-right ctm-border-left"><span class="lnr lnr-rocket pr-0 pb-lg-2 font-23"></span><span class="">Hesabatlar</span></a>												
												</div>
												 <div class="col-6 align-items-center shadow-none text-center">												
													<a href="dlinks.php" class="text-dark p-4 ctm-border-right"><span class="lnr lnr-sync pr-0 pb-lg-2 font-23"></span><span class="">D-Ş</span></a>												
												</div>
												<div class="col-6 align-items-center shadow-none text-center">											
													<a href="delete_user.php" class="text-dark p-4 last-slider-btn1 ctm-border-right ctm-border-left"><span class="lnr lnr-cog pr-0 pb-lg-2 font-23"></span><span class="">Del</span></a>												
												</div>
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
					
					<div class="col-xl-9 col-lg-8  col-md-12">
						
						<!-- <div class="card ctm-border-radius shadow-sm grow">
							<div class="card-header">
								<h4 class="card-title mb-0 d-inline-block">Your Admin</h4>
							</div>
							<div class="card-body">
								<a class="mb-0 cursor-pointer d-block">
								<span class="avatar" data-toggle="tooltip" data-placement="top" title="Richard Wilson"><img src="assets/img/profiles/img-10.jpg" alt="Richard Wilson" class="img-fluid"></span>
								<span class="ml-4">1 Admin</span>
								</a>
							</div>
						</div> -->
						<div class="quicklink-sidebar-menu ctm-border-radius shadow-sm grow bg-white card">
								<div class="card-body">
									<div class="list-group list-group-horizontal-lg" id="v-pills-tab" role="tablist">
										<a class=" active list-group-item" id="v-pills-home-tab" data-toggle="pill" href="#v-pills-home" role="tab" aria-controls="v-pills-home" aria-selected="true">İstifadəçi əlavəsi</a>
										<a class="list-group-item" id="v-pills-profile-tab" data-toggle="pill" href="#v-pills-profile" role="tab" aria-controls="v-pills-profile" aria-selected="false">Şöbə əlavəsi</a>
										<a class="list-group-item" id="v-pills-messages-tab" data-toggle="pill" href="#v-pills-messages" role="tab" aria-controls="v-pills-messages" aria-selected="false">Təqvim</a>
										<a class="list-group-item" id="v-pills-settings-tab" data-toggle="pill" href="#v-pills-settings" role="tab" aria-controls="v-pills-settings" aria-selected="false">Əməkdaşların Qiymətləndirilməsi</a>
										<a class="list-group-item" id="v-pills-settings-tab1" data-toggle="pill" href="#v-pills-settings1" role="tab" aria-controls="v-pills-settings1" aria-selected="false">Şöbələrin Qiymətləndirilməsi</a>
										<a class="list-group-item" id="v-pills-settings-tab1" data-toggle="pill" href="#v-pills-settings2" role="tab" aria-controls="v-pills-settings1" aria-selected="false">Departament əlavəsi</a>
										<a class="list-group-item" id="v-pills-director-top-departments-tab" data-toggle="pill" href="#v-pills-director-top-departments" role="tab" aria-controls="v-pills-director-top-departments" aria-selected="false">Ассоциация директоров с топ-департаментами</a>


									</div>
								</div>
							</div>
						<div class="card ctm-border-radius shadow-sm grow">
							<div class="card-body">
								<div class="tab-content" id="v-pills-tabContent">
								
									<!-- Tab1-->
									<div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
										<div class="table">
										<!-- <h4>İstifadəçi əlavə et</h4><br> -->
        <div class="form-section">
            
            <form method="post">
    <div class="col-md-12 form-group">
        <input type="text" name="name" class="form-control" placeholder="Ad Soyad Ata adı" required>
    </div>
    <div class="col-md-12 form-group">
        <input type="email" name="email" class="form-control" placeholder="E-Poçt" required>
    </div>
    <div class="col-md-12 form-group">
        <input type="password" name="password" class="form-control" placeholder="Şifrə" required>
    </div>
    <div class="col-md-12 form-group">
        <select name="role_id" required class="form-control">
                    <option value="" disabled selected>Rolu seçin</option>
                    <option value="1">Admin</option>
                    <option value="2">Şöbə Mudiri</option>
                    <option value="3">Direktor</option>
                    <option value="4">İşçi</option>
					<option value="5">Departament Rəhbəri</option>
                </select>
    </div>
    <div class="col-md-12 form-group">
        <select name="department_id" class="form-control">
            <option value="" disabled selected>Şöbəni Seçin</option>
            <?php foreach ($departments as $department): ?>
                <option value="<?= $department['id'] ?>"><?= $department['name'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-12 form-group">
        <select name="top_department_id" class="form-control">
            <option value="" disabled selected>Departamenti Seçin</option>
            <?php foreach ($top_departments as $top_department): ?>
                <option value="<?= $top_department['id'] ?>"><?= $top_department['name'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
<div class="col-md-12 form-group">
    <label for="hire_date">Tarix Vəzifəsinə Başlanması:</label>
    <input type="date" name="hire_date" class="form-control" placeholder="dd.mm.yyyy" required>
</div>
    <div class="col-md-12 form-group">
        <input type="text" name="position" class="form-control" placeholder="Vəzifəsi" required>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="submit-section text-center btn-add">
                <button class="btn btn-theme text-white ctm-border-radius button-1" type="submit" name="add_user">Əlavə Et</button>
            </div>
        </div>
    </div>
</form>

        </div>
										</div>
									</div>
									<!-- /Tab1-->
									
									<!-- Tab2-->
									<div class="tab-pane fade" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">
										<div class="table">
        <div class="form-section">
            <!-- <h2>Добавить департамент</h2> -->
            <form method="post">
			<div class="col-md-12 form-group">
                <input type="text" name="department_name" class="form-control" placeholder="Şöbənin adı" required></div>
                <!-- <button type="submit" name="add_department">Добавить департамент</button> -->
				<div class="row">
                  <div class="col-12">
                    <div class="submit-section text-center btn-add">
                      <button class="btn btn-theme text-white ctm-border-radius button-1" type="submit" name="add_department">Əlavə Et</button>
                   </div>
                  </div>
                </div>
            </form>
        </div>
										</div>
									</div>
									<!-- /Tab2 -->
									
									<!-- Tab3 -->
<div class="tab-pane fade" id="v-pills-messages" role="tabpanel" aria-labelledby="v-pills-messages-tab">
    <div class="table">
        <div class="form-section">
            <h4>Tarix diapazonlarının qurulması</h4><br>
            <form method="post">
				
				<div class="row">
				    <div class="col-md-6 form-group">
		                <label for="start_day_1">1-ci aralığın başlanğıcı:</label>
		                <input type="number" class="form-control" name="start_day_1" placeholder="1-ci aralığın başlanğıcı (1-31)" min="1" max="31" required>
				    </div>
				    <div class="col-md-6 form-group">
		                <label for="end_day_1">1-ci aralığın sonu:</label>
		                <input type="number" class="form-control" name="end_day_1" placeholder="1-ci aralığın sonu (1-31)" min="1" max="31" required>
				    </div>
				</div>
				
				<div class="row">
				    <div class="col-md-6 form-group">
		                <label for="start_day_2">2-ci aralığın başlanğıcı:</label>
		                <input type="number" class="form-control" name="start_day_2" placeholder="2-ci aralığın başlanğıcı (1-31)" min="1" max="31" required>
				    </div>
				    <div class="col-md-6 form-group">
		                <label for="end_day_2">2-ci aralığın sonu:</label>
		                <input type="number" class="form-control" name="end_day_2" placeholder="2-ci aralığın sonu (1-31)" min="1" max="31" required>
				    </div>
				</div>

                <!-- Submit button -->
                <div class="row">
                    <div class="col-12 text-center">
                        <button class="btn btn-theme text-white ctm-border-radius button-1" type="submit" name="set_date_ranges">Tarix diapazonlarını təyin edin</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- /Tab3 -->
									
									<!-- Tab4 -->
									<div class="tab-pane fade" id="v-pills-settings" role="tabpanel" aria-labelledby="v-pills-settings-tab">
										<div class="table">
        <div class="form-section">
            <!-- <h2>Оценить сотрудника</h2> -->
            <form method="post">
			<div class="col-md-12 form-group">
                <select name="user_id" required class="form-control">
                    <option value="" disabled selected>Əməkdaşı seç</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>"><?= $user['name'] ?></option>
                    <?php endforeach; ?>
                </select></div>
				<div class="col-md-12 form-group">
                <input type="number" class="form-control" name="score_1" placeholder="Davamiyyət" min="1" max="10" required> </div>
				<div class="col-md-12 form-group">
				<input type="number" class="form-control" name="score_2" placeholder="Korporativ etika" min="1" max="10" required> </div>
				<div class="col-md-12 form-group">
				<input type="number" class="form-control" name="score_3" placeholder="Geyim qaydalarına riayət" min="1" max="10" required> </div>
				<div class="col-md-12 form-group"> 
				<input type="number" class="form-control" name="score_4" placeholder="Təşəbbüskarlıq" min="1" max="10" required> </div>
				<div class="col-md-12 form-group">
				<input type="number" class="form-control" name="score_5" placeholder="Komanda işi" min="1" max="10" required> </div>
				<div class="col-md-12 form-group">
				<input type="number" class="form-control" name="score_6" placeholder="Çeviklik" min="1" max="10" required> </div>
				<div class="col-md-12 form-group">
				<input type="number" class="form-control" name="score_7" placeholder="Problemi həll etmə bacarığı" min="1" max="10" required> </div>
				<div class="col-md-12 form-group">
				<input type="number" class="form-control" name="score_8" placeholder="Korrespondensiya" min="1" max="10" required> </div>
				<div class="col-md-12 form-group">
				<input type="number" class="form-control" name="score_9" placeholder="Hesabatlıq" min="1" max="10" required> </div>
				<div class="col-md-12 form-group">
				<input type="number" class="form-control" name="score_10" placeholder="Bu silinməlidir" min="1" max="10" required> </div>

                <!-- <button type="submit" name="evaluate_employee">Оценить сотрудника</button> -->
				<div class="row">
                  <div class="col-12">
                    <div class="submit-section text-center btn-add">
                      <button class="btn btn-theme text-white ctm-border-radius button-1" type="submit" name="evaluate_employee">Əməkdaşı Qiymətləndir</button>
                   </div>
                  </div>
                </div>
            </form>
        </div>
										</div>
									</div>
									<!-- /Tab4 -->
									
									<!-- Tab5 -->
									<div class="tab-pane fade" id="v-pills-settings1" role="tabpanel" aria-labelledby="v-pills-settings-tab1">
										<div class="table">
        <div class="form-section">
            <!-- <h2>Оценить департамент</h2> -->
            <form method="post">
			<div class="col-md-12 form-group">
                <select name="department_id" class="form-control" required >
                    <option value="" disabled selected>Şöbəni Seç</option>
                    <?php foreach ($departments as $department): ?>
                        <option value="<?= $department['id'] ?>"><?= $department['name'] ?></option>
                    <?php endforeach; ?>
                </select></div>
				<div class="col-md-12 form-group">
                <input type="number" class="form-control" name="score_1" placeholder="Davamiyyət" min="1" max="5" required></div>
				<div class="col-md-12 form-group">
                <input type="number" class="form-control" name="score_2" placeholder="Korporativ etika" min="1" max="5" required></div>
				<div class="col-md-12 form-group">
                <input type="number" class="form-control" name="score_3" placeholder="Komanda işi" min="1" max="5" required></div>
				<div class="col-md-12 form-group">
                <input type="number" class="form-control" name="score_4" placeholder="Təşəbbüskarlıq" min="1" max="5" required></div>
				<div class="col-md-12 form-group">
                <input type="number" class="form-control" name="score_5" placeholder="Korrespondensiya" min="1" max="5" required></div>
				<div class="col-md-12 form-group">
                <input type="number" class="form-control" name="score_6" placeholder="Proses və prosedurlara riayət" min="1" max="5" required></div>
				<div class="col-md-12 form-group">
                <input type="number" class="form-control" name="score_7" placeholder="Hesabatlıq" min="1" max="5" required></div>
				<div class="row">
                  <div class="col-12">
                    <div class="submit-section text-center btn-add">
                      <button class="btn btn-theme text-white ctm-border-radius button-1" type="submit" name="evaluate_department">Şöbəni Qiymətləndir</button>
                   </div>
                  </div>
                </div>

                <!-- <button type="submit" class="form-control" name="evaluate_department">Оценить департамент</button> -->
            </form>
        </div>
										</div>
									</div>
									<!-- /Tab5 -->
									
																		<!-- Tab5 -->
									<div class="tab-pane fade" id="v-pills-settings2" role="tabpanel" aria-labelledby="v-pills-settings-tab1">
										<div class="table">
        <div class="form-section">
       <h4>Departament əlavə et</h4>
    <form method="post">
        <label for="name">Departamentin adı:</label>
        <input type="text" id="name" class="form-control" name="name" required>
        <!-- <button type="submit" name="add_top_department">Əlavə et</button> --><br>
		<div class="row">
                  <div class="col-12">
                    <div class="submit-section text-center btn-add">
                      <button class="btn btn-theme text-white ctm-border-radius button-1" type="submit" name="add_top_department">Əlavə et</button>
                   </div>
                  </div>
                </div>
    </form>
        </div>
										</div>
									</div>
									<!-- /Tab5 -->
									
									<div class="tab-pane fade" id="v-pills-director-top-departments" role="tabpanel" aria-labelledby="v-pills-director-top-departments-tab">
    <div class="table">
        <div class="form-section">
            <h4>Ассоциировать директоров с топ-департаментами</h4>
            <form method="post">
                <!-- Выбор директора -->
                <div class="col-md-12 form-group">
                    <label for="director_id">Выберите директора:</label>
                    <select name="director_id" class="form-control" required>
                        <option value="" disabled selected>Выберите директора</option>
                        <?php foreach ($directors as $director): ?>
                            <option value="<?= $director['id'] ?>"><?= $director['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Выбор топ-департаментов -->
                <div class="col-md-12 form-group">
                    <label for="top_departments">Выберите топ-департаменты:</label>
                    <select name="top_departments[]" class="form-control" multiple required>
                        <?php foreach ($top_departments as $top_department): ?>
                            <option value="<?= $top_department['id'] ?>"><?= $top_department['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Кнопка отправки -->
                <div class="row">
                    <div class="col-12">
                        <div class="submit-section text-center btn-add">
                            <button class="btn btn-theme text-white ctm-border-radius button-1" type="submit" name="associate_director">Сохранить</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Таблица с текущими ассоциациями директоров и топ-департаментов -->
    <div class="table-responsive">
        <h4>Текущие ассоциации директоров с топ-департаментами</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Директор</th>
                    <th>Топ-департаменты</th>
                    <th>Действия</th> <!-- Добавляем колонку для кнопки удаления -->
                </tr>
            </thead>
            <tbody>
                <?php foreach ($existing_associations as $director_id => $top_departments): ?>
                    <tr>
                        <td><?= htmlspecialchars($directors_map[$director_id]) ?></td>
                        <td><?= htmlspecialchars(implode(', ', $top_departments)) ?></td>
                        <td>
                            <!-- Кнопка удаления -->
                            <form method="post" style="display:inline-block;">
                                <input type="hidden" name="director_id" value="<?= $director_id ?>">
                                <button class="btn btn-danger" type="submit" name="delete_association">Удалить</button>
                            </form>
                        </td>
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
			<!--/Content-->
			
		</div>
		<!-- Inner Wrapper -->
		
		<div class="sidebar-overlay" id="sidebar_overlay"></div>
				
		<!-- jQuery -->
		<script src="assets/js/jquery-3.2.1.min.js"></script>
		
		<!-- Bootstrap Core JS -->
		<script src="assets/js/popper.min.js"></script>
		<script src="assets/js/bootstrap.min.js"></script>	
		
		<!-- Sticky sidebar JS -->
		<script src="assets/plugins/theia-sticky-sidebar/ResizeSensor.js"></script>		
		<script src="assets/plugins/theia-sticky-sidebar/theia-sticky-sidebar.js"></script>		
			
		<!-- Custom Js -->
		<script src="assets/js/script.js"></script>
		
	</body>
</html>