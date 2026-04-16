<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $department = trim($_POST['department'] ?? '');
    
    
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        $error = "Please fill in all required fields.";
    } elseif (strlen($username) < 3) {
        $error = "Username must be at least 3 characters long.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $conn = getDBConnection();
        
        
        
        $check_username = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check_username->bind_param("s", $username);
        $check_username->execute();
        $check_username->store_result();
        
        if ($check_username->num_rows > 0) {
            $error = "Username already exists. Please choose another.";
        } else {
            
            $check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check_email->bind_param("s", $email);
            $check_email->execute();
            $check_email->store_result();
            
            if ($check_email->num_rows > 0) {
                $error = "Email already registered. Please use another email or login.";
            } else {
                
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, phone, department, role, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'User', 'Active', NOW())");
                $stmt->bind_param("ssssss", $username, $email, $hashed_password, $full_name, $phone, $department);
                
                if ($stmt->execute()) {
                    $success = "Registration successful! You can now login with your credentials.";
                    
                    $username = $email = $full_name = $phone = $department = '';
                } else {
                    $error = "Registration failed: " . $conn->error;
                }
                
                $stmt->close();
            }
            
            $check_email->close();
        }
        
        $check_username->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Incident Response System</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .password-strength {
            margin-top: 0.5rem;
            height: 4px;
            background: var(--accent-blue);
            border-radius: 2px;
            overflow: hidden;
        }
        .strength-bar {
            height: 100%;
            transition: width 0.3s ease, background-color 0.3s ease;
            width: 0%;
        }
        .strength-text {
            font-size: 0.85rem;
            margin-top: 0.25rem;
            color: var(--text-muted);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box" style="max-width: 600px;">
            <div class="login-header">
                <div class="login-icon">🛡️</div>
                <h1>Create Account</h1>
                <p>Join our Incident Response System</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                    <div style="margin-top: 1rem;">
                        <a href="login.php" class="btn btn-primary btn-block">Go to Login</a>
                    </div>
                </div>
            <?php else: ?>
                <form method="POST" action="register.php" class="login-form" id="registerForm">
                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" required 
                               placeholder="" value="<?php echo htmlspecialchars($full_name ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" id="username" name="username" class="form-control" required 
                               placeholder="Choose a username (min 3 characters)" 
                               value="<?php echo htmlspecialchars($username ?? ''); ?>"
                               minlength="3">
                        <small style="color: var(--text-muted); font-size: 0.85rem;">Must be at least 3 characters</small>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" class="form-control" required 
                               placeholder="your.email@example.com" 
                               value="<?php echo htmlspecialchars($email ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" class="form-control" required 
                               placeholder="Create a strong password (min 8 characters)" minlength="8">
                        <div class="password-strength">
                            <div class="strength-bar" id="strengthBar"></div>
                        </div>
                        <div class="strength-text" id="strengthText">Password strength: None</div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required 
                               placeholder="Re-enter your password">
                        <small id="matchText" style="color: var(--text-muted); font-size: 0.85rem;"></small>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number (Optional)</label>
                        <input type="tel" id="phone" name="phone" class="form-control" 
                               placeholder="+1234567890" value="<?php echo htmlspecialchars($phone ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="department">Department (Optional)</label>
                        <input type="text" id="department" name="department" class="form-control" 
                               placeholder="e.g., IT, HR, Finance" value="<?php echo htmlspecialchars($department ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">Create Account</button>
                    </div>

                    <div class="login-footer">
                        <p>Already have an account? <a href="login.php">Login here</a></p>
                        <p><a href="index.php">Back to Home</a></p>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
        
        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');
        const confirmPassword = document.getElementById('confirm_password');
        const matchText = document.getElementById('matchText');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            let strengthLabel = 'None';
            let color = 'transparent';

            if (password.length >= 8) strength++;
            if (password.match(/[a-z]+/)) strength++;
            if (password.match(/[A-Z]+/)) strength++;
            if (password.match(/[0-9]+/)) strength++;
            if (password.match(/[$@#&!]+/)) strength++;

            switch(strength) {
                case 0:
                case 1:
                    strengthLabel = 'Weak';
                    color = '#ef4444';
                    break;
                case 2:
                case 3:
                    strengthLabel = 'Medium';
                    color = '#fbbf24';
                    break;
                case 4:
                case 5:
                    strengthLabel = 'Strong';
                    color = '#4ade80';
                    break;
            }

            strengthBar.style.width = (strength * 20) + '%';
            strengthBar.style.backgroundColor = color;
            strengthText.textContent = 'Password strength: ' + strengthLabel;
            strengthText.style.color = color;
        });

        
        confirmPassword.addEventListener('input', function() {
            const password = passwordInput.value;
            const confirm = this.value;

            if (confirm.length === 0) {
                matchText.textContent = '';
            } else if (password === confirm) {
                matchText.textContent = '✓ Passwords match';
                matchText.style.color = '#4ade80';
            } else {
                matchText.textContent = '✗ Passwords do not match';
                matchText.style.color = '#ef4444';
            }
        });

        
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = passwordInput.value;
            const confirm = confirmPassword.value;

            if (password !== confirm) {
                e.preventDefault();
                alert('Passwords do not match!');
                confirmPassword.focus();
            }
        });
    </script>
</body>
</html>