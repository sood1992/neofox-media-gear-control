<?php
// classes/GearRequest.php
class GearRequest {
    private $conn;
    private $table_name = "gear_requests";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (requester_name, requester_email, required_items, request_dates, purpose) 
                  VALUES (:name, :email, :items, :dates, :purpose)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":name", $data['requester_name']);
        $stmt->bindParam(":email", $data['requester_email']);
        $stmt->bindParam(":items", $data['required_items']);
        $stmt->bindParam(":dates", $data['request_dates']);
        $stmt->bindParam(":purpose", $data['purpose']);
        
        return $stmt->execute();
    }

    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $status, $admin_notes = '') {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = :status, admin_notes = :notes 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":notes", $admin_notes);
        
        return $stmt->execute();
    }
}
?>