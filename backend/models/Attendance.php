<?php
/**
 * Attendance Model
 * CICS Attendance System
 */

require_once __DIR__ . '/../database/Database.php';

class Attendance {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function createSession($data) {
        $sql = "INSERT INTO attendance_sessions 
                (subject_id, instructor_id, session_date, start_time, gps_latitude, gps_longitude) 
                VALUES (:subject_id, :instructor_id, :session_date, :start_time, :gps_latitude, :gps_longitude)";
        
        $params = [
            ':subject_id' => $data['subject_id'],
            ':instructor_id' => $data['instructor_id'],
            ':session_date' => $data['session_date'],
            ':start_time' => $data['start_time'],
            ':gps_latitude' => $data['gps_latitude'] ?? null,
            ':gps_longitude' => $data['gps_longitude'] ?? null
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function endSession($sessionId) {
        $sql = "UPDATE attendance_sessions 
                SET end_time = TIME(NOW()), status = 'ended' 
                WHERE id = :id";
        $this->db->query($sql, [':id' => $sessionId]);
        return true;
    }
    
    public function getActiveSession($subjectId, $date = null) {
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        $sql = "SELECT * FROM attendance_sessions 
                WHERE subject_id = :subject_id 
                AND session_date = :session_date 
                AND status = 'active' 
                ORDER BY start_time DESC 
                LIMIT 1";
        
        return $this->db->fetchOne($sql, [
            ':subject_id' => $subjectId,
            ':session_date' => $date
        ]);
    }
    
    public function getSessionById($sessionId) {
        $sql = "SELECT * FROM attendance_sessions WHERE id = :id LIMIT 1";
        return $this->db->fetchOne($sql, [':id' => $sessionId]);
    }
    
    public function markAttendance($data) {
        $sql = "INSERT INTO attendance_records 
                (session_id, student_id, time_in, status, gps_latitude, gps_longitude, device_fingerprint) 
                VALUES (:session_id, :student_id, :time_in, :status, :gps_latitude, :gps_longitude, :device_fingerprint)
                ON DUPLICATE KEY UPDATE 
                time_in = VALUES(time_in),
                status = VALUES(status),
                gps_latitude = VALUES(gps_latitude),
                gps_longitude = VALUES(gps_longitude)";
        
        $params = [
            ':session_id' => $data['session_id'],
            ':student_id' => $data['student_id'],
            ':time_in' => $data['time_in'],
            ':status' => $data['status'],
            ':gps_latitude' => $data['gps_latitude'] ?? null,
            ':gps_longitude' => $data['gps_longitude'] ?? null,
            ':device_fingerprint' => $data['device_fingerprint'] ?? null
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
    
    public function markTimeOut($sessionId, $studentId) {
        $sql = "UPDATE attendance_records 
                SET time_out = NOW() 
                WHERE session_id = :session_id AND student_id = :student_id";
        $this->db->query($sql, [
            ':session_id' => $sessionId,
            ':student_id' => $studentId
        ]);
        return true;
    }
    
    public function getRecords($filters = []) {
        $sql = "SELECT ar.*, 
                       s.student_id, s.first_name, s.last_name, s.program, s.section,
                       sub.code as subject_code, sub.name as subject_name,
                       as.session_date, as.start_time
                FROM attendance_records ar
                JOIN students s ON ar.student_id = s.id
                JOIN attendance_sessions as ON ar.session_id = as.id
                JOIN subjects sub ON as.subject_id = sub.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['student_id'])) {
            $sql .= " AND ar.student_id = :student_id";
            $params[':student_id'] = $filters['student_id'];
        }
        
        if (!empty($filters['session_id'])) {
            $sql .= " AND ar.session_id = :session_id";
            $params[':session_id'] = $filters['session_id'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND ar.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if (!empty($filters['start_date'])) {
            $sql .= " AND as.session_date >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $sql .= " AND as.session_date <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }
        
        if (!empty($filters['program'])) {
            $sql .= " AND s.program = :program";
            $params[':program'] = $filters['program'];
        }
        
        $sql .= " ORDER BY as.session_date DESC, ar.time_in DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT :limit";
            $params[':limit'] = (int)$filters['limit'];
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getSummary($filters = []) {
        $sql = "SELECT 
                    COUNT(DISTINCT ar.id) as total_records,
                    SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) as present,
                    SUM(CASE WHEN ar.status = 'late' THEN 1 ELSE 0 END) as late,
                    SUM(CASE WHEN ar.status = 'absent' THEN 1 ELSE 0 END) as absent
                FROM attendance_records ar
                JOIN attendance_sessions as ON ar.session_id = as.id
                JOIN students s ON ar.student_id = s.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['start_date'])) {
            $sql .= " AND as.session_date >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $sql .= " AND as.session_date <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }
        
        if (!empty($filters['program'])) {
            $sql .= " AND s.program = :program";
            $params[':program'] = $filters['program'];
        }
        
        return $this->db->fetchOne($sql, $params);
    }
}

