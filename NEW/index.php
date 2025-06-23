<?php
require_once 'config/database.php';
require_once 'classes/SMART.php';

$database = new Database();
$db = $database->getConnection();
$smart = new SMART($db);

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPK SMART - Mobile Crowd Computing Resource Selection</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #ecf0f1;
            --accent-color: #2c3e50;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }

        .sidebar {
            background: linear-gradient(180deg, #ffffff, var(--secondary-color));
            min-height: calc(100vh - 76px);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .nav-link {
            color: var(--accent-color);
            padding: 12px 20px;
            margin: 5px 0;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .nav-link:hover, .nav-link.active {
            background-color: var(--primary-color);
            color: white;
            transform: translateX(5px);
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-color), #5dade2);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #5dade2);
            border: none;
            border-radius: 25px;
            padding: 10px 25px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }

        .table {
            border-radius: 10px;
            overflow: hidden;
        }

        .table thead th {
            background-color: var(--primary-color);
            color: white;
            border: none;
            font-weight: 600;
        }

        .badge {
            border-radius: 20px;
            padding: 8px 15px;
        }

        .content-wrapper {
            padding: 30px;
        }

        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
        }

        .stats-card h3 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .method-card {
            border-left: 5px solid var(--primary-color);
            transition: all 0.3s ease;
        }

        .method-card:hover {
            border-left-color: var(--success-color);
        }

        .formula-box {
            background-color: #f8f9fa;
            border: 2px dashed var(--primary-color);
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
        }

        .step-indicator {
            background-color: var(--primary-color);
            color: white;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
        }

        .chart-container {
            position: relative;
            height: 400px;
            width: 100%;
        }

        .chart-small {
            height: 300px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-mobile-alt me-2"></i>
                SPK SMART - MCC Resource Selection
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-user me-1"></i> Admin</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?= $page == 'dashboard' ? 'active' : '' ?>" href="index.php?page=dashboard">
                                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $page == 'data' ? 'active' : '' ?>" href="index.php?page=data">
                                <i class="fas fa-database me-2"></i> Data Alternatif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $page == 'kriteria' ? 'active' : '' ?>" href="index.php?page=kriteria">
                                <i class="fas fa-list me-2"></i> Data Kriteria
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $page == 'bobot' ? 'active' : '' ?>" href="index.php?page=bobot">
                                <i class="fas fa-weight-hanging me-2"></i> Input Bobot
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $page == 'roc' ? 'active' : '' ?>" href="index.php?page=roc">
                                <i class="fas fa-calculator me-2"></i> Bobot ROC
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $page == 'perhitungan' ? 'active' : '' ?>" href="index.php?page=perhitungan">
                                <i class="fas fa-chart-line me-2"></i> Perhitungan SMART
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $page == 'hasil' ? 'active' : '' ?>" href="index.php?page=hasil">
                                <i class="fas fa-trophy me-2"></i> Hasil & Ranking
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $page == 'perbandingan' ? 'active' : '' ?>" href="index.php?page=perbandingan">
                                <i class="fas fa-balance-scale me-2"></i> Perbandingan
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 content-wrapper">
                <?php
                switch($page) {
                    case 'dashboard':
                        include 'pages/dashboard.php';
                        break;
                    case 'data':
                        include 'pages/data.php';
                        break;
                    case 'kriteria':
                        include 'pages/kriteria.php';
                        break;
                    case 'bobot':
                        include 'pages/bobot.php';
                        break;
                    case 'roc':
                        include 'pages/roc.php';
                        break;
                    case 'perhitungan':
                        include 'pages/perhitungan.php';
                        break;
                    case 'hasil':
                        include 'pages/hasil.php';
                        break;
                    case 'perbandingan':
                        include 'pages/perbandingan.php';
                        break;
                    default:
                        include 'pages/dashboard.php';
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
