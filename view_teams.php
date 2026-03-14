<?php
session_start();
include("../config/db.php"); 

if(!isset($_SESSION['admin'])) { 
    header("Location: admin_login.php"); 
    exit(); 
}

$sql = "SELECT p.project_name, 
               CONCAT(e.first_name, ' ', e.last_name) AS employee_name, 
               e.designation, 
               pa.role,
               p.start_date,
               p.end_date
        FROM project_assignments pa
        JOIN projects p ON pa.project_id = p.project_id
        JOIN employees e ON pa.employee_id = e.employee_id
        ORDER BY p.project_name ASC, pa.role ASC";

$result = $conn->query($sql);

$team_structure = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $team_structure[$row['project_name']][] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Structure - EPMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }
        
        .project-team-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            transition: transform 0.3s;
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .project-team-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
        }
        
        .project-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        
        .project-header::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 200px;
            height: 200px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }
        
        .project-header h3 {
            margin: 0;
            font-size: 20px;
            font-weight: 600;
            position: relative;
            z-index: 1;
        }
        
        .project-header p {
            margin: 5px 0 0;
            opacity: 0.9;
            font-size: 13px;
            position: relative;
            z-index: 1;
        }
        
        .project-dates {
            display: flex;
            gap: 20px;
            margin-top: 10px;
            font-size: 12px;
            opacity: 0.8;
            position: relative;
            z-index: 1;
        }
        
        .team-members {
            padding: 20px;
        }
        
        .member-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            border-bottom: 1px solid #eef2f6;
            transition: background 0.3s;
        }
        
        .member-item:last-child {
            border-bottom: none;
        }
        
        .member-item:hover {
            background: #f8faff;
        }
        
        .member-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 18px;
        }
        
        .member-info {
            flex: 1;
        }
        
        .member-info h4 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
        }
        
        .member-info p {
            margin: 5px 0 0;
            color: var(--gray);
            font-size: 13px;
        }
        
        .role-badge {
            background: rgba(102, 126, 234, 0.1);
            color: var(--primary);
            padding: 6px 15px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .stats-overview {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-item {
            background: white;
            padding: 20px;
            border-radius: 16px;
            text-align: center;
            box-shadow: var(--shadow-sm);
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: var(--gray);
            font-size: 13px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px;
            background: white;
            border-radius: 20px;
        }
        
        .empty-state i {
            font-size: 60px;
            color: var(--gray-light);
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            color: var(--dark);
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: var(--gray);
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .team-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-overview {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo">
            <i class="fas fa-project-diagram"></i>
            <span>EPMS Admin</span>
        </div>
        <ul>
            <li>
                <a href="dashboard.php">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="add_employee.php">
                    <i class="fas fa-user-plus"></i>
                    <span>Add Employee</span>
                </a>
            </li>
            <li>
                <a href="add_project.php">
                    <i class="fas fa-briefcase"></i>
                    <span>Add Project</span>
                </a>
            </li>
            <li>
                <a href="assign_project.php">
                    <i class="fas fa-users"></i>
                    <span>Assign Projects</span>
                </a>
            </li>
            <li class="active">
                <a href="view_teams.php">
                    <i class="fas fa-diagram-project"></i>
                    <span>Team Structure</span>
                </a>
            </li>
            <li class="logout">
                <a href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <button class="menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="page-title">
                <h1><i class="fas fa-diagram-project"></i> Team Structure</h1>
            </div>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span>Admin</span>
            </div>
        </div>

        <div class="content-wrapper">
            <?php
            $total_projects = count($team_structure);
            $total_assignments = $result ? $result->num_rows : 0;
            $unique_employees = $result ? $conn->query("SELECT COUNT(DISTINCT employee_id) as count FROM project_assignments")->fetch_assoc()['count'] : 0;
            ?>
            
            <div class="stats-overview">
                <div class="stat-item">
                    <div class="stat-value"><?php echo $total_projects; ?></div>
                    <div class="stat-label">Active Projects</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo $total_assignments; ?></div>
                    <div class="stat-label">Total Assignments</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo $unique_employees; ?></div>
                    <div class="stat-label">Team Members</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo $total_projects > 0 ? round($total_assignments / $total_projects, 1) : 0; ?></div>
                    <div class="stat-label">Avg Team Size</div>
                </div>
            </div>

            <?php if (!empty($team_structure)): ?>
                <div class="team-grid">
                    <?php foreach ($team_structure as $project_name => $members): ?>
                        <div class="project-team-card">
                            <div class="project-header">
                                <h3><?php echo htmlspecialchars($project_name); ?></h3>
                                <div class="project-dates">
                                    <?php 
                                    $first_member = $members[0];
                                    if (!empty($first_member['start_date'])): 
                                    ?>
                                        <span><i class="far fa-calendar-alt"></i> Start: <?php echo date('M d, Y', strtotime($first_member['start_date'])); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($first_member['end_date'])): ?>
                                        <span><i class="far fa-calendar-check"></i> End: <?php echo date('M d, Y', strtotime($first_member['end_date'])); ?></span>
                                    <?php endif; ?>
                                </div>
                                <p><i class="fas fa-users"></i> <?php echo count($members); ?> Team Members</p>
                            </div>
                            <div class="team-members">
                                <?php foreach ($members as $member): ?>
                                    <div class="member-item">
                                        <div class="member-avatar">
                                            <?php echo strtoupper(substr($member['employee_name'], 0, 1)); ?>
                                        </div>
                                        <div class="member-info">
                                            <h4><?php echo htmlspecialchars($member['employee_name']); ?></h4>
                                            <p><?php echo htmlspecialchars($member['designation'] ?? 'No Designation'); ?></p>
                                        </div>
                                        <div class="role-badge">
                                            <i class="fas fa-tag"></i>
                                            <?php echo htmlspecialchars($member['role']); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <h3>No Team Assignments Yet</h3>
                    <p>Start by assigning employees to projects to build your team structure.</p>
                    <a href="assign_project.php" class="btn-primary" style="display: inline-block; padding: 12px 30px; text-decoration: none;">
                        <i class="fas fa-plus-circle"></i>
                        Create Assignment
                    </a>
                </div>
            <?php endif; ?>

            <?php if (!empty($team_structure)): ?>
            <div style="margin-top: 30px; text-align: right;">
                <button class="btn-secondary" onclick="exportToPDF()" style="width: auto; padding: 10px 20px;">
                    <i class="fas fa-file-pdf"></i>
                    Export as PDF
                </button>
                <button class="btn-secondary" onclick="exportToExcel()" style="width: auto; padding: 10px 20px; margin-left: 10px;">
                    <i class="fas fa-file-excel"></i>
                    Export to Excel
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        function exportToPDF() {
            alert('PDF export functionality will be implemented here');
        }

        function exportToExcel() {
            alert('Excel export functionality will be implemented here');
        }

        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.placeholder = 'Search teams...';
        searchInput.style.cssText = 'padding: 10px 15px; border: 2px solid var(--gray-light); border-radius: 30px; width: 300px; margin-bottom: 20px;';
        
        const topBar = document.querySelector('.top-bar');
        const searchContainer = document.createElement('div');
        searchContainer.style.cssText = 'margin-left: auto; margin-right: 20px;';
        searchContainer.appendChild(searchInput);
        topBar.appendChild(searchContainer);

        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const projectCards = document.querySelectorAll('.project-team-card');
            
            projectCards.forEach(card => {
                const projectName = card.querySelector('.project-header h3').textContent.toLowerCase();
                const members = Array.from(card.querySelectorAll('.member-info h4')).map(m => m.textContent.toLowerCase());
                
                if (projectName.includes(searchTerm) || members.some(m => m.includes(searchTerm))) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>