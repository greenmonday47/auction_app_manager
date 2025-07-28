<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Auction System</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            max-width: 400px;
        }
        
        .login-header {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .login-header h2 {
            margin: 0;
            font-weight: 300;
        }
        
        .login-body {
            padding: 40px 30px;
        }
        
        .form-floating {
            margin-bottom: 20px;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            border: none;
            border-radius: 10px;
            padding: 15px;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        
        .alert {
            border: none;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .setup-section {
            border-top: 1px solid #e9ecef;
            margin-top: 30px;
            padding-top: 30px;
        }
        
        .setup-section h5 {
            color: #6c757d;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
        }
        
        .pin-input {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 5px;
        }
        
        .pin-input::placeholder {
            letter-spacing: 5px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-gavel fa-3x mb-3"></i>
            <h2>Auction Admin</h2>
            <p class="mb-0">Administrative Access</p>
        </div>
        
        <div class="login-body">
            <!-- Flash Messages -->
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>
            
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>
            
            <!-- Login Form -->
            <form action="<?= base_url('admin/login') ?>" method="post">
                <div class="form-floating">
                    <input type="password" 
                           class="form-control pin-input" 
                           id="pin" 
                           name="pin" 
                           placeholder="••••" 
                           maxlength="4" 
                           pattern="[0-9]{4}" 
                           required>
                    <label for="pin">Enter PIN</label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-login">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
            
            <!-- Setup Section (only show if no admin exists) -->
            <?php 
            $adminModel = new \App\Models\AdminModel();
            if ($adminModel->countAll() === 0): 
            ?>
            <div class="setup-section">
                <h5>Initial Setup</h5>
                <p class="text-muted small">No admin account found. Create the first admin account:</p>
                
                <form action="<?= base_url('admin/setup') ?>" method="post">
                    <div class="form-floating">
                        <input type="password" 
                               class="form-control pin-input" 
                               id="setup_pin" 
                               name="pin" 
                               placeholder="••••" 
                               maxlength="4" 
                               pattern="[0-9]{4}" 
                               required>
                        <label for="setup_pin">Create Admin PIN</label>
                    </div>
                    
                    <button type="submit" class="btn btn-success btn-login">
                        <i class="fas fa-user-plus"></i> Create Admin Account
                    </button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // PIN input formatting
        document.querySelectorAll('.pin-input').forEach(input => {
            input.addEventListener('input', function(e) {
                // Only allow numbers
                this.value = this.value.replace(/[^0-9]/g, '');
                
                // Auto-submit when 4 digits are entered
                if (this.value.length === 4) {
                    this.form.submit();
                }
            });
            
            // Focus on first PIN input
            if (input.id === 'pin') {
                input.focus();
            }
        });
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    </script>
</body>
</html> 