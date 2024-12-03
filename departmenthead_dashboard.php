

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

// Если нет текущего диапазона дат
if (!$current_date_range) {
    echo "Reytinqlər yalnız ayın təyin olunmuş günlərində verilə bilər.";
    exit;
}

// Проверка, делал ли пользователь оценку в текущий диапазон дат
$evaluation_check_stmt = $pdo->prepare("SELECT * FROM date_range_evaluations WHERE user_id = ? AND date_range_id = ?");
$evaluation_check_stmt->execute([$user_id, $current_date_range['id']]);
$evaluation_status = $evaluation_check_stmt->fetch();

// Если пользователь уже оценил департаменты и сотрудников в текущий диапазон дат
if ($evaluation_status && $evaluation_status['evaluated_departments'] && $evaluation_status['evaluated_employees']) {
    echo "Siz artıq cari tarix diapazonu üçün qiymətləndirmə etmisiniz.";
	echo "<a href='department_head_report.php'>Nəticələr</a>";
    exit;
}

if (isset($_POST['evaluate_departments'])) {
    $departments = $_POST['departments'];
    
    foreach ($departments as $department_id => $scores) {
        $stmt = $pdo->prepare("INSERT INTO department_evaluations (department_id, evaluator_id, score_1, score_2, score_3, score_4, score_5, score_6, score_7) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE score_1 = VALUES(score_1), score_2 = VALUES(score_2), score_3 = VALUES(score_3), score_4 = VALUES(score_4), score_5 = VALUES(score_5), score_6 = VALUES(score_6), score_7 = VALUES(score_7)");
        $stmt->execute([$department_id, $user_id, $scores['score_1'], $scores['score_2'], $scores['score_3'], $scores['score_4'], $scores['score_5'], $scores['score_6'], $scores['score_7']]);
    }

    // Обновление статуса оценки департаментов в текущий диапазон дат
    if ($evaluation_status) {
        $update_evaluation_stmt = $pdo->prepare("UPDATE date_range_evaluations SET evaluated_departments = TRUE WHERE user_id = ? AND date_range_id = ?");
        $update_evaluation_stmt->execute([$user_id, $current_date_range['id']]);
    } else {
        $insert_evaluation_stmt = $pdo->prepare("INSERT INTO date_range_evaluations (user_id, date_range_id, evaluated_departments) VALUES (?, ?, TRUE)");
        $insert_evaluation_stmt->execute([$user_id, $current_date_range['id']]);
    }

    echo "Şöbələrin qiymətləndirilməsi uğurla əlavə edildi!";
	header("Location: departmenthead_dashboard.php");
}

if (isset($_POST['evaluate_employees'])) {
    $employees = $_POST['employees'];
    
    foreach ($employees as $employee_id => $scores) {
        $stmt = $pdo->prepare("INSERT INTO evaluations (user_id, evaluator_id, score_1, score_2, score_3, score_4, score_5, score_6, score_7, score_8, score_9) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE score_1 = VALUES(score_1), score_2 = VALUES(score_2), score_3 = VALUES(score_3), score_4 = VALUES(score_4), score_5 = VALUES(score_5), score_6 = VALUES(score_6), score_7 = VALUES(score_7), score_8 = VALUES(score_8), score_9 = VALUES(score_9)");
        $stmt->execute([$employee_id, $user_id, $scores['score_1'], $scores['score_2'], $scores['score_3'], $scores['score_4'], $scores['score_5'], $scores['score_6'], $scores['score_7'], $scores['score_8'], $scores['score_9']]);
    }

    // Обновление статуса оценки сотрудников в текущий диапазон дат
    if ($evaluation_status) {
        $update_evaluation_stmt = $pdo->prepare("UPDATE date_range_evaluations SET evaluated_employees = TRUE WHERE user_id = ? AND date_range_id = ?");
        $update_evaluation_stmt->execute([$user_id, $current_date_range['id']]);
    } else {
        $insert_evaluation_stmt = $pdo->prepare("INSERT INTO date_range_evaluations (user_id, date_range_id, evaluated_employees) VALUES (?, ?, TRUE)");
        $insert_evaluation_stmt->execute([$user_id, $current_date_range['id']]);
    }

    echo "Оценки сотрудников добавлены!";
}

