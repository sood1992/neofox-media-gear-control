<?php
// assets.php - Asset Management Page
// Fix session path issue
if (!session_id()) {
    ini_set('session.save_path', sys_get_temp_dir());
    session_start();
}
require_once 'config/database.php';
require_once 'classes/Asset.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$asset = new Asset($db);

$assets = $asset->getAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assets - Neofox Gear Control</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-cogs"></i> Neofox Gear Control
            </a>
<div class="navbar-nav ms-auto">
    <a class="nav-link" href="index.php">Dashboard</a>
    <a class="nav-link" href="dashboard_advanced.php">Analytics</a>
    <a class="nav-link" href="assets.php">Assets</a>
       <a class="nav-link" href="scanner.php">Scanner</a>
    <a class="nav-link" href="bulk_scanner_v2.php">QR Scanner</a>
    <a class="nav-link" href="requests.php">Requests</a>
    <a class="nav-link" href="logout.php">Logout</a>
     <a class="nav-link" href="maintenance.php">Maintenance</a>
</div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-box"></i> Asset Management</h2>
                    <a href="add_asset.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Asset
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="assetsTable" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Asset ID</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Condition</th>
                                        <th>Current Borrower</th>
                                        <th>QR Code</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assets as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['asset_id']); ?></td>
                                        <td><?php echo htmlspecialchars($item['asset_name']); ?></td>
                                        <td><?php echo htmlspecialchars($item['category']); ?></td>
                                        <td>
                                            <?php
                                            $status_colors = [
                                                'available' => 'success',
                                                'checked_out' => 'warning',
                                                'maintenance' => 'info',
                                                'lost' => 'danger'
                                            ];
                                            ?>
                                            <span class="badge bg-<?php echo $status_colors[$item['status']]; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $item['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $condition_colors = [
                                                'excellent' => 'success',
                                                'good' => 'warning',
                                                'needs_repair' => 'danger'
                                            ];
                                            ?>
                                            <span class="badge bg-<?php echo $condition_colors[$item['condition_status']]; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $item['condition_status'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($item['current_borrower'] ?? '-'); ?></td>
                                        <td>
                                            <a href="<?php echo $item['qr_code']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-qrcode"></i> View QR
                                            </a>
                                        </td>
                                        <td>
                                            <a href="edit_asset.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($item['status'] == 'available'): ?>
                                            <a href="checkout.php?asset_id=<?php echo $item['asset_id']; ?>" class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-sign-out-alt"></i>
                                            </a>
                                            <?php elseif ($item['status'] == 'checked_out'): ?>
                                            <a href="checkin.php?asset_id=<?php echo $item['asset_id']; ?>" class="btn btn-sm btn-outline-warning">
                                                <i class="fas fa-sign-in-alt"></i>
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#assetsTable').DataTable({
                pageLength: 25,
                order: [[1, 'asc']],
                responsive: true
            });
        });
    </script>
</body>
</html>