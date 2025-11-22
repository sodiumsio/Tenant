<?php
// properties.php
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
        if(isset($_POST['create_property'])) {
            $property->name = $_POST['name'];
            $property->address = $_POST['address'];
            $property->type = $_POST['type'];
            $property->rent_amount = $_POST['rent_amount'];
            $property->status = $_POST['status'];
            
            if($property->create()){
                $message = "Property created successfully.";
                $message_type = "success";
            } else {
                $message = "Unable to create property.";
                $message_type = "danger";
            }
        }
        
        if(isset($_POST['update_property'])) {
            $property->id = $_POST['property_id'];
            $property->name = $_POST['name'];
            $property->address = $_POST['address'];
            $property->type = $_POST['type'];
            $property->rent_amount = $_POST['rent_amount'];
            $property->status = $_POST['status'];
            
            if($property->update()){
                $message = "Property updated successfully.";
                $message_type = "success";
            } else {
                $message = "Unable to update property.";
                $message_type = "danger";
            }
        }
        
        if(isset($_POST['delete_property'])) {
            $property->id = $_POST['property_id'];
            if($property->delete()){
                $message = "Property deleted successfully.";
                $message_type = "success";
            } else {
                $message = "Unable to delete property.";
                $message_type = "danger";
            }
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = "danger";
    }
}

