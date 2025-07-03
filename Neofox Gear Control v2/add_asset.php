<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fix session path issue
if (!session_id()) {
    ini_set('session.save_path', sys_get_temp_dir());
    session_start();
}
require_once 'config/database.php';
require_once 'classes/Asset.php';
require_once 'classes/QRGenerator.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$asset = new Asset($db);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $asset_id = $_POST['asset_id'] ?: 'NF' . time();
    $base_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
$qr_url = QRGenerator::generateAssetQR($asset_id, $base_url);
    
    $data = [
        'asset_id' => $asset_id,
        'asset_name' => $_POST['asset_name'],
        'category' => $_POST['category'],
        'description' => $_POST['description'],
        'serial_number' => $_POST['serial_number'],
        'qr_code' => $qr_url,
        'condition_status' => $_POST['condition_status'],
        'notes' => $_POST['notes']
    ];
    
    if ($asset->create($data)) {
        $success = "Asset added successfully!";
    } else {
        $error = "Failed to add asset.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Asset - Neofox Gear Control</title>
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
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-plus"></i> Add New Asset</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="asset_id" class="form-label">Asset ID</label>
                                        <input type="text" class="form-control" id="asset_id" name="asset_id" 
                                            placeholder="Leave blank for auto-generation">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="asset_name" class="form-label">Asset Name *</label>
                                        <input type="text" class="form-control" id="asset_name" name="asset_name" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="category" class="form-label">Category *</label>
                                        <select class="form-control" id="category" name="category" required>
                                            <option value="">Select category...</option>
                                            <option value="Camera">Camera</option>
                                            <option value="Audio">Audio</option>
                                            <option value="Lighting">Lighting</option>
                                            <option value="Drone">Drone</option>
                                            <option value="Tripod">Tripod</option>
                                            <option value="Lens">Lens</option>
                                            <option value="Monitor">Monitor</option>
                                            <option value="Storage">Storage</option>
                                            <option value="Cables">Cables</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="condition_status" class="form-label">Condition *</label>
                                        <select class="form-control" id="condition_status" name="condition_status" required>
                                            <option value="excellent">Excellent</option>
                                            <option value="good">Good</option>
                                            <option value="needs_repair">Needs Repair</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="serial_number" class="form-label">Serial Number</label>
                                <input type="text" class="form-control" id="serial_number" name="serial_number">
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-save"></i> Add Asset
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <a href="assets.php" class="btn btn-secondary w-100">
                                        <i class="fas fa-arrow-left"></i> Back to Assets
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>