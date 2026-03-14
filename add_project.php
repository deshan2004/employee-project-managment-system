<?php
session_start();
include("../config/db.php");
if(!isset($_SESSION['admin'])) { header("Location: admin_login.php"); exit(); }

if (isset($_POST['register_project'])) {
    $project_name = $_POST['project_name'];
    $description = $_POST['description'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    $stmt = $conn->prepare("INSERT INTO projects (project_name, description, start_date, end_date) VALUES (?,?,?,?)");
    
    if ($stmt) {
        $stmt->bind_param("ssss", $project_name, $description, $start_date, $end_date);
        if($stmt->execute()){
            $success = "Project registered successfully!";
        } else {
            $error = "Execution Error: " . $conn->error;
        }
    } else {
        $error = "Database Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Project - EPMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .project-preview {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            padding: 25px;
            color: white;
            margin-top: 30px;
        }
        .date-range {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        .date-range .form-group {
            flex: 1;
        }
        .date-separator {
            color: var(--gray);
            font-weight: bold;
            margin-top: 10px;
        }
        .char-counter {
            font-size: 12px;
            color: var(--gray);
            text-align: right;
            margin-top: 5px;
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
            <li class="active">
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
                <h1><i class="fas fa-briefcase"></i> Register New Project</h1>
            </div>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span>Admin</span>
            </div>
        </div>

        <div class="content-wrapper">
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

                <form method="POST" class="modern-form" id="projectForm">
                    <div class="form-header">
                        <h3>Project Information</h3>
                        <p>Fill in the details to create a new project</p>
                    </div>

                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label for="project_name">
                                <i class="fas fa-tag"></i>
                                Project Name <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   id="project_name" 
                                   name="project_name" 
                                   placeholder="e.g., Website Redesign" 
                                   required>
                        </div>

                        <div class="form-group full-width">
                            <label for="description">
                                <i class="fas fa-align-left"></i>
                                Project Description
                            </label>
                            <textarea id="description" 
                                      name="description" 
                                      rows="5" 
                                      placeholder="Describe the project scope, objectives, and key deliverables..."
                                      oninput="updateCharCount()"></textarea>
                            <div class="char-counter">
                                <span id="charCount">0</span>/500 characters
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="start_date">
                                <i class="fas fa-calendar-alt"></i>
                                Start Date
                            </label>
                            <input type="date" 
                                   id="start_date" 
                                   name="start_date" 
                                   value="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <div class="form-group">
                            <label for="end_date">
                                <i class="fas fa-calendar-check"></i>
                                End Date
                            </label>
                            <input type="date" 
                                   id="end_date" 
                                   name="end_date">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="reset" class="btn-secondary" onclick="resetForm()">
                            <i class="fas fa-undo"></i>
                            Reset
                        </button>
                        <button type="submit" name="register_project" class="btn-primary">
                            <i class="fas fa-save"></i>
                            Register Project
                        </button>
                    </div>
                </form>
            </div>

            <div class="project-preview">
                <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                    <i class="fas fa-info-circle" style="font-size: 32px;"></i>
                    <h3 style="margin: 0;">Project Preview</h3>
                </div>
                <div id="previewContent">
                    <p style="opacity: 0.9; margin-bottom: 15px;">Fill in the project details to see a preview</p>
                </div>
            </div>

            <div class="info-card" style="margin-top: 30px;">
                <div class="info-header">
                    <i class="fas fa-lightbulb"></i>
                    <h3>Project Planning Tips</h3>
                </div>
                <ul class="info-list">
                    <li><i class="fas fa-check-circle"></i> Define clear project objectives</li>
                    <li><i class="fas fa-check-circle"></i> Set realistic deadlines</li>
                    <li><i class="fas fa-check-circle"></i> Identify required resources</li>
                    <li><i class="fas fa-check-circle"></i> Plan for risk management</li>
                    <li><i class="fas fa-check-circle"></i> Establish communication channels</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        function updateCharCount() {
            const description = document.getElementById('description');
            const charCount = document.getElementById('charCount');
            charCount.textContent = description.value.length;
            
            if (description.value.length > 450) {
                charCount.style.color = '#ef4444';
            } else {
                charCount.style.color = 'var(--gray)';
            }
        }

        const projectName = document.getElementById('project_name');
        const description = document.getElementById('description');
        const startDate = document.getElementById('start_date');
        const endDate = document.getElementById('end_date');
        const previewContent = document.getElementById('previewContent');

        function updatePreview() {
            let html = '';
            
            if (projectName.value) {
                html += `<h4 style="font-size: 20px; margin-bottom: 10px;">${projectName.value}</h4>`;
            }
            
            if (description.value) {
                html += `<p style="opacity: 0.9; margin-bottom: 15px;">${description.value.substring(0, 100)}${description.value.length > 100 ? '...' : ''}</p>`;
            }
            
            if (startDate.value || endDate.value) {
                html += '<div style="display: flex; gap: 20px; margin-top: 15px;">';
                if (startDate.value) {
                    html += `<div><i class="fas fa-calendar-alt" style="margin-right: 5px;"></i> Start: ${new Date(startDate.value).toLocaleDateString()}</div>`;
                }
                if (endDate.value) {
                    html += `<div><i class="fas fa-calendar-check" style="margin-right: 5px;"></i> End: ${new Date(endDate.value).toLocaleDateString()}</div>`;
                }
                html += '</div>';
            }
            
            if (html) {
                previewContent.innerHTML = html;
            } else {
                previewContent.innerHTML = '<p style="opacity: 0.9; margin-bottom: 15px;">Fill in the project details to see a preview</p>';
            }
        }

        projectName.addEventListener('input', updatePreview);
        description.addEventListener('input', updatePreview);
        startDate.addEventListener('change', updatePreview);
        endDate.addEventListener('change', updatePreview);

        function resetForm() {
            document.getElementById('projectForm').reset();
            updateCharCount();
            updatePreview();
        }

        document.getElementById('projectForm').addEventListener('submit', function(e) {
            const start = new Date(document.getElementById('start_date').value);
            const end = new Date(document.getElementById('end_date').value);
            
            if (document.getElementById('end_date').value && start > end) {
                e.preventDefault();
                alert('End date must be after start date');
            }
        });

        document.getElementById('start_date').addEventListener('change', function() {
            document.getElementById('end_date').min = this.value;
        });
    </script>
</body>
</html>