# 🔍 EMAIL NOTIFICATION TROUBLESHOOTING GUIDE

## ❌ Problem: Emails Not Sending After Ending Session

You mentioned that emails are not being sent to parents after ending a session. Let me help you fix this!

---

## ✅ Root Cause Analysis

After analyzing your codebase, I found the issue:

**The `active-sessions.php` file is missing the email notification trigger code!**

Specifically:
1. ❌ Missing email notification call in `performSessionAction` function
2. ❌ Missing `sendParentNotifications` function  
3. ❌ Missing EmailJS script tags

---

## 🔧 Solution: 2 Simple Changes

### **Change 1: Update JavaScript Function**

**File:** `frontend/views/intructor/active-sessions.php`  
**Location:** Around line 284-313

**Find this:**
```javascript
Toast.success(successMessage);
setTimeout(() => window.location.reload(), 600);
```

**Add this code BEFORE the Toast.success line:**
```javascript
// If ending a session, trigger parent email notifications
if (endpoint === 'end-session' && result.data && result.data.session_id) {
  sendParentNotifications(result.data.session_id);
}
```

**Then add this NEW function after the `performSessionAction` function:**
```javascript
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

---

### **Change 2: Add EmailJS Scripts**

**File:** `frontend/views/intructor/active-sessions.php`  
**Location:** Around line 406-408

**Find this:**
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

## 📋 Verification Checklist

After making the changes, verify:

- [ ] **Backend is configured** ✅ (Already done - returns session_id)
- [ ] **EmailJS config has credentials** ✅ (Already done - service_2dr6r2e)
- [ ] **Email templates created** ✅ (Already done - template_s3xyad9, template_xgdr6y7)
- [ ] **performSessionAction updated** ← **DO THIS**
- [ ] **sendParentNotifications function added** ← **DO THIS**
- [ ] **EmailJS scripts added** ← **DO THIS**

---

## 🧪 Testing Steps

1. **Open browser console** (F12)
2. **Log in as instructor**
3. **Start a session**
4. **End the session**
5. **Check console** - you should see:
   ```
   [EmailJS Parent] ✅ Configuration loaded successfully
   [EmailJS Parent] Service ID: service_2dr6r2e
   [Parent Notifications] Sent X emails, Y failed
   ```

---

## 🔍 Additional Checks

### **Check 1: Parent Emails in Database**

Run this SQL query to verify parents have email addresses:

```sql
SELECT 
    p.id,
    p.email,
    CONCAT(s.first_name, ' ', s.last_name) as student_name,
    s.program,
    s.year_level,
    s.section
FROM parents p
JOIN students s ON p.student_id = s.id
WHERE p.email IS NOT NULL AND p.email != '';
```

If no results, you need to add parent emails to the database!

---

### **Check 2: Browser Console Errors**

After ending a session, check for these errors:

| Error | Meaning | Solution |
|-------|---------|----------|
| `parentEmailNotifier is not defined` | Scripts not loaded | Add EmailJS scripts |
| `Cannot read property 'session_id'` | Backend not returning session_id | Already fixed ✅ |
| `Failed to fetch` | API endpoint issue | Check network tab |
| `EmailJS not configured` | Config file issue | Already fixed ✅ |

---

### **Check 3: Network Tab**

1. Open browser DevTools (F12)
2. Go to **Network** tab
3. End a session
4. Look for these requests:
   - ✅ `POST /backend/api/attendance/end-session` - Should return `session_id`
   - ✅ `GET /backend/api/email/session-notifications?session_id=X` - Should return parent data
   - ✅ EmailJS requests to `api.emailjs.com`

---

## 📊 Expected Flow

```
1. User clicks "End Session"
   ↓
2. performSessionAction called with endpoint='end-session'
   ↓
3. Backend API called: POST /attendance/end-session
   ↓
4. Backend returns: { success: true, data: { session_id: 123 } }
   ↓
5. JavaScript checks: endpoint === 'end-session' ✅
   ↓
6. sendParentNotifications(123) called
   ↓
7. parentEmailNotifier.sendSessionNotifications(123)
   ↓
8. Fetches parent data: GET /email/session-notifications?session_id=123
   ↓
9. Sends emails via EmailJS (one per parent)
   ↓
10. Logs results to console
   ↓
11. Page reloads
```

---

## 🚨 Common Issues & Solutions

### **Issue 1: "No parents to notify"**

**Cause:** No parent records in database for students in that class

**Solution:**
1. Check if students have parent records:
   ```sql
   SELECT s.id, s.first_name, s.last_name, p.id as parent_id, p.email
   FROM students s
   LEFT JOIN parents p ON s.id = p.student_id
   WHERE s.program = 'BSCS' AND s.year_level = 1 AND s.section = 'A';
   ```
2. Add parent records if missing

---

### **Issue 2: Emails going to spam**

**Cause:** EmailJS emails may be flagged as spam initially

**Solution:**
1. Check spam folder
2. Mark as "Not Spam"
3. Add sender to contacts
4. Ask parents to whitelist the sender

---

### **Issue 3: "Rate limit exceeded"**

**Cause:** EmailJS free tier: 200 emails/month

**Solution:**
1. Check EmailJS dashboard for usage
2. Upgrade to paid plan if needed
3. Or reduce email frequency

---

## 📁 Files to Check

| File | Status | Purpose |
|------|--------|---------|
| `frontend/views/intructor/active-sessions.php` | ❌ **NEEDS EDITING** | Add email trigger |
| `frontend/assets/js/emailjs-parent-config.js` | ✅ Configured | EmailJS credentials |
| `frontend/assets/js/parent-email-notifier.js` | ✅ Ready | Email sending utility |
| `backend/controllers/AttendanceController.php` | ✅ Returns session_id | Backend API |
| `backend/controllers/EmailController.php` | ✅ Ready | Email API endpoints |
| `backend/services/EmailService.php` | ✅ Ready | Email service logic |

---

## 🎯 Quick Fix Summary

**You only need to edit 1 file:** `frontend/views/intructor/active-sessions.php`

**Make 2 changes:**
1. Add email notification trigger (3 lines of code)
2. Add sendParentNotifications function (12 lines of code)
3. Add 3 script tags

**See `ACTIVE_SESSIONS_CHANGES.js` for exact code!**

---

## 📞 Still Not Working?

If emails still don't send after making these changes:

1. **Check browser console** for errors
2. **Check Network tab** for failed requests
3. **Verify parent emails exist** in database
4. **Test EmailJS** directly in their dashboard
5. **Enable debug mode** in `emailjs-parent-config.js`:
   ```javascript
   debug: true
   ```

---

**Created:** November 25, 2025  
**Status:** Ready to fix - just edit active-sessions.php!
