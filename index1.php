<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

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
		
		<title>Dashboard Page</title>
				
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
											<div class="user-info align-right dropdown d-inline-block header-dropdown">
												<a href="javascript:void(0)" data-toggle="dropdown" class=" menu-style dropdown-toggle">
													<div class="user-avatar d-inline-block">
														<img src="assets/img/profiles/img-6.jpg" alt="user avatar" class="rounded-circle img-fluid" width="55">
													</div>
												</a>
												
												<!-- Notifications -->
												<div class="dropdown-menu notification-dropdown-menu shadow-lg border-0 p-3 m-0 dropdown-menu-right">
													<a class="dropdown-item p-2" href="employment.html">
														<span class="media align-items-center">
															<span class="lnr lnr-user mr-3"></span>
															<span class="media-body text-truncate">
																<span class="text-truncate">Profile</span>
															</span>
														</span>
													</a>
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
														<span class="text-truncate text-left">Əməkdaşlar</span>
													</span>
												</span>
											</a>
											<a class="p-2" href="company.php">
												<span class="media align-items-center">
													<span class="lnr lnr-apartment mr-3"></span>
													<span class="media-body text-truncate text-left">
														<span class="text-truncate text-left">Departamentlər</span>
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
								<div class="row">
								<div class="col-12">
									<div class="card ctm-border-radius shadow-sm grow">
										<div class="card-body py-4">
											<div class="row">
												<div class="col-md-12 mr-auto text-left">
													<div class="custom-search input-group">
														<div class="custom-breadcrumb">
															<ol class="breadcrumb no-bg-color d-inline-block p-0 m-0 mb-2">
																<li class="breadcrumb-item d-inline-block"><a href="#" class="text-dark">Home</a></li>
																<li class="breadcrumb-item d-inline-block active">Dashboard</li>
															</ol>
															<h4 class="text-dark">Admin Dashboard</h4>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
								<!-- <div class="user-card card shadow-sm bg-white text-center ctm-border-radius grow">
									<div class="user-info card-body">
										<div class="user-avatar mb-4">
											<img src="assets/img/profiles/img-13.jpg" alt="User Avatar" class="img-fluid rounded-circle" width="100">
										</div>
										<div class="user-details">
											<h4><b>Welcome Admin</b></h4>
										</div>
									</div>
								</div> -->
								<!-- Sidebar -->
								<div class="sidebar-wrapper d-lg-block d-md-none d-none">
									<div class="card ctm-border-radius shadow-sm border-none grow">
										<div class="card-body">
											<div class="row no-gutters">
												<div class="col-6 align-items-center text-center">
													<a href="#" class="text-white active p-4 first-slider-btn ctm-border-right ctm-border-left ctm-border-top"><span class="lnr lnr-home pr-0 pb-lg-2 font-23"></span><span class="">Ümumi</span></a>												
												</div>
												<div class="col-6 align-items-center shadow-none text-center">											
													<a href="employees.php" class="text-dark p-4 second-slider-btn ctm-border-right ctm-border-top"><span class="lnr lnr-users pr-0 pb-lg-2 font-23"></span><span class="">Əməklaşlar</span></a>												
												</div>
												<div class="col-6 align-items-center shadow-none text-center">												
													<a href="company.php" class="text-dark p-4 ctm-border-right ctm-border-left"><span class="lnr lnr-apartment pr-0 pb-lg-2 font-23"></span><span class="">Şöbələr</span></a>												
												</div>
												<div class="col-6 align-items-center shadow-none text-center">												
													<a href="calendar.php" class="text-dark p-4 ctm-border-right"><span class="lnr lnr-calendar-full pr-0 pb-lg-2 font-23"></span><span class="">Təqvim</span></a>												
												</div>
												<div class="col-6 align-items-center shadow-none text-center">											
													<a href="leave.php" class="text-dark p-4 ctm-border-right ctm-border-left"><span class="lnr lnr-file-add pr-0 pb-lg-2 font-23"></span><span class="">Forma-1</span></a>											
												</div>
												<div class="col-6 align-items-center shadow-none text-center">											
													<a href="reviews.php" class="text-dark p-4 last-slider-btn ctm-border-right"><span class="lnr lnr-book pr-0 pb-lg-2 font-23"></span><span class="">Forma-2</span></a>												
												</div>
												<div class="col-6 align-items-center shadow-none text-center">												
													<a href="reports.php" class="text-dark p-4 ctm-border-right ctm-border-left"><span class="lnr lnr-rocket pr-0 pb-lg-2 font-23"></span><span class="">Hesabatlar</span></a>												
												</div>
												<div class="col-6 align-items-center shadow-none text-center">												
													<a href="manage.php" class="text-dark p-4 ctm-border-right"><span class="lnr lnr-sync pr-0 pb-lg-2 font-23"></span><span class="">Rollar</span></a>												
												</div>
												
											</div>
										</div>
									</div>
								</div>
								
								<!-- /Sidebar -->
								
							</aside>
						</div>
						
						<div class="col-xl-9 col-lg-8  col-md-12">
							<div class="quicklink-sidebar-menu ctm-border-radius shadow-sm bg-white card grow">
									<div class="card-body">
										<ul class="list-group list-group-horizontal-lg">
											<li class="list-group-item text-center active button-5"><a href="#" class="text-white">Admin Dashboard</a></li>
											<li class="list-group-item text-center button-6"><a class="text-dark" href="employees-dashboard.php">Employees Dashboard</a></li>
										</ul>
									</div>
								</div>
							<!-- Widget -->
							<div class="row">
								<div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12">
									<div class="card dash-widget ctm-border-radius shadow-sm grow">
										<div class="card-body">
											<div class="card-icon bg-primary">
												<i class="fa fa-users" aria-hidden="true"></i>
											</div>
											<div class="card-right">
												<h4 class="card-title">Əməkdaşlar</h4>
												<p class="card-text">700</p>
											</div>
										</div>
									</div>
								</div>
								<div class="col-xl-3 col-lg-6 col-sm-6 col-12">
									<div class="card dash-widget ctm-border-radius shadow-sm grow">
										<div class="card-body">
											<div class="card-icon bg-warning">
												<i class="fa fa-building-o"></i>
											</div>
											<div class="card-right">
												<h4 class="card-title">Şöbələr</h4>
												<p class="card-text">30</p>
											</div>
										</div>
									</div>
								</div>
								<div class="col-xl-3 col-lg-6 col-sm-6 col-12">
									<div class="card dash-widget ctm-border-radius shadow-sm grow">
										<div class="card-body">
											<div class="card-icon bg-danger">
												<i class="fa fa-suitcase" aria-hidden="true"></i>
											</div>
											<div class="card-right">
												<h4 class="card-title">Müşahidə Şurası</h4>
												<p class="card-text">3</p>
											</div>
										</div>
									</div>
								</div>
								<div class="col-xl-3 col-lg-6 col-sm-6 col-12">
									<div class="card dash-widget ctm-border-radius shadow-sm grow">
										<div class="card-body">
											<div class="card-icon bg-success">
												<i class="fa fa-file-archive-o" aria-hidden="true"></i>
											</div>
											<div class="card-right">
												<h4 class="card-title">Hesabatlar</h4>
												<p class="card-text">58</p>
											</div>
										</div>
									</div>
								</div>
							</div>
							<!-- / Widget -->
							
							<div class="row">
								<div class="col-lg-6 col-md-12 d-flex">
								
									<!-- Team Leads List -->
									<div class="card flex-fill team-lead shadow-sm grow">
										<div class="card-header">
											<h4 class="card-title mb-0 d-inline-block">Ümumi Əməkdaşlar </h4>
											<a href="employees.php" class="dash-card float-right mb-0 text-primary">İdarə Et </a>
										</div>
										<div class="card-body">
											<div class="media mb-3">
												<div class="e-avatar avatar-online mr-3"><img src="assets/img/profiles/img-6.jpg" alt="Maria Cotton" class="img-fluid"></div>
												<div class="media-body">
													<h6 class="m-0">Maria Cotton</h6>
													<p class="mb-0 ctm-text-sm">PHP</p>
												</div>
											</div>
											<hr>
											<div class="media mb-3">
												<div class="e-avatar avatar-online mr-3"><img class="img-fluid" src="assets/img/profiles/img-5.jpg" alt="Linda Craver"></div>
												<div class="media-body">
													<h6 class="m-0">Danny Ward</h6>
													<p class="mb-0 ctm-text-sm">Design</p>
												</div>
											</div>
											<hr>
											<div class="media mb-3">
												<div class="e-avatar avatar-online mr-3"><img src="assets/img/profiles/img-4.jpg" alt="Linda Craver" class="img-fluid"></div>
												<div class="media-body">
													<h6 class="m-0">Linda Craver</h6>
													<p class="mb-0 ctm-text-sm">IOS</p>
												</div>
											</div>
											<hr>
											<div class="media mb-3">
												<div class="e-avatar avatar-online mr-3"><img class="img-fluid" src="assets/img/profiles/img-3.jpg" alt="Linda Craver"></div>
												<div class="media-body">
													<h6 class="m-0">Jenni Sims</h6>
													<p class="mb-0 ctm-text-sm">Android</p>
												</div>
											</div>
											<hr>
											<div class="media">
												<div class="e-avatar avatar-offline mr-3"><img class="img-fluid" src="assets/img/profiles/img-2.jpg" alt="Linda Craver"></div>
												<div class="media-body">
													<h6 class="m-0">John Gibbs</h6>
													<p class="mb-0 ctm-text-sm">Business</p>
												</div>
											</div>
										</div>
									</div>
								</div>

								<div class="col-lg-6">
									<div class="card ctm-border-radius shadow-sm grow">
										<div class="card-header">
											<h4 class="card-title mb-0 d-inline-block">Sistem Loq</h4>
											<a href="javascript:void(0)" class="d-inline-block float-right text-primary"><i class="lnr lnr-sync"></i></a>
										</div>
										<div class="card-body recent-activ">
											<div class="recent-comment">
												<a href="javascript:void(0)" class="dash-card text-dark">
													<div class="dash-card-container">
														<div class="dash-card-icon text-primary">
															<i class="fa fa-birthday-cake" aria-hidden="true"></i>
														</div>
														<div class="dash-card-content">
															<h6 class="mb-0">No Birthdays Today</h6>
														</div>
													</div>
												</a>
												<hr>
												<a href="javascript:void(0)" class="dash-card text-dark">
													<div class="dash-card-container">
														<div class="dash-card-icon text-warning">
															<i class="fa fa-bed" aria-hidden="true"></i>
														</div>
														<div class="dash-card-content">
															<h6 class="mb-0">Ralph Baker is off sick today</h6>
														</div>
														<div class="dash-card-avatars">
															<div class="e-avatar"><img class="img-fluid" src="assets/img/profiles/img-9.jpg" alt="Avatar"></div>
														</div>
													</div>
												</a>
												<hr>
												<a href="javascript:void(0)" class="dash-card text-dark">
													<div class="dash-card-container">
														<div class="dash-card-icon text-success">
															<i class="fa fa-child" aria-hidden="true"></i>
														</div>
														<div class="dash-card-content">
															<h6 class="mb-0">Ralph Baker is parenting leave today</h6>
														</div>
														<div class="dash-card-avatars">
															<div class="e-avatar"><img class="img-fluid" src="assets/img/profiles/img-9.jpg" alt="Avatar"></div>
														</div>
													</div>
												</a>
												<hr>
												<a href="javascript:void(0)" class="dash-card text-dark">
													<div class="dash-card-container">
														<div class="dash-card-icon text-danger">
															<i class="fa fa-suitcase"></i>
														</div>
														<div class="dash-card-content">
															<h6 class="mb-0">Danny ward is away today</h6>
														</div>
														<div class="dash-card-avatars">
															<div class="e-avatar"><img class="img-fluid" src="assets/img/profiles/img-5.jpg" alt="Avatar"></div>
														</div>
													</div>
												</a>
												<hr>
												<a href="javascript:void(0)" class="dash-card text-dark">
													<div class="dash-card-container">
														<div class="dash-card-icon text-pink">
															<i class="fa fa-home" aria-hidden="true"></i>
														</div>
														<div class="dash-card-content">
															<h6 class="mb-0">You are working from home today</h6>
														</div>
														<div class="dash-card-avatars">
															<div class="e-avatar"><img class="img-fluid" src="assets/img/profiles/img-6.jpg" alt="Maria Cotton"></div>
														</div>
													</div>
												</a>
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
				
		<!-- Chart JS -->
		<script src="assets/js/Chart.min.js"></script>
		<script src="assets/js/chart.js"></script>
		
		<!-- Sticky sidebar JS -->
		<script src="assets/plugins/theia-sticky-sidebar/ResizeSensor.js"></script>		
		<script src="assets/plugins/theia-sticky-sidebar/theia-sticky-sidebar.js"></script>		
			
		<!-- Custom Js -->
		<script src="assets/js/script.js"></script>
		
	</body>
</html>