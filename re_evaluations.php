<?php
require 'config.php';
session_start();

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

// Получение средних оценок сотрудников за выбранный месяц и департамент
$evaluation_query = "
    SELECT u.name as employee_name,
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

$evaluation_query .= " GROUP BY u.name";

$evaluation_stmt = $pdo->prepare($evaluation_query);

$params = ['filter_month' => $filter_month];
if ($filter_department) {
    $params['filter_department'] = $filter_department;
}

$evaluation_stmt->execute($params);
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
    <title>Yenidən Qiymətləndirmə</title>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.7.1/css/buttons.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js"></script>
		
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
										<div class="user-notification-block align-right d-inline-block">
											
										</div>
										
										<!-- User notification-->
										
										
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
							<!-- Sidebar -->
								<div class="sidebar-wrapper d-lg-block d-md-none d-none">
									<div class="card ctm-border-radius shadow-sm grow border-none">
										<div class="card-body">
                                        <div class="row no-gutters">
												<div class="col-6 align-items-center text-center">
													<a href="#" class="text-dark p-4 first-slider-btn ctm-border-right ctm-border-left ctm-border-top"><span class="lnr lnr-home pr-0 pb-lg-2 font-23"></span><span class="">Ümumi</span></a>												
												</div>
												<div class="col-6 align-items-center shadow-none text-center">											
													<a href="reports_director.php" class="text-dark p-4 second-slider-btn ctm-border-right ctm-border-top"><span class="lnr lnr-users pr-0 pb-lg-2 font-23"></span><span class="">İşçi Reytinqləri</span></a>												
												</div>
												
												<div class="col-6 align-items-center shadow-none text-center">												
													<a href="reports_director_departments.php" class="text-dark p-4 ctm-border-right ctm-border-left"><span class="lnr lnr-rocket pr-0 pb-lg-2 font-23"></span><span class="">Şöbə Reytinqləri</span></a>												
												</div>
												
												<div class="col-6 align-items-center shadow-none text-center">												
													<a href="top_department_evaluations.php" class="text-dark p-4 ctm-border-right ctm-border-left"><span class="lnr lnr-rocket pr-0 pb-lg-2 font-23"></span><span class="">Departament Reytinqləri</span></a>												
												</div>
												<div class="col-6 align-items-center shadow-none text-center">												
													<a href="re_evaluations.php" class="text-white active p-4 ctm-border-right ctm-border-left"><span class="lnr lnr-sync pr-0 pb-lg-2 font-23"></span><span class="">Yenidən Qiymətləndirmə</span></a>												
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
								<h4 class="card-title mb-0 d-inline-block">İcraçı direktorun müavin və müşavirlərinin hesablanan balları...</h4>
							</div>

						</div>
						<div class="col-xl-12 col-lg-12 col-md-12 col-12 d-flex">
									<div class="card flex-fill ctm-border-radius shadow-sm grow" style="color:#FF0000; font-weight:bold;">
										<div class="card-header">
											<h4 class="card-title mb-0">Qiymətləndirmə formasının doldurulması ilə bağlı təlimat</h4>
										</div>
										<div class="card-body">
											<p class="card-text mb-3">Qiymətləndirmə 5 (beş) ballıq sistemə əsasən aparılır: 5 (mükəmməl), 4 (gözləntilərdən yüksək), 3 (yaxşı), 2 (inkişafa ehtiyac var), 1 (gözləntiləri qarşılamır).</p>
											<p class="card-text mb-3">
												5 - Bütün razılaşdırılmış davranış göstəricilərini əhəmiyyətli dərəcədə keyfiyyətli xidmət və bütün  vəzifə tələblərinin öhdəsindən məharətlə gəlməklə həqiqətən də mükəmməl davranış nümayiş etdirilir.  İşçi bu Səriştə üzrə nümunəvi şəxsdir.   
											</p>
											<p class="card-text mb-3">
												4 - Gözləniləndən daha yüksək davranış nümayiş etdirməklə gözləntiləri üstələyir. İşçi bu Səriştə çərçivəsində bəzi davranışlar üzrə nümunə olur. 
											</p>
											<p class="card-text mb-3">
												3 - Gözlənilən davranışı nümayiş etdirir.Hər hansı istiqamətləndirmə olmadan səriştə üzrə yaxşı davranış nümayiş etdirir.
											</p>
											<p class="card-text mb-3">
												2 - Baxmayaraq ki, bu səviyyədə gözlənilən bəzi davranışları nümayiş etdirir, inkişafa ehtiyacı var. Eyni zamanda davranış çox vaxt istiqamətləndirmə əsasında nümayiş etdirilir.
											</p>
											<p class="card-text mb-3">
												1 - İşçi gözlənilən davranışları nümayiş etdirmir. İşçi istiqamətləndirmə olmadan tələb olunan davranışı nümayiş etdirə bilmir.
											</p>
											<p class="card-text mb-3">Qiymətləndirilmə ədalətli aparılmalı və qərəzli olmamalıdır.</p>
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
								<div class="col-md-12 form-group">
									<form method="get" action="">
									<div class="row">
										<label for="month" class="form-control col-3">Tarix Seçin:</label>
										<input type="month" class="form-control col-3" id="month" name="month" value="<?= htmlspecialchars($filter_month) ?>">
										
										<label for="department_id" class="form-control col-3">Şöbəni Seçin:</label>
										<select id="department_id" name="department_id" class="form-control col-3">
											<option value="">bütün Şöbələr</option>
											<?php foreach ($departments as $department): ?>
												<option value="<?= htmlspecialchars($department['id']) ?>" <?= $filter_department == $department['id'] ? 'selected' : '' ?>>
													<?= htmlspecialchars($department['name']) ?>
												</option>
											<?php endforeach; ?>
										</select>

										<div class="col-12">
										<div class="submit-section text-center btn-add">
											<button class="btn btn-theme text-white ctm-border-radius button-1" type="submit" >Filteri Tətbiq et</button>
											</div>
											</div>
									</div>

										<!-- <button type="submit">Применить фильтр</button> -->
									</form>

									<h4>Heç bir nəticə tapılmadı</h4>
									<table id="evaluationTable" class="display">
									
									<!--  -->
								</div>
								</div>
										</div>
									</div>
									<!-- /Tab1-->
									
									
									
								</div>
							</div>
							</div>
			<!--/Content-->
			
		</div>
		<!-- Inner Wrapper -->
		
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