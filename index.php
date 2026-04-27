<?php
include 'db.php';

// Add Task
if (isset($_POST['add'])) {
    $task = trim(mysqli_real_escape_string($conn, $_POST['task']));
    $priority = trim(mysqli_real_escape_string($conn, $_POST['priority']));
    $due = trim(mysqli_real_escape_string($conn, $_POST['due_date']));
    
    if (!empty($task) && !empty($priority) && !empty($due)) {
        $conn->query("INSERT INTO tasks (task_name, priority, due_date, status) VALUES ('$task', '$priority', '$due', 0)");
    }
}

// Mark Complete
if (isset($_GET['complete'])) {
    $id = intval($_GET['complete']);
    $conn->query("UPDATE tasks SET status=1 WHERE id=$id");
}

// Delete Task
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM tasks WHERE id=$id");
}

// Get all tasks - SINGLE QUERY ONLY
$result = $conn->query("SELECT * FROM tasks ORDER BY created_at DESC");

// Calculate stats from result
$total = $result->num_rows;
$completed = 0;
$tasks_data = array();

while($row = $result->fetch_assoc()) {
    $tasks_data[] = $row;
    if($row['status'] == 1) {
        $completed++;
    }
}
$pending = $total - $completed;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskFlow - Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #0f172a;
            color: #e2e8f0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 260px;
            height: 100vh;
            background: #1e293b;
            border-right: 1px solid #334155;
            padding: 30px 0;
            z-index: 1000;
        }
        
        .sidebar-logo {
            padding: 0 20px 30px;
            border-bottom: 1px solid #334155;
            margin-bottom: 20px;
        }
        
        .sidebar-logo h2 {
            font-size: 24px;
            font-weight: 700;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-menu li {
            padding: 12px 20px;
            margin: 5px 10px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .sidebar-menu li.active {
            background: #3b82f6;
            color: white;
        }
        
        .sidebar-menu li:hover {
            background: #334155;
        }
        
        .main-content {
            margin-left: 260px;
            padding: 40px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }
        
        .header h1 {
            font-size: 32px;
            font-weight: 700;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 25px;
            transition: all 0.3s;
        }
        
        .stat-card:hover {
            border-color: #3b82f6;
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.1);
        }
        
        .stat-label {
            font-size: 14px;
            color: #94a3b8;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-value {
            font-size: 36px;
            font-weight: 700;
            color: #3b82f6;
        }
        
        .task-input-section {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 40px;
        }
        
        .task-input-section h3 {
            margin-bottom: 20px;
            font-size: 18px;
            font-weight: 600;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: #cbd5e1;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px 15px;
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 8px;
            color: #e2e8f0;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 15px;
            align-items: flex-end;
        }
        
        .btn-add {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            padding: 10px 25px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            height: 40px;
        }
        
        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }
        
        .tasks-section {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 12px;
            overflow: hidden;
        }
        
        .section-header {
            padding: 20px 25px;
            border-bottom: 1px solid #334155;
            font-size: 18px;
            font-weight: 600;
        }
        
        .empty-state {
            padding: 40px;
            text-align: center;
            color: #64748b;
        }
        
        .empty-state p {
            font-size: 16px;
            margin: 0;
        }
        
        .tasks-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .tasks-table thead {
            background: #0f172a;
        }
        
        .tasks-table th {
            padding: 15px 20px;
            text-align: left;
            font-size: 12px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #334155;
        }
        
        .tasks-table td {
            padding: 15px 20px;
            border-bottom: 1px solid #334155;
        }
        
        .tasks-table tr:hover {
            background: #0f172a;
        }
        
        .priority-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .priority-High {
            background: rgba(239, 68, 68, 0.2);
            color: #fca5a5;
        }
        
        .priority-Medium {
            background: rgba(251, 146, 60, 0.2);
            color: #fdba74;
        }
        
        .priority-Low {
            background: rgba(34, 197, 94, 0.2);
            color: #86efac;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-done {
            background: rgba(34, 197, 94, 0.2);
            color: #86efac;
        }
        
        .status-pending {
            background: rgba(168, 85, 247, 0.2);
            color: #d8b4fe;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn-action {
            background: none;
            border: none;
            color: #64748b;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 4px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
        }
        
        .btn-action:hover {
            color: #e2e8f0;
            background: #334155;
        }
        
        .chart-section {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 25px;
            display: flex;
            flex-direction: column;
        }
        
        .chart-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .chart-container {
            position: relative;
            height: 250px;
        }
        
        @media (max-width: 1200px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-logo">
            <h2>📋 TaskFlow</h2>
        </div>
        <ul class="sidebar-menu">
            <li class="active">📊 Dashboard</li>
            <li>✓ Tasks</li>
            <li>📈 Analytics</li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Welcome Back</h1>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Tasks</div>
                <div class="stat-value"><?php echo $total; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Completed</div>
                <div class="stat-value"><?php echo $completed; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Pending</div>
                <div class="stat-value"><?php echo $pending; ?></div>
            </div>
        </div>

        <div class="task-input-section">
            <h3>Add New Task</h3>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Task Name</label>
                        <input type="text" name="task" placeholder="Enter task name" required>
                    </div>
                    <div class="form-group">
                        <label>Priority</label>
                        <select name="priority" required>
                            <option value="">Select Priority</option>
                            <option value="Low">Low</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="High">High</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Due Date</label>
                        <input type="date" name="due_date" required>
                    </div>
                    <button type="submit" name="add" class="btn-add">Add</button>
                </div>
            </form>
        </div>

        <div class="content-grid">
            <div class="tasks-section">
                <div class="section-header">Tasks</div>
                <?php if ($total == 0): ?>
                <div class="empty-state">
                    <p>No tasks yet. Create one to get started!</p>
                </div>
                <?php else: ?>
                <table class="tasks-table">
                    <thead>
                        <tr>
                            <th>Task Name</th>
                            <th>Priority</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($tasks_data as $row): 
                            $priority_class = 'priority-' . $row['priority'];
                            $status_class = $row['status'] ? 'status-done' : 'status-pending';
                            $status_text = $row['status'] ? 'Done' : 'Pending';
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['task_name']); ?></td>
                            <td>
                                <span class="priority-badge <?php echo $priority_class; ?>">
                                    <?php echo htmlspecialchars($row['priority']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($row['due_date']); ?></td>
                            <td>
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <?php echo $status_text; ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <?php if(!$row['status']): ?>
                                    <a href="?complete=<?php echo $row['id']; ?>" class="btn-action" title="Complete">✓</a>
                                    <?php endif; ?>
                                    <a href="?delete=<?php echo $row['id']; ?>" class="btn-action" title="Delete" onclick="return confirm('Delete this task?')">✕</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <div class="chart-section">
                <div class="chart-title">Task Overview</div>
                <div class="chart-container">
                    <canvas id="taskChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('taskChart').getContext('2d');
        const taskChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'Pending'],
                datasets: [{
                    data: [<?php echo $completed; ?>, <?php echo $pending; ?>],
                    backgroundColor: [
                        '#22c55e',
                        '#8b5cf6'
                    ],
                    borderColor: '#1e293b',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#e2e8f0',
                            padding: 20,
                            font: {
                                size: 14
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>