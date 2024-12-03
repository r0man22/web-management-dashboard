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
// Проверка роли пользователя (например, 3 - Директор)
if ($_SESSION['role_id'] != 1) {
    echo "У вас нет доступа к этой странице.";
    exit;
}

// Определение выбранного месяца
$selected_month = isset($_POST['month']) ? $_POST['month'] : date('Y-m');

// Получение отчетов по всем сотрудникам всех департаментов
$employees = $pdo->prepare("
    SELECT u.id, u.name, 
           DATE_FORMAT(e.created_at, '%Y-%m') AS month,
           IFNULL(SUM(e.score_1), 0) AS score_1,
           IFNULL(SUM(e.score_2), 0) AS score_2,
           IFNULL(SUM(e.score_3), 0) AS score_3,
           IFNULL(SUM(e.score_4), 0) AS score_4,
           IFNULL(SUM(e.score_5), 0) AS score_5,
           IFNULL(SUM(e.score_6), 0) AS score_6,
           IFNULL(SUM(e.score_7), 0) AS score_7,
           IFNULL(SUM(e.score_8), 0) AS score_8,
           IFNULL(SUM(e.score_9), 0) AS score_9,
           IFNULL(SUM(e.score_1 + e.score_2 + e.score_3 + e.score_4 + e.score_5 + e.score_6 + e.score_7 + e.score_8 + e.score_9), 0) AS total_score,
           u.department_id, d.name AS department_name
    FROM users u
    JOIN departments d ON u.department_id = d.id
    LEFT JOIN evaluations e ON u.id = e.user_id AND DATE_FORMAT(e.created_at, '%Y-%m') = ?
    GROUP BY u.id, u.name, DATE_FORMAT(e.created_at, '%Y-%m'), u.department_id, d.name
    ORDER BY department_name, month DESC
");
$employees->execute([$selected_month]);
$employee_reports = $employees->fetchAll();

// Получение отчетов по всем департаментам
$departments = $pdo->prepare("
    SELECT d.id, d.name,
           DATE_FORMAT(de.created_at, '%Y-%m') AS month,
           IFNULL(SUM(de.score_1), 0) AS score_1,
           IFNULL(SUM(de.score_2), 0) AS score_2,
           IFNULL(SUM(de.score_3), 0) AS score_3,
           IFNULL(SUM(de.score_4), 0) AS score_4,
           IFNULL(SUM(de.score_5), 0) AS score_5,
           IFNULL(SUM(de.score_6), 0) AS score_6,
           IFNULL(SUM(de.score_7), 0) AS score_7,
           IFNULL(SUM(de.score_1 + de.score_2 + de.score_3 + de.score_4 + de.score_5 + de.score_6 + de.score_7), 0) AS total_score
    FROM departments d
    LEFT JOIN department_evaluations de ON d.id = de.department_id AND DATE_FORMAT(de.created_at, '%Y-%m') = ?
    GROUP BY d.id, d.name, DATE_FORMAT(de.created_at, '%Y-%m')
    ORDER BY d.name, month DESC
");
$departments->execute([$selected_month]);
$department_reports = $departments->fetchAll();
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
		
		<!-- Select2 CSS -->
		<link rel="stylesheet" href="assets/plugins/select2/select2.min.css">
		
		<!-- Datetimepicker CSS -->
		<link rel="stylesheet" href="assets/css/bootstrap-datetimepicker.min.css">
				
		<!-- Tagsinput CSS -->
		<link rel="stylesheet" href="assets/plugins/bootstrap-tagsinput/bootstrap-tagsinput.css">
				
		<!-- Fontawesome CSS -->
        <link rel="stylesheet" href="assets/css/font-awesome.min.css">
				
		<!-- Custom CSS -->
		<link rel="stylesheet" href="assets/css/style.css">

		 <!-- DataTables CSS -->
		 <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">

		<!-- DataTables Buttons CSS -->
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
		
		<title>Hesabat Forma - 2</title>
		
		<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!--[if lt IE 9]>
		<script src="assets/js/html5shiv.min.js"></script>
		<script src="assets/js/respond.min.js"></script>
		<![endif]-->
		
	</head>
	<body>
			
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
											<div class="user-info align-right dropdown d-inline-block header-dropdown">
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
															<li class="breadcrumb-item d-inline-block active">Reports</li>
														</ol>
														<h4 class="text-dark">Reports</h4>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<!-- Sidebar -->
								<div class="sidebar-wrapper d-lg-block d-md-none d-none">
									<div class="card ctm-border-radius shadow-sm border-none grow">
										<div class="card-body">
											<div class="row no-gutters">
												<div class="col-6 align-items-center text-center">
													<a href="dashboard.php" class="text-dark p-4 first-slider-btn ctm-border-right ctm-border-left ctm-border-top"><span class="lnr lnr-home pr-0 pb-lg-2 font-23"></span><span class="">Ümumi</span></a>												
												</div>
												
												<div class="col-6 align-items-center shadow-none text-center">												
													<a href="reports.php" class="text-white active p-4 ctm-border-right ctm-border-left"><span class="lnr lnr-rocket pr-0 pb-lg-2 font-23"></span><span class="">Hesabatlar</span></a>												
												</div>
												<div class="col-6 align-items-center shadow-none text-center">												
													<a href="dlinks.php" class="text-dark p-4 ctm-border-right"><span class="lnr lnr-sync pr-0 pb-lg-2 font-23"></span><span class="">Dep-Şöbə</span></a>												
												</div>
												<div class="col-6 align-items-center shadow-none text-center">											
													<a href="delete_user.php" class="text-dark p-4 last-slider-btn1 ctm-border-right ctm-border-left"><span class="lnr lnr-cog pr-0 pb-lg-2 font-23"></span><span class="">Sil</span></a>												
												</div>
												
											</div>
										</div>
									</div>
								</div>
								
								<!-- /Sidebar -->
								
							</aside>
						</div>
						
						<div class="col-xl-9 col-lg-8  col-md-12">
							<div class="quicklink-sidebar-menu ctm-border-radius shadow-sm grow bg-white card">
									<div class="card-body">
										

										<ul class="list-group list-group-horizontal-lg">
											<li class="list-group-item text-center button-6"><a href="reports.php" class="text-dark">Forma 1 hesabat</a></li>
											<li class="list-group-item text-center active button-5"><a class="text-white" href="reports2.php">Forma 2 hesabat</a></li>
											
										</ul>
									</div>
								</div>
								<div class="card shadow-sm ctm-border-radius grow">
									<div class="card-body align-center">
										<div class="row filter-row">
											<div class="col-sm-6 col-md-6 col-lg-6 col-xl-3"> 
											<form method="post">

            <label for="month" class="form-control">Tarix Seçin:</label>
			<div class="col-md-12 form-group">

            <input type="month" class="form-control" name="month" id="month" value="<?= htmlspecialchars($selected_month) ?>" required></div>
            <!-- <button type="submit">Показать отчеты</button> -->
			<div class="row">
                                                    <div class="col-12">
                                                        <div class="submit-section text-center btn-add">
                                                            <button class="btn btn-theme text-white ctm-border-radius button-1" type="submit">Hesabatlara Bax</button>
                                                        </div>
                                                    </div>
                                                </div>
        									</form>
											
										</div>
									</div>
								</div>
								<div class="card shadow-sm grow ctm-border-radius">
									<div class="card-body align-center">
										<ul class="nav flex-row nav-pills" id="pills-tab" role="tablist" >
											<li class="nav-item mr-2">
												<a class="nav-link active mb-2" id="pills-home-tab" data-toggle="pill" href="#pills-home" role="tab" aria-controls="pills-home" aria-selected="true">Bütün şöbələrin əməkdaşlarının qiymətləndirilməsi
</a>
											</li>
											<!-- <li class="nav-item">
												<a class="nav-link" id="pills-profile-tab" data-toggle="pill" href="#pills-profile" role="tab" aria-controls="pills-profile" aria-selected="false">Personal reports</a>
											</li> -->
										</ul>
									</div>
								</div>
							<div class="card shadow-sm grow ctm-border-radius">
								<div class="card-body align-center">
									<div class="tab-content" id="pills-tabContent">
									
										<!--Tab 1-->
										<div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
										<div class="card shadow-sm grow ctm-border-radius">
								<div class="card-body align-center">
								

									<div class="employee-office-table">
										<div class="table-responsive">
											<table id="reports-3">
											<thead>
                <tr>
                    <th>Adı Soyadı Ata adı</th>
                    <th>Şöbəsi</th>
                    <th>Ay</th>
                    <th>Davamiyyət</th>
                    <th>Korporativ etika</th>
                    <th>Geyim qaydalarına riayət</th>
                    <th>Təşəbbüskarlıq</th>
                    <th>Komanda işi</th>
                    <th>Çeviklik</th>
                    <th>Problemi həll etmə bacarığı</th>
                    <th>Korrespondensiya</th>
                    <th>Hesabatlıq</th>
                    <th>Aylıq Ümumi</th>
                </tr>
            </thead>
												<tbody>
												<?php if (empty($employee_reports)): ?>
                    <tr>
                        <td colspan="14">Нет данных для выбранного месяца.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($employee_reports as $employee): ?>
                        <tr>
                            <td><?= htmlspecialchars($employee['name']) ?></td>
                            <td><?= htmlspecialchars($employee['department_name']) ?></td>
                            <td><?= htmlspecialchars($employee['month']) ?></td>
                            <td><?= $employee['score_1'] ?></td>
                            <td><?= $employee['score_2'] ?></td>
                            <td><?= $employee['score_3'] ?></td>
                            <td><?= $employee['score_4'] ?></td>
                            <td><?= $employee['score_5'] ?></td>
                            <td><?= $employee['score_6'] ?></td>
                            <td><?= $employee['score_7'] ?></td>
                            <td><?= $employee['score_8'] ?></td>
                            <td><?= $employee['score_9'] ?></td>
                            <td><?= $employee['total_score'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
												
											</table>
										</div>
									</div>
									<div class="text-center mt-3">
										<a href="javascript:void(0)" class="btn btn-theme ctm-border-radius text-white button-1">Hesabatı Yüklə</a>
									</div>
								</div>
							</div>
								
										
										<!--/Tab 1-->
										
										
										
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
				
		<!-- Datetimepicker JS -->
		<script src="assets/plugins/select2/moment.min.js"></script>
		<script src="assets/js/bootstrap-datetimepicker.min.js"></script>
				
		<!-- Select2 JS -->
		<script src="assets/plugins/select2/select2.min.js"></script>
		
		<!-- Tagsinput JS -->
		<script src="assets/plugins/bootstrap-tagsinput/bootstrap-tagsinput.min.js"></script>
			
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
			$('#reports-3').DataTable({
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
