# Parent Email Notification Setup Guide

This guide will help you set up automated email notifications to parents after each subject session ends, informing them about their child's attendance status.

## Table of Contents
1. [EmailJS Account Setup](#emailjs-account-setup)
2. [Creating Email Templates](#creating-email-templates)
3. [Configuration](#configuration)
4. [Testing](#testing)
5. [Troubleshooting](#troubleshooting)

---

## 1. EmailJS Account Setup

### Step 1: Create an EmailJS Account
1. Go to [https://www.emailjs.com/](https://www.emailjs.com/)
2. Click **Sign Up** (if you already have an account, just log in)
3. Verify your email address

### Step 2: Add an Email Service
1. In your EmailJS dashboard, go to **Email Services**
2. Click **Add New Service**
3. Choose your email provider (recommended: **Gmail** for ease of use)
4. Follow the authentication steps:
   - For Gmail: Click "Connect Account" and authorize EmailJS
5. Give your service a name (e.g., "CICS Parent Notifications")
6. **Copy the Service ID** (e.g., `service_abc1234`) - you'll need this later

### Step 3: Get Your Public Key
1. Go to **Account** → **General**
2. Find your **Public Key** (e.g., `jUwIzjJuJocSFO2Qj`)
3. **Copy this key** - you'll need it for configuration

---

## 2. Creating Email Templates

You need to create **THREE** email templates for different attendance scenarios:

### Template 1: Student Attended (Present/Late)
**Template Name:** `Parent Notification - Attended`

**Subject:**
```
{{student_name}} Attended {{subject_name}} - {{session_date}}
```

**Email Body:**
```html
Dear {{parent_name}},

This is to inform you that your child, {{student_name}} ({{student_id}}), has attended the following class:

Subject: {{subject_name}} ({{subject_code}})
Instructor: {{instructor_name}}
Date: {{session_date}}
Time: {{session_time}}
Room: {{room}}

Attendance Status: {{attendance_status}}
Time In: {{time_in}}
{{#time_out}}Time Out: {{time_out}}{{/time_out}}

{{#is_late}}
⚠️ Please note: Your child was marked as LATE for this session.
{{/is_late}}

Thank you for your attention.

Best regards,
CICS Attendance System
Zamboanga Peninsula Polytechnic State University
```

**Template ID:** Copy this (e.g., `template_attended123`)

---

### Template 2: Student Absent
**Template Name:** `Parent Notification - Absent`

**Subject:**
```
⚠️ ABSENCE ALERT: {{student_name}} - {{subject_name}} - {{session_date}}
```

**Email Body:**
```html
Dear {{parent_name}},

This is an ABSENCE ALERT for your child.

Student: {{student_name}} ({{student_id}})
Subject: {{subject_name}} ({{subject_code}})
Instructor: {{instructor_name}}
Date: {{session_date}}
Time: {{session_time}}
Room: {{room}}

Attendance Status: ❌ ABSENT

Your child did not attend this class session. If this is unexpected, please contact the instructor or check with your child.

If there was a valid reason for the absence, you may request an attendance correction through the system.

Thank you for your attention.

Best regards,
CICS Attendance System
Zamboanga Peninsula Polytechnic State University
```

**Template ID:** Copy this (e.g., `template_absent123`)

---

### Template 3: Session Summary (Optional - for all students)
**Template Name:** `Parent Notification - Session Summary`

**Subject:**
```
Class Session Summary: {{subject_name}} - {{session_date}}
```

**Email Body:**
```html
Dear {{parent_name}},

Here is the attendance summary for your child's recent class:

Student: {{student_name}} ({{student_id}})
Subject: {{subject_name}} ({{subject_code}})
Instructor: {{instructor_name}}
Date: {{session_date}}
Session Time: {{session_time}}
Room: {{room}}

Attendance Status: {{attendance_status}}
{{#time_in}}Time In: {{time_in}}{{/time_in}}
{{#time_out}}Time Out: {{time_out}}{{/time_out}}

{{#is_present}}✅ Your child attended this class.{{/is_present}}
{{#is_late}}⚠️ Your child was late to this class.{{/is_late}}
{{#is_absent}}❌ Your child was absent from this class.{{/is_absent}}

Thank you for staying informed about your child's attendance.

Best regards,
CICS Attendance System
Zamboanga Peninsula Polytechnic State University
```

**Template ID:** Copy this (e.g., `template_summary123`)

---

## 3. Configuration

### Step 1: Update the Configuration File

Open the file: `frontend/assets/js/emailjs-parent-config.js`

Replace the placeholder values with your actual EmailJS credentials:

```javascript
window.EMAILJS_PARENT_CONFIG = {
    serviceId: 'YOUR_SERVICE_ID',           // e.g., 'service_abc1234'
    publicKey: 'YOUR_PUBLIC_KEY',           // e.g., 'jUwIzjJuJocSFO2Qj'
    templates: {
        attended: 'YOUR_ATTENDED_TEMPLATE_ID',   // e.g., 'template_attended123'
        absent: 'YOUR_ABSENT_TEMPLATE_ID',       // e.g., 'template_absent123'
        summary: 'YOUR_SUMMARY_TEMPLATE_ID'      // e.g., 'template_summary123'
    }
};
```

### Step 2: Verify Template Variables

Make sure your EmailJS templates include these variables:

**Required Variables:**
- `{{parent_name}}` - Parent's full name
- `{{parent_email}}` - Parent's email (auto-filled by EmailJS)
- `{{student_name}}` - Student's full name
- `{{student_id}}` - Student ID number
- `{{subject_name}}` - Subject/course name
- `{{subject_code}}` - Subject code
- `{{instructor_name}}` - Instructor's name
- `{{session_date}}` - Date of the session
- `{{session_time}}` - Time of the session
- `{{room}}` - Room number
- `{{attendance_status}}` - Present/Late/Absent
- `{{time_in}}` - Time student marked attendance
- `{{time_out}}` - Time student marked time out (if applicable)

**Conditional Variables:**
- `{{#is_present}}...{{/is_present}}` - Shows only if student was present
- `{{#is_late}}...{{/is_late}}` - Shows only if student was late
- `{{#is_absent}}...{{/is_absent}}` - Shows only if student was absent

---

## 4. Testing

### Test the Email System

1. **End a test session** as an instructor
2. Check the browser console for any errors
3. Check the parent's email inbox (including spam folder)
4. Verify that:
   - Email arrives within 1-2 minutes
   - All student information is correct
   - Attendance status is accurate
   - Formatting looks good

### Test Different Scenarios

Test all three attendance statuses:
- ✅ **Present** - Student attended on time
- ⚠️ **Late** - Student attended but was late
- ❌ **Absent** - Student did not attend

---

## 5. Troubleshooting

### Emails Not Sending

**Problem:** No emails are being sent after session ends

**Solutions:**
1. Check browser console for errors
2. Verify EmailJS credentials in `emailjs-parent-config.js`
3. Ensure EmailJS service is connected and active
4. Check EmailJS dashboard for error logs
5. Verify parent email addresses are valid in the database

### Gmail Authentication Errors

**Problem:** "Gmail authentication required" error

**Solutions:**
1. Go to EmailJS dashboard → Email Services
2. Click on your Gmail service
3. Click "Reconnect" and re-authorize
4. Make sure you're using the Gmail account you want to send from

### Template Variables Not Showing

**Problem:** Email shows `{{variable_name}}` instead of actual values

**Solutions:**
1. Check that variable names in template match exactly (case-sensitive)
2. Verify the backend is sending the correct data
3. Check browser console for the data being sent to EmailJS

### Emails Going to Spam

**Problem:** Emails are landing in spam folder

**Solutions:**
1. Ask parents to mark emails as "Not Spam"
2. Add your sending email to their contacts
3. Use a professional email address (not a personal Gmail)
4. Consider using a custom domain email service

### Rate Limiting

**Problem:** "Too many requests" error

**Solutions:**
1. EmailJS free tier has limits (200 emails/month)
2. Upgrade to a paid plan for higher limits
3. Implement email batching/queuing for large classes

---

## Email Sending Logic

### When Emails Are Sent

Emails are automatically sent to parents when:
1. **Instructor ends a session** - Triggers email sending for all students in that class
2. **Student attended (Present/Late)** - Parent receives "Attended" email
3. **Student was absent** - Parent receives "Absent Alert" email

### What Parents Receive

- **If student attended:** Email with attendance details and time in/out
- **If student was late:** Same as attended, but with a warning notice
- **If student was absent:** Alert email notifying about the absence

---

## Privacy & Security Notes

1. **Parent email addresses** are stored securely in the database
2. **Only parents** of students in the class receive emails
3. **No sensitive data** (passwords, etc.) is sent via email
4. **EmailJS** uses secure HTTPS connections
5. **Email logs** are stored for tracking purposes

---

## Support

If you encounter issues:

1. Check the **browser console** for error messages
2. Review the **EmailJS dashboard** for delivery status
3. Verify **database records** have parent email addresses
4. Test with a **small class** first before rolling out

---

## Quick Reference

### Configuration File Location
```
frontend/assets/js/emailjs-parent-config.js
```

### Backend Email Service
```
backend/services/EmailService.php
```

### Email Sending Trigger
```
backend/controllers/AttendanceController.php → endSession()
```

### Database Tables Used
- `parents` - Parent contact information
- `students` - Student details
- `attendance_records` - Attendance data
- `attendance_sessions` - Session information
- `email_notifications` - Email sending logs

---

## Example Email Preview

### For Present Student:
```
Subject: John Doe Attended Database Management - November 25, 2025

Dear Mr. Smith,

This is to inform you that your child, John Doe (2021-12345), 
has attended the following class:

Subject: Database Management (CS301)
Instructor: Prof. Jane Garcia
Date: November 25, 2025
Time: 10:00 AM - 12:00 PM
Room: CS Lab 1

Attendance Status: Present
Time In: 10:05 AM
Time Out: 11:55 AM

Thank you for your attention.

Best regards,
CICS Attendance System
```

---

**Last Updated:** November 2025
**Version:** 1.0
