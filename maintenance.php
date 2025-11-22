<?php
// maintenance.php
session_start();
require_once "config/database.php";
require_once "models/Property.php";
require_once "models/Tenant.php";
require_once "models/Maintenance.php";

$database = new Database();
$db = $database->getConnection();

$property = new Property($db);
$tenant = new Tenant($db);
$maintenance = new Maintenance($db);

$message = '';
$message_type = '';

// Handle form submissions
if($_POST){
    try {
        if(isset($_POST['create_maintenance'])) {
            $maintenance->tenant_id = $_POST['tenant_id'];
            $maintenance->property_id = $_POST['property_id'];
            $maintenance->title = $_POST['title'];
            $maintenance->description = $_POST['description'];
            $maintenance->priority = $_POST['priority'];
            $maintenance->status = $_POST['status'];
            $maintenance->assigned_to = $_POST['assigned_to'];
            
            if($maintenance->create()){
                $message = "Maintenance request created successfully.";
                $message_type = "success";
            } else {
                $message = "Unable to create maintenance request.";
                $message_type = "danger";
            }
        }
        
        if(isset($_POST['update_maintenance'])) {
            $maintenance->id = $_POST['maintenance_id'];
            $maintenance->title = $_POST['title'];
            $maintenance->description = $_POST['description'];
            $maintenance->priority = $_POST['priority'];
            $maintenance->status = $_POST['status'];
            $maintenance->assigned_to = $_POST['assigned_to'];
            
            if($maintenance->update()){
                $message = "Maintenance request updated successfully.";
                $message_type = "success";
            } else {
                $message = "Unable to update maintenance request.";
                $message_type = "danger";
            }
        }
        
        if(isset($_POST['delete_maintenance'])) {
            $maintenance->id = $_POST['maintenance_id'];
            if($maintenance->delete()){
                $message = "Maintenance request deleted successfully.";
                $message_type = "success";
            } else {
                $message = "Unable to delete maintenance request.";
                $message_type = "danger";
            }
        }
        
        if(isset($_POST['complete_maintenance'])) {
            $maintenance->id = $_POST['maintenance_id'];
            if($maintenance->complete()){
                $message = "Maintenance request marked as completed.";
                $message_type = "success";
            } else {
                $message = "Unable to complete maintenance request.";
                $message_type = "danger";
            }
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = "danger";
    }
}

// Get maintenance statistics
$total_requests = $maintenance->read()->rowCount();
$open_requests = $maintenance->getActiveRequests()->rowCount();
$high_priority = $maintenance->getHighPriorityRequests()->rowCount();
$completed_this_month = $maintenance->getCompletedThisMonth()->rowCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Management - Tenant System</title>
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
        .priority-low { border-left: 4px solid #28a745 !important; }
        .priority-medium { border-left: 4px solid #ffc107 !important; }
        .priority-high { border-left: 4px solid #fd7e14 !important; }
        .priority-urgent { border-left: 4px solid #dc3545 !important; }
        .status-open { background-color: #e3f2fd; }
        .status-in_progress { background-color: #fff3cd; }
        .status-completed { background-color: #d4edda; }
        .status-cancelled { background-color: #f8d7da; }
        .table-actions { white-space: nowrap; }
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
                            <a class="nav-link active" href="maintenance.php">
                                <i class="fas fa-tools"></i> Maintenance
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Maintenance Management</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMaintenanceModal">
                        <i class="fas fa-plus"></i> New Request
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
                                            Total Requests</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_requests; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-tools fa-2x text-gray-300"></i>
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
                                            Open Requests</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $open_requests; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                                            High Priority</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $high_priority; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
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
                                            Completed This Month</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $completed_this_month; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Maintenance Filters -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6>Filter Maintenance Requests</h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="open" <?php echo (isset($_GET['status']) && $_GET['status'] == 'open') ? 'selected' : ''; ?>>Open</option>
                                    <option value="in_progress" <?php echo (isset($_GET['status']) && $_GET['status'] == 'in_progress') ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="completed" <?php echo (isset($_GET['status']) && $_GET['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                                    <option value="cancelled" <?php echo (isset($_GET['status']) && $_GET['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Priority</label>
                                <select name="priority" class="form-select">
                                    <option value="">All Priorities</option>
                                    <option value="low" <?php echo (isset($_GET['priority']) && $_GET['priority'] == 'low') ? 'selected' : ''; ?>>Low</option>
                                    <option value="medium" <?php echo (isset($_GET['priority']) && $_GET['priority'] == 'medium') ? 'selected' : ''; ?>>Medium</option>
                                    <option value="high" <?php echo (isset($_GET['priority']) && $_GET['priority'] == 'high') ? 'selected' : ''; ?>>High</option>
                                    <option value="urgent" <?php echo (isset($_GET['priority']) && $_GET['priority'] == 'urgent') ? 'selected' : ''; ?>>Urgent</option>
                                </select>
                            </div>
                            <div class="col-md-3">
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
                            <div class="col-md-3">
                                <label class="form-label">Assigned To</label>
                                <input type="text" name="assigned_to" class="form-control" value="<?php echo $_GET['assigned_to'] ?? ''; ?>" placeholder="Technician name">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Apply Filters</button>
                                <a href="maintenance.php" class="btn btn-secondary">Clear</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Maintenance Requests Table -->
                <div class="card">
                    <div class="card-header">
                        <h6>Maintenance Requests</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Property & Tenant</th>
                                        <th>Description</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Assigned To</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Apply filters
                                    $status_filter = $_GET['status'] ?? '';
                                    $priority_filter = $_GET['priority'] ?? '';
                                    $property_filter = $_GET['property_id'] ?? '';
                                    $assigned_filter = $_GET['assigned_to'] ?? '';
                                    
                                    $stmt = $maintenance->readWithFilters($status_filter, $priority_filter, $property_filter, $assigned_filter);
                                    
                                    if($stmt->rowCount() > 0) {
                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            $priority_class = [
                                                'low' => 'success',
                                                'medium' => 'warning',
                                                'high' => 'danger',
                                                'urgent' => 'dark'
                                            ];
                                            
                                            $status_class = [
                                                'open' => 'primary',
                                                'in_progress' => 'warning',
                                                'completed' => 'success',
                                                'cancelled' => 'secondary'
                                            ];
                                            
                                            $status_text = [
                                                'open' => 'Open',
                                                'in_progress' => 'In Progress',
                                                'completed' => 'Completed',
                                                'cancelled' => 'Cancelled'
                                            ];
                                            
                                            echo "<tr class='priority-{$row['priority']} status-{$row['status']}'>
                                                    <td>
                                                        <strong>{$row['title']}</strong>
                                                    </td>
                                                    <td>
                                                        <div><strong>{$row['property_name']}</strong></div>
                                                        <div><small class='text-muted'>{$row['first_name']} {$row['last_name']}</small></div>
                                                    </td>
                                                    <td>
                                                        <div class='text-truncate' style='max-width: 200px;' title='{$row['description']}'>
                                                            {$row['description']}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class='badge bg-{$priority_class[$row['priority']]}'>
                                                            " . ucfirst($row['priority']) . "
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class='badge bg-{$status_class[$row['status']]}'>
                                                            {$status_text[$row['status']]}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        " . ($row['assigned_to'] ?: '<span class="text-muted">Not assigned</span>') . "
                                                    </td>
                                                    <td>
                                                        " . date('M j, Y', strtotime($row['created_at'])) . "
                                                    </td>
                                                    <td class='table-actions'>
                                                        <button class='btn btn-sm btn-outline-primary view-maintenance' 
                                                                data-maintenance-id='{$row['id']}'
                                                                data-bs-toggle='modal' 
                                                                data-bs-target='#viewMaintenanceModal'>
                                                            <i class='fas fa-eye'></i>
                                                        </button>
                                                        <button class='btn btn-sm btn-outline-warning edit-maintenance'
                                                                data-maintenance-id='{$row['id']}'
                                                                data-bs-toggle='modal'
                                                                data-bs-target='#editMaintenanceModal'>
                                                            <i class='fas fa-edit'></i>
                                                        </button>
                                                        " . ($row['status'] != 'completed' ? "
                                                        <button class='btn btn-sm btn-outline-success complete-maintenance'
                                                                data-maintenance-id='{$row['id']}'
                                                                data-maintenance-title='{$row['title']}'
                                                                data-bs-toggle='modal'
                                                                data-bs-target='#completeMaintenanceModal'>
                                                            <i class='fas fa-check'></i>
                                                        </button>
                                                        " : "") . "
                                                        <button class='btn btn-sm btn-outline-danger delete-maintenance'
                                                                data-maintenance-id='{$row['id']}'
                                                                data-maintenance-title='{$row['title']}'
                                                                data-bs-toggle='modal'
                                                                data-bs-target='#deleteMaintenanceModal'>
                                                            <i class='fas fa-trash'></i>
                                                        </button>
                                                    </td>
                                                  </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='8' class='text-center text-muted'>No maintenance requests found</td></tr>";
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

    <!-- Add Maintenance Modal -->
    <div class="modal fade" id="addMaintenanceModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Create Maintenance Request</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tenant *</label>
                                    <select name="tenant_id" class="form-select" id="tenantSelect" required>
                                        <option value="">Select Tenant</option>
                                        <?php
                                        $tenants = $tenant->getActiveTenants();
                                        while ($tenant_row = $tenants->fetch(PDO::FETCH_ASSOC)) {
                                            echo "<option value='{$tenant_row['id']}' data-property='{$tenant_row['property_id']}'>
                                                    {$tenant_row['first_name']} {$tenant_row['last_name']} - {$tenant_row['property_name']}
                                                  </option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Property *</label>
                                    <select name="property_id" class="form-select" id="propertySelect" required>
                                        <option value="">Select Property</option>
                                        <?php
                                        $properties = $property->read();
                                        while ($prop_row = $properties->fetch(PDO::FETCH_ASSOC)) {
                                            echo "<option value='{$prop_row['id']}'>{$prop_row['name']} - {$prop_row['address']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Title *</label>
                                    <input type="text" name="title" class="form-control" placeholder="Brief description of the issue" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Description *</label>
                                    <textarea name="description" class="form-control" rows="4" placeholder="Detailed description of the maintenance issue..." required></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Priority *</label>
                                    <select name="priority" class="form-select" required>
                                        <option value="low">Low</option>
                                        <option value="medium" selected>Medium</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Status *</label>
                                    <select name="status" class="form-select" required>
                                        <option value="open" selected>Open</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Assigned To</label>
                                    <input type="text" name="assigned_to" class="form-control" placeholder="Technician name">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="create_maintenance" class="btn btn-primary">Create Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Maintenance Modal -->
    <div class="modal fade" id="viewMaintenanceModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Maintenance Request Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="maintenanceDetails">
                    <!-- Details will be loaded via JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Maintenance Modal -->
    <div class="modal fade" id="editMaintenanceModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="maintenance_id" id="edit_maintenance_id">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Maintenance Request</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="editMaintenanceForm">
                        <!-- Form will be loaded via JavaScript -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="update_maintenance" class="btn btn-primary">Update Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Complete Maintenance Modal -->
    <div class="modal fade" id="completeMaintenanceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="maintenance_id" id="complete_maintenance_id">
                    <div class="modal-header">
                        <h5 class="modal-title">Complete Maintenance Request</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to mark <strong id="complete_maintenance_title"></strong> as completed?</p>
                        <p class="text-muted">This will update the status to "Completed" and record the completion date.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="complete_maintenance" class="btn btn-success">Mark as Completed</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteMaintenanceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="maintenance_id" id="delete_maintenance_id">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Deletion</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete the maintenance request: <strong id="delete_maintenance_title"></strong>?</p>
                        <p class="text-danger">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="delete_maintenance" class="btn btn-danger">Delete Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Auto-fill property when tenant is selected
            $('#tenantSelect').change(function() {
                const selectedOption = $(this).find('option:selected');
                const propertyId = selectedOption.data('property');
                
                if (propertyId) {
                    $('#propertySelect').val(propertyId).trigger('change');
                }
            });

            // View Maintenance Details
            $('.view-maintenance').click(function() {
                const maintenanceId = $(this).data('maintenance-id');
                
                $.ajax({
                    url: 'ajax/ajax_get_maintenance.php',
                    type: 'GET',
                    data: { maintenance_id: maintenanceId },
                    success: function(response) {
                        $('#maintenanceDetails').html(response);
                    }
                });
            });

            // Edit Maintenance
            $('.edit-maintenance').click(function() {
                const maintenanceId = $(this).data('maintenance-id');
                $('#edit_maintenance_id').val(maintenanceId);
                
                $.ajax({
                    url: 'ajax/ajax_get_maintenance_form.php',
                    type: 'GET', 
                    data: { maintenance_id: maintenanceId },
                    success: function(response) {
                        $('#editMaintenanceForm').html(response);
                    }
                });
            });

            // Complete Maintenance Confirmation
            $('.complete-maintenance').click(function() {
                const maintenanceId = $(this).data('maintenance-id');
                const maintenanceTitle = $(this).data('maintenance-title');
                
                $('#complete_maintenance_id').val(maintenanceId);
                $('#complete_maintenance_title').text(maintenanceTitle);
            });

            // Delete Maintenance Confirmation
            $('.delete-maintenance').click(function() {
                const maintenanceId = $(this).data('maintenance-id');
                const maintenanceTitle = $(this).data('maintenance-title');
                
                $('#delete_maintenance_id').val(maintenanceId);
                $('#delete_maintenance_title').text(maintenanceTitle);
            });
        });
    </script>
</body>
</html>