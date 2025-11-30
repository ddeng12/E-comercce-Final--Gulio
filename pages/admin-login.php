<?php
/**
 * Admin Login Page
 * Secure login for administrators
 */

// Handle login form submission
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'admin_login') {
    $email = Security::sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } else {
        $auth = new Auth();
        $result = $auth->login($email, $password);
        
        if ($result['success']) {
            // Check if user is admin
            if (Auth::isAdmin()) {
                Logger::info('Admin login successful', ['email' => $email]);
                header('Location: admin/');
                exit;
            } else {
                $auth->logout();
                $error = 'Access denied. Admin privileges required.';
                Logger::warning('Non-admin attempted admin login', ['email' => $email]);
            }
        } else {
            $error = $result['message'];
            Logger::warning('Failed admin login attempt', ['email' => $email]);
        }
    }
}
?>

<div class="admin-login-screen">
    <div class="login-container">
        <div class="login-header">
            <div class="logo">
                <i class="fas fa-shield-alt"></i>
                <h1>Admin Access</h1>
            </div>
            <p>Secure administrator login</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="login-form">
            <input type="hidden" name="action" value="admin_login">
            
            <div class="form-group">
                <label for="email">
                    <i class="fas fa-user"></i>
                    Admin Email
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required 
                    autocomplete="username"
                    placeholder="Enter your admin email"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i>
                    Password
                </label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                    autocomplete="current-password"
                    placeholder="Enter your password"
                >
            </div>
            
            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i>
                Access Admin Dashboard
            </button>
        </form>
        
        <div class="login-footer">
            <p><a href="?page=login">← Regular User Login</a></p>
            <p><a href="?page=home">← Back to Home</a></p>
        </div>
        
        <div class="security-notice">
            <i class="fas fa-info-circle"></i>
            <small>This area is restricted to authorized administrators only. All access attempts are logged.</small>
        </div>
    </div>
</div>

<style>
.admin-login-screen {
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.login-container {
    background: white;
    border-radius: 15px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    padding: 40px;
    width: 100%;
    max-width: 400px;
    text-align: center;
}

.login-header {
    margin-bottom: 30px;
}

.logo {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    margin-bottom: 10px;
}

.logo i {
    font-size: 32px;
    color: #667eea;
}

.logo h1 {
    font-size: 28px;
    font-weight: 700;
    color: #333;
    margin: 0;
}

.login-header p {
    color: #666;
    font-size: 16px;
    margin: 0;
}

.alert {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    text-align: left;
}

.alert-error {
    background: #fee;
    color: #c33;
    border: 1px solid #fcc;
}

.alert-success {
    background: #efe;
    color: #363;
    border: 1px solid #cfc;
}

.login-form {
    text-align: left;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}

.form-group label i {
    color: #667eea;
    width: 16px;
}

.form-group input {
    width: 100%;
    padding: 15px;
    border: 2px solid #e1e5e9;
    border-radius: 8px;
    font-size: 16px;
    transition: border-color 0.3s;
    box-sizing: border-box;
}

.form-group input:focus {
    outline: none;
    border-color: #667eea;
}

.login-btn {
    width: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 15px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.login-btn:hover {
    transform: translateY(-2px);
}

.login-footer {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.login-footer p {
    margin: 8px 0;
}

.login-footer a {
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
}

.login-footer a:hover {
    text-decoration: underline;
}

.security-notice {
    margin-top: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    text-align: left;
}

.security-notice i {
    color: #6c757d;
    margin-top: 2px;
}

.security-notice small {
    color: #6c757d;
    line-height: 1.4;
}

@media (max-width: 480px) {
    .login-container {
        padding: 30px 20px;
    }
    
    .logo h1 {
        font-size: 24px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Focus on email field
    document.getElementById('email').focus();
    
    // Add loading state to form
    const form = document.querySelector('.login-form');
    const submitBtn = document.querySelector('.login-btn');
    
    form.addEventListener('submit', function() {
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Authenticating...';
        submitBtn.disabled = true;
    });
});
</script>
