<?php
require_once __DIR__ . '/../includes/auth.php';

$error = '';
$message = '';
$returnUrl = $_GET['return'] ?? '?page=home';

// Check if user was redirected after successful registration
if (isset($_GET['registered']) && $_GET['registered'] == '1') {
    $message = 'Registration successful! Please log in with your credentials.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    try {
        $email = Security::sanitizeInput($_POST['email'] ?? '', 'email');
        $password = $_POST['password'] ?? '';
        
        // Get return URL from POST (preserved from GET)
        $returnUrl = Security::sanitizeInput($_POST['return_url'] ?? $_GET['return'] ?? '?page=home', 'url');
        
        $auth = new Auth();
        $result = $auth->login($email, $password);
        
        if ($result['success']) {
            // Get user role and redirect accordingly
            $userRole = Auth::getUserRole();
            
            // Automatic role-based redirection
            // Check both account_role and role fields
            $accountRole = $_SESSION['user']['account_role'] ?? $userRole;
            $isAdmin = in_array($accountRole, ['admin', 'super_admin', 'vendor']) || 
                       in_array($userRole, ['admin', 'super_admin', 'vendor']);
            
            if ($isAdmin) {
                // Admins and vendors go to admin dashboard
                Logger::info('Admin/Vendor login successful - redirecting to admin dashboard', [
                    'email' => $email, 
                    'userRole' => $userRole,
                    'account_role' => $accountRole,
                    'session_user' => $_SESSION['user'] ?? []
                ]);
                header('Location: admin/');
                exit;
            } else {
                // Regular users go to return URL or home
                $redirectUrl = filter_var($returnUrl, FILTER_SANITIZE_URL);
                if (empty($redirectUrl) || !preg_match('/^\?page=/', $redirectUrl)) {
                    $redirectUrl = '?page=home';
                }
                Logger::info('User login successful', ['email' => $email, 'role' => $userRole, 'redirect' => $redirectUrl]);
                header('Location: ' . $redirectUrl);
                exit;
            }
        } else {
            $error = $result['message'] ?? 'Login failed';
        }
    } catch (Exception $e) {
        $error = 'Login failed: ' . $e->getMessage();
        Logger::error('Login exception', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    }
}
?>

<div class="login-screen">
    <div class="header">
        <h1>Welcome Back!</h1>
        <p>Sign in to continue</p>
        <small style="opacity: 0.7; font-size: 12px; display: block; margin-top: 8px;">
            <i class="fas fa-info-circle"></i> Admins and vendors will be automatically redirected to the admin dashboard after login
        </small>
    </div>
    
    <div class="content">
        <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?= Security::escape($error) ?>
            <?php if (strpos($error, 'Database') !== false || strpos($error, 'setup.php') !== false): ?>
                <br><br>
                <a href="setup.php" style="color: #FF6B35; font-weight: bold;">Click here to run database setup</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?= Security::escape($message) ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" class="auth-form">
            <input type="hidden" name="action" value="login">
            <input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">
            <input type="hidden" name="return_url" value="<?= Security::escape($returnUrl) ?>">
            
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-envelope"></i>
                    Email Address
                </label>
                <input 
                    type="email" 
                    name="email" 
                    class="form-input" 
                    placeholder="your@email.com"
                    required
                    autocomplete="email"
                    value="<?= Security::escape($_POST['email'] ?? '') ?>"
                >
            </div>
            
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-lock"></i>
                    Password
                </label>
                <input 
                    type="password" 
                    name="password" 
                    class="form-input" 
                    placeholder="Enter your password"
                    required
                    autocomplete="current-password"
                >
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-full">
                    <i class="fas fa-sign-in-alt"></i>
                    Sign In
                </button>
            </div>
            
            <div class="form-group" style="text-align: center; margin-top: var(--space-sm);">
                <a href="?page=forgot-password" style="color: var(--primary-orange); text-decoration: none; font-size: 14px;">
                    Forgot your password?
                </a>
            </div>
        </form>
        
        <div class="auth-links">
            <p>Don't have an account? <a href="?page=register">Sign up</a></p>
            <p><a href="?page=home">Continue as Guest</a></p>
        </div>
    </div>
</div>

<style>
.login-screen {
    min-height: 100vh;
    background: var(--light-gray);
}

.auth-form {
    background: var(--white);
    padding: var(--space-lg);
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-sm);
    margin-bottom: var(--space-md);
}

.form-group {
    margin-bottom: var(--space-md);
}

.form-label {
    display: block;
    font-weight: 500;
    color: var(--text-dark);
    margin-bottom: var(--space-sm);
    display: flex;
    align-items: center;
    gap: var(--space-xs);
}

.form-label i {
    color: var(--primary-orange);
}

.form-input {
    width: 100%;
    padding: var(--space-md);
    border: 2px solid var(--medium-gray);
    border-radius: var(--radius-md);
    font-size: 16px;
    transition: border-color 0.3s ease;
}

.form-input:focus {
    outline: none;
    border-color: var(--primary-orange);
}

.btn-full {
    width: 100%;
    padding: var(--space-md);
    font-size: 16px;
}

.alert {
    padding: var(--space-md);
    border-radius: var(--radius-md);
    margin-bottom: var(--space-md);
    display: flex;
    align-items: center;
    gap: var(--space-sm);
}

.alert-error {
    background: #fee;
    color: #c33;
    border-left: 4px solid #dc3545;
}

.alert-success {
    background: #efe;
    color: #3c3;
    border-left: 4px solid #28a745;
}

.auth-links {
    text-align: center;
    padding: var(--space-md);
}

.auth-links a {
    color: var(--primary-orange);
    text-decoration: none;
    font-weight: 500;
}

.auth-links a:hover {
    text-decoration: underline;
}

</style>

