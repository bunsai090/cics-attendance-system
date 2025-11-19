<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - CICS Attendance System</title>
  <link rel="stylesheet" href="../assets/css/main.css">
  <link rel="stylesheet" href="../assets/css/pages/login.css">
</head>
<body class="login-page">
  <div class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <div class="auth-logos">
          <img src="https://uploadthingy.s3.us-west-1.amazonaws.com/qHYtTa1uNrpFjc66NgGcuM/ZPPUS-CICS_LOGO.jpg" alt="CICS Logo" class="auth-logo">
          <img src="https://uploadthingy.s3.us-west-1.amazonaws.com/h5rtnYfu5NzN7nEjkomYz5/ZPPSU-LOGO.jpg" alt="ZPPSU Logo" class="auth-logo">
        </div>
        <h1 class="auth-title">Login to Your Account</h1>
      </div>
      <div class="auth-body">
        <form class="auth-form" id="loginForm" method="POST" action="#">
          <div class="form-group">
            <label for="email" class="form-label required">Email/Student ID</label>
            <div class="input-group input-group-email">
              <svg class="input-group-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
              </svg>
              <input type="text" name="email" id="email" class="form-input" placeholder="Enter your email or student ID" required>
            </div>
          </div>
          <div class="form-group">
            <label for="password" class="form-label required">Password</label>
            <div class="input-group input-group-password">
              <svg class="input-group-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
              </svg>
              <input type="password" name="password" id="password" class="form-input" placeholder="Enter your password" required>
              <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                <svg class="eye-icon-show" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <svg class="eye-icon-hide" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="display: none;">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 01-4.243-4.243m4.242 4.242L9.88 9.88" />
                </svg>
              </button>
            </div>
          </div>
          <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>
        <div class="mt-3 text-center">
          <a href="#" class="text-sm forgot-password-link">Forgot Password?</a>
        </div>
        <div class="mt-2 text-center">
          <p class="text-sm" style="color: #4b5563;">
            Don't have an account? <a href="register.php" class="create-account-link">Create Account</a>
          </p>
        </div>
      </div>
    </div>
    <div class="auth-footer">
      <p>
        © 2025 Zamboanga Peninsula Polytechnic State University<br>
        <a href="mailto:support@zppsu.edu.ph">support@zppsu.edu.ph</a>
      </p>
    </div>
  </div>

  <script src="../assets/js/global.js"></script>
  <script>
    // Custom password toggle for login page
    document.addEventListener('DOMContentLoaded', function() {
      const passwordToggle = document.querySelector('.password-toggle');
      const passwordInput = document.getElementById('password');
      
      if (passwordToggle && passwordInput) {
        passwordToggle.addEventListener('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          const isPassword = passwordInput.type === 'password';
          passwordInput.type = isPassword ? 'text' : 'password';
          passwordToggle.classList.toggle('active');
        });
      }

      // Override FormValidator for login page - disable password length validation
      const originalValidateField = FormValidator.validateField;
      FormValidator.validateField = function(field) {
        // For login page password field, skip length validation
        if (field.id === 'password' && field.type === 'password') {
          const value = field.value.trim();
          let isValid = true;

          // Remove previous error states
          field.classList.remove('error');
          const errorMsg = field.parentElement.querySelector('.form-error');
          if (errorMsg) {
            errorMsg.remove();
          }

          // Only check if required and empty
          if (field.hasAttribute('required') && !value) {
            this.showError(field, 'This field is required');
            isValid = false;
          }

          return isValid;
        }

        // For other fields, use original validation but skip password length check
        const value = field.value.trim();
        let isValid = true;

        // Remove previous error states
        field.classList.remove('error');
        const errorMsg = field.parentElement.querySelector('.form-error');
        if (errorMsg) {
          errorMsg.remove();
        }

        // Required validation
        if (field.hasAttribute('required') && !value) {
          this.showError(field, 'This field is required');
          isValid = false;
        }

        // Email validation
        if (field.type === 'email' && value) {
          const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if (!emailRegex.test(value)) {
            this.showError(field, 'Please enter a valid email address');
            isValid = false;
          }
        }

        // Skip password length validation for login page
        // Password validation only for registration forms (with data-confirm attribute)
        if (field.type === 'password' && value && field.hasAttribute('data-confirm')) {
          if (value.length < 8) {
            this.showError(field, 'Password must be at least 8 characters');
            isValid = false;
          }
        }

        // Password confirmation
        if (field.hasAttribute('data-confirm')) {
          const confirmField = document.querySelector(`[name="${field.getAttribute('data-confirm')}"]`);
          if (confirmField && value !== confirmField.value) {
            this.showError(field, 'Passwords do not match');
            isValid = false;
          }
        }

        return isValid;
      };
    });

    document.getElementById('loginForm').addEventListener('submit', function(e) {
      e.preventDefault();
      if (FormValidator.validate(this)) {
        // Simulate login - replace with actual API call
        Toast.success('Login successful! Redirecting...', 'Success');
        setTimeout(() => {
          // Role will be determined by backend based on user credentials
          window.location.href = 'student/dashboard.php';
        }, 1500);
      }
    });
  </script>
</body>
</html>

