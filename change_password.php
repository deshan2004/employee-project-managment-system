<?php
session_start();
include("../config/db.php");

$user_id = $_SESSION['employee'] ?? $_SESSION['admin'] ?? 0;

if ($user_id == 0) {
    header("Location: ../employee_login.php");
    exit();
}

if(isset($_POST['change'])){
    $old = $_POST['old_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if ($new !== $confirm) {
        $error = "New passwords do not match!";
    } else {
        $stmt = $conn->prepare("SELECT password FROM users WHERE user_id=?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($hashed);
        $stmt->fetch();
        $stmt->close();

        if(password_verify($old, $hashed)){
            $new_hash = password_hash($new, PASSWORD_DEFAULT);
            $stmt2 = $conn->prepare("UPDATE users SET password=? WHERE user_id=?");
            $stmt2->bind_param("si", $new_hash, $user_id);
            
            if($stmt2->execute()){
                $success = "Password changed successfully!";
            } else {
                $error = "Update failed. Please try again.";
            }
            $stmt2->close();
        } else {
            $error = "Old password incorrect!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - EPMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e9edf5 100%);
            min-height: 100vh;
            color: #1e293b;
        }
        
        .sidebar {
            position: fixed; left: 0; top: 0; height: 100vh; width: 280px;
            background: linear-gradient(180deg, #1a1f2e 0%, #2a3142 100%);
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            transition: all 0.3s ease; z-index: 1000; overflow-y: auto;
            color: white;
        }
        
        .sidebar .logo { padding: 25px 20px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar .logo i { font-size: 32px; color: #6c7ee8; margin-right: 10px; }
        .sidebar .logo span { font-size: 20px; font-weight: 700; color: white; }
        
        .sidebar ul { list-style: none; padding: 20px 15px; }
        .sidebar ul li { margin-bottom: 5px; }
        .sidebar ul li a {
            display: flex; align-items: center; gap: 15px; padding: 14px 20px;
            color: rgba(255,255,255,0.7); text-decoration: none; font-size: 15px;
            font-weight: 500; border-radius: 12px; transition: all 0.3s ease;
        }
        .sidebar ul li a i { font-size: 20px; width: 24px; text-align: center; }
        .sidebar ul li:hover a, .sidebar ul li.active a { background: rgba(255,255,255,0.1); color: white; transform: translateX(5px); }
        .sidebar ul li.active a { background: linear-gradient(90deg, #4361ee 0%, #3a56d4 100%); box-shadow: 0 4px 15px rgba(67,97,238,0.3); }
        
        .main-content { margin-left: 280px; min-height: 100vh; padding: 30px; }
        
        .password-card {
            background: white; border-radius: 20px; padding: 35px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); max-width: 500px; margin: 40px auto;
        }
        
        .card-title { font-size: 24px; font-weight: 700; color: #1e293b; margin-bottom: 10px; display: flex; align-items: center; gap: 10px; }
        .card-title i { color: #4361ee; font-size: 28px; background: rgba(67,97,238,0.1); padding: 12px; border-radius: 12px; }
        .text-muted { color: #64748b; margin-bottom: 25px; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #1e293b; }
        .form-group label i { margin-right: 8px; color: #4361ee; }
        
        .form-control {
            width: 100%; padding: 14px 16px; border: 2px solid #e2e8f0;
            border-radius: 12px; font-size: 15px; transition: all 0.3s ease;
        }
        .form-control:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 4px rgba(67,97,238,0.1); }
        
        .password-input { position: relative; }
        
        .btn-primary {
            width: 100%; padding: 16px; background: linear-gradient(135deg, #4361ee 0%, #3a56d4 100%);
            color: white; border: none; border-radius: 12px; font-size: 16px;
            font-weight: 600; cursor: pointer; transition: all 0.3s ease;
            display: flex; align-items: center; justify-content: center; gap: 10px;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(67,97,238,0.3); }
        
        .alert {
            padding: 16px; border-radius: 12px; margin-bottom: 25px;
            display: flex; align-items: center; gap: 10px; font-size: 14px;
            animation: slideIn 0.3s ease;
        }
        .alert-success { background: #d1fae5; color: #10b981; border: 1px solid #a7f3d0; }
        .alert-danger { background: #fee2e2; color: #ef4444; border: 1px solid #fecaca; }
        
        @keyframes slideIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        
        .w-100 { width: 100%; }
        
        @media (max-width: 992px) { .sidebar { transform: translateX(-100%); } .main-content { margin-left: 0; } }
        @media (max-width: 768px) { .main-content { padding: 20px; } .password-card { padding: 25px; } }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo"><i class="fas fa-project-diagram"></i> <span>EPMS</span></div>
        <ul>
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="profile.php"><i class="fas fa-user"></i> My Profile</a></li>
            <li class="active"><a href="change_password.php"><i class="fas fa-key"></i> Password</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="password-card">
            <div class="card-title">
                <i class="fas fa-shield-alt"></i> Security Settings
            </div>
            <p class="text-muted">Update your account password regularly to keep it secure.</p>

            <?php if(isset($success)): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
            <?php endif; ?>

            <form method="post" id="passwordForm">
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Current Password</label>
                    <div class="password-input">
                        <input type="password" name="old_password" class="form-control" placeholder="Enter current password" required>
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-key"></i> New Password</label>
                    <input type="password" name="new_password" id="new_password" class="form-control" placeholder="At least 6 characters" required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-check-circle"></i> Confirm New Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Repeat new password" required>
                </div>

                <button type="submit" name="change" class="btn-primary w-100">
                    <i class="fas fa-sync-alt"></i> Update Password
                </button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const newPass = document.getElementById('new_password').value;
            const confirmPass = document.getElementById('confirm_password').value;

            if (newPass.length < 6) {
                e.preventDefault();
                alert('New password must be at least 6 characters long!');
            } else if (newPass !== confirmPass) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
        });
    </script>
</body>
</html>