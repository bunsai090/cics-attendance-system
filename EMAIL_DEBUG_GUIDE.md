# 🔍 EMAIL NOTIFICATION DEBUG GUIDE

## 🚨 NO EMAILS RECEIVED? Follow This Checklist

### **STEP 1: Run the Debug Page** ⭐ DO THIS FIRST

1. Open your browser
2. Go to: **`http://localhost/cics-attendance-system/backend/debug_email.php`**
3. Review all the checks - this will tell you exactly what's wrong

---

### **STEP 2: Check EmailJS Dashboard** ⭐ CRITICAL

**The #1 reason emails don't send: Templates not created in EmailJS!**

1. Go to: **https://dashboard.emailjs.com/**
2. Log in with your account
3. Click **"Email Templates"** in the left sidebar
4. **Check if you have these templates:**
   - ✅ Template ID: `template_s3xyad9` (for Attended/Late)
   - ✅ Template ID: `template_xgdr6y7` (for Absent)

**If templates are missing:**
- You MUST create them in the EmailJS dashboard
- Use the HTML files in `email-templates/` folder
- See `email-templates/EMAILJS_TEMPLATE_SETUP.md` for instructions

---

### **STEP 3: Check Parent Email Addresses**

**Emails can't be sent if parents don't have email addresses!**

Run the debug page to see if parents have emails. If not:

1. Go to your database (phpMyAdmin)
2. Open the `parents` table
3. Check if the `email` column has actual email addresses
4. **Add test email addresses** if needed

**Quick SQL to check:**
```sql
SELECT COUNT(*) FROM parents WHERE email IS NOT NULL AND email != '';
```

If this returns 0, **you need to add parent emails!**

---

### **STEP 4: Check Browser Console**

1. Open Active Sessions page
2. Press **F12** to open DevTools
3. Go to **Console** tab
4. End a session
5. **Look for these messages:**

**✅ Good (emails should send):**
```
[EmailJS Parent] ✅ Configuration loaded successfully
[ParentEmailNotifier] Initialized successfully
[ParentEmailNotifier] Sending email: ...
[ParentEmailNotifier] Email sent successfully
[ParentEmailNotifier] Batch results: { sent: 2, failed: 0 }
```

**❌ Bad (emails won't send):**
```
[EmailJS Parent] ⚠️ EmailJS is not configured yet!
[ParentEmailNotifier] EmailJS library not loaded
Error: Failed to fetch notification data
```

---

### **STEP 5: Common Issues & Solutions**

#### **Issue 1: "No parents to notify"**
**Cause:** No parent records with email addresses  
**Solution:** Add parent emails to the database

#### **Issue 2: "EmailJS not configured"**
**Cause:** Templates not created in EmailJS dashboard  
**Solution:** Create templates using the HTML files provided

#### **Issue 3: "Failed to fetch notification data"**
**Cause:** Database table missing  
**Solution:** Run `http://localhost/cics-attendance-system/backend/migrate_email_table.php`

#### **Issue 4: Emails sent but not received**
**Possible causes:**
- ✉️ Check **spam folder**
- 🚫 EmailJS free tier limit reached (200 emails/month)
- ❌ Wrong email address in parent record
- ⏰ EmailJS service delay (can take a few minutes)

**Check EmailJS Dashboard:**
1. Go to https://dashboard.emailjs.com/
2. Click "Email History"
3. See if emails were sent and their status

---

### **STEP 6: Test with a Real Email**

1. Make sure you have at least ONE parent with a valid email (your email)
2. Make sure that student is enrolled in a subject
3. Start a session for that subject
4. Have the student mark attendance (or add manually)
5. End the session
6. Check console for success messages
7. Check your email (and spam folder!)

---

### **STEP 7: Verify EmailJS Templates**

**Template 1: Attended (template_s3xyad9)**
- **From Name:** CICS Attendance System
- **Subject:** `{{student_name}} Attended {{subject_name}} - {{session_date}}`
- **Content:** Use `email-templates/template-attended.html`

**Template 2: Absent (template_xgdr6y7)**
- **From Name:** CICS Attendance System  
- **Subject:** `⚠️ ABSENCE ALERT: {{student_name}} - {{subject_name}} - {{session_date}}`
- **Content:** Use `email-templates/template-absent.html`

**Variables used in templates:**
- `{{parent_name}}`
- `{{student_name}}`
- `{{subject_name}}`
- `{{session_date}}`
- `{{time_in}}`
- `{{time_out}}`
- `{{attendance_status}}`
- `{{instructor_name}}`

---

## 🎯 Quick Diagnostic

Run these checks in order:

1. ✅ **Database table exists?** → Run debug page
2. ✅ **Parents have emails?** → Check debug page
3. ✅ **EmailJS templates created?** → Check EmailJS dashboard
4. ✅ **Console shows success?** → Check browser console
5. ✅ **EmailJS shows sent?** → Check EmailJS dashboard
6. ✅ **Email received?** → Check inbox and spam

---

## 📞 Still Not Working?

If you've checked everything above and emails still aren't sending:

1. **Take a screenshot** of:
   - The debug page results
   - Browser console when ending a session
   - EmailJS dashboard (Email History page)

2. **Check these files exist:**
   - `frontend/assets/js/emailjs-parent-config.js` ✅
   - `frontend/assets/js/parent-email-notifier.js` ✅
   - `backend/services/EmailService.php` ✅
   - `backend/controllers/EmailController.php` ✅

3. **Verify scripts are loaded:**
   - Open Active Sessions page
   - View page source (Ctrl+U)
   - Search for "emailjs-parent-config.js"
   - Should see: `<script src="../../assets/js/emailjs-parent-config.js"></script>`

---

## 🔑 Most Common Cause

**90% of the time, emails don't send because:**

1. **EmailJS templates were never created** in the dashboard
2. **Parent email addresses are missing** from the database

**Fix:** 
- Create templates in EmailJS dashboard
- Add parent emails to database

---

**Debug Page:** `http://localhost/cics-attendance-system/backend/debug_email.php`

**Created:** November 25, 2025  
**Status:** Ready to debug!
