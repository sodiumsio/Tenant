<?php
// dashboard.php
session_start();
require_once "config/database.php";
require_once "models/Property.php";
require_once "models/Tenant.php";
require_once "models/Payment.php";
require_once "models/Maintenance.php";

$database = new Database();
$db = $database->getConnection();

$property = new Property($db);
$tenant = new Tenant($db);
$payment = new Payment($db);
$maintenance = new Maintenance($db);

// Get counts for dashboard
$properties_count = $property->read()->rowCount();
$tenants_count = $tenant->read()->rowCount();
$active_maintenance = $maintenance->getActiveRequests()->rowCount();
$pending_payments = $payment->getPendingPayments()->rowCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #343a40;
        }
        .sidebar .nav-link {
            color: #fff;
        }
        .sidebar .nav-link:hover {
            background: #495057;
        }
        .kpi-card {
            transition: transform 0.2s;
        }
        .kpi-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <h5 class="text-white px-3">Tenant Management</h5>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="properties.php">
                                <i class="fas fa-building"></i> Properties
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="tenants.php">
                                <i class="fas fa-users"></i> Tenants
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="payments.php">
                                <i class="fas fa-money-bill-wave"></i> Payments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="maintenance.php">
                                <i class="fas fa-tools"></i> Maintenance
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                </div>

                <!-- KPI Cards -->

   <div class="row">
                  <div class="row">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Properties</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $properties_count; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-building fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Total Tenants</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $tenants_count; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Pending Payments</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pending_payments; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-danger shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                            Active Maintenance</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $active_maintenance; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-tools fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6>Recent Tenants</h6>
                            </div>
                            <div class="card-body">
                                <?php
                                $stmt = $tenant->read();
                                if($stmt->rowCount() > 0) {
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<div class='d-flex justify-content-between border-bottom py-2'>
                                                <div>
                                                    <strong>{$row['first_name']} {$row['last_name']}</strong>
                                                    <br><small class='text-muted'>{$row['email']}</small>
                                                </div>
                                                <span class='badge bg-" . ($row['status'] == 'active' ? 'success' : 'warning') . "'>
                                                    {$row['status']}
                                                </span>
                                              </div>";
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6>Recent Maintenance Requests</h6>
                            </div>
                            <div class="card-body">
                                <?php
                                $stmt = $maintenance->read();
                                if($stmt->rowCount() > 0) {
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        $priority_class = [
                                            'low' => 'success',
                                            'medium' => 'warning', 
                                            'high' => 'danger',
                                            'urgent' => 'dark'
                                        ];
                                        echo "<div class='d-flex justify-content-between border-bottom py-2'>
                                                <div>
                                                    <strong>{$row['title']}</strong>
                                                    <br><small class='text-muted'>{$row['description']}</small>
                                                </div>
                                                <span class='badge bg-{$priority_class[$row['priority']]}'>
                                                    {$row['priority']}
                                                </span>
                                              </div>";
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>