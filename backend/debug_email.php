<?php

/**
 * Email Notification Debug Page
 * This page helps diagnose why emails aren't being sent
 */

require_once __DIR__ . '/database/Database.php';
require_once __DIR__ . '/services/EmailService.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Notification Debugger</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #f5f5f5;
        }

        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333;
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
        }

        h2 {
            color: #555;
            margin-top: 30px;
            border-left: 4px solid #007bff;
            padding-left: 10px;
        }

        .check {
            background: #f8f9fa;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
            border-left: 4px solid #6c757d;
        }

        .check.success {
            background: #d4edda;
            border-left-color: #28a745;
        }

        .check.error {
            background: #f8d7da;
            border-left-color: #dc3545;
        }

        .check.warning {
            background: #fff3cd;
            border-left-color: #ffc107;
        }

        .check-title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .check-detail {
            font-size: 14px;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #007bff;
            color: white;
        }

        tr:hover {
            background: #f5f5f5;
        }

        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }

        .badge-success {
            background: #28a745;
            color: white;
        }

        .badge-danger {
            background: #dc3545;
            color: white;
        }

        .badge-warning {
            background: #ffc107;
            color: #333;
        }

        .badge-info {
            background: #17a2b8;
            color: white;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>📧 Email Notification System Debugger</h1>
        <p>This page checks all components of the email notification system.</p>

        <?php
        $db = Database::getInstance()->getConnection();
        $checks = [];
        $errors = [];
        $warnings = [];

        // CHECK 1: Database Tables
        echo "<h2>1️⃣ Database Tables</h2>";

        $tables = ['parents', 'students', 'attendance_sessions', 'attendance_records', 'email_notifications'];
        foreach ($tables as $table) {
            try {
                $stmt = $db->query("SELECT COUNT(*) as count FROM $table");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $count = $result['count'];

                echo "<div class='check success'>";
                echo "<div class='check-title'>✅ Table: $table</div>";
                echo "<div class='check-detail'>Records: $count</div>";
                echo "</div>";
            } catch (PDOException $e) {
                echo "<div class='check error'>";
                echo "<div class='check-title'>❌ Table: $table</div>";
                echo "<div class='check-detail'>Error: " . $e->getMessage() . "</div>";
                echo "</div>";
                $errors[] = "Table $table is missing or has issues";
            }
        }

        // CHECK 2: Parents with Email Addresses
        echo "<h2>2️⃣ Parents with Email Addresses</h2>";

        try {
            $stmt = $db->query("
                SELECT COUNT(*) as total,
                       SUM(CASE WHEN email IS NOT NULL AND email != '' THEN 1 ELSE 0 END) as with_email
                FROM parents
            ");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['with_email'] > 0) {
                echo "<div class='check success'>";
                echo "<div class='check-title'>✅ Parents with Email</div>";
                echo "<div class='check-detail'>{$result['with_email']} out of {$result['total']} parents have email addresses</div>";
                echo "</div>";
            } else {
                echo "<div class='check error'>";
                echo "<div class='check-title'>❌ No Parents with Email</div>";
                echo "<div class='check-detail'>Total parents: {$result['total']}, but NONE have email addresses!</div>";
                echo "</div>";
                $errors[] = "No parent email addresses found in database";
            }

            // Show sample parents
            $stmt = $db->query("
                SELECT p.id, p.email, CONCAT(s.first_name, ' ', s.last_name) as student_name
                FROM parents p
                JOIN students s ON p.student_id = s.id
                WHERE p.email IS NOT NULL AND p.email != ''
                LIMIT 5
            ");
            $parents = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($parents)) {
                echo "<h3>Sample Parents:</h3>";
                echo "<table>";
                echo "<tr><th>Parent ID</th><th>Email</th><th>Student Name</th></tr>";
                foreach ($parents as $parent) {
                    echo "<tr>";
                    echo "<td>{$parent['id']}</td>";
                    echo "<td>{$parent['email']}</td>";
                    echo "<td>{$parent['student_name']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        } catch (PDOException $e) {
            echo "<div class='check error'>";
            echo "<div class='check-title'>❌ Error checking parents</div>";
            echo "<div class='check-detail'>" . $e->getMessage() . "</div>";
            echo "</div>";
        }

        // CHECK 3: Recent Sessions
        echo "<h2>3️⃣ Recent Attendance Sessions</h2>";

        try {
            $stmt = $db->query("
                SELECT 
                    ats.id,
                    ats.status,
                    ats.session_date,
                    ats.start_time,
                    ats.end_time,
                    sub.name as subject_name,
                    sub.code as subject_code,
                    CONCAT(i.first_name, ' ', i.last_name) as instructor_name,
                    (SELECT COUNT(*) FROM attendance_records ar WHERE ar.session_id = ats.id) as student_count
                FROM attendance_sessions ats
                JOIN subjects sub ON ats.subject_id = sub.id
                JOIN instructors i ON ats.instructor_id = i.id
                ORDER BY ats.session_date DESC, ats.id DESC
                LIMIT 5
            ");
            $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($sessions)) {
                echo "<table>";
                echo "<tr><th>Session ID</th><th>Subject</th><th>Date</th><th>Status</th><th>Students</th><th>Instructor</th></tr>";
                foreach ($sessions as $session) {
                    $statusClass = $session['status'] === 'active' ? 'success' : 'info';
                    echo "<tr>";
                    echo "<td>{$session['id']}</td>";
                    echo "<td>{$session['subject_name']} ({$session['subject_code']})</td>";
                    echo "<td>{$session['session_date']}</td>";
                    echo "<td><span class='badge badge-{$statusClass}'>{$session['status']}</span></td>";
                    echo "<td>{$session['student_count']}</td>";
                    echo "<td>{$session['instructor_name']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<div class='check warning'>";
                echo "<div class='check-title'>⚠️ No Sessions Found</div>";
                echo "<div class='check-detail'>No attendance sessions in the database</div>";
                echo "</div>";
            }
        } catch (PDOException $e) {
            echo "<div class='check error'>";
            echo "<div class='check-title'>❌ Error checking sessions</div>";
            echo "<div class='check-detail'>" . $e->getMessage() . "</div>";
            echo "</div>";
        }

        // CHECK 4: Email Notifications Log
        echo "<h2>4️⃣ Email Notification Logs</h2>";

        try {
            $stmt = $db->query("
                SELECT 
                    en.*,
                    p.email as parent_email,
                    CONCAT(s.first_name, ' ', s.last_name) as student_name
                FROM email_notifications en
                JOIN parents p ON en.parent_id = p.id
                JOIN students s ON en.student_id = s.id
                ORDER BY en.created_at DESC
                LIMIT 10
            ");
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($notifications)) {
                echo "<table>";
                echo "<tr><th>ID</th><th>Parent Email</th><th>Student</th><th>Type</th><th>Status</th><th>Created</th><th>Sent</th></tr>";
                foreach ($notifications as $notif) {
                    $statusClass = $notif['status'] === 'sent' ? 'success' : ($notif['status'] === 'failed' ? 'danger' : 'warning');
                    echo "<tr>";
                    echo "<td>{$notif['id']}</td>";
                    echo "<td>{$notif['parent_email']}</td>";
                    echo "<td>{$notif['student_name']}</td>";
                    echo "<td>{$notif['type']}</td>";
                    echo "<td><span class='badge badge-{$statusClass}'>{$notif['status']}</span></td>";
                    echo "<td>{$notif['created_at']}</td>";
                    echo "<td>" . ($notif['sent_at'] ?? 'Not sent') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<div class='check warning'>";
                echo "<div class='check-title'>⚠️ No Email Logs</div>";
                echo "<div class='check-detail'>No email notifications have been logged yet. This is normal if you haven't ended any sessions.</div>";
                echo "</div>";
            }
        } catch (PDOException $e) {
            echo "<div class='check error'>";
            echo "<div class='check-title'>❌ Error checking email logs</div>";
            echo "<div class='check-detail'>" . $e->getMessage() . "</div>";
            echo "</div>";
        }

        // CHECK 5: EmailJS Configuration
        echo "<h2>5️⃣ EmailJS Configuration</h2>";

        $configFile = __DIR__ . '/../frontend/assets/js/emailjs-parent-config.js';
        if (file_exists($configFile)) {
            $configContent = file_get_contents($configFile);

            // Check for placeholder values
            if (strpos($configContent, 'YOUR_SERVICE_ID') !== false) {
                echo "<div class='check error'>";
                echo "<div class='check-title'>❌ EmailJS Not Configured</div>";
                echo "<div class='check-detail'>Service ID still has placeholder value</div>";
                echo "</div>";
                $errors[] = "EmailJS service ID not configured";
            } elseif (strpos($configContent, 'service_2dr6r2e') !== false) {
                echo "<div class='check success'>";
                echo "<div class='check-title'>✅ EmailJS Service ID Configured</div>";
                echo "<div class='check-detail'>Service ID: service_2dr6r2e</div>";
                echo "</div>";
            }

            if (strpos($configContent, 'YOUR_PUBLIC_KEY') !== false) {
                echo "<div class='check error'>";
                echo "<div class='check-title'>❌ EmailJS Not Configured</div>";
                echo "<div class='check-detail'>Public key still has placeholder value</div>";
                echo "</div>";
                $errors[] = "EmailJS public key not configured";
            } elseif (strpos($configContent, '2VclqPtJ0av9LLc9-') !== false) {
                echo "<div class='check success'>";
                echo "<div class='check-title'>✅ EmailJS Public Key Configured</div>";
                echo "<div class='check-detail'>Public Key: 2VclqPtJ0av9LLc9-</div>";
                echo "</div>";
            }

            // Check template IDs
            if (strpos($configContent, 'template_s3xyad9') !== false) {
                echo "<div class='check success'>";
                echo "<div class='check-title'>✅ Attended Template Configured</div>";
                echo "<div class='check-detail'>Template ID: template_s3xyad9</div>";
                echo "</div>";
            }

            if (strpos($configContent, 'template_xgdr6y7') !== false) {
                echo "<div class='check success'>";
                echo "<div class='check-title'>✅ Absent Template Configured</div>";
                echo "<div class='check-detail'>Template ID: template_xgdr6y7</div>";
                echo "</div>";
            }
        } else {
            echo "<div class='check error'>";
            echo "<div class='check-title'>❌ Configuration File Missing</div>";
            echo "<div class='check-detail'>File not found: emailjs-parent-config.js</div>";
            echo "</div>";
            $errors[] = "EmailJS configuration file missing";
        }

        // SUMMARY
        echo "<h2>📊 Summary</h2>";

        if (empty($errors)) {
            echo "<div class='check success'>";
            echo "<div class='check-title'>✅ All Checks Passed!</div>";
            echo "<div class='check-detail'>The system appears to be configured correctly.</div>";
            echo "</div>";

            echo "<h3>🔍 Next Steps to Debug:</h3>";
            echo "<ol>";
            echo "<li><strong>Check Browser Console:</strong> Open DevTools (F12) and look for any JavaScript errors</li>";
            echo "<li><strong>Check EmailJS Dashboard:</strong> Log into EmailJS and check the 'Email History' tab</li>";
            echo "<li><strong>Verify Templates:</strong> Make sure you've created the email templates in EmailJS dashboard</li>";
            echo "<li><strong>Check Spam Folder:</strong> Emails might be going to spam</li>";
            echo "<li><strong>Test with Console:</strong> End a session and watch the browser console for logs</li>";
            echo "</ol>";
        } else {
            echo "<div class='check error'>";
            echo "<div class='check-title'>❌ Issues Found</div>";
            echo "<div class='check-detail'>";
            echo "<ul>";
            foreach ($errors as $error) {
                echo "<li>$error</li>";
            }
            echo "</ul>";
            echo "</div>";
            echo "</div>";
        }
        ?>

        <h2>🧪 Manual Test</h2>
        <p>Want to test email sending? Check the browser console when you end a session. You should see:</p>
        <code>[ParentEmailNotifier] Initialized successfully</code><br>
        <code>[ParentEmailNotifier] Sending email: ...</code><br>
        <code>[ParentEmailNotifier] Email sent successfully</code>

    </div>
</body>

</html>