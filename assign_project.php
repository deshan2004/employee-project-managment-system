<?php
session_start();
include("../config/db.php");

if(!isset($_SESSION['admin'])) { header("Location: admin_login.php"); exit(); }

if(isset($_POST['assign'])){
    $p_id = $_POST['project_id'];
    $e_id = $_POST['employee_id']; 
    $role = $_POST['role'];

    $stmt = $conn->prepare("INSERT INTO project_assignments (project_id, employee_id, role) VALUES (?,?,?)");
    $stmt->bind_param("iis", $p_id, $e_id, $role);
    
    if($stmt->execute()) {
        $success = "Employee Assigned Successfully!";
    } else {
        $error = "Error: " . $conn->error;
    }
}

$projects = $conn->query("SELECT project_id, project_name FROM projects");
$employees = $conn->query("SELECT employee_id, first_name, last_name, designation FROM employees");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Projects - EPMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .assignment-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            padding: 20px;
            color: white;
            margin-bottom: 20px;
        }
        .role-suggestions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        .role-tag {
            background: rgba(255,255,255,0.2);
            padding: 8px 15px;
            border-radius: 30px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s;
            border: 1px solid rgba(255,255,255,0.3);
        }
        .role-tag:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }
        .role-tag i {
            margin-right: 5px;
        }
        .employee-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .employee-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        .recent-assignments {
            background: white;
            border-radius: 16px;
            padding: 25px;
            margin-top: 30px;
            box-shadow: var(--shadow-lg);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
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
            <li class="active">
                <a href="assign_project.php">
                    <i class="fas fa-users"></i>
                    <span>Assign Projects</span>
                </a>
            </li>
            <li>
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
                <h1><i class="fas fa-users"></i> Assign Projects</h1>
            </div>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span>Admin</span>
            </div>
        </div>

        <div class="content-wrapper">
            <div class="assignment-card">
                <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                    <i class="fas fa-tasks" style="font-size: 28px;"></i>
                    <h3 style="margin: 0;">Team Assignment Portal</h3>
                </div>
                <p style="opacity: 0.9;">Assign employees to projects and define their roles for effective team management.</p>
            </div>

            <div class="form-container">
                <?php if(isset($success)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <?php if(isset($error)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="modern-form" id="assignForm">
                    <div class="form-header">
                        <h3>Create New Assignment</h3>
                        <p>Select project, employee, and define their role</p>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="project_id">
                                <i class="fas fa-briefcase"></i>
                                Select Project <span class="required">*</span>
                            </label>
                            <select name="project_id" id="project_id" required>
                                <option value="">-- Choose Project --</option>
                                <?php while($p = $projects->fetch_assoc()): ?>
                                    <option value="<?= $p['project_id'] ?>">
                                        <?= htmlspecialchars($p['project_name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="employee_id">
                                <i class="fas fa-user-tie"></i>
                                Select Employee <span class="required">*</span>
                            </label>
                            <select name="employee_id" id="employee_id" required>
                                <option value="">-- Choose Employee --</option>
                                <?php if($employees->num_rows > 0): ?>
                                    <?php while($e = $employees->fetch_assoc()): ?>
                                        <option value="<?= $e['employee_id'] ?>" 
                                                data-designation="<?= htmlspecialchars($e['designation'] ?? '') ?>">
                                            <?= htmlspecialchars($e['first_name'] . " " . $e['last_name']) ?>
                                            <?= $e['designation'] ? ' - ' . htmlspecialchars($e['designation']) : '' ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group full-width">
                            <label for="role">
                                <i class="fas fa-tag"></i>
                                Role in Project <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   id="role" 
                                   name="role" 
                                   placeholder="e.g., Lead Developer, Project Manager, UI Designer" 
                                   required>
                            
                            <div class="role-suggestions">
                                <span class="role-tag" onclick="setRole('Project Manager')">
                                    <i class="fas fa-crown"></i> Project Manager
                                </span>
                                <span class="role-tag" onclick="setRole('Lead Developer')">
                                    <i class="fas fa-code"></i> Lead Developer
                                </span>
                                <span class="role-tag" onclick="setRole('UI/UX Designer')">
                                    <i class="fas fa-paint-brush"></i> UI/UX Designer
                                </span>
                                <span class="role-tag" onclick="setRole('Quality Analyst')">
                                    <i class="fas fa-check-double"></i> Quality Analyst
                                </span>
                                <span class="role-tag" onclick="setRole('Database Admin')">
                                    <i class="fas fa-database"></i> Database Admin
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="reset" class="btn-secondary">
                            <i class="fas fa-undo"></i>
                            Clear
                        </button>
                        <button type="submit" name="assign" class="btn-primary">
                            <i class="fas fa-link"></i>
                            Assign to Project
                        </button>
                    </div>
                </form>
            </div>

            <div class="recent-assignments" id="previewSection" style="display: none;">
                <h3 style="margin-bottom: 20px;">
                    <i class="fas fa-eye" style="color: var(--primary);"></i>
                    Assignment Preview
                </h3>
                <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 200px;">
                        <label style="color: var(--gray); font-size: 13px;">Project</label>
                        <p id="previewProject" style="font-weight: 600; font-size: 16px;">-</p>
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <label style="color: var(--gray); font-size: 13px;">Employee</label>
                        <p id="previewEmployee" style="font-weight: 600; font-size: 16px;">-</p>
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <label style="color: var(--gray); font-size: 13px;">Role</label>
                        <p id="previewRole" style="font-weight: 600; font-size: 16px;">-</p>
                    </div>
                </div>
            </div>

            <div class="info-card" style="margin-top: 30px;">
                <div class="info-header">
                    <i class="fas fa-lightbulb"></i>
                    <h3>Assignment Best Practices</h3>
                </div>
                <ul class="info-list">
                    <li><i class="fas fa-check-circle"></i> Match employee skills with project requirements</li>
                    <li><i class="fas fa-check-circle"></i> Define clear roles and responsibilities</li>
                    <li><i class="fas fa-check-circle"></i> Consider workload balance across team members</li>
                    <li><i class="fas fa-check-circle"></i> Assign a project lead for better coordination</li>
                    <li><i class="fas fa-check-circle"></i> Document role expectations clearly</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        function setRole(role) {
            document.getElementById('role').value = role;
        }

        const projectSelect = document.getElementById('project_id');
        const employeeSelect = document.getElementById('employee_id');
        const roleInput = document.getElementById('role');
        const previewSection = document.getElementById('previewSection');
        const previewProject = document.getElementById('previewProject');
        const previewEmployee = document.getElementById('previewEmployee');
        const previewRole = document.getElementById('previewRole');

        function updatePreview() {
            let showPreview = false;

            if (projectSelect.value) {
                const projectText = projectSelect.options[projectSelect.selectedIndex].text;
                previewProject.textContent = projectText;
                showPreview = true;
            } else {
                previewProject.textContent = '-';
            }

            if (employeeSelect.value) {
                const employeeText = employeeSelect.options[employeeSelect.selectedIndex].text;
                previewEmployee.textContent = employeeText;
                showPreview = true;
            } else {
                previewEmployee.textContent = '-';
            }

            if (roleInput.value) {
                previewRole.textContent = roleInput.value;
                showPreview = true;
            } else {
                previewRole.textContent = '-';
            }

            if (showPreview) {
                previewSection.style.display = 'block';
            } else {
                previewSection.style.display = 'none';
            }
        }

        projectSelect.addEventListener('change', updatePreview);
        employeeSelect.addEventListener('change', updatePreview);
        roleInput.addEventListener('input', updatePreview);

        document.getElementById('assignForm').addEventListener('submit', function(e) {
            if (!projectSelect.value || !employeeSelect.value || !roleInput.value) {
                e.preventDefault();
                alert('Please fill in all required fields');
            }
        });

        employeeSelect.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const designation = selected.getAttribute('data-designation');
            
            if (designation) {
                console.log('Selected employee designation:', designation);
            }
        });
    </script>
</body>
</html>