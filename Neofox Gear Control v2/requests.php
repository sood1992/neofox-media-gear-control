<?php
// requests.php - Admin View of Gear Requests
// Fix session path issue
if (!session_id()) {
    ini_set('session.save_path', sys_get_temp_dir());
    session_start();
}
require_once 'config/database.php';
require_once 'classes/GearRequest.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$gear_request = new GearRequest($db);

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $request_id = $_POST['request_id'];
    $new_status = $_POST['status'];
    $admin_notes = $_POST['admin_notes'];
    
    if ($gear_request->updateStatus($request_id, $new_status, $admin_notes)) {
        $success = "Request status updated successfully!";
    } else {
        $error = "Failed to update request status.";
    }
}

$requests = $gear_request->getAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gear Requests - Neofox Gear Control</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-cogs"></i> Neofox Gear Control
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">Dashboard</a>
                <a class="nav-link" href="assets.php">Assets</a>
                <a class="nav-link" href="scanner.php">Scanner</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-calendar-check"></i> Gear Requests</h2>
                    <a href="gear_request.php" class="btn btn-info">
                        <i class="fas fa-plus"></i> New Request
                    </a>
                </div>
            </div>
        </div>

        <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="row">
            <?php foreach ($requests as $request): ?>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><?php echo htmlspecialchars($request['requester_name']); ?></h6>
                        <?php
                        $status_colors = [
                            'pending' => 'warning',
                            'approved' => 'success',
                            'rejected' => 'danger'
                        ];
                        ?>
                        <span class="badge bg-<?php echo $status_colors[$request['status']]; ?>">
                            <?php echo ucfirst($request['status']); ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <p><strong>Dates:</strong> <?php echo htmlspecialchars($request['request_dates']); ?></p>
                        <p><strong>Equipment:</strong><br><?php echo nl2br(htmlspecialchars($request['required_items'])); ?></p>
                        <?php if ($request['purpose']): ?>
                        <p><strong>Purpose:</strong><br><?php echo nl2br(htmlspecialchars($request['purpose'])); ?></p>
                        <?php endif; ?>
                        <p><small class="text-muted">Requested: <?php echo date('M j, Y g:i A', strtotime($request['created_at'])); ?></small></p>
                        
                        <?php if ($_SESSION['role'] == 'admin'): ?>
                        <hr>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                            <div class="row">
                                <div class="col-md-6">
                                    <select name="status" class="form-control form-control-sm" required>
                                        <option value="pending" <?php echo $request['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="approved" <?php echo $request['status'] == 'approved' ? 'selected' : ''; ?>>Approved</option>
                                        <option value="rejected" <?php echo $request['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <button type="submit" name="update_status" class="btn btn-sm btn-primary w-100">Update</button>
                                </div>
                            </div>
                            <div class="mt-2">
                                <textarea name="admin_notes" class="form-control form-control-sm" rows="2" 
                                    placeholder="Admin notes..."><?php echo htmlspecialchars($request['admin_notes']); ?></textarea>
                            </div>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (empty($requests)): ?>
        <div class="text-center mt-5">
            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
            <h4 class="text-muted">No gear requests yet</h4>
            <p class="text-muted">Gear requests will appear here when submitted.</p>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
?>