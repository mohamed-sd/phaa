/**
 * Authentication Check Utility
 * Include this in admin pages to require login
 */

const API_BASE = './api.php';

async function checkAuth() {
  try {
    const res = await fetch(`${API_BASE}?action=check_session`);
    const json = await res.json();
    
    if (!json.success) {
      // Not authenticated, redirect to login
      window.location.href = './login.php';
      return false;
    }
    
    // Store user info
    localStorage.setItem('auth_user', JSON.stringify(json.data));
    
    // Set up logout button if exists
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
      const userDisplay = document.getElementById('userDisplay');
      if (userDisplay) {
        userDisplay.textContent = `مرحباً ${json.data.full_name}`;
      }
      logoutBtn.addEventListener('click', logout);
    }
    
    return true;
  } catch (err) {
    console.error('Auth check failed:', err);
    window.location.href = './login.php';
    return false;
  }
}

async function logout() {
  try {
    const res = await fetch(`${API_BASE}?action=logout`, { method: 'POST' });
    const json = await res.json();
    if (json.success) {
      localStorage.removeItem('auth_user');
      window.location.href = './login.php';
    }
  } catch (err) {
    console.error('Logout failed:', err);
    window.location.href = './login.php';
  }
}

// Run auth check on page load
document.addEventListener('DOMContentLoaded', checkAuth);
