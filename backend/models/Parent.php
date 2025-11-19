<?php
/**
 * Parent Model
 * CICS Attendance System
 */

require_once __DIR__ . '/../database/Database.php';

class ParentModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create($data) {
        $sql = "INSERT INTO parents 
                (student_id, first_name, last_name, email, contact_number, relationship) 
                VALUES (:student_id, :first_name, :last_name, :email, :contact_number, :relationship)";
        
        $params = [
            ':student_id' => $data['student_id'],
            ':first_name' => $data['first_name'],
            ':last_name' => $data['last_name'],
            ':email' => $data['email'],
            ':contact_number' => $data['contact_number'],
            ':relationship' => $data['relationship']
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function findByStudentId($studentId) {
        $sql = "SELECT * FROM parents WHERE student_id = :student_id LIMIT 1";
        return $this->db->fetchOne($sql, [':student_id' => $studentId]);
    }
    
    public function findById($id) {
        $sql = "SELECT p.*, s.student_id, s.first_name as student_first_name, s.last_name as student_last_name
                FROM parents p
                JOIN students s ON p.student_id = s.id
                WHERE p.id = :id LIMIT 1";
        return $this->db->fetchOne($sql, [':id' => $id]);
    }
    
    public function update($id, $data) {
        $fields = [];
        $params = [':id' => $id];
        
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
            $params[":$key"] = $value;
        }
        
        $sql = "UPDATE parents SET " . implode(', ', $fields) . " WHERE id = :id";
        $this->db->query($sql, $params);
        return true;
    }
}

