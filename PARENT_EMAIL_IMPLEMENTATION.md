# Parent Email Notification Implementation Guide

## Overview
This guide will help you integrate the automated parent email notification system into your CICS Attendance System. The system automatically sends emails to parents when an instructor ends a session, notifying them about their child's attendance status (Present, Late, or Absent).

---

## ✅ Files Already Created

The following files have been created and are ready to use:

1. **`PARENT_EMAIL_SETUP_GUIDE.md`** - Detailed EmailJS setup instructions
2. **`frontend/assets/js/emailjs-parent-config.js`** - EmailJS configuration file
3. **`frontend/assets/js/parent-email-notifier.js`** - Client-side email sending utility
4. **`backend/services/EmailService.php`** - Backend email service
5. **`backend/controllers/EmailController.php`** - Email API controller

---

## 📝 Manual Changes Required

### Step 1: Update Backend API Routes

**File:** `backend/api/index.php`

✅ **Already completed** - Email routes have been added to handle:
- `GET /email/session-notifications` - Get notification data for a session
- `POST /email/log-notification` - Log notification status
- `GET /email/stats` - Get notification statistics
- `GET /email/recent` - Get recent notifications

---

### Step 2: Update AttendanceController

**File:** `backend/controllers/AttendanceController.php`

✅ **Already completed** - The `endSession()` method now returns the `session_id` in the response.

---

### Step 3: Update Instructor Active Sessions Page

**File:** `frontend/views/intructor/active-sessions.php`

**Location:** Near the end of the file, before `</body>`