// Получение сотрудников и департаментов для выбора
$employees = $pdo->query("SELECT id, name, hire_date FROM users WHERE department_id = $department_id AND id != $user_id")->fetchAll();
$departments = $pdo->query("SELECT * FROM departments WHERE id != $department_id")->fetchAll();

$current_date = new DateTime(); // текущая дата

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
													<a href="departmenthead_dashboard.php" class="text-white active p-4 second-slider-btn ctm-border-right ctm-border-top"><span class="lnr lnr-sync pr-0 pb-lg-2 font-23"></span><span class="">Qiymətləndirmə</span></a>												
												</div>
												
												<div class="col-6 align-items-center shadow-none text-center">												
													<a href="department_head_report.php" class="text-dark p-4 ctm-border-right ctm-border-left"><span class="lnr lnr-rocket pr-0 pb-lg-2 font-23"></span><span class="">Nəticələr</span></a>												
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
								<h4 class="card-title mb-0 d-inline-block">Şöbə müdiri Paneli</h4>
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

		<?php if ($evaluation_status && $evaluation_status['evaluated_employees']): ?>
                <a href="department_head_report.php">Hesabatlar</a>
            <?php else: ?>
                <div class="form-section">
                    <!-- <h4>İşçiləri qiymətləndirin!</h4> -->
					<br>
					<h4>Forma - 2 İşçiləri qiymətləndirin! </h4><br>
                    <?php if ($evaluation_status && $evaluation_status['evaluated_departments']): ?>
                        <form method="post">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>İşçi</th>
                                        <th>Davamiyyət</th>
                                        <th>Etika və</th>
                                        <th>Geyim qayd</th>
                                        <th>Təşəbbüs-<br>karlıq</th>
                                        <th>Komanda</th>
                                        <th>Çeviklik</th>
                                        <th>Problem Həll<br>Etmə Bacarığı</th>
                                        <th>Korrespondensiya</th>
                                        <th>Hesabat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($employees as $employee): ?>
								<?php
                                // Проверка, прошли ли 90 дней с даты вступления на работу
                                $hire_date = new DateTime($employee['hire_date']);
                                $interval = $hire_date->diff($current_date);
                                $is_evaluable = $interval->days >= 90; // Если прошло 90 или более дней
                                ?>
                                        <tr>
                                            <td><?= htmlspecialchars($employee['name']) ?></td>
                                            <input type="hidden" class="form-control" name="employees[<?= $employee['id'] ?>][employee_id]" value="<?= $employee['id'] ?>">
                                            <td><input <?= $is_evaluable ? '' : 'disabled' ?> type="number" class="form-control" name="employees[<?= $employee['id'] ?>][score_1]" placeholder=" " min="1" max="5" required ></td>
                                            <td><input <?= $is_evaluable ? '' : 'disabled' ?> type="number" class="form-control" name="employees[<?= $employee['id'] ?>][score_2]" placeholder=" " min="1" max="5" required></td>
                                            <td><input <?= $is_evaluable ? '' : 'disabled' ?> type="number" class="form-control" name="employees[<?= $employee['id'] ?>][score_3]" placeholder=" " min="1" max="5" required></td>
                                            <td><input <?= $is_evaluable ? '' : 'disabled' ?> type="number" class="form-control" name="employees[<?= $employee['id'] ?>][score_4]" placeholder=" " min="1" max="5" required></td>
                                            <td><input <?= $is_evaluable ? '' : 'disabled' ?> type="number" class="form-control" name="employees[<?= $employee['id'] ?>][score_5]" placeholder=" " min="1" max="5" required></td>
                                            <td><input <?= $is_evaluable ? '' : 'disabled' ?> type="number" class="form-control" name="employees[<?= $employee['id'] ?>][score_6]" placeholder=" " min="1" max="5" required></td>
                                            <td><input <?= $is_evaluable ? '' : 'disabled' ?> type="number" class="form-control" name="employees[<?= $employee['id'] ?>][score_7]" placeholder=" " min="1" max="5" required></td>
                                            <td><input <?= $is_evaluable ? '' : 'disabled' ?> type="number" class="form-control" name="employees[<?= $employee['id'] ?>][score_8]" placeholder=" " min="1" max="5" required></td>
                                            <td><input <?= $is_evaluable ? '' : 'disabled' ?> type="number" class="form-control" name="employees[<?= $employee['id'] ?>][score_9]" placeholder=" " min="1" max="5" required></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <div class="row">
                                <div class="col-12">
                                    <div class="submit-section text-center btn-add">
                                        <button class="btn btn-theme text-white ctm-border-radius button-1" type="submit" name="evaluate_employees">İşçilərə qiymət verin</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    <?php else: ?>
                        <p>İşçiləri qiymətləndirmədən əvvəl bütün şöbələri qiymətləndirin.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($evaluation_status && $evaluation_status['evaluated_departments']): ?>
                <a href="department_head_report.php">Hesabat</a>
            <?php else: ?>
                <div class="form-section">
				<!-- <h4>Şöbələri qiymətləndirin!</h4> -->
									<br>
                    <h4>Forma - 1 Şöbələri qiymətləndirin!</h4><br>
                    <form method="post">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Şöbə</th>
                                    <th>Davamiyyət</th>
                                    <th>Etika və geyim<br>qaydalarına riayət</th>
                                    <th>Komanda</th>
                                    <th>Təşəbbüs-<br>karlıq</th>
                                    <th>Korrespon-<br>densiya</th>
                                    <th>Proses və prosedur</th>
                                    <th>Hesabatlılıq</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($departments as $department): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($department['name'] . ' Şöbəsi') ?></td>
                                        <input type="hidden" class="form-control" name="departments[<?= $department['id'] ?>][department_id]" value="<?= $department['id'] ?>">
                                        <td><input type="number" class="form-control" name="departments[<?= $department['id'] ?>][score_1]" placeholder="" min="1" max="5" required></td>
                                        <td><input type="number" class="form-control" name="departments[<?= $department['id'] ?>][score_2]" placeholder=" " min="1" max="5" required></td>
                                        <td><input type="number" class="form-control" name="departments[<?= $department['id'] ?>][score_3]" placeholder=" " min="1" max="5" required></td>
                                        <td><input type="number" class="form-control" name="departments[<?= $department['id'] ?>][score_4]" placeholder=" " min="1" max="5" required></td>
                                        <td><input type="number" class="form-control" name="departments[<?= $department['id'] ?>][score_5]" placeholder=" " min="1" max="5" required></td>
                                        <td><input type="number" class="form-control" name="departments[<?= $department['id'] ?>][score_6]" placeholder=" " min="1" max="5" required></td>
                                        <td><input type="number" class="form-control" name="departments[<?= $department['id'] ?>][score_7]" placeholder=" " min="1" max="5" required></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-12">
                                <div class="submit-section text-center btn-add">
                                    <button class="btn btn-theme text-white ctm-border-radius button-1" type="submit" name="evaluate_departments">Şöbələri Qiymətləndir</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- /Tab1-->
									
									
									
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



