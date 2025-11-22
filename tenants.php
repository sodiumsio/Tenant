<?php
// tenants.php
session_start();
require_once "config/database.php";
require_once "models/Property.php";
require_once "models/Tenant.php";

$database = new Database();
$db = $database->getConnection();

$property = new Property($db);
$tenant = new Tenant($db);

$message = '';
$message_type = '';

// Handle form submissions
if($_POST){
    try {
        if(isset($_POST['create_tenant'])) {
            $tenant->property_id = $_POST['property_id'];
            $tenant->first_name = $_POST['first_name'];
            $tenant->last_name = $_POST['last_name'];
            $tenant->email = $_POST['email'];
            $tenant->phone = $_POST['phone'];
            $tenant->lease_start = $_POST['lease_start'];
            $tenant->lease_end = $_POST['lease_end'];
            $tenant->rent_amount = $_POST['rent_amount'];
            $tenant->status = $_POST['status'];
            
            if($tenant->create()){
                $message = "Tenant added successfully.";
                $message_type = "success";
            } else {
                $message = "Unable to add tenant.";
                $message_type = "danger";
            }
        }
        
        if(isset($_POST['update_tenant'])) {
            $tenant->id = $_POST['tenant_id'];
            $tenant->property_id = $_POST['property_id'];
            $tenant->first_name = $_POST['first_name'];
            $tenant->last_name = $_POST['last_name'];
            $tenant->email = $_POST['email'];
            $tenant->phone = $_POST['phone'];
            $tenant->lease_start = $_POST['lease_start'];
            $tenant->lease_end = $_POST['lease_end'];
            $tenant->rent_amount = $_POST['rent_amount'];
            $tenant->status = $_POST['status'];
            
            if($tenant->update()){
                $message = "Tenant updated successfully.";
                $message_type = "success";
            } else {
                $message = "Unable to update tenant.";
                $message_type = "danger";
            }
        }
        
        if(isset($_POST['delete_tenant'])) {
            $tenant->id = $_POST['tenant_id'];
            if($tenant->delete()){
                $message = "Tenant deleted successfully.";
                $message_type = "success";
            } else {
                $message = "Unable to delete tenant.";
                $message_type = "danger";
            }
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = "danger";
    }
}

// Get tenant statistics
$total_tenants = $tenant->read()->rowCount();
$active_tenants = $tenant->getActiveTenants()->rowCount();
$vacant_properties = $property->getVacantProperties()->rowCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Management - Tenant System</title>
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
        .tenant-status-active { border-left: 4px solid #28a745 !important; }
        .tenant-status-inactive { border-left: 4px solid #6c757d !important; }
        .tenant-status-pending { border-left: 4px solid #ffc107 !important; }
        .table-actions { white-space: nowrap; }
        .lease-expiring { background-color: #fff3cd !important; }
        .lease-expired { background-color: #f8d7da !important; }
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
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="properties.php">
                                <i class="fas fa-building"></i> Properties
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="tenants.php">
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
                    <h1 class="h2">Tenant Management</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTenantModal">
                        <i class="fas fa-plus"></i> Add Tenant
                    </button>
                </div>

                <!-- Alert Message -->
                <?php if($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <!-- KPI Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Tenants</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_tenants; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-users fa-2x text-gray-300"></i>
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
                                            Active Tenants</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $active_tenants; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-user-check fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Vacant Properties</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $vacant_properties; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-home fa-2x text-gray-300"></i>
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
                                            Lease Expiring Soon</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            <?php 
                                            $expiring_soon = $tenant->getTenantsWithExpiringLease(30)->rowCount();
                                            echo $expiring_soon;
                                            ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calendar-exclamation fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tenant Filters -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6>Filter Tenants</h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="active" <?php echo (isset($_GET['status']) && $_GET['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo (isset($_GET['status']) && $_GET['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                    <option value="pending" <?php echo (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Property</label>
                                <select name="property_id" class="form-select">
                                    <option value="">All Properties</option>
                                    <?php
                                    $properties = $property->read();
                                    while ($prop_row = $properties->fetch(PDO::FETCH_ASSOC)) {
                                        $selected = (isset($_GET['property_id']) && $_GET['property_id'] == $prop_row['id']) ? 'selected' : '';
                                        echo "<option value='{$prop_row['id']}' {$selected}>{$prop_row['name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Lease Status</label>
                                <select name="lease_status" class="form-select">
                                    <option value="">All Leases</option>
                                    <option value="active" <?php echo (isset($_GET['lease_status']) && $_GET['lease_status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="expiring" <?php echo (isset($_GET['lease_status']) && $_GET['lease_status'] == 'expiring') ? 'selected' : ''; ?>>Expiring Soon</option>
                                    <option value="expired" <?php echo (isset($_GET['lease_status']) && $_GET['lease_status'] == 'expired') ? 'selected' : ''; ?>>Expired</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Apply Filters</button>
                                <a href="tenants.php" class="btn btn-secondary">Clear</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tenants Table -->
                <div class="card">
                    <div class="card-header">
                        <h6>All Tenants</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Tenant Name</th>
                                        <th>Contact Info</th>
                                        <th>Property</th>
                                        <th>Lease Period</th>
                                        <th>Rent Amount</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Apply filters
                                    $status_filter = $_GET['status'] ?? '';
                                    $property_filter = $_GET['property_id'] ?? '';
                                    $lease_status_filter = $_GET['lease_status'] ?? '';
                                    
                                    $stmt = $tenant->readWithFilters($status_filter, $property_filter, $lease_status_filter);
                                    
                                    if($stmt->rowCount() > 0) {
                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            $status_class = [
                                                'active' => 'success',
                                                'inactive' => 'secondary',
                                                'pending' => 'warning'
                                            ];
                                            
                                            // Check lease status for row styling
                                            $lease_class = '';
                                            $days_remaining = floor((strtotime($row['lease_end']) - time()) / (60 * 60 * 24));
                                            
                                            if ($days_remaining < 0) {
                                                $lease_class = 'lease-expired';
                                                $lease_status = "<span class='badge bg-danger'>Expired</span>";
                                            } elseif ($days_remaining <= 30) {
                                                $lease_class = 'lease-expiring';
                                                $lease_status = "<span class='badge bg-warning'>Expiring in {$days_remaining} days</span>";
                                            } else {
                                                $lease_status = "<span class='badge bg-success'>Active</span>";
                                            }
                                            
                                            echo "<tr class='tenant-status-{$row['status']} {$lease_class}'>
                                                    <td>
                                                        <strong>{$row['first_name']} {$row['last_name']}</strong>
                                                    </td>
                                                    <td>
                                                        <div><i class='fas fa-envelope text-muted'></i> {$row['email']}</div>
                                                        <div><i class='fas fa-phone text-muted'></i> " . ($row['phone'] ?: 'N/A') . "</div>
                                                    </td>
                                                    <td>
                                                        <strong>{$row['property_name']}</strong><br>
                                                        <small class='text-muted'>{$row['address']}</small>
                                                    </td>
                                                    <td>
                                                        <div>" . date('M j, Y', strtotime($row['lease_start'])) . "</div>
                                                        <div>to " . date('M j, Y', strtotime($row['lease_end'])) . "</div>
                                                        <div>{$lease_status}</div>
                                                    </td>
                                                    <td>
                                                        <strong>$" . number_format($row['rent_amount'], 2) . "</strong><br>
                                                        <small class='text-muted'>per month</small>
                                                    </td>
                                                    <td>
                                                        <span class='badge bg-{$status_class[$row['status']]}'>
                                                            " . ucfirst($row['status']) . "
                                                        </span>
                                                    </td>
                                                    <td class='table-actions'>
                                                        <button class='btn btn-sm btn-outline-primary view-tenant' 
                                                                data-tenant-id='{$row['id']}'
                                                                data-bs-toggle='modal' 
                                                                data-bs-target='#viewTenantModal'>
                                                            <i class='fas fa-eye'></i>
                                                        </button>
                                                        <button class='btn btn-sm btn-outline-warning edit-tenant'
                                                                data-tenant-id='{$row['id']}'
                                                                data-bs-toggle='modal'
                                                                data-bs-target='#editTenantModal'>
                                                            <i class='fas fa-edit'></i>
                                                        </button>
                                                        <button class='btn btn-sm btn-outline-danger delete-tenant'
                                                                data-tenant-id='{$row['id']}'
                                                                data-tenant-name='{$row['first_name']} {$row['last_name']}'
                                                                data-bs-toggle='modal'
                                                                data-bs-target='#deleteTenantModal'>
                                                            <i class='fas fa-trash'></i>
                                                        </button>
                                                    </td>
                                                  </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='7' class='text-center text-muted'>No tenants found</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Tenant Modal -->
    <div class="modal fade" id="addTenantModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Tenant</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">First Name *</label>
                                    <input type="text" name="first_name" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Last Name *</label>
                                    <input type="text" name="last_name" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email *</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" name="phone" class="form-control">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Property *</label>
                                    <select name="property_id" class="form-select" id="propertySelect" required>
                                        <option value="">Select Property</option>
                                        <?php
                                        $vacant_properties = $property->getVacantProperties();
                                        while ($prop_row = $vacant_properties->fetch(PDO::FETCH_ASSOC)) {
                                            echo "<option value='{$prop_row['id']}' data-rent='{$prop_row['rent_amount']}'>
                                                    {$prop_row['name']} - $" . number_format($prop_row['rent_amount'], 2) . " - {$prop_row['address']}
                                                  </option>";
                                        }
                                        ?>
                                    </select>
                                    <small class="form-text text-muted">Only vacant properties are shown</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Rent Amount *</label>
                                    <input type="number" name="rent_amount" step="0.01" class="form-control" id="rentAmount" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Lease Start Date *</label>
                                    <input type="date" name="lease_start" class="form-control" id="leaseStart" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Lease End Date *</label>
                                    <input type="date" name="lease_end" class="form-control" id="leaseEnd" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status *</label>
                                    <select name="status" class="form-select" required>
                                        <option value="active">Active</option>
                                        <option value="pending">Pending</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="create_tenant" class="btn btn-primary">Add Tenant</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Tenant Modal -->
    <div class="modal fade" id="viewTenantModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tenant Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="tenantDetails">
                    <!-- Details will be loaded via JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Tenant Modal -->
    <div class="modal fade" id="editTenantModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="tenant_id" id="edit_tenant_id">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Tenant</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="editTenantForm">
                        <!-- Form will be loaded via JavaScript -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="update_tenant" class="btn btn-primary">Update Tenant</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteTenantModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="tenant_id" id="delete_tenant_id">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Deletion</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete the tenant: <strong id="delete_tenant_name"></strong>?</p>
                        <p class="text-danger">This action cannot be undone and will remove all associated data.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="delete_tenant" class="btn btn-danger">Delete Tenant</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Auto-fill rent amount when property is selected
            $('#propertySelect').change(function() {
                const selectedOption = $(this).find('option:selected');
                const rentAmount = selectedOption.data('rent');
                
                if (rentAmount) {
                    $('#rentAmount').val(rentAmount);
                }
            });

            // Set default lease dates
            const today = new Date();
            const oneYearLater = new Date();
            oneYearLater.setFullYear(today.getFullYear() + 1);
            
            $('#leaseStart').val(today.toISOString().split('T')[0]);
            $('#leaseEnd').val(oneYearLater.toISOString().split('T')[0]);

            // View Tenant Details
            $('.view-tenant').click(function() {
                const tenantId = $(this).data('tenant-id');
                
                $.ajax({
                    url: 'ajax/ajax_get_tenant.php',
                    type: 'GET',
                    data: { tenant_id: tenantId },
                    success: function(response) {
                        $('#tenantDetails').html(response);
                    }
                });
            });

            // Edit Tenant
            $('.edit-tenant').click(function() {
                const tenantId = $(this).data('tenant-id');
                $('#edit_tenant_id').val(tenantId);
                
                $.ajax({
                    url: 'ajax/ajax_get_tenant_form.php',
                    type: 'GET', 
                    data: { tenant_id: tenantId },
                    success: function(response) {
                        $('#editTenantForm').html(response);
                    }
                });
            });

            // Delete Tenant Confirmation
            $('.delete-tenant').click(function() {
                const tenantId = $(this).data('tenant-id');
                const tenantName = $(this).data('tenant-name');
                
                $('#delete_tenant_id').val(tenantId);
                $('#delete_tenant_name').text(tenantName);
            });
        });
    </script>
</body>
</html>