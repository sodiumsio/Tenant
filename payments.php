<?php

session_start();
require_once "config/database.php";
require_once "models/Property.php";
require_once "models/Tenant.php";
require_once "models/Payment.php";

$database = new Database();
$db = $database->getConnection();

$payment = new Payment($db);
$tenant = new Tenant($db);
$property = new Property($db);

$message = '';
$message_type = '';


if($_POST){
    try {
        if(isset($_POST['create_payment'])) {
            
            $required_fields = ['tenant_id', 'property_id', 'amount', 'due_date', 'payment_method', 'status'];
            $missing_fields = [];
            
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    $missing_fields[] = $field;
                }
            }
            
            if (!empty($missing_fields)) {
                $message = "Missing required fields: " . implode(', ', $missing_fields);
                $message_type = "danger";
            } else {
                
                $payment->tenant_id = $_POST['tenant_id'] ?? null;
                $payment->property_id = $_POST['property_id'] ?? null;
                $payment->amount = $_POST['amount'] ?? 0;
                $payment->payment_date = $_POST['payment_date'] ?? null;
                $payment->due_date = $_POST['due_date'] ?? null;
                $payment->payment_method = $_POST['payment_method'] ?? 'cash';
                $payment->status = $_POST['status'] ?? 'pending';
                $payment->description = $_POST['description'] ?? '';
                
                if($payment->create()){
                    $message = "Payment recorded successfully.";
                    $message_type = "success";
                } else {
                    $message = "Unable to record payment.";
                    $message_type = "danger";
                }
            }
        }
        
        if(isset($_POST['update_payment'])) {
            if (empty($_POST['payment_id'])) {
                $message = "Payment ID is required for update.";
                $message_type = "danger";
            } else {
                $payment->id = $_POST['payment_id'];
                $payment->amount = $_POST['amount'] ?? 0;
                $payment->payment_date = $_POST['payment_date'] ?? null;
                $payment->due_date = $_POST['due_date'] ?? null;
                $payment->payment_method = $_POST['payment_method'] ?? 'cash';
                $payment->status = $_POST['status'] ?? 'pending';
                $payment->description = $_POST['description'] ?? '';
                
                if($payment->update()){
                    $message = "Payment updated successfully.";
                    $message_type = "success";
                } else {
                    $message = "Unable to update payment.";
                    $message_type = "danger";
                }
            }
        }
        
        if(isset($_POST['delete_payment'])) {
            if (empty($_POST['payment_id'])) {
                $message = "Payment ID is required for deletion.";
                $message_type = "danger";
            } else {
                $payment->id = $_POST['payment_id'];
                if($payment->delete()){
                    $message = "Payment deleted successfully.";
                    $message_type = "success";
                } else {
                    $message = "Unable to delete payment.";
                    $message_type = "danger";
                }
            }
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = "danger";
    }
}


$total_revenue = $payment->getTotalRevenue();
$pending_payments = $payment->getPendingPaymentsCount();
$overdue_payments = $payment->getOverduePaymentsCount();