<!-- Tab1
<div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
    <div class="table-responsive">
        <div class="container">

		<?php if ($evaluation_status && $evaluation_status['evaluated_employees']): ?>
                <a href="department_head_report.php">Hesabatlar</a>
            <?php else: ?>
                <div class="form-section">
                    <h4>İşçilərə qiymət verin</h4>
                    <?php if ($evaluation_status && $evaluation_status['evaluated_departments']): ?>
                        <form method="post">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>İşçi</th>
                                        <th>Davamiyyət</th>
                                        <th>Etika</th>
                                        <th>Geyim</th>
                                        <th>Təşəbbüs</th>
                                        <th>Komanda</th>
                                        <th>Çeviklik</th>
                                        <th>Problem Həlli</th>
                                        <th>Korrespondensiya</th>
                                        <th>Hesabat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($employees as $employee): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($employee['name']) ?></td>
                                            <input type="hidden" class="form-control" name="employees[<?= $employee['id'] ?>][employee_id]" value="<?= $employee['id'] ?>">
                                            <td><input type="number" class="form-control" name="employees[<?= $employee['id'] ?>][score_1]" placeholder=" " min="1" max="5" required></td>
                                            <td><input type="number" class="form-control" name="employees[<?= $employee['id'] ?>][score_2]" placeholder=" " min="1" max="5" required></td>
                                            <td><input type="number" class="form-control" name="employees[<?= $employee['id'] ?>][score_3]" placeholder=" " min="1" max="5" required></td>
                                            <td><input type="number" class="form-control" name="employees[<?= $employee['id'] ?>][score_4]" placeholder=" " min="1" max="5" required></td>
                                            <td><input type="number" class="form-control" name="employees[<?= $employee['id'] ?>][score_5]" placeholder=" " min="1" max="5" required></td>
                                            <td><input type="number" class="form-control" name="employees[<?= $employee['id'] ?>][score_6]" placeholder=" " min="1" max="5" required></td>
                                            <td><input type="number" class="form-control" name="employees[<?= $employee['id'] ?>][score_7]" placeholder=" " min="1" max="5" required></td>
                                            <td><input type="number" class="form-control" name="employees[<?= $employee['id'] ?>][score_8]" placeholder=" " min="1" max="5" required></td>
                                            <td><input type="number" class="form-control" name="employees[<?= $employee['id'] ?>][score_9]" placeholder=" " min="1" max="5" required></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <div class="row">
                                <div class="col-12">
                                    <div class="submit-section text-center btn-add">
                                        <button class="btn btn-theme text-white ctm-border-radius button-1" type="submit" name="evaluate_employees">İşçilərə qiymət verin</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    <?php else: ?>
                        <p>İşçiləri qiymətləndirmədən əvvəl bütün şöbələri qiymətləndirin.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($evaluation_status && $evaluation_status['evaluated_departments']): ?>
                <a href="department_head_report.php">Hesabat</a>
            <?php else: ?>
                <div class="form-section">
                    <h4>Şöbələri Qiymətləndirin</h4>
                    <form method="post">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Şöbə</th>
                                    <th>Davamiyyət</th>
                                    <th>Etika</th>
                                    <th>Komanda</th>
                                    <th>Təşəbbüs</th>
                                    <th>Korrespondensiya</th>
                                    <th>Proses</th>
                                    <th>Hesabat</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($departments as $department): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($department['name'] . ' Şöbəsi') ?></td>
                                        <input type="hidden" class="form-control" name="departments[<?= $department['id'] ?>][department_id]" value="<?= $department['id'] ?>">
                                        <td><input type="number" class="form-control" name="departments[<?= $department['id'] ?>][score_1]" placeholder=" " min="1" max="5" required></td>
                                        <td><input type="number" class="form-control" name="departments[<?= $department['id'] ?>][score_2]" placeholder=" " min="1" max="5" required></td>
                                        <td><input type="number" class="form-control" name="departments[<?= $department['id'] ?>][score_3]" placeholder=" " min="1" max="5" required></td>
                                        <td><input type="number" class="form-control" name="departments[<?= $department['id'] ?>][score_4]" placeholder=" " min="1" max="5" required></td>
                                        <td><input type="number" class="form-control" name="departments[<?= $department['id'] ?>][score_5]" placeholder=" " min="1" max="5" required></td>
                                        <td><input type="number" class="form-control" name="departments[<?= $department['id'] ?>][score_6]" placeholder=" " min="1" max="5" required></td>
                                        <td><input type="number" class="form-control" name="departments[<?= $department['id'] ?>][score_7]" placeholder=" " min="1" max="5" required></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-12">
                                <div class="submit-section text-center btn-add">
                                    <button class="btn btn-theme text-white ctm-border-radius button-1" type="submit" name="evaluate_departments">Şöbələri Qiymətləndir</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
