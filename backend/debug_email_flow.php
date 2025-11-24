<?php

/**
 * Advanced Email Debug - Check exact data flow
 */

require_once __DIR__ . '/database/Database.php';
require_once __DIR__ . '/services/EmailService.php';

header('Content-Type: text/html; charset=utf-8');

// Get session ID from URL
$sessionId = $_GET['session_id'] ?? null;

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Email Flow Debugger</title>
    <style>
        body {
            font-family: monospace;
            padding: 20px;
            background: #1e1e1e;
            color: #d4d4d4;
        }

        .section {
            background: #252526;
            padding: 15px;
            margin: 10px 0;
            border-left: 4px solid #007acc;
        }

        .success {
            border-left-color: #4ec9b0;
        }

        .error {
            border-left-color: #f48771;
        }

        .warning {
            border-left-color: #dcdcaa;
        }

        pre {
            background: #1e1e1e;
            padding: 10px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #3c3c3c;
        }

        th {
            background: #007acc;
            color: white;
        }

        input {
            padding: 8px;
            background: #3c3c3c;
            border: 1px solid #007acc;
            color: #d4d4d4;
        }

        button {
            padding: 10px 20px;
            background: #007acc;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background: #005a9e;
        }
    </style>
</head>

<body>
    <h1>🔍 Email Notification Flow Debugger</h1>

    <form method="GET">
        <label>Session ID: </label>
        <input type="number" name="session_id" value="<?php echo htmlspecialchars($sessionId ?? ''); ?>" required>
        <button type="submit">Debug This Session</button>
    </form>

    <?php if ($sessionId): ?>

        <?php
        $db = Database::getInstance()->getConnection();
        $emailService = new EmailService();

        // STEP 1: Check if session exists
        echo "<div class='section'>";
        echo "<h2>STEP 1: Session Details</h2>";

        $stmt = $db->prepare("
            SELECT 
                ats.*,
                sub.name as subject_name,
                sub.code as subject_code,
                sub.program as subject_program,
                sub.year_level as subject_year,
                sub.section as subject_section,
                CONCAT(i.first_name, ' ', i.last_name) as instructor_name
            FROM attendance_sessions ats
            JOIN subjects sub ON ats.subject_id = sub.id
            JOIN instructors i ON ats.instructor_id = i.id
            WHERE ats.id = :session_id
        ");
        $stmt->execute([':session_id' => $sessionId]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($session) {
            echo "<div class='success'>";
            echo "<strong>✅ Session Found</strong><br>";
            echo "Subject: {$session['subject_name']} ({$session['subject_code']})<br>";
            echo "Program: {$session['subject_program']}, Year: {$session['subject_year']}, Section: {$session['subject_section']}<br>";
            echo "Instructor: {$session['instructor_name']}<br>";
            echo "Status: {$session['status']}<br>";
            echo "</div>";
        } else {
            echo "<div class='error'><strong>❌ Session Not Found</strong></div>";
            echo "</div>";
            exit;
        }
        echo "</div>";

        // STEP 2: Check attendance records
        echo "<div class='section'>";
        echo "<h2>STEP 2: Attendance Records</h2>";

        $stmt = $db->prepare("
            SELECT 
                ar.*,
                s.id as student_db_id,
                s.student_id as student_number,
                CONCAT(s.first_name, ' ', s.last_name) as student_name,
                s.program as student_program,
                s.year_level as student_year,
                s.section as student_section
            FROM attendance_records ar
            JOIN students s ON ar.student_id = s.id
            WHERE ar.session_id = :session_id
        ");
        $stmt->execute([':session_id' => $sessionId]);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($records)) {
            echo "<div class='warning'><strong>⚠️ No Attendance Records</strong></div>";
        } else {
            echo "<div class='success'><strong>✅ Found " . count($records) . " attendance record(s)</strong></div>";
            echo "<table>";
            echo "<tr><th>Student</th><th>Student Number</th><th>Program</th><th>Year</th><th>Section</th><th>Status</th><th>Time In</th></tr>";
            foreach ($records as $record) {
                echo "<tr>";
                echo "<td>{$record['student_name']}</td>";
                echo "<td>{$record['student_number']}</td>";
                echo "<td>{$record['student_program']}</td>";
                echo "<td>{$record['student_year']}</td>";
                echo "<td>{$record['student_section']}</td>";
                echo "<td>{$record['status']}</td>";
                echo "<td>{$record['time_in']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        echo "</div>";

        // STEP 3: Check parents using the ACTUAL query from EmailService
        echo "<div class='section'>";
        echo "<h2>STEP 3: Parents Query (EmailService Logic)</h2>";

        $sql = "SELECT 
                p.id as parent_id,
                p.first_name as parent_first_name,
                p.last_name as parent_last_name,
                p.email as parent_email,
                p.contact_number as parent_contact,
                p.relationship,
                s.id as student_id,
                s.student_id as student_number,
                s.first_name as student_first_name,
                s.last_name as student_last_name,
                s.program,
                s.year_level,
                s.section
            FROM parents p
            INNER JOIN students s ON p.student_id = s.id
            INNER JOIN subjects sub ON (
                s.program = sub.program 
                AND s.year_level = sub.year_level 
                AND s.section = sub.section
            )
            INNER JOIN attendance_sessions sess ON sub.id = sess.subject_id
            WHERE sess.id = :session_id
            AND p.email IS NOT NULL
            AND p.email != ''";

        $stmt = $db->prepare($sql);
        $stmt->execute([':session_id' => $sessionId]);
        $parents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($parents)) {
            echo "<div class='error'>";
            echo "<strong>❌ NO PARENTS FOUND!</strong><br>";
            echo "This is why emails aren't being sent!<br><br>";
            echo "<strong>Possible reasons:</strong><br>";
            echo "1. Student's program/year/section doesn't match the subject<br>";
            echo "2. Parent record doesn't exist for the student<br>";
            echo "3. Parent doesn't have an email address<br>";
            echo "</div>";

            // Debug: Check parents without the complex join
            echo "<h3>Debug: All Parents for Students in This Session</h3>";
            $stmt = $db->prepare("
                SELECT DISTINCT
                    p.*,
                    s.student_id as student_number,
                    CONCAT(s.first_name, ' ', s.last_name) as student_name,
                    s.program as student_program,
                    s.year_level as student_year,
                    s.section as student_section
                FROM parents p
                JOIN students s ON p.student_id = s.id
                JOIN attendance_records ar ON ar.student_id = s.id
                WHERE ar.session_id = :session_id
            ");
            $stmt->execute([':session_id' => $sessionId]);
            $allParents = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($allParents)) {
                echo "<div class='error'><strong>❌ No parent records exist for students in this session</strong></div>";
            } else {
                echo "<div class='warning'><strong>⚠️ Found " . count($allParents) . " parent(s) but they don't match the EmailService query</strong></div>";
                echo "<table>";
                echo "<tr><th>Parent Name</th><th>Email</th><th>Student</th><th>Student Program</th><th>Student Year</th><th>Student Section</th></tr>";
                foreach ($allParents as $parent) {
                    $emailStatus = $parent['email'] ? '✅' : '❌ NO EMAIL';
                    echo "<tr>";
                    echo "<td>{$parent['first_name']} {$parent['last_name']}</td>";
                    echo "<td>{$emailStatus} {$parent['email']}</td>";
                    echo "<td>{$parent['student_name']}</td>";
                    echo "<td>{$parent['student_program']}</td>";
                    echo "<td>{$parent['student_year']}</td>";
                    echo "<td>{$parent['student_section']}</td>";
                    echo "</tr>";
                }
                echo "</table>";

                // Show subject requirements
                echo "<h3>Subject Requirements:</h3>";
                echo "<div class='warning'>";
                echo "Subject expects: Program={$session['subject_program']}, Year={$session['subject_year']}, Section={$session['subject_section']}<br>";
                echo "Students must match ALL THREE to be included in email notifications!";
                echo "</div>";
            }
        } else {
            echo "<div class='success'><strong>✅ Found " . count($parents) . " parent(s) to notify</strong></div>";
            echo "<table>";
            echo "<tr><th>Parent Name</th><th>Email</th><th>Student</th><th>Relationship</th></tr>";
            foreach ($parents as $parent) {
                echo "<tr>";
                echo "<td>{$parent['parent_first_name']} {$parent['parent_last_name']}</td>";
                echo "<td>{$parent['parent_email']}</td>";
                echo "<td>{$parent['student_first_name']} {$parent['student_last_name']}</td>";
                echo "<td>{$parent['relationship']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        echo "</div>";

        // STEP 4: Test the actual API endpoint
        echo "<div class='section'>";
        echo "<h2>STEP 4: API Endpoint Test</h2>";
        echo "<p>Testing: <code>GET /backend/api/email/session-notifications?session_id={$sessionId}</code></p>";

        $apiUrl = "http://localhost/cics-attendance-system/backend/api/email/session-notifications?session_id={$sessionId}";
        echo "<p><a href='{$apiUrl}' target='_blank'>Click here to test API endpoint</a></p>";
        echo "</div>";

        ?>

    <?php endif; ?>

</body>

</html>