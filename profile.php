<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['employee'])) {
    header("Location: employee_login.php");
    exit();
}

$user_id = $_SESSION['employee'];

$stmt = $conn->prepare("SELECT u.email, e.full_name, e.designation, e.department, e.phone, e.bio, e.employee_id 
                        FROM users u 
                        JOIN employees e ON u.user_id = e.user_id 
                        WHERE u.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();

$emp_real_id = $employee['employee_id'];

if(isset($_POST['update'])){
    $name = $_POST['name'];
    $designation = $_POST['designation'];
    $department = $_POST['department'];
    $phone = $_POST['phone'];
    $bio = $_POST['bio'] ?? '';

    $up_stmt = $conn->prepare("UPDATE employees SET full_name=?, designation=?, department=?, phone=?, bio=? WHERE user_id=?");
    $up_stmt->bind_param("sssssi", $name, $designation, $department, $phone, $bio, $user_id);
    
    if($up_stmt->execute()) {
        $success = "Profile updated successfully!";
        $employee['full_name'] = $name;
        $employee['designation'] = $designation;
        $employee['department'] = $department;
        $employee['phone'] = $phone;
        $employee['bio'] = $bio;
    } else {
        $error = "Error updating profile: " . $conn->error;
    }
}

$stats_query = $conn->prepare("SELECT 
    COUNT(*) as total_logs,
    SUM(hours_worked) as total_hours,
    COUNT(DISTINCT DATE(date)) as active_days
    FROM daily_logs 
    WHERE employee_id = ?");
$stats_query->bind_param("i", $emp_real_id);
$stats_query->execute();
$stats = $stats_query->get_result()->fetch_assoc();

$assignments = $conn->query("SELECT p.project_name, pa.role, p.start_date, p.end_date
                            FROM project_assignments pa
                            JOIN projects p ON pa.project_id = p.project_id
                            WHERE pa.employee_id = $emp_real_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - EPMS</title>
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
        .sidebar ul li.logout { margin-top: 40px; }
        .sidebar ul li.logout a { color: #ffb347; }
        
        .main-content { margin-left: 280px; min-height: 100vh; transition: all 0.3s ease; }
        
        .top-bar {
            background: white; padding: 20px 30px; display: flex;
            align-items: center; justify-content: space-between;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 100;
        }
        
        .menu-toggle { display: none; background: none; border: none; font-size: 24px; color: #1e293b; cursor: pointer; }
        
        .page-title h1 { font-size: 24px; font-weight: 600; color: #1e293b; }
        .page-title h1 i { margin-right: 10px; color: #4361ee; }
        
        .user-info {
            display: flex; align-items: center; gap: 10px;
            background: #f1f5f9; padding: 8px 16px; border-radius: 30px;
            cursor: pointer; transition: all 0.3s ease;
        }
        .user-info:hover { background: #e2e8f0; }
        .user-info i { font-size: 24px; color: #4361ee; }
        .user-info span { font-weight: 500; }
        
        .content-wrapper { padding: 30px; }
        
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px; padding: 40px; color: white;
            margin-bottom: 30px; position: relative; overflow: hidden;
        }
        
        .profile-header::before {
            content: ''; position: absolute; top: -50%; right: -50%;
            width: 200%; height: 200%; background: rgba(255,255,255,0.1);
            transform: rotate(30deg);
        }
        
        .profile-avatar {
            width: 120px; height: 120px; background: white;
            border-radius: 50%; display: flex; align-items: center;
            justify-content: center; margin-bottom: 20px;
            border: 4px solid rgba(255,255,255,0.3); position: relative; z-index: 1;
        }
        .profile-avatar i { font-size: 60px; color: #667eea; }
        
        .profile-name { font-size: 32px; font-weight: 700; margin-bottom: 5px; position: relative; z-index: 1; }
        .profile-title { font-size: 18px; opacity: 0.9; margin-bottom: 15px; position: relative; z-index: 1; }
        
        .profile-stats {
            display: flex; gap: 40px; margin-top: 20px; position: relative; z-index: 1;
        }
        .stat { text-align: center; }
        .stat-value { font-size: 28px; font-weight: 700; }
        .stat-label { font-size: 14px; opacity: 0.9; }
        
        .profile-grid {
            display: grid; grid-template-columns: 2fr 1fr; gap: 25px;
        }
        
        .profile-card {
            background: white; border-radius: 20px; padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        
        .card-title {
            font-size: 18px; font-weight: 600; margin-bottom: 20px;
            display: flex; align-items: center; gap: 10px;
            padding-bottom: 15px; border-bottom: 2px solid #f1f5f9;
        }
        .card-title i { color: #4361ee; font-size: 22px; }
        
        /* Form */
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block; margin-bottom: 8px; font-weight: 600;
            font-size: 14px; color: #1e293b;
        }
        
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 12px 16px; border: 2px solid #e2e8f0;
            border-radius: 12px; font-size: 15px; transition: all 0.3s ease;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none; border-color: #4361ee; box-shadow: 0 0 0 4px rgba(67,97,238,0.1);
        }
        
        .btn-primary {
            background: #667eea; color: white; border: none;
            padding: 12px 25px; border-radius: 12px; font-size: 16px;
            font-weight: 600; cursor: pointer; transition: all 0.3s ease;
            display: inline-flex; align-items: center; gap: 10px;
        }
        .btn-primary:hover { background: #5a67d8; transform: translateY(-2px); box-shadow: 0 10px 20px rgba(102,126,234,0.3); }
        
        .info-row {
            display: flex; margin-bottom: 15px; padding-bottom: 15px;
            border-bottom: 1px solid #eef2f6;
        }
        .info-label { width: 100px; color: #64748b; font-size: 14px; }
        .info-value { flex: 1; font-weight: 500; color: #1e293b; }
        
        .assignment-item {
            display: flex; align-items: center; gap: 15px;
            padding: 15px; border-radius: 12px; background: #f8fafc;
            margin-bottom: 10px; transition: all 0.3s ease;
        }
        .assignment-item:hover { background: #f1f5f9; transform: translateX(5px); }
        
        .assignment-info { flex: 1; }
        .assignment-info h4 { font-size: 16px; font-weight: 600; color: #1e293b; margin-bottom: 5px; }
        .assignment-info p { font-size: 14px; color: #64748b; }
        
        .role-badge {
            background: rgba(102, 126, 234, 0.1); color: #667eea;
            padding: 4px 12px; border-radius: 30px; font-size: 12px;
            font-weight: 600; white-space: nowrap;
        }
        
        .alert {
            padding: 15px; border-radius: 12px; margin-bottom: 20px;
            display: flex; align-items: center; gap: 10px; font-size: 14px;
            animation: slideIn 0.3s ease;
        }
        .alert-success { background: #d1fae5; color: #10b981; border: 1px solid #a7f3d0; }
        .alert-error { background: #fee2e2; color: #ef4444; border: 1px solid #fecaca; }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.active { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .menu-toggle { display: block; }
        }
        
        @media (max-width: 768px) {
            .profile-grid { grid-template-columns: 1fr; }
            .profile-header { padding: 30px; }
            .profile-stats { gap: 20px; flex-wrap: wrap; }
            .profile-name { font-size: 24px; }
            .content-wrapper { padding: 20px; }
            .top-bar { padding: 15px 20px; }
        }
        
        @media (max-width: 480px) {
            .profile-avatar { width: 100px; height: 100px; }
            .profile-avatar i { font-size: 50px; }
            .profile-stats { flex-direction: column; gap: 15px; }
            .stat { display: flex; justify-content: space-between; align-items: center; }
            .user-info span { display: none; }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo"><i class="fas fa-user-tie"></i> <span>Employee Portal</span></div>
        <ul>
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li class="active"><a href="profile.php"><i class="fas fa-user-circle"></i> My Profile</a></li>
            <li><a href="daily_log.php"><i class="fas fa-journal-whills"></i> Daily Logs</a></li>
            <li><a href="change_password.php"><i class="fas fa-key"></i> Change Password</a></li>
            <li class="logout"><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
            <div class="page-title"><h1><i class="fas fa-user-circle"></i> My Profile</h1></div>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span><?php echo htmlspecialchars($employee['full_name'] ?? 'Employee'); ?></span>
            </div>
        </div>

        <div class="content-wrapper">
            <div class="profile-header">
                <div class="profile-avatar"><i class="fas fa-user-circle"></i></div>
                <div class="profile-name"><?php echo htmlspecialchars($employee['full_name'] ?? 'New Employee'); ?></div>
                <div class="profile-title">
                    <i class="fas fa-briefcase"></i> <?php echo htmlspecialchars($employee['designation'] ?? 'Designation'); ?> • 
                    <i class="fas fa-building"></i> <?php echo htmlspecialchars($employee['department'] ?? 'Department'); ?>
                </div>
                <div class="profile-stats">
                    <div class="stat">
                        <div class="stat-value"><?php echo $stats['total_logs'] ?? 0; ?></div>
                        <div class="stat-label"><i class="fas fa-clipboard-list"></i> Total Logs</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value"><?php echo number_format($stats['total_hours'] ?? 0, 1); ?></div>
                        <div class="stat-label"><i class="fas fa-clock"></i> Hours</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value"><?php echo $stats['active_days'] ?? 0; ?></div>
                        <div class="stat-label"><i class="fas fa-calendar-check"></i> Days</div>
                    </div>
                </div>
            </div>

            <div class="profile-grid">
                <!-- Left Column - Edit Profile -->
                <div class="profile-card">
                    <div class="card-title"><i class="fas fa-edit"></i> Edit Profile Information</div>

                    <?php if(isset($success)): ?>
                        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <?php if(isset($error)): ?>
                        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <label><i class="fas fa-user"></i> Full Name</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($employee['full_name'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-briefcase"></i> Designation</label>
                            <input type="text" name="designation" value="<?php echo htmlspecialchars($employee['designation'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-building"></i> Department</label>
                            <select name="department">
                                <option value="">Select Department</option>
                                <?php 
                                $depts = ['IT', 'HR', 'Finance', 'Marketing', 'Operations'];
                                foreach($depts as $d) {
                                    $sel = ($employee['department'] == $d) ? 'selected' : '';
                                    echo "<option value='$d' $sel>$d</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-phone"></i> Phone Number</label>
                            <input type="text" name="phone" value="<?php echo htmlspecialchars($employee['phone'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-align-left"></i> Bio</label>
                            <textarea name="bio" rows="4"><?php echo htmlspecialchars($employee['bio'] ?? ''); ?></textarea>
                        </div>
                        
                        <button type="submit" name="update" class="btn-primary">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </form>
                </div>

                <div>
                    <div class="profile-card" style="margin-bottom: 25px;">
                        <div class="card-title"><i class="fas fa-info-circle"></i> Quick Info</div>
                        <div class="info-row">
                            <div class="info-label"><i class="fas fa-id-card"></i> ID</div>
                            <div class="info-value">EMP-<?php echo str_pad($emp_real_id, 4, '0', STR_PAD_LEFT); ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label"><i class="fas fa-envelope"></i> Email</div>
                            <div class="info-value"><?php echo htmlspecialchars($employee['email']); ?></div>
                        </div>
                        <?php if(!empty($employee['phone'])): ?>
                        <div class="info-row">
                            <div class="info-label"><i class="fas fa-phone-alt"></i> Phone</div>
                            <div class="info-value"><?php echo htmlspecialchars($employee['phone']); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="profile-card">
                        <div class="card-title"><i class="fas fa-tasks"></i> Current Assignments</div>
                        <?php if ($assignments && $assignments->num_rows > 0): ?>
                            <?php while($ass = $assignments->fetch_assoc()): ?>
                                <div class="assignment-item">
                                    <i class="fas fa-project-diagram" style="color: #667eea; font-size: 20px;"></i>
                                    <div class="assignment-info">
                                        <h4><?php echo htmlspecialchars($ass['project_name']); ?></h4>
                                        <p><i class="fas fa-user-tag"></i> <?php echo htmlspecialchars($ass['role']); ?></p>
                                    </div>
                                    <span class="role-badge">Active</span>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div style="text-align: center; color: #64748b; padding: 30px;">
                                <i class="fas fa-folder-open" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                                <p>No assignments yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
    </script>
</body>
</html>