Tab1-->

<!--
<div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
    <div class="table-responsive">
        <div class="container">

		<?php if ($evaluation_status && $evaluation_status['evaluated_employees']): ?>
                <a href="department_head_report.php">Hesabatlar</a>
            <?php else: ?>
                <div class="form-section">
                    <h4>Forma 2 - İşçilərə qiymət verin</h4>
                    <?php if ($evaluation_status && $evaluation_status['evaluated_departments']): ?>
                        <form method="post">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>İşçi</th>
                                        <th>Davamiyyət</th>
                                        <th>Korporativ etika</th>
                                        <th>Geyim qayda-<br>larına riayət</th>
                                        <th>Təşəbbüskarlıq</th>
                                        <th>Komanda işi</th>
                                        <th>Çeviklik</th>
                                        <th>Problemi həll <br>etmə bacarığı</th>
                                        <th>Korrespondensiya</th>
                                        <th>Hesabatlıq</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($employees as $employee): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($employee['name']) ?></td>
                                            <input type="hidden" class="form-control" name="employees[<?= $employee['id'] ?>][employee_id]" value="<?= $employee['id'] ?>">
                                            <td><input type="number" class="form-control" name="employees[<?= $employee['id'] ?>][score_1]" placeholder="Davamiyyət" min="1" max="5" required></td>
                                            <td><input type="number" class="form-control" name="employees[<?= $employee['id'] ?>][score_2]" placeholder="Korporativ etika" min="1" max="5" required></td>
                                            <td><input type="number" class="form-control" name="employees[<?= $employee['id'] ?>][score_3]" placeholder="Geyim qaydalarına riayət" min="1" max="5" required></td>
                                            <td><input type="number" class="form-control" name="employees[<?= $employee['id'] ?>][score_4]" placeholder="Təşəbbüskarlıq" min="1" max="5" required></td>
                                            <td><input type="number" class="form-control" name="employees[<?= $employee['id'] ?>][score_5]" placeholder="Komanda işi" min="1" max="5" required></td>
                                            <td><input type="number" class="form-control" name="employees[<?= $employee['id'] ?>][score_6]" placeholder="Çeviklik" min="1" max="5" required></td>
                                            <td><input type="number" class="form-control" name="employees[<?= $employee['id'] ?>][score_7]" placeholder="Problemi həll etmə bacarığı" min="1" max="5" required></td>
                                            <td><input type="number" class="form-control" name="employees[<?= $employee['id'] ?>][score_8]" placeholder="Korrespondensiya" min="1" max="5" required></td>
                                            <td><input type="number" class="form-control" name="employees[<?= $employee['id'] ?>][score_9]" placeholder="Hesabatlıq" min="1" max="5" required></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <div class="row">
                                <div class="col-12">
                                    <div class="submit-section text-center btn-add">
                                        <button class="btn btn-theme text-white ctm-border-radius button-1" type="submit" name="evaluate_employees">İşçilərə qiymət verin</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    <?php else: ?>
                        <p>İşçiləri qiymətləndirmədən əvvəl bütün şöbələri qiymətləndirin.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($evaluation_status && $evaluation_status['evaluated_departments']): ?>
                <a href="department_head_report.php">Hesabat</a>
            <?php else: ?>
                <div class="form-section">
                    <h4>Forma 1 - Şöbələri Qiymətləndirin</h4>
                    <form method="post">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Şöbə</th>
                                    <th>Davamiyyət</th>
                                    <th>Korporativ etika</th>
                                    <th>Komanda işi</th>
                                    <th>Təşəbbüskarlıq</th>
                                    <th>Korrespondensiya</th>
                                    <th>Proses və prosedurlara riayət</th>
                                    <th>Hesabatlıq</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($departments as $department): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($department['name'] . ' Şöbəsi') ?></td>
                                        <input type="hidden" class="form-control" name="departments[<?= $department['id'] ?>][department_id]" value="<?= $department['id'] ?>">
                                        <td><input type="number" class="form-control" name="departments[<?= $department['id'] ?>][score_1]" placeholder="Davamiyyət" min="1" max="5" required></td>
                                        <td><input type="number" class="form-control" name="departments[<?= $department['id'] ?>][score_2]" placeholder="Korporativ etika" min="1" max="5" required></td>
                                        <td><input type="number" class="form-control" name="departments[<?= $department['id'] ?>][score_3]" placeholder="Komanda işi" min="1" max="5" required></td>
                                        <td><input type="number" class="form-control" name="departments[<?= $department['id'] ?>][score_4]" placeholder="Təşəbbüskarlıq" min="1" max="5" required></td>
                                        <td><input type="number" class="form-control" name="departments[<?= $department['id'] ?>][score_5]" placeholder="Korrespondensiya" min="1" max="5" required></td>
                                        <td><input type="number" class="form-control" name="departments[<?= $department['id'] ?>][score_6]" placeholder="Proses və prosedurlara riayət" min="1" max="5" required></td>
                                        <td><input type="number" class="form-control" name="departments[<?= $department['id'] ?>][score_7]" placeholder="Hesabatlıq" min="1" max="5" required></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-12">
                                <div class="submit-section text-center btn-add">
                                    <button class="btn btn-theme text-white ctm-border-radius button-1" type="submit" name="evaluate_departments">Şöbələri Qiymətləndir</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>


								-->