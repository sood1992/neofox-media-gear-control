<?php
// bulk_scanner_v2.php - Enhanced Bulk Scanner with Review & Submit
// Fix session path issue
if (!session_id()) {
    ini_set('session.save_path', sys_get_temp_dir());
    session_start();
}

require_once 'config/database.php';
require_once 'classes/Asset.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$asset = new Asset($db);

// Get all users for dropdown
$users_query = "SELECT username FROM users WHERE role IN ('admin', 'team_member') ORDER BY username";
$users_stmt = $db->prepare($users_query);
$users_stmt->execute();
$users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] == 'get_asset_info') {
        $asset_id = $_POST['asset_id'];
        $asset_info = $asset->getByAssetId($asset_id);
        
        if ($asset_info) {
            echo json_encode(['success' => true, 'asset' => $asset_info]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Asset not found']);
        }
        exit();
    }
    
    if ($_POST['action'] == 'process_batch') {
        $operation = $_POST['operation'];
        $asset_ids = json_decode($_POST['asset_ids'], true);
        $results = [];
        
        foreach ($asset_ids as $asset_id) {
            $asset_info = $asset->getByAssetId($asset_id);
            
            if (!$asset_info) {
                $results[] = ['asset_id' => $asset_id, 'success' => false, 'message' => 'Asset not found'];
                continue;
            }
            
            if ($operation == 'bulk_checkout') {
                $borrower = $_POST['borrower_name'];
                $expected_return = $_POST['expected_return_date'];
                $purpose = $_POST['purpose'];
                
                if ($asset_info['status'] != 'available') {
                    $results[] = ['asset_id' => $asset_id, 'asset_name' => $asset_info['asset_name'], 'success' => false, 'message' => 'Not available'];
                    continue;
                }
                
                if ($asset->checkOut($asset_id, $borrower, $expected_return, $purpose)) {
                    $results[] = ['asset_id' => $asset_id, 'asset_name' => $asset_info['asset_name'], 'success' => true, 'message' => 'Checked out'];
                } else {
                    $results[] = ['asset_id' => $asset_id, 'asset_name' => $asset_info['asset_name'], 'success' => false, 'message' => 'Checkout failed'];
                }
                
            } elseif ($operation == 'bulk_checkin') {
                $condition = $_POST['condition'];
                $notes = $_POST['notes'];
                
                if ($asset_info['status'] != 'checked_out') {
                    $results[] = ['asset_id' => $asset_id, 'asset_name' => $asset_info['asset_name'], 'success' => false, 'message' => 'Not checked out'];
                    continue;
                }
                
                if ($asset->checkIn($asset_id, $condition, $notes)) {
                    $results[] = ['asset_id' => $asset_id, 'asset_name' => $asset_info['asset_name'], 'success' => true, 'message' => 'Checked in'];
                } else {
                    $results[] = ['asset_id' => $asset_id, 'asset_name' => $asset_info['asset_name'], 'success' => false, 'message' => 'Checkin failed'];
                }
            }
        }
        
        echo json_encode(['success' => true, 'results' => $results]);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk QR Scanner - Neofox Gear Control</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <style>
        .scanner-container {
            max-width: 500px;
            margin: 0 auto;
        }
        .operation-panel {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .scanned-item {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .scanned-item.pending {
            border-left: 5px solid #ffc107;
            background-color: #fffdf7;
        }
        .scanned-item.success {
            border-left: 5px solid #28a745;
            background-color: #f8fff9;
        }
        .scanned-item.error {
            border-left: 5px solid #dc3545;
            background-color: #fff8f8;
        }
        .scanned-list {
            max-height: 400px;
            overflow-y: auto;
        }
        .scanner-status {
            text-align: center;
            padding: 20px;
            border: 2px dashed #ccc;
            border-radius: 10px;
            margin: 20px 0;
        }
        .mode-selector {
            margin-bottom: 20px;
        }
        .mode-btn {
            margin: 0 10px;
        }
        #qr-reader {
            border: 2px solid #007bff;
            border-radius: 10px;
        }
        .batch-actions {
            position: sticky;
            bottom: 0;
            background: white;
            padding: 15px;
            border-top: 1px solid #ddd;
            margin-top: 20px;
        }
        .remove-btn {
            padding: 2px 8px;
            font-size: 0.8rem;
        }
        .asset-info {
            flex-grow: 1;
        }
    </style>
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
                <a class="nav-link" href="bulk_manage.php">Bulk Manage</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12 text-center">
                <h2><i class="fas fa-qrcode"></i> Bulk QR Scanner</h2>
                <p class="text-muted">Scan multiple QR codes, review the list, then submit all at once</p>
            </div>
        </div>

        <div class="row">
            <!-- Scanner Section -->
            <div class="col-md-6">
                <div class="operation-panel">
                    <h5><i class="fas fa-cog"></i> Scanner Mode</h5>
                    
                    <div class="mode-selector text-center">
                        <button type="button" class="btn btn-success mode-btn" onclick="setMode('checkout')" id="checkoutModeBtn">
                            <i class="fas fa-sign-out-alt"></i> Checkout Mode
                        </button>
                        <button type="button" class="btn btn-warning mode-btn" onclick="setMode('checkin')" id="checkinModeBtn">
                            <i class="fas fa-sign-in-alt"></i> Checkin Mode
                        </button>
                    </div>
                    
                    <div id="currentMode" class="text-center mb-3">
                        <span class="badge bg-light text-dark">Select a mode to start scanning</span>
                    </div>
                </div>

                <!-- Checkout Form -->
                <div class="card" id="checkoutSettings" style="display: none;">
                    <div class="card-header bg-success text-white">
                        <h6><i class="fas fa-sign-out-alt"></i> Checkout Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="bulk_borrower" class="form-label">Borrower *</label>
                            <select class="form-control" id="bulk_borrower" required>
                                <option value="">Select team member...</option>
                                <?php foreach ($users as $user): ?>
                                <option value="<?php echo htmlspecialchars($user['username']); ?>">
                                    <?php echo htmlspecialchars($user['username']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="bulk_return_date" class="form-label">Expected Return *</label>
                            <input type="datetime-local" class="form-control" id="bulk_return_date" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="bulk_purpose" class="form-label">Purpose</label>
                            <input type="text" class="form-control" id="bulk_purpose" placeholder="Project or shoot purpose...">
                        </div>
                    </div>
                </div>

                <!-- Checkin Form -->
                <div class="card" id="checkinSettings" style="display: none;">
                    <div class="card-header bg-warning text-dark">
                        <h6><i class="fas fa-sign-in-alt"></i> Checkin Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="bulk_condition" class="form-label">Equipment Condition *</label>
                            <select class="form-control" id="bulk_condition" required>
                                <option value="">Select condition...</option>
                                <option value="excellent">Excellent</option>
                                <option value="good">Good</option>
                                <option value="needs_repair">Needs Repair</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="bulk_notes" class="form-label">Return Notes</label>
                            <input type="text" class="form-control" id="bulk_notes" placeholder="Any issues or observations...">
                        </div>
                    </div>
                </div>

                <!-- Scanner -->
                <div class="scanner-container">
                    <div id="scanner-status" class="scanner-status">
                        <i class="fas fa-camera fa-2x text-muted"></i>
                        <p>Select a mode to activate scanner</p>
                    </div>
                    <div id="qr-reader" style="display: none;"></div>
                    
                    <div class="text-center mt-3">
                        <button id="start-scan" class="btn btn-primary" style="display: none;" onclick="startScanning()">
                            <i class="fas fa-play"></i> Start Scanning
                        </button>
                        <button id="stop-scan" class="btn btn-danger" style="display: none;" onclick="stopScanning()">
                            <i class="fas fa-stop"></i> Stop Scanning
                        </button>
                    </div>
                </div>
            </div>

            <!-- Scanned Items List -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5><i class="fas fa-list"></i> Scanned Items (<span id="item-count">0</span>)</h5>
                        <button class="btn btn-sm btn-outline-secondary" onclick="clearScannedList()">Clear All</button>
                    </div>
                    <div class="card-body p-0">
                        <div id="scanned-items" class="scanned-list p-3">
                            <div class="text-center text-muted p-4">
                                <i class="fas fa-qrcode fa-2x"></i>
                                <p>Start scanning to add items to this list</p>
                            </div>
                        </div>
                        
                        <div class="batch-actions" id="batch-actions" style="display: none;">
                            <div class="d-grid gap-2">
                                <button class="btn btn-success btn-lg" onclick="processBatch()" id="process-btn">
                                    <i class="fas fa-check"></i> Process All Items
                                </button>
                                <small class="text-muted text-center">Review your list above, then click to process all items</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Processing Results -->
                <div class="card mt-3" id="results-card" style="display: none;">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-bar"></i> Processing Results</h5>
                    </div>
                    <div class="card-body">
                        <div id="processing-results"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let html5QrcodeScanner = null;
        let isScanning = false;
        let currentMode = null;
        let scannedItems = new Map(); // Use Map to avoid duplicates
        let lastScannedTime = 0;
        let scanCooldown = 1500;

        function setMode(mode) {
            currentMode = mode;
            
            // Update UI
            if (mode === 'checkout') {
                document.getElementById('checkoutModeBtn').classList.add('active');
                document.getElementById('checkinModeBtn').classList.remove('active');
                document.getElementById('checkoutSettings').style.display = 'block';
                document.getElementById('checkinSettings').style.display = 'none';
                document.getElementById('currentMode').innerHTML = '<span class="badge bg-success">CHECKOUT MODE</span>';
                
                // Set default return date
                const tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                tomorrow.setHours(17, 0, 0, 0);
                document.getElementById('bulk_return_date').value = tomorrow.toISOString().slice(0, 16);
                
            } else if (mode === 'checkin') {
                document.getElementById('checkinModeBtn').classList.add('active');
                document.getElementById('checkoutModeBtn').classList.remove('active');
                document.getElementById('checkinSettings').style.display = 'block';
                document.getElementById('checkoutSettings').style.display = 'none';
                document.getElementById('currentMode').innerHTML = '<span class="badge bg-warning">CHECKIN MODE</span>';
            }
            
            // Show scanner controls
            document.getElementById('scanner-status').innerHTML = `
                <i class="fas fa-qrcode fa-2x text-primary"></i>
                <p>Ready to scan in ${mode.toUpperCase()} mode</p>
            `;
            document.getElementById('start-scan').style.display = 'inline-block';
            
            // Update process button text
            updateProcessButton();
        }

        function startScanning() {
            if (!currentMode) {
                alert('Please select a mode first (Checkout or Checkin)');
                return;
            }
            
            if (!isScanning) {
                html5QrcodeScanner = new Html5QrcodeScanner(
                    "qr-reader", 
                    { 
                        fps: 5,
                        qrbox: {width: 250, height: 250},
                        rememberLastUsedCamera: true
                    }
                );
                html5QrcodeScanner.render(onScanSuccess, onScanFailure);
                
                document.getElementById('qr-reader').style.display = 'block';
                document.getElementById('scanner-status').style.display = 'none';
                document.getElementById('start-scan').style.display = 'none';
                document.getElementById('stop-scan').style.display = 'inline-block';
                isScanning = true;
            }
        }

        function stopScanning() {
            if (isScanning && html5QrcodeScanner) {
                html5QrcodeScanner.clear();
                document.getElementById('qr-reader').style.display = 'none';
                document.getElementById('scanner-status').style.display = 'block';
                document.getElementById('start-scan').style.display = 'inline-block';
                document.getElementById('stop-scan').style.display = 'none';
                isScanning = false;
            }
        }

        function onScanSuccess(decodedText, decodedResult) {
            const currentTime = Date.now();
            if (currentTime - lastScannedTime < scanCooldown) {
                return;
            }
            lastScannedTime = currentTime;
            
            // Extract asset_id from QR code URL
            const urlMatch = decodedText.match(/asset_id=([^&]+)/);
            if (!urlMatch) {
                showToast('error', 'Invalid QR Code');
                return;
            }
            
            const assetId = urlMatch[1];
            
            // Check if already in list
            if (scannedItems.has(assetId)) {
                showToast('warning', `${assetId} already in list`);
                return;
            }
            
            // Get asset info and add to list
            getAssetInfo(assetId);
        }

        function onScanFailure(error) {
            // Silent
        }

        function getAssetInfo(assetId) {
            const formData = new FormData();
            formData.append('ajax', '1');
            formData.append('action', 'get_asset_info');
            formData.append('asset_id', assetId);
            
            fetch('bulk_scanner_v2.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    addItemToList(data.asset);
                    showToast('success', `Added: ${data.asset.asset_name}`);
                } else {
                    showToast('error', `${assetId}: ${data.message}`);
                }
            })
            .catch(error => {
                showToast('error', 'Network error');
            });
        }

        function addItemToList(asset) {
            scannedItems.set(asset.asset_id, asset);
            updateScannedItemsDisplay();
        }

        function removeItemFromList(assetId) {
            scannedItems.delete(assetId);
            updateScannedItemsDisplay();
        }

        function updateScannedItemsDisplay() {
            const container = document.getElementById('scanned-items');
            const count = scannedItems.size;
            
            document.getElementById('item-count').textContent = count;
            
            if (count === 0) {
                container.innerHTML = `
                    <div class="text-center text-muted p-4">
                        <i class="fas fa-qrcode fa-2x"></i>
                        <p>Start scanning to add items to this list</p>
                    </div>
                `;
                document.getElementById('batch-actions').style.display = 'none';
            } else {
                let html = '';
                scannedItems.forEach((asset, assetId) => {
                    const statusColor = asset.status === 'available' ? 'success' : 
                                      asset.status === 'checked_out' ? 'warning' : 'secondary';
                    
                    html += `
                        <div class="scanned-item pending">
                            <div class="asset-info">
                                <strong>${asset.asset_name}</strong>
                                <small class="d-block text-muted">${asset.category} - ${asset.asset_id}</small>
                                <span class="badge bg-${statusColor}">${asset.status.replace('_', ' ')}</span>
                            </div>
                            <button class="btn btn-outline-danger btn-sm remove-btn" onclick="removeItemFromList('${assetId}')">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `;
                });
                
                container.innerHTML = html;
                document.getElementById('batch-actions').style.display = 'block';
            }
            
            updateProcessButton();
        }

        function updateProcessButton() {
            const btn = document.getElementById('process-btn');
            const count = scannedItems.size;
            
            if (currentMode === 'checkout') {
                btn.innerHTML = `<i class="fas fa-sign-out-alt"></i> Checkout ${count} Items`;
                btn.className = 'btn btn-success btn-lg';
            } else if (currentMode === 'checkin') {
                btn.innerHTML = `<i class="fas fa-sign-in-alt"></i> Check-in ${count} Items`;
                btn.className = 'btn btn-warning btn-lg';
            }
        }

        function clearScannedList() {
            if (scannedItems.size > 0 && !confirm('Clear all scanned items?')) {
                return;
            }
            scannedItems.clear();
            updateScannedItemsDisplay();
            document.getElementById('results-card').style.display = 'none';
        }

        function processBatch() {
            if (scannedItems.size === 0) {
                alert('No items to process');
                return;
            }
            
            if (!validateSettings()) {
                return;
            }
            
            if (!confirm(`Process ${scannedItems.size} items in ${currentMode} mode?`)) {
                return;
            }
            
            const formData = new FormData();
            formData.append('ajax', '1');
            formData.append('action', 'process_batch');
            formData.append('operation', 'bulk_' + currentMode);
            formData.append('asset_ids', JSON.stringify(Array.from(scannedItems.keys())));
            
            if (currentMode === 'checkout') {
                formData.append('borrower_name', document.getElementById('bulk_borrower').value);
                formData.append('expected_return_date', document.getElementById('bulk_return_date').value);
                formData.append('purpose', document.getElementById('bulk_purpose').value);
            } else {
                formData.append('condition', document.getElementById('bulk_condition').value);
                formData.append('notes', document.getElementById('bulk_notes').value);
            }
            
            // Show processing state
            document.getElementById('process-btn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            document.getElementById('process-btn').disabled = true;
            
            fetch('bulk_scanner_v2.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                showProcessingResults(data.results);
                scannedItems.clear();
                updateScannedItemsDisplay();
                
                // Re-enable button
                document.getElementById('process-btn').disabled = false;
                updateProcessButton();
            })
            .catch(error => {
                alert('Network error: ' + error.message);
                document.getElementById('process-btn').disabled = false;
                updateProcessButton();
            });
        }

        function showProcessingResults(results) {
            let successCount = 0;
            let errorCount = 0;
            let html = '';
            
            results.forEach(result => {
                if (result.success) {
                    successCount++;
                    html += `<div class="scanned-item success">
                        <div class="asset-info">
                            <strong>${result.asset_name || result.asset_id}</strong>
                            <small class="d-block text-muted">${result.message}</small>
                        </div>
                        <i class="fas fa-check-circle text-success"></i>
                    </div>`;
                } else {
                    errorCount++;
                    html += `<div class="scanned-item error">
                        <div class="asset-info">
                            <strong>${result.asset_name || result.asset_id}</strong>
                            <small class="d-block text-muted">${result.message}</small>
                        </div>
                        <i class="fas fa-times-circle text-danger"></i>
                    </div>`;
                }
            });
            
            html = `
                <div class="mb-3">
                    <span class="badge bg-success me-2">${successCount} Success</span>
                    <span class="badge bg-danger">${errorCount} Errors</span>
                </div>
                ${html}
            `;
            
            document.getElementById('processing-results').innerHTML = html;
            document.getElementById('results-card').style.display = 'block';
        }

        function validateSettings() {
            if (currentMode === 'checkout') {
                if (!document.getElementById('bulk_borrower').value) {
                    alert('Please select a borrower');
                    return false;
                }
                if (!document.getElementById('bulk_return_date').value) {
                    alert('Please set an expected return date');
                    return false;
                }
            } else if (currentMode === 'checkin') {
                if (!document.getElementById('bulk_condition').value) {
                    alert('Please select equipment condition');
                    return false;
                }
            }
            return true;
        }

        function showToast(type, message) {
            const toast = document.createElement('div');
            toast.className = `alert alert-${type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'danger'} alert-dismissible fade show position-fixed`;
            toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 300px;';
            toast.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 3000);
        }
    </script>
</body>
</html>