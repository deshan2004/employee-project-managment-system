<?php
include("../config/db.php");

if(isset($_POST['add_team'])){
    $team_name = $_POST['team_name'];
    $stmt = $conn->prepare("INSERT INTO team_structure (team_name) VALUES (?)");
    $stmt->bind_param("s",$team_name);
    if($stmt->execute()) $success = "Team added successfully!";
    else $error = $conn->error;
}

if(isset($_POST['assign'])){
    $project_id = $_POST['project_id'];
    $emp_id = $_POST['emp_id'];
    $team_id = $_POST['team_id'];

    $stmt = $conn->prepare("INSERT INTO project_assignments (project_id, emp_id, team_id) VALUES (?,?,?)");
    $stmt->bind_param("iii", $project_id, $emp_id, $team_id);
    if($stmt->execute()) $success2 = "Employee assigned to project!";
    else $error2 = $conn->error;
}

$projects = $conn->query("SELECT * FROM projects");
$employees = $conn->query("SELECT e.emp_id,u.name FROM employees e JOIN users u ON e.user_id=u.user_id");
$teams = $conn->query("SELECT * FROM team_structure");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Team & Assignment</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<h2>Add Team</h2>
<form method="post">
    Team Name: <input type="text" name="team_name" required>
    <input type="submit" name="add_team" value="Add Team">
</form>
<?php if(isset($success)) echo $success; if(isset($error)) echo $error; ?>

<h2>Assign Employee to Project</h2>
<form method="post">
    Project: <select name="project_id" required>
        <?php while($p = $projects->fetch_assoc()){ echo "<option value='{$p['project_id']}'>{$p['project_name']}</option>"; } ?>
    </select><br>
    Employee: <select name="emp_id" required>
        <?php while($e = $employees->fetch_assoc()){ echo "<option value='{$e['emp_id']}'>{$e['name']}</option>"; } ?>
    </select><br>
    Team: <select name="team_id" required>
        <?php while($t = $teams->fetch_assoc()){ echo "<option value='{$t['team_id']}'>{$t['team_name']}</option>"; } ?>
    </select><br>
    <input type="submit" name="assign" value="Assign">
</form>
<?php if(isset($success2)) echo $success2; if(isset($error2)) echo $error2; ?>
</body>
</html>