<?php

/**
 * Add Test Attendance Record
 * This adds a fake attendance record to test email notifications
 */

require_once __DIR__ . '/database/Database.php';

$sessionId = $_GET['session_id'] ?? null;

if (!$sessionId) {
    die("Please provide session_id in URL: ?session_id=23");
}

$db = Database::getInstance()->getConnection();

// Get session details
$stmt = $db->prepare("SELECT * FROM attendance_sessions WHERE id = :id");
$stmt->execute([':id' => $sessionId]);
$session = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$session) {
    die("Session not found!");
}

// Get a student who matches the subject
$stmt = $db->prepare("
    SELECT s.* 
    FROM students s
    JOIN subjects sub ON (
        s.program = sub.program 
        AND s.year_level = sub.year_level 
        AND s.section = sub.section
    )
    WHERE sub.id = :subject_id
    LIMIT 1
");
$stmt->execute([':subject_id' => $session['subject_id']]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("No matching student found for this subject!");
}

// Check if attendance already exists
$stmt = $db->prepare("
    SELECT * FROM attendance_records 
    WHERE session_id = :session_id AND student_id = :student_id
");
$stmt->execute([
    ':session_id' => $sessionId,
    ':student_id' => $student['id']
]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    echo "✅ Attendance record already exists!<br>";
    echo "Student: {$student['first_name']} {$student['last_name']}<br>";
    echo "Status: {$existing['status']}<br>";
    echo "Time In: {$existing['time_in']}<br>";
} else {
    // Add attendance record
    $stmt = $db->prepare("
        INSERT INTO attendance_records (session_id, student_id, time_in, status, created_at)
        VALUES (:session_id, :student_id, NOW(), 'present', NOW())
    ");
    $stmt->execute([
        ':session_id' => $sessionId,
        ':student_id' => $student['id']
    ]);

    echo "✅ Test attendance record added!<br>";
    echo "Student: {$student['first_name']} {$student['last_name']} ({$student['student_id']})<br>";
    echo "Status: Present<br>";
    echo "Time: " . date('Y-m-d H:i:s') . "<br>";
}

echo "<br><a href='debug_email_flow.php?session_id={$sessionId}'>→ Run Debugger Again</a>";