**Find this code (around line 284-313):**
```javascript
const performSessionAction = async (button, endpoint, payload, successMessage) => {
  const originalText = button.textContent.trim();
  const loadingText = button.dataset.loadingText || 'Please wait...';
  button.disabled = true;
  button.textContent = loadingText;

  try {
    const response = await fetch(`${API_BASE}/attendance/${endpoint}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      credentials: 'include',
      body: JSON.stringify(payload)
    });

    const result = await response.json();

    if (!response.ok || !result.success) {
      throw new Error(result.message || 'Something went wrong');
    }

    Toast.success(successMessage);
    setTimeout(() => window.location.reload(), 600);
  } catch (error) {
    Toast.error(error.message || 'Unable to complete the action');
    button.disabled = false;
    button.textContent = originalText;
  }
};
```

**Replace with:**
```javascript
const performSessionAction = async (button, endpoint, payload, successMessage) => {
  const originalText = button.textContent.trim();
  const loadingText = button.dataset.loadingText || 'Please wait...';
  button.disabled = true;
  button.textContent = loadingText;

  try {
    const response = await fetch(`${API_BASE}/attendance/${endpoint}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      credentials: 'include',
      body: JSON.stringify(payload)
    });

    const result = await response.json();

    if (!response.ok || !result.success) {
      throw new Error(result.message || 'Something went wrong');
    }

    // If ending a session, trigger parent email notifications
    if (endpoint === 'end-session' && result.data && result.data.session_id) {
      sendParentNotifications(result.data.session_id);
    }

    Toast.success(successMessage);
    setTimeout(() => window.location.reload(), 600);
  } catch (error) {
    Toast.error(error.message || 'Unable to complete the action');
    button.disabled = false;
    button.textContent = originalText;
  }
};

// Send parent email notifications after session ends
const sendParentNotifications = async (sessionId) => {
  try {
    if (window.parentEmailNotifier) {
      const result = await window.parentEmailNotifier.sendSessionNotifications(sessionId);
      
      if (result.success && result.sent > 0) {
        console.log(`[Parent Notifications] Sent ${result.sent} emails, ${result.failed} failed`);
      }
    }
  } catch (error) {
    console.error('[Parent Notifications] Error:', error);
    // Don't show error to user - email sending is a background task
  }
};
```

**Then, find this code (around line 406-407):**
```html
  </script>
  <script src="../../assets/js/auto-end-sessions.js"></script>
</body>
```

**Replace with:**
```html
  </script>
  <!-- EmailJS for Parent Notifications -->
  <script src="https://cdn.jsdelivr.net/npm/emailjs-com@3/dist/email.min.js"></script>
  <script src="../../assets/js/emailjs-parent-config.js"></script>
  <script src="../../assets/js/parent-email-notifier.js"></script>
  <script src="../../assets/js/auto-end-sessions.js"></script>
</body>
```

---

## 🔧 EmailJS Configuration

### Step 1: Create EmailJS Account and Service

1. Go to [https://www.emailjs.com/](https://www.emailjs.com/)
2. Sign up or log in
3. Add an Email Service (Gmail recommended)
4. Get your **Service ID** and **Public Key**

### Step 2: Create Email Templates

Create **THREE** templates in EmailJS:

#### Template 1: Student Attended (Present/Late)
- **Template Name:** `Parent Notification - Attended`
- **Subject:** `{{student_name}} Attended {{subject_name}} - {{session_date}}`
- **Body:** See `PARENT_EMAIL_SETUP_GUIDE.md` for full template

#### Template 2: Student Absent
- **Template Name:** `Parent Notification - Absent`
- **Subject:** `⚠️ ABSENCE ALERT: {{student_name}} - {{subject_name}} - {{session_date}}`
- **Body:** See `PARENT_EMAIL_SETUP_GUIDE.md` for full template

#### Template 3: Session Summary (Optional)
- **Template Name:** `Parent Notification - Session Summary`
- **Subject:** `Class Session Summary: {{subject_name}} - {{session_date}}`
- **Body:** See `PARENT_EMAIL_SETUP_GUIDE.md` for full template

### Step 3: Update Configuration File

**File:** `frontend/assets/js/emailjs-parent-config.js`

Replace the placeholder values:

```javascript
window.EMAILJS_PARENT_CONFIG = {
    serviceId: 'YOUR_SERVICE_ID_HERE',        // Replace with your Service ID
    publicKey: 'YOUR_PUBLIC_KEY_HERE',        // Replace with your Public Key
    
    templates: {
        attended: 'YOUR_ATTENDED_TEMPLATE_ID',  // Replace with Template ID
        absent: 'YOUR_ABSENT_TEMPLATE_ID',      // Replace with Template ID
        summary: 'YOUR_SUMMARY_TEMPLATE_ID'     // Replace with Template ID (optional)
    },
    
    enabled: true,
    sendOnlyForAbsent: false,
    sendSummaryForAll: false,
    retryAttempts: 3,
    retryDelay: 2000,
    debug: false
};
```

---

## 🧪 Testing

### Test the System

1. **Start a test session** as an instructor
2. **Have a student mark attendance** (or manually add attendance records)
3. **End the session**
4. **Check the browser console** for email sending logs
5. **Check parent email inbox** (including spam folder)

### Expected Behavior

When you end a session:
1. Session ends successfully
2. Email notification process starts in the background
3. Console logs show: `[Parent Notifications] Sent X emails, Y failed`
4. Parents receive emails within 1-2 minutes
5. Emails are logged in the `email_notifications` table

---

## 📊 Email Template Variables

The following variables are available in your EmailJS templates:

### Required Variables
- `{{parent_name}}` - Parent's full name
- `{{parent_email}}` - Parent's email
- `{{student_name}}` - Student's full name
- `{{student_id}}` - Student ID number
- `{{subject_name}}` - Subject/course name
- `{{subject_code}}` - Subject code
- `{{instructor_name}}` - Instructor's name
- `{{session_date}}` - Date of the session
- `{{session_time}}` - Time of the session
- `{{room}}` - Room number
- `{{attendance_status}}` - Present/Late/Absent

### Optional Variables
- `{{time_in}}` - Time student marked attendance
- `{{time_out}}` - Time student marked time out

### Conditional Variables
- `{{#is_present}}...{{/is_present}}` - Shows only if student was present
- `{{#is_late}}...{{/is_late}}` - Shows only if student was late
- `{{#is_absent}}...{{/is_absent}}` - Shows only if student was absent

---

## 🔍 Troubleshooting

### Emails Not Sending

**Check:**
1. Browser console for errors
2. EmailJS configuration is correct
3. Parent email addresses exist in database
4. EmailJS service is connected and active
5. Network tab for failed API requests

**Solutions:**
1. Verify EmailJS credentials in `emailjs-parent-config.js`
2. Check EmailJS dashboard for error logs
3. Ensure parent records have valid email addresses
4. Re-authenticate Gmail service in EmailJS

### Template Variables Not Showing

**Problem:** Email shows `{{variable_name}}` instead of actual values

**Solutions:**
1. Check variable names match exactly (case-sensitive)
2. Verify backend is sending correct data
3. Check browser console for data being sent to EmailJS

### Rate Limiting

**Problem:** "Too many requests" error

**Solutions:**
1. EmailJS free tier: 200 emails/month
2. Upgrade to paid plan for higher limits
3. Implement email batching/queuing

---

## 📁 Database Schema

The `email_notifications` table tracks all email sending attempts:

```sql
CREATE TABLE IF NOT EXISTS `email_notifications` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_id` INT(11) UNSIGNED NOT NULL,
  `student_id` INT(11) UNSIGNED NOT NULL,
  `type` ENUM('daily_summary', 'absence_alert', 'late_alert') NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `status` ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
  `sent_at` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`parent_id`) REFERENCES `parents`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
);
```

---

## 🎯 Feature Flags

You can customize the email notification behavior in `emailjs-parent-config.js`:

```javascript
enabled: true,                    // Set to false to disable all emails
sendOnlyForAbsent: false,         // Set to true to only email for absent students
sendSummaryForAll: false,         // Set to true to send summary emails to all parents
retryAttempts: 3,                 // Number of retry attempts if email fails
retryDelay: 2000,                 // Delay between retries in milliseconds
debug: false                      // Set to true to log email sending details
```

---

## 📧 API Endpoints

### Get Session Notifications
```
GET /backend/api/email/session-notifications?session_id={id}
```
Returns parent and attendance data for all students in the session.

### Log Notification Status
```
POST /backend/api/email/log-notification
Body: {
  "notification_id": 123,
  "status": "sent",
  "sent_at": "2025-11-25 10:30:00"
}
```
Updates the status of an email notification.

### Get Notification Statistics
```
GET /backend/api/email/stats?student_id={id}&status={status}
```
Returns statistics about email notifications.

### Get Recent Notifications
```
GET /backend/api/email/recent?limit=50&offset=0
```
Returns recent email notifications.

---

## 🚀 Next Steps

1. ✅ Complete the manual changes in `active-sessions.php`
2. ✅ Set up EmailJS account and create templates
3. ✅ Update `emailjs-parent-config.js` with your credentials
4. ✅ Test with a small class first
5. ✅ Monitor email logs and adjust as needed
6. ✅ Roll out to all instructors

---

## 📚 Additional Resources

- **EmailJS Documentation:** [https://www.emailjs.com/docs/](https://www.emailjs.com/docs/)
- **Parent Email Setup Guide:** `PARENT_EMAIL_SETUP_GUIDE.md`
- **Email Service Code:** `backend/services/EmailService.php`
- **Email Controller Code:** `backend/controllers/EmailController.php`

---

## ⚠️ Important Notes

1. **Don't use the existing EmailJS config** - The instructor page uses a different EmailJS configuration for sending instructor credentials. The parent notification system uses its own separate configuration.

2. **Email sending is asynchronous** - Emails are sent in the background after the session ends. The page will reload before all emails are sent.

3. **Check spam folders** - Initial emails may go to spam. Ask parents to mark as "Not Spam" and add to contacts.

4. **Rate limits apply** - EmailJS free tier has a 200 emails/month limit. Plan accordingly for large classes.

5. **Privacy** - Only parents of students in the class receive emails. No sensitive data is sent.

---

**Last Updated:** November 25, 2025
**Version:** 1.0
