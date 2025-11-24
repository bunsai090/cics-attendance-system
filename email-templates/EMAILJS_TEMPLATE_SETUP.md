# EmailJS Template Setup - Quick Guide

## 📧 Template Configuration

### **Common Settings for All Templates**

| Field | Value |
|-------|-------|
| **From Name** | `CICS Attendance System` |
| **From Email** | ✓ Use Default Email Address (checked) |
| **Reply To** | `{{reply_to}}` |

---

## 📝 Template 1: Student Attended (Present/Late)

### **Template Name**
```
Parent Notification - Attended
```

### **Subject**
```
{{student_name}} Attended {{subject_name}} - {{session_date}}
```

### **Content**
Copy the entire HTML from: `email-templates/template-attended.html`

**How to use:**
1. In EmailJS, click "Edit Content"
2. Switch to "Desktop" view
3. Click the `</>` (code) icon
4. Delete all existing content
5. Paste the HTML from `template-attended.html`
6. Click "Save"

---

## 📝 Template 2: Student Absent

### **Template Name**
```
Parent Notification - Absent
```

### **Subject**
```
⚠️ ABSENCE ALERT: {{student_name}} - {{subject_name}} - {{session_date}}
```

### **Content**
Copy the entire HTML from: `email-templates/template-absent.html`

**How to use:**
1. In EmailJS, click "Edit Content"
2. Switch to "Desktop" view
3. Click the `</>` (code) icon
4. Delete all existing content
5. Paste the HTML from `template-absent.html`
6. Click "Save"

---

## 📝 Template 3: Session Summary (Optional)

### **Template Name**
```
Parent Notification - Session Summary
```

### **Subject**
```
Class Session Summary: {{subject_name}} - {{session_date}}
```

### **Content**
Copy the entire HTML from: `email-templates/template-summary.html`

**How to use:**
1. In EmailJS, click "Edit Content"
2. Switch to "Desktop" view
3. Click the `</>` (code) icon
4. Delete all existing content
5. Paste the HTML from `template-summary.html`
6. Click "Save"

---

## 🎨 Template Variables Used

All templates use these variables (automatically filled by the system):

### **Parent Information**
- `{{parent_name}}` - Parent's full name
- `{{reply_to}}` - Reply-to email address

### **Student Information**
- `{{student_name}}` - Student's full name
- `{{student_id}}` - Student ID number

### **Subject Information**
- `{{subject_name}}` - Subject/course name
- `{{subject_code}}` - Subject code
- `{{instructor_name}}` - Instructor's name

### **Session Information**
- `{{session_date}}` - Date of the session (e.g., "November 25, 2025")
- `{{session_time}}` - Time of the session (e.g., "10:00 AM - 12:00 PM")
- `{{room}}` - Room number

### **Attendance Information**
- `{{attendance_status}}` - Present/Late/Absent
- `{{time_in}}` - Time student marked attendance (e.g., "10:05 AM")
- `{{time_out}}` - Time student marked time out (optional)

### **Conditional Blocks**
- `{{#is_present}}...{{/is_present}}` - Shows only if student was present
- `{{#is_late}}...{{/is_late}}` - Shows only if student was late
- `{{#is_absent}}...{{/is_absent}}` - Shows only if student was absent
- `{{#time_out}}...{{/time_out}}` - Shows only if time out was recorded

---

## 📋 Step-by-Step Setup

### **1. Create Template in EmailJS**
1. Go to EmailJS Dashboard → Email Templates
2. Click "Create New Template"
3. Enter the template name (see above)

### **2. Configure Template Settings**
1. **From Name:** `CICS Attendance System`
2. **From Email:** Check "Use Default Email Address"
3. **Reply To:** `{{reply_to}}`
4. **Subject:** Copy the subject line from above

### **3. Add HTML Content**
1. Click "Edit Content"
2. Make sure you're on "Desktop" view
3. Click the `</>` (code/HTML) icon in the toolbar
4. Delete all existing content
5. Open the corresponding HTML file from `email-templates/` folder
6. Copy ALL the HTML code
7. Paste it into the EmailJS editor
8. Click "Save"

### **4. Test the Template**
1. Click "Test It" button
2. Fill in sample values for all variables:
   - `parent_name`: John Doe
   - `student_name`: Jane Doe
   - `student_id`: 2021-12345
   - `subject_name`: Database Management
   - `subject_code`: CS301
   - `instructor_name`: Prof. Smith
   - `session_date`: November 25, 2025
   - `session_time`: 10:00 AM - 12:00 PM
   - `room`: CS Lab 1
   - `attendance_status`: Present
   - `time_in`: 10:05 AM
   - `time_out`: 11:55 AM
   - `is_present`: true (for attended template)
   - `is_late`: false
   - `is_absent`: false
   - `reply_to`: instructor@zppsu.edu
3. Send test email to your own email
4. Check inbox (and spam folder)
5. Verify formatting and content

### **5. Copy Template ID**
1. After saving, you'll see the Template ID (e.g., `template_abc123`)
2. Copy this ID
3. You'll need it for the configuration file

---

## 🔧 Update Configuration File

After creating all 3 templates, update `frontend/assets/js/emailjs-parent-config.js`:

```javascript
window.EMAILJS_PARENT_CONFIG = {
    serviceId: 'YOUR_SERVICE_ID',           // From Email Services
    publicKey: 'YOUR_PUBLIC_KEY',           // From Account → General
    
    templates: {
        attended: 'template_abc123',        // Template 1 ID
        absent: 'template_def456',          // Template 2 ID
        summary: 'template_ghi789'          // Template 3 ID
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

## ✅ Checklist

- [ ] Created Template 1: Attended
- [ ] Created Template 2: Absent
- [ ] Created Template 3: Summary (optional)
- [ ] Tested all templates with sample data
- [ ] Copied all Template IDs
- [ ] Updated `emailjs-parent-config.js`
- [ ] Verified emails look good on mobile and desktop

---

## 🎨 Template Features

### **Template 1: Attended (Present/Late)**
- ✅ Blue gradient header
- ✅ Green badge for Present
- ✅ Yellow badge for Late
- ✅ Warning box for late arrivals
- ✅ Clean info cards
- ✅ Mobile responsive

### **Template 2: Absent**
- ✅ Red gradient header
- ✅ Red alert banner
- ✅ Absence notification
- ✅ Action items for parents
- ✅ Mobile responsive

### **Template 3: Summary**
- ✅ Purple gradient header
- ✅ Conditional status messages
- ✅ Visual status icons
- ✅ Comprehensive summary
- ✅ Mobile responsive

---

## 📱 Mobile Responsive

All templates are fully responsive and will look great on:
- ✅ Desktop computers
- ✅ Tablets
- ✅ Mobile phones
- ✅ Email clients (Gmail, Outlook, Yahoo, etc.)

---

## 🔍 Troubleshooting

### **Variables showing as {{variable_name}}**
- Make sure variable names match exactly (case-sensitive)
- Check that the backend is sending the correct data

### **Formatting looks broken**
- Make sure you copied the ENTIRE HTML code
- Don't modify the CSS styles
- Test in different email clients

### **Conditional blocks not working**
- EmailJS uses Mustache syntax: `{{#condition}}...{{/condition}}`
- Make sure the condition variable is set to `true` or `false`

---

**Created:** November 25, 2025
**Version:** 1.0
