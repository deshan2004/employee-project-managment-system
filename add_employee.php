<?php
session_start();
include("../config/db.php");

if(!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

if(isset($_POST['register'])){
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];  
    $phone = $_POST['phone'];
    $department = $_POST['department'];
    $designation = $_POST['designation'] ?? 'Employee'; 

    $stmt = $conn->prepare("INSERT INTO users(email, password, role) VALUES (?, ?, 'employee')");
    $stmt->bind_param("ss", $email, $password);
    
    if($stmt->execute()){
        $user_id = $stmt->insert_id;

        $stmt2 = $conn->prepare("INSERT INTO employees(user_id, first_name, last_name, phone, department, designation) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt2->bind_param("isssss", $user_id, $first_name, $last_name, $phone, $department, $designation);
        
        if($stmt2->execute()){
            $success = "Employee Registered Successfully!";
        } else {
            $conn->query("DELETE FROM users WHERE user_id = $user_id");
            $error = "Error adding employee details: " . $conn->error;
        }
    } else {
        $error = "Error creating user account: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Employee - EPMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="sidebar">
        <div class="logo"><i class="fas fa-project-diagram"></i> <span>EPMS</span></div>
        <ul>
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li class="active"><a href="add_employee.php"><i class="fas fa-user-plus"></i> Add Employee</a></li>
            <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="container-fluid">
            <div class="profile-card" style="max-width: 700px; margin: auto; background: white; padding: 30px; border-radius: 15px;">
                <h2 style="margin-bottom: 20px;"><i class="fas fa-user-plus"></i> Register New Employee</h2>

                <?php if(isset($success)) echo "<div class='alert alert-success' style='color: green; font-weight: bold;'>$success</div>"; ?>
                <?php if(isset($error)) echo "<div class='alert alert-danger' style='color: red; font-weight: bold;'>$error</div>"; ?>

                <form method="post" id="employeeForm">
                    <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                        <div style="flex: 1;">
                            <label>First Name</label>
                            <input type="text" name="first_name" class="form-control" required placeholder="John">
                        </div>
                        <div style="flex: 1;">
                            <label>Last Name</label>
                            <input type="text" name="last_name" class="form-control" required placeholder="Doe">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 15px;">
                        <label>Email Address (Login Username)</label>
                        <input type="email" name="email" class="form-control" required placeholder="example@mail.com">
                    </div>

                    <div class="form-group" style="margin-bottom: 15px;">
                        <label>Login Password</label>
                        <input type="password" name="password" id="password" class="form-control" required placeholder="Minimum 6 characters">
                    </div>

                    <div style="display: flex; gap: 15px; margin-bottom: 20px;">
                        <div style="flex: 1;">
                            <label>Phone Number</label>
                            <input type="text" name="phone" class="form-control" placeholder="0712345678">
                        </div>
                        <div style="flex: 1;">
                            <label>Department</label>
                            <select name="department" class="form-control" required>
                                <option value="IT">IT Department</option>
                                <option value="HR">HR Department</option>
                                <option value="Finance">Finance</option>
                                <option value="Marketing">Marketing</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" name="register" class="btn-primary" style="width: 100%; padding: 12px; border-radius: 8px;">
                        <i class="fas fa-save"></i> Register Employee
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>