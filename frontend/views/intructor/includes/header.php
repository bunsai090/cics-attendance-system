<div class="main-header">
  <div style="display: flex; align-items: center; gap: var(--spacing-md);">
    <img src="../../assets/img/ZPPUS-CICS LOGO.jpg" alt="CICS Logo" style="width: 40px; height: 40px; border-radius: var(--radius-md);">
    <h1 class="main-header-title">Campus Attendance System</h1>
  </div>
  <div class="main-header-actions">
    <div style="display: flex; align-items: center; gap: var(--spacing-sm);">
      <div title="Profile">
        <?php
        // Prefer a project PNG if present on disk and fall back to an inline SVG avatar to avoid broken images.
        $profileImgFile = __DIR__ . '/../../../assets/img/Instructor-Profile.png';
        // Correct the URL to include the project directory
        $profileImgUrl = '/cics-attendance-system/frontend/assets/img/Instructor-Profile.png';

        if (file_exists($profileImgFile)) {
          echo '<img src="' . $profileImgUrl . '" alt="Profile" class="minimalist-avatar-img">';
        } else {
          // Inline minimalist SVG avatar (always renders)
          echo '
          <div class="minimalist-avatar" aria-hidden="true">
            <svg class="minimalist-avatar-img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <circle cx="12" cy="8" r="3.2" fill="#fff" stroke="#2b3b4a" stroke-width="0.8"/>
              <path d="M4 20c1.6-4 6.4-6 8-6s6.4 2 8 6" fill="#fff" stroke="#2b3b4a" stroke-width="0.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          ';
        }
        ?>
      </div>
    </div>
    <button class="btn-icon" title="Logout" id="instructorHeaderLogoutBtn">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
      </svg>
    </button>
  </div>
</div>