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
    echo "У вас нет доступа к этой странице.";
    exit;
}

if (isset($_POST['link_department'])) {
    $department_id = $_POST['department_id'];
    $top_department_id = $_POST['top_department_id'];

    try {
        $stmt = $pdo->prepare("UPDATE departments SET top_department_id = ? WHERE id = ?");
        $stmt->execute([$top_department_id, $department_id]);
        echo "Şöbə Departamentlə Uğurla Əlaqələndirildi.";
    } catch (PDOException $e) {
        echo "Ошибка: " . $e->getMessage();
    }
}

// Обработка удаления ассоциации
if (isset($_POST['delete_association'])) {
    $department_id = $_POST['department_id'];

    try {
        // Удаление связи с топ-департаментом (обновление на NULL)
        $stmt = $pdo->prepare("UPDATE departments SET top_department_id = NULL WHERE id = ?");
        $stmt->execute([$department_id]);

        echo "Ассоциация департамента успешно удалена!";
    } catch (PDOException $e) {
        echo "Ошибка: " . $e->getMessage();
    }
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
        <title>Reviews Page</title>
    </head>
    <body>
        <div class="inner-wrapper">
            <header class="header">
                <div class="top-header-section">
                    <div class="container-fluid">
                        <div class="row align-items-center">
                            <div class="col-lg-3 col-md-3 col-sm-3 col-6">
                                <div class="logo my-3 my-sm-0">
                                    <a href="#"><img src="assets/img/logo.png" alt="logo image" class="img-fluid" width="100"></a>
                                </div>
                            </div>
                            <div class="col-lg-9 col-md-9 col-sm-9 col-6 text-right">
                                <div class="user-block d-none d-lg-block">
                                    <div class="row align-items-center">
                                        <div class="col-lg-12 col-md-12 col-sm-12">
                                            <div class="user-info align-right dropdown d-inline-block">
                                                <a href="javascript:void(0)" data-toggle="dropdown" class="menu-style dropdown-toggle">
                                                    <div class="user-avatar d-inline-block"><?php echo $user_name; ?></div>
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
                                                    <a href="dashboard.php" class="text-dark p-4 first-slider-btn ctm-border-right ctm-border-left ctm-border-top">
                                                        <span class="lnr lnr-home pr-0 pb-lg-2 font-23"></span><span>Ümumi</span>
                                                    </a>
                                                </div>
                                                <div class="col-6 align-items-center shadow-none text-center">
                                                    <a href="dlinks.php" class="text-white active p-4 ctm-border-right">
                                                        <span class="lnr lnr-sync pr-0 pb-lg-2 font-23"></span><span>Dep-Şöbə</span>
                                                    </a>
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
                                    <h4 class="card-title mb-0 d-inline-block">Departamentlər və Şöbələrin əlaqələndirilməsi</h4>
                                    <form method="post">
                                        <label for="department_id">Şöbə:</label>
                                        <select id="department_id" class="form-control" name="department_id" required>
                                            <?php
                                            // Подключение к базе данных
                                            $stmt = $pdo->query("SELECT id, name FROM departments");
                                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                echo "<option value='{$row['id']}'>{$row['name']}</option>";
                                            }
                                            ?>
                                        </select>

                                        <label for="top_department_id">Departament:</label>
                                        <select id="top_department_id" class="form-control" name="top_department_id" required>
                                            <?php
                                            $stmt = $pdo->query("SELECT id, name FROM top_departments");
                                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                echo "<option value='{$row['id']}'>{$row['name']}</option>";
                                            }
                                            ?>
                                        </select>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="submit-section text-center btn-add">
                                                    <button class="btn btn-theme text-white ctm-border-radius button-1" type="submit" name="link_department">Əlaqələndir</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>

                                    <h4>Əlaqələndirilmiş Departament və Şöbələrin siyahısı</h4>
                                    <table class="table table-bordered">
                                        <tr>
                                            <th>Departament</th>
                                            <th>Şöbə</th>
                                            <th>Действия</th>
                                        </tr>
                                        <?php
                                        // Получение связанных департаментов с топ-департаментами
                                        $sql = "SELECT d.id AS dept_id, td.id AS top_dept_id, td.name AS top_name, d.name AS dept_name
                                                FROM departments d
                                                JOIN top_departments td ON d.top_department_id = td.id";
                                        $stmt = $pdo->query($sql);
                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            echo "<tr>";
                                            echo "<td>{$row['top_name']}</td>";
                                            echo "<td>{$row['dept_name']}</td>";
                                            echo "<td>
                                                    <form method='post'>
                                                        <input type='hidden' name='department_id' value='{$row['dept_id']}'>
                                                        <button class='btn btn-danger' type='submit' name='delete_association'>Удалить</button>
                                                    </form>
                                                  </td>";
                                            echo "</tr>";
                                        }
                                        ?>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="sidebar-overlay" id="sidebar_overlay"></div>
        <script src="assets/js/jquery-3.2.1.min.js"></script>
        <script src="assets/js/popper.min.js"></script>
        <script src="assets/js/bootstrap.min.js"></script>
    </body>
</html>
