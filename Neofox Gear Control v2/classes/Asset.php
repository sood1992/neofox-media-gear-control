<?php
// Enhanced Asset.php with better error handling and validation
class Asset {
    private $conn;
    private $table_name = "assets";
    private $errors = [];

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getErrors() {
        return $this->errors;
    }

    private function validateAssetData($data) {
        $this->errors = [];
        
        if (empty($data['asset_name'])) {
            $this->errors[] = "Asset name is required";
        }
        
        if (empty($data['category'])) {
            $this->errors[] = "Category is required";
        }
        
        if (empty($data['asset_id'])) {
            $this->errors[] = "Asset ID is required";
        } elseif ($this->assetIdExists($data['asset_id'])) {
            $this->errors[] = "Asset ID already exists";
        }
        
        return empty($this->errors);
    }

    private function assetIdExists($asset_id) {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE asset_id = :asset_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":asset_id", $asset_id);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function create($data) {
        if (!$this->validateAssetData($data)) {
            return false;
        }

        try {
            $this->conn->beginTransaction();
            
            $query = "INSERT INTO " . $this->table_name . " 
                      (asset_id, asset_name, category, description, serial_number, qr_code, condition_status, notes) 
                      VALUES (:asset_id, :asset_name, :category, :description, :serial_number, :qr_code, :condition_status, :notes)";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(":asset_id", $data['asset_id']);
            $stmt->bindParam(":asset_name", $data['asset_name']);
            $stmt->bindParam(":category", $data['category']);
            $stmt->bindParam(":description", $data['description']);
            $stmt->bindParam(":serial_number", $data['serial_number']);
            $stmt->bindParam(":qr_code", $data['qr_code']);
            $stmt->bindParam(":condition_status", $data['condition_status']);
            $stmt->bindParam(":notes", $data['notes']);
            
            $result = $stmt->execute();
            
            if ($result) {
                // Log asset creation
                $this->logAssetAction($data['asset_id'], 'created', 'Asset created in system');
                $this->conn->commit();
                return true;
            } else {
                $this->conn->rollBack();
                $this->errors[] = "Failed to create asset";
                return false;
            }
        } catch (PDOException $e) {
            $this->conn->rollBack();
            $this->errors[] = "Database error: " . $e->getMessage();
            return false;
        }
    }

    public function update($id, $data) {
        try {
            $this->conn->beginTransaction();
            
            $query = "UPDATE " . $this->table_name . " 
                      SET asset_name = :asset_name, category = :category, description = :description, 
                          serial_number = :serial_number, condition_status = :condition_status, 
                          notes = :notes, updated_at = NOW()
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(":id", $id);
            $stmt->bindParam(":asset_name", $data['asset_name']);
            $stmt->bindParam(":category", $data['category']);
            $stmt->bindParam(":description", $data['description']);
            $stmt->bindParam(":serial_number", $data['serial_number']);
            $stmt->bindParam(":condition_status", $data['condition_status']);
            $stmt->bindParam(":notes", $data['notes']);
            
            $result = $stmt->execute();
            
            if ($result) {
                // Get asset_id for logging
                $asset = $this->getById($id);
                $this->logAssetAction($asset['asset_id'], 'updated', 'Asset information updated');
                $this->conn->commit();
                return true;
            } else {
                $this->conn->rollBack();
                $this->errors[] = "Failed to update asset";
                return false;
            }
        } catch (PDOException $e) {
            $this->conn->rollBack();
            $this->errors[] = "Database error: " . $e->getMessage();
            return false;
        }
    }

