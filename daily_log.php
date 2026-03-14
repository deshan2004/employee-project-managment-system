<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['employee'])) {
    header("Location: employee_login.php");
    exit();
}

$employee_id = $_SESSION['employee'];

$stmt_emp = $conn->prepare("SELECT employee_id FROM employees WHERE user_id = ?");
$stmt_emp->bind_param("i", $employee_id);
$stmt_emp->execute();
$emp_res = $stmt_emp->get_result()->fetch_assoc();
$real_emp_id = $emp_res['employee_id'];

if (isset($_POST['submit'])) {
    $date = $_POST['date'];
    $work_done = $_POST['work_done'];

    if (!empty($date) && !empty($work_done)) {
        $stmt = $conn->prepare("INSERT INTO daily_logs (employee_id, date, work_done) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $real_emp_id, $date, $work_done);
        if($stmt->execute()) { $success = "Log added successfully!"; } 
        else { $error = "Error adding log."; }
    }
}

$logs_result = $conn->query("SELECT date, work_done FROM daily_logs WHERE employee_id = $real_emp_id ORDER BY date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Daily Logs - EPMS</title>
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
        
        h2 { font-size: 28px; font-weight: 700; color: #1e293b; margin-bottom: 25px; }
        h2 i { color: #4361ee; margin-right: 10px; }
        
        .log-card {
            background: white; border-radius: 20px; padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); margin-bottom: 25px;
        }
        
        .card-title {
            font-size: 18px; font-weight: 600; color: #1e293b;
            margin-bottom: 20px; display: flex; align-items: center; gap: 10px;
            padding-bottom: 15px; border-bottom: 2px solid #f1f5f9;
        }
        .card-title i { color: #4361ee; font-size: 22px; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block; margin-bottom: 8px; font-weight: 600;
            font-size: 14px; color: #1e293b;
        }
        
        .form-control {
            width: 100%; padding: 12px 16px; border: 2px solid #e2e8f0;
            border-radius: 12px; font-size: 15px; transition: all 0.3s ease;
        }
        .form-control:focus { outline: none; border-color: #4361ee; box-shadow: 0 0 0 4px rgba(67,97,238,0.1); }
        
        textarea.form-control { resize: vertical; min-height: 120px; }
        
        .btn-primary {
            width: 100%; padding: 14px; background: linear-gradient(135deg, #4361ee 0%, #3a56d4 100%);
            color: white; border: none; border-radius: 12px; font-size: 16px;
            font-weight: 600; cursor: pointer; transition: all 0.3s ease;
            display: flex; align-items: center; justify-content: center; gap: 10px;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(67,97,238,0.3); }
        
        .table-responsive { overflow-x: auto; }
        .table { width: 100%; border-collapse: collapse; }
        .table th {
            text-align: left; padding: 15px; background: #f8fafc;
            font-weight: 600; font-size: 14px; color: #1e293b;
            border-bottom: 2px solid #e2e8f0;
        }
        .table td { padding: 15px; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
        .table tbody tr:hover { background: #f8fafc; }
        
        .alert {
            padding: 15px; border-radius: 12px; margin-bottom: 20px;
            display: flex; align-items: center; gap: 10px; font-size: 14px;
        }
        .alert-success { background: #d1fae5; color: #10b981; border: 1px solid #a7f3d0; }
        .alert-danger { background: #fee2e2; color: #ef4444; border: 1px solid #fecaca; }
        
        .row { display: flex; flex-wrap: wrap; margin: 0 -15px; }
        .col-md-4 { width: 33.33%; padding: 0 15px; }
        .col-md-8 { width: 66.67%; padding: 0 15px; }
        
        .w-100 { width: 100%; }
        .mb-4 { margin-bottom: 1.5rem; }
        
        @media (max-width: 992px) { .sidebar { transform: translateX(-100%); } .main-content { margin-left: 0; } }
        @media (max-width: 768px) { .col-md-4, .col-md-8 { width: 100%; } .main-content { padding: 20px; } }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo"><i class="fas fa-project-diagram"></i> <span>EPMS</span></div>
        <ul>
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="profile.php"><i class="fas fa-user"></i> My Profile</a></li>
            <li class="active"><a href="daily_log.php"><i class="fas fa-book"></i> Daily Logs</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h2><i class="fas fa-clipboard-list"></i> Daily Work Progress</h2>

        <div class="row">
            <div class="col-md-4">
                <div class="log-card">
                    <div class="card-title"><i class="fas fa-pen"></i> Add New Log</div>
                    <?php if(isset($success)): ?>
                        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
                    <?php endif; ?>
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
                    <?php endif; ?>
                    <form method="post">
                        <div class="form-group">
                            <label><i class="fas fa-calendar"></i> Date</label>
                            <input type="date" name="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-tasks"></i> What did you do today?</label>
                            <textarea name="work_done" class="form-control" rows="5" placeholder="Describe your work..." required></textarea>
                        </div>
                        <button type="submit" name="submit" class="btn-primary w-100">
                            <i class="fas fa-save"></i> Save Log
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-md-8">
                <div class="log-card">
                    <div class="card-title"><i class="fas fa-history"></i> Recent Logs</div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Work Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($logs_result->num_rows > 0): ?>
                                    <?php while($row = $logs_result->fetch_assoc()): ?>
                                    <tr>
                                        <td style="white-space: nowrap; font-weight: 600;">
                                            <i class="fas fa-calendar-day" style="color: #4361ee; margin-right: 5px;"></i>
                                            <?php echo $row['date']; ?>
                                        </td>
                                        <td><?php echo nl2br(htmlspecialchars($row['work_done'])); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="2" style="text-align: center; color: #64748b; padding: 30px;">
                                            <i class="fas fa-info-circle" style="font-size: 24px; margin-bottom: 10px; display: block;"></i>
                                            No logs found. Add your first log!
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>