// Get property statistics
$total_properties = $property->read()->rowCount();
$vacant_properties = $property->getVacantProperties()->rowCount();
$occupied_properties = $property->getOccupiedProperties()->rowCount();
$maintenance_properties = $property->getMaintenanceProperties()->rowCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Management - Tenant System</title>
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
        .property-card {
            border-left: 4px solid;
            transition: all 0.3s ease;
        }
        .property-card:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .status-vacant { border-left-color: #ffc107 !important; }
        .status-occupied { border-left-color: #28a745 !important; }
        .status-maintenance { border-left-color: #dc3545 !important; }
        .table-actions { white-space: nowrap; }
        .property-type-badge {
            font-size: 0.75em;
            text-transform: uppercase;
        }
        .address-text {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
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
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="properties.php">
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
                    <h1 class="h2">Property Management</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPropertyModal">
                        <i class="fas fa-plus"></i> Add Property
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
                                            Total Properties</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_properties; ?></div>
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
                                            Occupied</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $occupied_properties; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-user-check fa-2x text-gray-300"></i>
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
                                            Vacant</div>
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
                        <div class="card border-left-danger shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                            Under Maintenance</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $maintenance_properties; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-tools fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Property Filters -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6>Filter Properties</h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="vacant" <?php echo (isset($_GET['status']) && $_GET['status'] == 'vacant') ? 'selected' : ''; ?>>Vacant</option>
                                    <option value="occupied" <?php echo (isset($_GET['status']) && $_GET['status'] == 'occupied') ? 'selected' : ''; ?>>Occupied</option>
                                    <option value="maintenance" <?php echo (isset($_GET['status']) && $_GET['status'] == 'maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Type</label>
                                <select name="type" class="form-select">
                                    <option value="">All Types</option>
                                    <option value="apartment" <?php echo (isset($_GET['type']) && $_GET['type'] == 'apartment') ? 'selected' : ''; ?>>Apartment</option>
                                    <option value="house" <?php echo (isset($_GET['type']) && $_GET['type'] == 'house') ? 'selected' : ''; ?>>House</option>
                                    <option value="commercial" <?php echo (isset($_GET['type']) && $_GET['type'] == 'commercial') ? 'selected' : ''; ?>>Commercial</option>
                                    <option value="condo" <?php echo (isset($_GET['type']) && $_GET['type'] == 'condo') ? 'selected' : ''; ?>>Condo</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Rent Range</label>
                                <select name="rent_range" class="form-select">
                                    <option value="">Any Rent</option>
                                    <option value="0-1000" <?php echo (isset($_GET['rent_range']) && $_GET['rent_range'] == '0-1000') ? 'selected' : ''; ?>>$0 - $1,000</option>
                                    <option value="1000-2000" <?php echo (isset($_GET['rent_range']) && $_GET['rent_range'] == '1000-2000') ? 'selected' : ''; ?>>$1,000 - $2,000</option>
                                    <option value="2000-3000" <?php echo (isset($_GET['rent_range']) && $_GET['rent_range'] == '2000-3000') ? 'selected' : ''; ?>>$2,000 - $3,000</option>
                                    <option value="3000+" <?php echo (isset($_GET['rent_range']) && $_GET['rent_range'] == '3000+') ? 'selected' : ''; ?>>$3,000+</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Apply Filters</button>
                                <a href="properties.php" class="btn btn-secondary">Clear</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Properties Table -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">All Properties</h6>
                        <span class="badge bg-primary"><?php echo $total_properties; ?> properties</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Property Name</th>
                                        <th>Address</th>
                                        <th>Type</th>
                                        <th>Rent Amount</th>
                                        <th>Status</th>
                                        <th>Tenant</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Apply filters
                                    $status_filter = $_GET['status'] ?? '';
                                    $type_filter = $_GET['type'] ?? '';
                                    $rent_filter = $_GET['rent_range'] ?? '';
                                    
                                    $stmt = $property->readWithFilters($status_filter, $type_filter, $rent_filter);
                                    
                                    if($stmt->rowCount() > 0) {
                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            $status_class = [
                                                'vacant' => 'warning',
                                                'occupied' => 'success',
                                                'maintenance' => 'danger'
                                            ];
                                            
                                            $type_badge = [
                                                'apartment' => 'info',
                                                'house' => 'primary',
                                                'commercial' => 'dark',
                                                'condo' => 'secondary'
                                            ];
                                            
                                            // Get current tenant for occupied properties
                                            $current_tenant = '';
                                            if ($row['status'] == 'occupied') {
                                                $tenant_stmt = $tenant->getTenantByProperty($row['id']);
                                                if ($tenant_data = $tenant_stmt->fetch(PDO::FETCH_ASSOC)) {
                                                    $current_tenant = $tenant_data['first_name'] . ' ' . $tenant_data['last_name'];
                                                }
                                            }
                                            
                                            echo "<tr class='property-card status-{$row['status']}'>
                                                    <td>
                                                        <strong>{$row['name']}</strong>
                                                    </td>
                                                    <td>
                                                        <div class='address-text' title='{$row['address']}'>
                                                            {$row['address']}
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class='badge bg-{$type_badge[$row['type']]} property-type-badge'>
                                                            " . ucfirst($row['type']) . "
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <strong class='text-success'>$" . number_format($row['rent_amount'], 2) . "</strong>
                                                        <br><small class='text-muted'>per month</small>
                                                    </td>
                                                    <td>
                                                        <span class='badge bg-{$status_class[$row['status']]}'>
                                                            " . ucfirst($row['status']) . "
                                                        </span>
                                                    </td>
                                                    <td>
                                                        " . ($current_tenant ?: '<span class="text-muted">No tenant</span>') . "
                                                    </td>
                                                    <td class='table-actions'>
                                                        <button class='btn btn-sm btn-outline-primary view-property' 
                                                                data-property-id='{$row['id']}'
                                                                data-bs-toggle='modal' 
                                                                data-bs-target='#viewPropertyModal'>
                                                            <i class='fas fa-eye'></i>
                                                        </button>
                                                        <button class='btn btn-sm btn-outline-warning edit-property'
                                                                data-property-id='{$row['id']}'
                                                                data-bs-toggle='modal'
                                                                data-bs-target='#editPropertyModal'>
                                                            <i class='fas fa-edit'></i>
                                                        </button>
                                                        <button class='btn btn-sm btn-outline-danger delete-property'
                                                                data-property-id='{$row['id']}'
                                                                data-property-name='{$row['name']}'
                                                                data-bs-toggle='modal'
                                                                data-bs-target='#deletePropertyModal'>
                                                            <i class='fas fa-trash'></i>
                                                        </button>
                                                    </td>
                                                  </tr>";
                                        }
                                    } else {
                                        echo "<tr>
                                                <td colspan='7' class='text-center py-4'>
                                                    <div class='text-muted'>
                                                        <i class='fas fa-building fa-3x mb-3'></i><br>
                                                        No properties found
                                                    </div>
                                                </td>
                                              </tr>";
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

    <!-- Add Property Modal -->
    <div class="modal fade" id="addPropertyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Property</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Property Name *</label>
                                    <input type="text" name="name" class="form-control" placeholder="e.g., Sunset Apartments Unit 101" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Type *</label>
                                    <select name="type" class="form-select" required>
                                        <option value="apartment">Apartment</option>
                                        <option value="house">House</option>
                                        <option value="commercial">Commercial</option>
                                        <option value="condo">Condo</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Address *</label>
                                    <textarea name="address" class="form-control" rows="3" placeholder="Full property address..." required></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Rent Amount *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" name="rent_amount" step="0.01" class="form-control" placeholder="0.00" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status *</label>
                                    <select name="status" class="form-select" required>
                                        <option value="vacant">Vacant</option>
                                        <option value="occupied">Occupied</option>
                                        <option value="maintenance">Maintenance</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="create_property" class="btn btn-primary">Add Property</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Property Modal -->
    <div class="modal fade" id="viewPropertyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Property Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="propertyDetails">
                    <!-- Details will be loaded via JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Property Modal -->
    <div class="modal fade" id="editPropertyModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="property_id" id="edit_property_id">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Property</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="editPropertyForm">
                        <!-- Form will be loaded via JavaScript -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="update_property" class="btn btn-primary">Update Property</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deletePropertyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="property_id" id="delete_property_id">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Deletion</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete the property: <strong id="delete_property_name"></strong>?</p>
                        <p class="text-danger">
                            <i class="fas fa-exclamation-triangle"></i> 
                            This action cannot be undone and will remove all associated data including tenants and payments.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="delete_property" class="btn btn-danger">Delete Property</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // View Property Details
            $('.view-property').click(function() {
                const propertyId = $(this).data('property-id');
                
                $.ajax({
                    url: 'ajax/ajax_get_property.php',
                    type: 'GET',
                    data: { property_id: propertyId },
                    success: function(response) {
                        $('#propertyDetails').html(response);
                    }
                });
            });

            // Edit Property
            $('.edit-property').click(function() {
                const propertyId = $(this).data('property-id');
                $('#edit_property_id').val(propertyId);
                
                $.ajax({
                    url: 'ajax/ajax_get_property_form.php',
                    type: 'GET', 
                    data: { property_id: propertyId },
                    success: function(response) {
                        $('#editPropertyForm').html(response);
                    }
                });
            });

            // Delete Property Confirmation
            $('.delete-property').click(function() {
                const propertyId = $(this).data('property-id');
                const propertyName = $(this).data('property-name');
                
                $('#delete_property_id').val(propertyId);
                $('#delete_property_name').text(propertyName);
            });
        });
    </script>
</body>
</html>