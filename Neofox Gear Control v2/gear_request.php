<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config/database.php';
require_once 'classes/Asset.php';  // This already contains GearRequest class
// require_once 'classes/GearRequest.php';  // Remove this line

$database = new Database();
$db = $database->getConnection();
$gear_request = new GearRequest($db);
$asset = new Asset($db);

// Get all available assets for the dropdown
$available_assets = $asset->getAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle multiple selected assets
    $selected_assets = isset($_POST['required_items']) ? $_POST['required_items'] : [];
    $assets_text = implode(', ', $selected_assets);
    
    $data = [
        'requester_name' => $_POST['requester_name'],
        'requester_email' => $_POST['requester_email'],
        'required_items' => $assets_text,
        'request_dates' => $_POST['request_dates'],
        'purpose' => $_POST['purpose']
    ];
    
    if ($gear_request->create($data)) {
        $success = "Gear request submitted successfully!";
        // Send notification to admin
        // EmailNotification::sendEmail('rishabh@neofoxmedia.com', 'New Gear Request - ' . $data['requester_name'], "A new gear request has been submitted");
    } else {
        $error = "Failed to submit gear request.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Gear - Neofox Gear Control</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .equipment-grid {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            background-color: #f8f9fa;
        }
        .equipment-item {
            display: flex;
            justify-content: between;
            align-items: center;
            padding: 10px;
            margin: 5px 0;
            background: white;
            border-radius: 5px;
            border: 1px solid #eee;
        }
        .equipment-item input[type="checkbox"] {
            margin-right: 10px;
        }
        .equipment-status {
            font-size: 0.8rem;
            margin-left: auto;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h4><i class="fas fa-calendar-alt"></i> Request Equipment</h4>
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
                                        <label for="requester_name" class="form-label">Your Name *</label>
                                        <input type="text" class="form-control" id="requester_name" name="requester_name" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="requester_email" class="form-label">Your Email</label>
                                        <input type="email" class="form-control" id="requester_email" name="requester_email">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Required Equipment * <small class="text-muted">(Select multiple items)</small></label>
                                <div class="equipment-grid">
                                    <?php foreach ($available_assets as $asset_item): ?>
                                    <div class="equipment-item">
                                        <input type="checkbox" name="required_items[]" value="<?php echo htmlspecialchars($asset_item['asset_name'] . ' (' . $asset_item['asset_id'] . ')'); ?>" id="asset_<?php echo $asset_item['id']; ?>">
                                        <label for="asset_<?php echo $asset_item['id']; ?>" class="mb-0 flex-grow-1">
                                            <strong><?php echo htmlspecialchars($asset_item['asset_name']); ?></strong>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($asset_item['category'] . ' - ' . $asset_item['asset_id']); ?></small>
                                        </label>
                                        <span class="equipment-status">
                                            <?php
                                            $status_colors = [
                                                'available' => 'success',
                                                'checked_out' => 'warning',
                                                'maintenance' => 'info',
                                                'lost' => 'danger'
                                            ];
                                            ?>
                                            <span class="badge bg-<?php echo $status_colors[$asset_item['status']]; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $asset_item['status'])); ?>
                                            </span>
                                        </span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <small class="form-text text-muted">Select the equipment you need for your project</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="request_dates" class="form-label">Requested Dates *</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="start_date" class="form-label">Start Date</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="end_date" class="form-label">End Date</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date" required>
                                    </div>
                                </div>
                                <input type="hidden" id="request_dates" name="request_dates">
                            </div>
                            
                            <div class="mb-3">
                                <label for="purpose" class="form-label">Purpose / Project Details</label>
                                <textarea class="form-control" id="purpose" name="purpose" rows="3" 
                                    placeholder="Brief description of your project and how the equipment will be used"></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-info w-100">
                                        <i class="fas fa-paper-plane"></i> Submit Request
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <a href="index.php" class="btn btn-secondary w-100">
                                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Combine start and end dates into request_dates field
        document.getElementById('start_date').addEventListener('change', updateRequestDates);
        document.getElementById('end_date').addEventListener('change', updateRequestDates);
        
        function updateRequestDates() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            
            if (startDate && endDate) {
                const startFormatted = new Date(startDate).toLocaleDateString();
                const endFormatted = new Date(endDate).toLocaleDateString();
                document.getElementById('request_dates').value = startFormatted + ' to ' + endFormatted;
            }
        }
    </script>
</body>
</html>