# EmailJS Template Setup - Visual Guide

## 📧 How to Add HTML Templates to EmailJS

### **Step 1: Access Template Editor**

1. Go to EmailJS Dashboard: https://dashboard.emailjs.com/
2. Click **"Email Templates"** in the left sidebar
3. Click **"Create New Template"** button

---

### **Step 2: Configure Basic Settings**

Fill in these fields in the right panel:

```
Template Name: Parent Notification - Attended
              (or "Absent" or "Summary" depending on which template)

From Name: CICS Attendance System

From Email: ✓ Use Default Email Address (checked)

Reply To: {{reply_to}}

Subject: {{student_name}} Attended {{subject_name}} - {{session_date}}
        (see EMAILJS_TEMPLATE_SETUP.md for other subjects)
```

---

### **Step 3: Add HTML Content**

#### **In the Content Editor:**

1. Click **"Edit Content"** button
2. Make sure you're on **"Desktop"** view (tab at the top)
3. Look for the **`</>`** icon in the toolbar (HTML/Code view)
4. Click the **`</>`** icon to switch to code view
5. You'll see existing HTML code - **DELETE ALL OF IT**
6. Open the HTML file:
   - For Attended: `email-templates/template-attended.html`
   - For Absent: `email-templates/template-absent.html`
   - For Summary: `email-templates/template-summary.html`
7. **Copy the ENTIRE HTML code** (Ctrl+A, then Ctrl+C)
8. **Paste it** into the EmailJS editor (Ctrl+V)
9. Click **"Save"** button

---

### **Step 4: Test the Template**

1. Click **"Test It"** button (top right)
2. Fill in sample values:

```javascript
{
  "parent_name": "Mr. John Smith",
  "student_name": "Jane Smith",
  "student_id": "2021-12345",
  "subject_name": "Database Management",
  "subject_code": "CS301",
  "instructor_name": "Prof. Maria Garcia",
  "session_date": "November 25, 2025",
  "session_time": "10:00 AM - 12:00 PM",
  "room": "CS Lab 1",
  "attendance_status": "Present",
  "time_in": "10:05 AM",
  "time_out": "11:55 AM",
  "is_present": true,
  "is_late": false,
  "is_absent": false,
  "reply_to": "instructor@zppsu.edu"
}
```

3. Enter your email address in "To Email"
4. Click **"Send Test Email"**
5. Check your inbox (and spam folder)

---

### **Step 5: Copy Template ID**

After saving, you'll see the **Template ID** at the top of the page.

Example: `template_abc1234`

**Copy this ID** - you'll need it for the configuration file.

---

## 🎯 Quick Reference

### **Template 1: Attended**

| Field | Value |
|-------|-------|
| **Name** | Parent Notification - Attended |
| **Subject** | `{{student_name}} Attended {{subject_name}} - {{session_date}}` |
| **HTML File** | `email-templates/template-attended.html` |
| **Test Values** | `is_present: true`, `is_late: false`, `is_absent: false` |

### **Template 2: Absent**

| Field | Value |
|-------|-------|
| **Name** | Parent Notification - Absent |
| **Subject** | `⚠️ ABSENCE ALERT: {{student_name}} - {{subject_name}} - {{session_date}}` |
| **HTML File** | `email-templates/template-absent.html` |
| **Test Values** | `is_present: false`, `is_late: false`, `is_absent: true` |

### **Template 3: Summary**

| Field | Value |
|-------|-------|
| **Name** | Parent Notification - Session Summary |
| **Subject** | `Class Session Summary: {{subject_name}} - {{session_date}}` |
| **HTML File** | `email-templates/template-summary.html` |
| **Test Values** | Set one of: `is_present`, `is_late`, or `is_absent` to `true` |

---

## 🔍 Important Notes

### **For Late Students (Template 1)**
When testing the "Attended" template for late students:
```javascript
{
  "is_present": false,
  "is_late": true,
  "is_absent": false,
  "attendance_status": "Late"
}
```

### **For Present Students (Template 1)**
When testing the "Attended" template for present students:
```javascript
{
  "is_present": true,
  "is_late": false,
  "is_absent": false,
  "attendance_status": "Present"
}
```

### **For Absent Students (Template 2)**
When testing the "Absent" template:
```javascript
{
  "is_present": false,
  "is_late": false,
  "is_absent": true,
  "attendance_status": "Absent"
}
```

---

## ✅ Verification Checklist

After creating each template:

- [ ] Template name is correct
- [ ] From Name is "CICS Attendance System"
- [ ] "Use Default Email Address" is checked
- [ ] Reply To is `{{reply_to}}`
- [ ] Subject line is correct with variables
- [ ] HTML code is pasted completely
- [ ] Test email sent successfully
- [ ] Email looks good on desktop
- [ ] Email looks good on mobile (forward to phone)
- [ ] Template ID copied and saved

---

## 🎨 What the Emails Look Like

### **Template 1: Attended (Present)**
- Blue gradient header with "📚 Attendance Notification"
- Green badge showing "✓ Present"
- Clean white info cards with class details
- Time in/out information
- Professional footer

### **Template 1: Attended (Late)**
- Blue gradient header with "📚 Attendance Notification"
- Yellow badge showing "⚠ Late"
- Clean white info cards with class details
- **Yellow warning box** about being late
- Time in/out information
- Professional footer

### **Template 2: Absent**
- Red gradient header with "⚠️ ABSENCE ALERT"
- Red badge showing "❌ Absent"
- Red alert banner with absence notification
- Clean white info cards with class details
- Blue action box with next steps
- Professional footer

### **Template 3: Summary**
- Purple gradient header with "📊 Class Session Summary"
- Status badge (green/yellow/red based on status)
- Student info card
- Clean white info cards with class details
- Large status message with icon (✅/⚠️/❌)
- Professional footer

---

## 📱 Mobile Preview

All templates are responsive and will automatically adjust for:
- iPhone/Android phones
- Tablets
- Desktop email clients
- Web email (Gmail, Outlook, Yahoo)

---

## 🚨 Common Mistakes to Avoid

1. ❌ **Don't** modify the HTML/CSS code
2. ❌ **Don't** forget to switch to code view (`</>` icon)
3. ❌ **Don't** leave the default EmailJS template content
4. ❌ **Don't** forget to copy the Template ID
5. ❌ **Don't** use the instructor EmailJS config (use the parent config)

---

## 🎯 After Creating All Templates

Update `frontend/assets/js/emailjs-parent-config.js`:

```javascript
window.EMAILJS_PARENT_CONFIG = {
    serviceId: 'service_xxxxxxx',           // Your Service ID
    publicKey: 'your_public_key_here',      // Your Public Key
    
    templates: {
        attended: 'template_abc123',        // Template 1 ID (copy from EmailJS)
        absent: 'template_def456',          // Template 2 ID (copy from EmailJS)
        summary: 'template_ghi789'          // Template 3 ID (copy from EmailJS)
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

**Need Help?** Check `EMAILJS_TEMPLATE_SETUP.md` for detailed instructions.

**Created:** November 25, 2025