$tenants = $tenant->getActiveTenantsForPayments();
$properties = $property->read();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Management - Tenant System</title>
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
        .payment-status-paid { border-left: 4px solid #28a745 !important; }
        .payment-status-pending { border-left: 4px solid #ffc107 !important; }
        .payment-status-overdue { border-left: 4px solid #dc3545 !important; }
        .table-actions { white-space: nowrap; }
        .select2-container--bootstrap5 .select2-selection { height: 38px; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse bg-dark">
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
                            <a class="nav-link active" href="payments.php">
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
                    <h1 class="h2">Payment Management</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
                        <i class="fas fa-plus"></i> Record Payment
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
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Total Revenue</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">Ksh<?php echo number_format($total_revenue, 2); ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Payments</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $payment->read()->rowCount(); ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-receipt fa-2x text-gray-300"></i>
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
                                            Overdue Payments</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $overdue_payments; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Filters -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6>Filter Payments</h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="paid" <?php echo (isset($_GET['status']) && $_GET['status'] == 'paid') ? 'selected' : ''; ?>>Paid</option>
                                    <option value="pending" <?php echo (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="overdue" <?php echo (isset($_GET['status']) && $_GET['status'] == 'overdue') ? 'selected' : ''; ?>>Overdue</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Payment Method</label>
                                <select name="payment_method" class="form-select">
                                    <option value="">All Methods</option>
                                    <option value="cash" <?php echo (isset($_GET['payment_method']) && $_GET['payment_method'] == 'cash') ? 'selected' : ''; ?>>Cash</option>
                                    <option value="bank_transfer" <?php echo (isset($_GET['payment_method']) && $_GET['payment_method'] == 'bank_transfer') ? 'selected' : ''; ?>>Bank Transfer</option>
                                    <option value="credit_card" <?php echo (isset($_GET['payment_method']) && $_GET['payment_method'] == 'credit_card') ? 'selected' : ''; ?>>Credit Card</option>
                                    <option value="check" <?php echo (isset($_GET['payment_method']) && $_GET['payment_method'] == 'check') ? 'selected' : ''; ?>>Check</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">From Date</label>
                                <input type="date" name="from_date" class="form-control" value="<?php echo $_GET['from_date'] ?? ''; ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">To Date</label>
                                <input type="date" name="to_date" class="form-control" value="<?php echo $_GET['to_date'] ?? ''; ?>">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Apply Filters</button>
                                <a href="payments.php" class="btn btn-secondary">Clear</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Payments Table -->
                <div class="card">
                    <div class="card-header">
                        <h6>All Payments</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Tenant</th>
                                        <th>Property</th>
                                        <th>Amount</th>
                                        <th>Due Date</th>
                                        <th>Payment Date</th>
                                        <th>Method</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Apply filters
                                    $status_filter = $_GET['status'] ?? '';
                                    $method_filter = $_GET['payment_method'] ?? '';
                                    $from_date = $_GET['from_date'] ?? '';
                                    $to_date = $_GET['to_date'] ?? '';
                                    
                                    $stmt = $payment->readWithFilters($status_filter, $method_filter, $from_date, $to_date);
                                    
                                    if($stmt->rowCount() > 0) {
                                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            $status_class = [
                                                'paid' => 'success',
                                                'pending' => 'warning',
                                                'overdue' => 'danger'
                                            ];
                                            
                                            $due_date_class = '';
                                            if ($row['status'] == 'pending' && strtotime($row['due_date']) < time()) {
                                                $due_date_class = 'text-danger';
                                            }
                                            
                                            echo "<tr class='payment-status-{$row['status']}'>
                                                    <td>
                                                        <strong>{$row['tenant_name']}</strong><br>
                                                        <small class='text-muted'>{$row['tenant_email']}</small>
                                                    </td>
                                                    <td>{$row['property_name']}</td>
                                                    <td><strong>$" . number_format($row['amount'], 2) . "</strong></td>
                                                    <td class='{$due_date_class}'>" . date('M j, Y', strtotime($row['due_date'])) . "</td>
                                                    <td>" . ($row['payment_date'] ? date('M j, Y', strtotime($row['payment_date'])) : '-') . "</td>
                                                    <td>" . ucfirst(str_replace('_', ' ', $row['payment_method'])) . "</td>
                                                    <td>
                                                        <span class='badge bg-{$status_class[$row['status']]}'>
                                                            " . ucfirst($row['status']) . "
                                                        </span>
                                                    </td>
                                                    <td class='table-actions'>
                                                        <button class='btn btn-sm btn-outline-primary view-payment' 
                                                                data-payment-id='{$row['id']}'
                                                                data-bs-toggle='modal' 
                                                                data-bs-target='#viewPaymentModal'>
                                                            <i class='fas fa-eye'></i>
                                                        </button>
                                                        <button class='btn btn-sm btn-outline-warning edit-payment'
                                                                data-payment-id='{$row['id']}'
                                                                data-bs-toggle='modal'
                                                                data-bs-target='#editPaymentModal'>
                                                            <i class='fas fa-edit'></i>
                                                        </button>
                                                        <button class='btn btn-sm btn-outline-danger delete-payment'
                                                                data-payment-id='{$row['id']}'
                                                                data-tenant-name='{$row['tenant_name']}'
                                                                data-amount='{$row['amount']}'
                                                                data-bs-toggle='modal'
                                                                data-bs-target='#deletePaymentModal'>
                                                            <i class='fas fa-trash'></i>
                                                        </button>
                                                    </td>
                                                  </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='8' class='text-center text-muted'>No payments found</td></tr>";
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

    <!-- Add Payment Modal -->
    <div class="modal fade" id="addPaymentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Record New Payment</h5>
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
                                        if ($tenants->rowCount() > 0) {
                                            while ($tenant_row = $tenants->fetch(PDO::FETCH_ASSOC)) {
                                                echo "<option value='{$tenant_row['id']}' 
                                                        data-property='{$tenant_row['property_id']}' 
                                                        data-rent='{$tenant_row['rent_amount']}'>
                                                        {$tenant_row['first_name']} {$tenant_row['last_name']} - {$tenant_row['property_name']}
                                                      </option>";
                                            }
                                        } else {
                                            echo "<option value=''>No active tenants found</option>";
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
                                        if ($properties->rowCount() > 0) {
                                            while ($prop_row = $properties->fetch(PDO::FETCH_ASSOC)) {
                                                echo "<option value='{$prop_row['id']}' data-rent='{$prop_row['rent_amount']}'>
                                                        {$prop_row['name']} - $" . number_format($prop_row['rent_amount'], 2) . "
                                                      </option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Amount *</label>
                                    <input type="number" name="amount" step="0.01" class="form-control" id="amountInput" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Payment Method *</label>
                                    <select name="payment_method" class="form-select" required>
                                        <option value="">Select Method</option>
                                        <option value="cash">Cash</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="credit_card">Credit Card</option>
                                        <option value="check">Check</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Due Date *</label>
                                    <input type="date" name="due_date" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Payment Date</label>
                                    <input type="date" name="payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status *</label>
                                    <select name="status" class="form-select" required>
                                        <option value="pending">Pending</option>
                                        <option value="paid">Paid</option>
                                        <option value="overdue">Overdue</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <input type="text" name="description" class="form-control" placeholder="e.g., Monthly Rent - January 2024">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="create_payment" class="btn btn-primary">Record Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Payment Modal -->
    <div class="modal fade" id="viewPaymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Payment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="paymentDetails">
                    <!-- Details will be loaded via JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Payment Modal -->
    <div class="modal fade" id="editPaymentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="payment_id" id="edit_payment_id">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="editPaymentForm">
                        <!-- Form will be loaded via JavaScript -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="update_payment" class="btn btn-primary">Update Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deletePaymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="payment_id" id="delete_payment_id">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Deletion</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete the payment for <strong id="delete_tenant_name"></strong> of $<strong id="delete_payment_amount"></strong>?</p>
                        <p class="text-danger">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="delete_payment" class="btn btn-danger">Delete Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        // Auto-fill amount when tenant is selected
        $('#tenantSelect').change(function() {
            const selectedOption = $(this).find('option:selected');
            const rentAmount = selectedOption.data('rent');
            const propertyId = selectedOption.data('property');
            
            if (rentAmount) {
                $('#amountInput').val(rentAmount);
            }
            if (propertyId) {
                $('#propertySelect').val(propertyId).trigger('change');
            }
        });

        // Auto-fill amount when property is selected
        $('#propertySelect').change(function() {
            const selectedOption = $(this).find('option:selected');
            const rentAmount = selectedOption.data('rent');
            
            if (rentAmount && !$('#amountInput').val()) {
                $('#amountInput').val(rentAmount);
            }
        });

        // View Payment Details
        $('.view-payment').click(function() {
            const paymentId = $(this).data('payment-id');
            
            $.ajax({
                url: 'ajax/ajax_get_payment.php',
                type: 'GET',
                data: { payment_id: paymentId },
                success: function(response) {
                    $('#paymentDetails').html(response);
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    $('#paymentDetails').html('<div class="alert alert-danger">Error loading payment details. Please check if ajax_get_payment.php exists.</div>');
                }
            });
        });

        // Edit Payment
        $('.edit-payment').click(function() {
            const paymentId = $(this).data('payment-id');
            $('#edit_payment_id').val(paymentId);
            
            $.ajax({
                url: 'ajax/ajax_get_payment_form.php',
                type: 'GET', 
                data: { payment_id: paymentId },
                success: function(response) {
                    $('#editPaymentForm').html(response);
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    $('#editPaymentForm').html('<div class="alert alert-danger">Error loading payment form. Please check if ajax_get_payment_form.php exists. Error: ' + error + '</div>');
                }
            });
        });

        // Delete Payment Confirmation
        $('.delete-payment').click(function() {
            const paymentId = $(this).data('payment-id');
            const tenantName = $(this).data('tenant-name');
            const amount = $(this).data('amount');
            
            $('#delete_payment_id').val(paymentId);
            $('#delete_tenant_name').text(tenantName);
            $('#delete_payment_amount').text(amount);
        });
    });
</script>
</body>

</html>
