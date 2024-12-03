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
													<a href="dashboard.php" class="text-dark p-4 first-slider-btn ctm-border-right ctm-border-left ctm-border-top"><span class="lnr lnr-home pr-0 pb-lg-2 font-23"></span><span class="">Ümumi</span></a>												
												</div>
																					
												
												<div class="col-6 align-items-center shadow-none text-center">												
													<a href="reports.php" class="text-dark p-4 ctm-border-right ctm-border-left"><span class="lnr lnr-rocket pr-0 pb-lg-2 font-23"></span><span class="">Hesabatlar</span></a>												
												</div>
												 <div class="col-6 align-items-center shadow-none text-center">												
													<a href="dlinks.php" class="text-dark p-4 ctm-border-right"><span class="lnr lnr-sync pr-0 pb-lg-2 font-23"></span><span class="">Dep-Şöbə</span></a>												
												</div>
												<div class="col-6 align-items-center shadow-none text-center">											
													<a href="delete_user.php" class="text-white active p-4 last-slider-btn1 ctm-border-right ctm-border-left"><span class="lnr lnr-cog pr-0 pb-lg-2 font-23"></span><span class="">Sil</span></a>												
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
				
						<div class="col-xl-9 col-lg-8 col-md-12">
                        <form method="post">
								<div class="card ctm-border-radius shadow-sm grow">
									<div class="card-header">
										<!-- Форма удаления пользователя -->
        <h4>İstifadəçini sil</h4><br>
        <form method="post">
            <select name="user_id" class="form-control" required>
                <option value="" disabled selected>İstifadəçini seçin</option>
                <?php foreach ($employees as $employee): ?>
                    <option value="<?= $employee['id'] ?>"><?= $employee['name'] ?></option>
                <?php endforeach; ?>
            </select>
            <!-- <button type="submit" name="delete_user">Удалить пользователя</button> --><br>
            <div class="row">
                  <div class="col-12">
                    <div class="submit-section text-center btn-add">
                      <button class="btn btn-theme text-white ctm-border-radius button-1" type="submit" name="delete_user">İstifadəçini sil</button>
                   </div>
                  </div>
                </div>
        </form>

        <!-- Форма удаления департамента -->
        <h4>Şöbəni sil</h4><br>
        <form method="post">
            <select name="department_id" class="form-control" required>
                <option value="" disabled selected>Şöbəni seçin</option>
                <?php foreach ($departments as $department): ?>
                    <option value="<?= $department['id'] ?>"><?= $department['name'] ?></option>
                <?php endforeach; ?>
            </select>
            <!-- <button type="submit" name="delete_department">Удалить департамент</button> --><br>
            <div class="row">
                  <div class="col-12">
                    <div class="submit-section text-center btn-add">
                      <button class="btn btn-theme text-white ctm-border-radius button-1" type="submit" name="delete_department">Şöbəni sil</button>
                   </div>
                  </div>
                </div>
        </form>
		
		    <h4>Departamenti sil</h4><br>
    <form action="delete_user.php" method="post">
        <select id="top_department_id" class="form-control" name="top_department_id" required>
            <?php
            // Подключение к базе данных
            $stmt = $pdo->query("SELECT id, name FROM top_departments");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<option value='{$row['id']}'>{$row['name']}</option>";
            }
            ?>
        </select>
        <!-- <button type="submit" name="delete_top_department">Удалить</button> --><br>
        <div class="row">
                  <div class="col-12">
                    <div class="submit-section text-center btn-add">
                      <button class="btn btn-theme text-white ctm-border-radius button-1" type="submit" name="delete_top_department">Departamenti sil</button>
                   </div>
                  </div>
                </div>
    </form>
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