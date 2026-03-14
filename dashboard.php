<?php
session_start();
if(!isset($_SESSION['employee'])){
    header("Location: employee_login.php");
    exit();
}
$user_id = $_SESSION['employee'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e9edf5 100%);
            min-height: 100vh;
            color: #1e293b;
            line-height: 1.6;
        }
        
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 280px;
            background: linear-gradient(180deg, #1a1f2e 0%, #2a3142 100%);
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            z-index: 1000;
            overflow-y: auto;
            color: white;
        }
        
        .sidebar .logo {
            padding: 25px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar .logo h4 {
            font-size: 22px;
            font-weight: 600;
            color: white;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .sidebar .logo h4 i {
            font-size: 28px;
            color: #6c7ee8;
            background: rgba(255, 255, 255, 0.1);
            padding: 8px;
            border-radius: 12px;
        }
        
        .sidebar ul {
            list-style: none;
            padding: 20px 15px;
        }
        
        .sidebar ul li {
            margin-bottom: 5px;
        }
        
        .sidebar ul li a {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 14px 20px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-size: 15px;
            font-weight: 500;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        
        .sidebar ul li a i {
            font-size: 20px;
            width: 24px;
            text-align: center;
        }
        
        .sidebar ul li:hover a,
        .sidebar ul li.active a {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }
        
        .sidebar ul li.active a {
            background: linear-gradient(90deg, #4361ee 0%, #3a56d4 100%);
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
        }
        
        .sidebar ul li.mt-5 {
            margin-top: 40px;
        }
        
        .sidebar ul li a.text-warning {
            color: #ffb347;
        }
        
        .main-content {
            margin-left: 280px;
            min-height: 100vh;
            padding: 30px;
            transition: all 0.3s ease;
        }
        
        .header-card {
            background: white;
            padding: 35px 30px;
            border-radius: 20px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            border: 1px solid rgba(0, 0, 0, 0.02);
            position: relative;
            overflow: hidden;
            animation: fadeInUp 0.5s ease;
        }
        
        .header-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 300px;
            height: 100%;
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.03) 0%, rgba(67, 97, 238, 0.08) 100%);
            clip-path: polygon(100% 0, 0% 100%, 100% 100%);
        }
        
        .header-card h2 {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
            position: relative;
        }
        
        .header-card h2::before {
            content: '👋';
            margin-right: 10px;
            font-size: 32px;
        }
        
        .header-card p {
            font-size: 16px;
            color: #64748b;
            position: relative;
            max-width: 500px;
        }
        
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }
        
        .card {
            background: white;
            border-radius: 20px;
            padding: 30px 25px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.12);
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.02);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            animation: fadeInUp 0.5s ease;
            animation-fill-mode: both;
        }
        
        .card:nth-child(1) {
            animation-delay: 0.1s;
        }
        
        .card:nth-child(2) {
            animation-delay: 0.2s;
        }
        
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 0;
            background: linear-gradient(180deg, #4361ee 0%, #6c7ee8 100%);
            transition: height 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
        }
        
        .card:hover::before {
            height: 100%;
        }
        
        .card i {
            font-size: 48px;
            margin-bottom: 20px;
            display: inline-block;
            padding: 15px;
            border-radius: 16px;
            transition: all 0.3s ease;
        }
        
        .card:first-child i {
            background: rgba(67, 97, 238, 0.1);
            color: #4361ee;
        }
        
        .card:last-child i {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }
        
        .card:hover i {
            transform: scale(1.1) rotate(5deg);
            background: #4361ee;
            color: white !important;
        }
        
        .card h3 {
            font-size: 22px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
        }
        
        .card p {
            color: #64748b;
            font-size: 14px;
            font-weight: 500;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .dashboard-cards {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .main-content {
                padding: 20px;
            }
            
            .header-card {
                padding: 25px 20px;
            }
            
            .header-card h2 {
                font-size: 24px;
            }
            
            .header-card h2::before {
                font-size: 28px;
            }
            
            .card {
                padding: 25px 20px;
            }
            
            .card i {
                font-size: 40px;
                padding: 12px;
            }
            
            .card h3 {
                font-size: 20px;
            }
        }
        
        @media (max-width: 480px) {
            .header-card h2 {
                font-size: 20px;
            }
            
            .header-card p {
                font-size: 14px;
            }
            
            .card i {
                font-size: 36px;
            }
            
            .card h3 {
                font-size: 18px;
            }
        }
        
        .mt-5 {
            margin-top: 3rem;
        }
        
        .text-warning {
            color: #ffb347;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <h4><i class="bi bi-person-badge"></i> Employee Port</h4>
        </div>
        <ul>
            <li class="active"><a href="dashboard.php"><i class="bi bi-house"></i> Dashboard</a></li>
            <li><a href="profile.php"><i class="bi bi-person-circle"></i> My Profile</a></li>
            <li><a href="daily_log.php"><i class="bi bi-journal-check"></i> Daily Logs</a></li>
            <li><a href="change_password.php"><i class="bi bi-shield-lock"></i> Password</a></li>
            <li class="mt-5"><a href="../logout.php" class="text-warning"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header-card">
            <h2>Welcome Back, Employee!</h2>
            <p>Check your assignments and update your daily progress.</p>
        </div>

        <div class="dashboard-cards">
            <div class="card">
                <i class="bi bi-card-list"></i>
                <h3>Tasks</h3>
                <p>View Assignments</p>
            </div>
            <div class="card">
                <i class="bi bi-calendar-event"></i>
                <h3>Logs</h3>
                <p>Update Daily Work</p>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.card').forEach(card => {
            card.addEventListener('click', function() {
                const link = this.querySelector('h3').innerText;
                if(link === 'Tasks') {
                    window.location.href = '#';
                } else if(link === 'Logs') {
                    window.location.href = 'daily_log.php';
                }
            });
        });
    </script>
</body>
</html>