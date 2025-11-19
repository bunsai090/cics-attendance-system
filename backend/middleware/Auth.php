<?php
/**
 * Authentication Middleware
 * CICS Attendance System
 */

class Auth {
    public static function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            $config = require __DIR__ . '/../config/app.php';
            session_name($config['session']['name']);
            session_set_cookie_params([
                'lifetime' => $config['session']['lifetime'],
                'path' => '/',
                'domain' => '',
                'secure' => $config['session']['secure'],
                'httponly' => $config['session']['httponly']
            ]);
            session_start();
        }
    }
    
    public static function login($userId, $role, $userData = []) {
        self::startSession();
        $_SESSION['user_id'] = $userId;
        $_SESSION['role'] = $role;
        $_SESSION['logged_in'] = true;
        $_SESSION['user_data'] = $userData;
        $_SESSION['login_time'] = time();
    }
    
    public static function logout() {
        self::startSession();
        session_unset();
        session_destroy();
    }
    
    public static function check() {
        self::startSession();
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    public static function user() {
        self::startSession();
        return $_SESSION['user_data'] ?? null;
    }
    
    public static function userId() {
        self::startSession();
        return $_SESSION['user_id'] ?? null;
    }
    
    public static function role() {
        self::startSession();
        return $_SESSION['role'] ?? null;
    }
    
    public static function requireAuth() {
        if (!self::check()) {
            require_once __DIR__ . '/../utils/Response.php';
            Response::unauthorized('Authentication required');
        }
    }
    
    public static function requireRole($allowedRoles) {
        self::requireAuth();
        
        if (!is_array($allowedRoles)) {
            $allowedRoles = [$allowedRoles];
        }
        
        $userRole = self::role();
        
        if (!in_array($userRole, $allowedRoles)) {
            require_once __DIR__ . '/../utils/Response.php';
            Response::forbidden('Insufficient permissions');
        }
    }
    
    public static function requireAdmin() {
        self::requireRole('admin');
    }
    
    public static function requireInstructor() {
        self::requireRole(['admin', 'instructor']);
    }
}

