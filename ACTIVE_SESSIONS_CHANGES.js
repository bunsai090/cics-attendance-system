/**
 * Parent Email Notification Integration
 * Add this code to active-sessions.php
 * 
 * INSTRUCTIONS:
 * 1. Open frontend/views/intructor/active-sessions.php
 * 2. Find the performSessionAction function (around line 284)
 * 3. Add the email notification code as shown below
 * 4. Add the script tags at the end of the file
 */

// ============================================
// STEP 1: Update performSessionAction function
// ============================================

// FIND THIS CODE (around line 284-313):
/*
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
*/

// REPLACE WITH THIS CODE:
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

        // ✅ NEW CODE: Trigger parent email notifications when ending a session
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

// ✅ NEW FUNCTION: Add this function after performSessionAction
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


// ============================================
// STEP 2: Add EmailJS Scripts
// ============================================

// FIND THIS CODE (around line 406-408):
/*
  </script>
  <script src="../../assets/js/auto-end-sessions.js"></script>
</body>

</html>
*/

// REPLACE WITH THIS CODE:
/*
  </script>
  <!-- EmailJS for Parent Notifications -->
  <script src="https://cdn.jsdelivr.net/npm/emailjs-com@3/dist/email.min.js"></script>
  <script src="../../assets/js/emailjs-parent-config.js"></script>
  <script src="../../assets/js/parent-email-notifier.js"></script>
  <script src="../../assets/js/auto-end-sessions.js"></script>
</body>

</html>
*/