    public function delete($id) {
        try {
            $this->conn->beginTransaction();
            
            // Get asset info before deletion for logging
            $asset = $this->getById($id);
            if (!$asset) {
                $this->errors[] = "Asset not found";
                return false;
            }
            
            // Check if asset is currently checked out
            if ($asset['status'] == 'checked_out') {
                $this->errors[] = "Cannot delete asset that is currently checked out";
                return false;
            }
            
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);
            
            $result = $stmt->execute();
            
            if ($result) {
                $this->logAssetAction($asset['asset_id'], 'deleted', 'Asset removed from system');
                $this->conn->commit();
                return true;
            } else {
                $this->conn->rollBack();
                $this->errors[] = "Failed to delete asset";
                return false;
            }
        } catch (PDOException $e) {
            $this->conn->rollBack();
            $this->errors[] = "Database error: " . $e->getMessage();
            return false;
        }
    }

    public function checkOut($asset_id, $borrower, $expected_return, $purpose) {
        try {
            $this->conn->beginTransaction();
            
            // Validate asset exists and is available
            $asset = $this->getByAssetId($asset_id);
            if (!$asset) {
                $this->errors[] = "Asset not found";
                return false;
            }
            
            if ($asset['status'] !== 'available') {
                $this->errors[] = "Asset is not available for checkout";
                return false;
            }
            
            // Validate return date is in the future
            if (strtotime($expected_return) <= time()) {
                $this->errors[] = "Expected return date must be in the future";
                return false;
            }
            
            $query = "UPDATE " . $this->table_name . " 
                      SET status = 'checked_out', current_borrower = :borrower, 
                          checkout_date = NOW(), expected_return_date = :expected_return 
                      WHERE asset_id = :asset_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":asset_id", $asset_id);
            $stmt->bindParam(":borrower", $borrower);
            $stmt->bindParam(":expected_return", $expected_return);
            
            if ($stmt->execute()) {
                $this->logTransaction($asset_id, $borrower, 'checkout', $purpose);
                $this->logAssetAction($asset_id, 'checked_out', "Checked out to {$borrower}");
                
                // Send email notification
                EmailNotification::sendCheckoutConfirmation(
                    'admin@neofoxmedia.com', 
                    $asset['asset_name'], 
                    $borrower, 
                    $expected_return
                );
                
                $this->conn->commit();
                return true;
            } else {
                $this->conn->rollBack();
                $this->errors[] = "Failed to check out asset";
                return false;
            }
        } catch (PDOException $e) {
            $this->conn->rollBack();
            $this->errors[] = "Database error: " . $e->getMessage();
            return false;
        }
    }

    public function checkIn($asset_id, $condition, $notes) {
        try {
            $this->conn->beginTransaction();
            
            // Get asset info and validate
            $asset = $this->getByAssetId($asset_id);
            if (!$asset) {
                $this->errors[] = "Asset not found";
                return false;
            }
            
            if ($asset['status'] !== 'checked_out') {
                $this->errors[] = "Asset is not currently checked out";
                return false;
            }
            
            $borrower = $asset['current_borrower'];
            
            $query = "UPDATE " . $this->table_name . " 
                      SET status = 'available', current_borrower = NULL, 
                          checkout_date = NULL, expected_return_date = NULL, 
                          condition_status = :condition 
                      WHERE asset_id = :asset_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":asset_id", $asset_id);
            $stmt->bindParam(":condition", $condition);
            
            if ($stmt->execute()) {
                $this->logTransaction($asset_id, $borrower, 'checkin', $notes, $condition);
                $this->logAssetAction($asset_id, 'checked_in', "Returned by {$borrower} in {$condition} condition");
                
                $this->conn->commit();
                return true;
            } else {
                $this->conn->rollBack();
                $this->errors[] = "Failed to check in asset";
                return false;
            }
        } catch (PDOException $e) {
            $this->conn->rollBack();
            $this->errors[] = "Database error: " . $e->getMessage();
            return false;
        }
    }

    // Enhanced search functionality
    public function search($term, $category = null, $status = null) {
        $conditions = [];
        $params = [];
        
        if (!empty($term)) {
            $conditions[] = "(asset_name LIKE :term OR asset_id LIKE :term OR description LIKE :term)";
            $params[':term'] = "%{$term}%";
        }
        
        if (!empty($category)) {
            $conditions[] = "category = :category";
            $params[':category'] = $category;
        }
        
        if (!empty($status)) {
            $conditions[] = "status = :status";
            $params[':status'] = $status;
        }
        
        $where_clause = empty($conditions) ? "" : "WHERE " . implode(" AND ", $conditions);
        
        $query = "SELECT * FROM " . $this->table_name . " {$where_clause} ORDER BY asset_name";
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get asset history
    public function getAssetHistory($asset_id) {
        $query = "SELECT t.*, a.asset_name 
                  FROM transactions t 
                  JOIN assets a ON t.asset_id = a.asset_id 
                  WHERE t.asset_id = :asset_id 
                  ORDER BY t.transaction_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":asset_id", $asset_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Enhanced statistics with trends
    public function getDetailedStats() {
        $query = "SELECT 
                    COUNT(*) as total_assets,
                    SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
                    SUM(CASE WHEN status = 'checked_out' THEN 1 ELSE 0 END) as checked_out,
                    SUM(CASE WHEN status = 'maintenance' THEN 1 ELSE 0 END) as maintenance,
                    SUM(CASE WHEN status = 'lost' THEN 1 ELSE 0 END) as lost,
                    SUM(CASE WHEN status = 'checked_out' AND expected_return_date < NOW() THEN 1 ELSE 0 END) as overdue,
                    AVG(CASE WHEN status = 'checked_out' THEN DATEDIFF(NOW(), checkout_date) ELSE NULL END) as avg_checkout_days
                  FROM " . $this->table_name;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get popular assets (most frequently checked out)
    public function getPopularAssets($limit = 10) {
        $query = "SELECT a.asset_id, a.asset_name, a.category, COUNT(t.id) as checkout_count
                  FROM assets a 
                  LEFT JOIN transactions t ON a.asset_id = t.asset_id AND t.transaction_type = 'checkout'
                  GROUP BY a.id 
                  ORDER BY checkout_count DESC 
                  LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByAssetId($asset_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE asset_id = :asset_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":asset_id", $asset_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY asset_name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOverdueAssets() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE status = 'checked_out' AND expected_return_date < NOW()
                  ORDER BY expected_return_date ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCheckedOutAssets() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE status = 'checked_out' ORDER BY checkout_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAssetStats() {
        return $this->getDetailedStats();
    }

    private function logTransaction($asset_id, $borrower, $type, $notes, $condition = null) {
        $query = "INSERT INTO transactions (asset_id, borrower_name, transaction_type, purpose, condition_on_return, notes) 
                  VALUES (:asset_id, :borrower, :type, :purpose, :condition, :notes)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":asset_id", $asset_id);
        $stmt->bindParam(":borrower", $borrower);
        $stmt->bindParam(":type", $type);
        $stmt->bindParam(":purpose", $notes);
        $stmt->bindParam(":condition", $condition);
        $stmt->bindParam(":notes", $notes);
        $stmt->execute();
    }

    private function logAssetAction($asset_id, $action, $details) {
        // This could be expanded to a separate audit log table
        error_log("ASSET ACTION: {$asset_id} - {$action} - {$details}");
    }
}
?>