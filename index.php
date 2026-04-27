Read [](file:///c%3A/Users/Dell/OneDrive/Desktop/TaskFLow/index.php#1-1), lines 1 to 100

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

// Complete
if (isset($_GET['complete'])) {
    $conn->query("UPDATE tasks SET status=1 WHERE id=" . intval($_GET['complete']));
}

// Delete
if (isset($_GET['delete'])) {
    $conn->query("DELETE FROM tasks WHERE id=" . intval($_GET['delete']));
}

$result = $conn->query("SELECT * FROM tasks ORDER BY created_at DESC");

$total = $result->num_rows;
$completed = 0;
$tasks_data = [];

while($row = $result->fetch_assoc()){
    $tasks_data[] = $row;
    if($row['status']==1) $completed++;
}
$pending = $total - $completed;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚀 TaskFlow - Productivity Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            color: #e2e8f0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        .navbar {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.95), rgba(15, 23, 42, 0.95));
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(59, 130, 246, 0.3);
            padding: 25px 50px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1001;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }
        
        .navbar-content {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .navbar-logo {
            font-size: 32px;
            font-weight: 800;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6, #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .navbar-subtitle {
            font-size: 14px;
            color: #94a3b8;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .sidebar {
            position: fixed;
            left: 0;
            top: 90px;
            width: 300px;
            height: calc(100vh - 90px);
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.98), rgba(15, 23, 42, 0.98));
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(59, 130, 246, 0.2);
            padding: 50px 0;
            z-index: 1000;
            box-shadow: 4px 0 32px rgba(0, 0, 0, 0.2);
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-menu li {
            padding: 18px 30px;
            margin: 10px 20px;
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            position: relative;
            font-size: 16px;
        }
        
        .sidebar-menu li.active {
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            color: white;
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
            transform: translateX(8px);
        }
        
        .sidebar-menu li:hover {
            background: rgba(59, 130, 246, 0.15);
            transform: translateX(5px);
        }
        
        .sidebar-menu li::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 6px;
            height: 0;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            border-radius: 3px;
            transition: height 0.3s;
        }
        
        .sidebar-menu li.active::before {
            height: 100%;
        }
        
        .main-content {
            margin-left: 300px;
            margin-top: 90px;
            padding: 60px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.9), rgba(15, 23, 42, 0.9));
            backdrop-filter: blur(20px);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 20px;
            padding: 35px;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6, #ec4899);
            border-radius: 20px 20px 0 0;
        }
        
        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px rgba(59, 130, 246, 0.2);
            border-color: rgba(59, 130, 246, 0.4);
        }
        
        .stat-icon {
            font-size: 40px;
            margin-bottom: 20px;
            opacity: 0.9;
        }
        
        .stat-label {
            font-size: 16px;
            color: #94a3b8;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 600;
        }
        
        .stat-value {
            font-size: 56px;
            font-weight: 800;
            color: #3b82f6;
            line-height: 1;
            text-shadow: 0 2px 10px rgba(59, 130, 246, 0.3);
        }
        
        .weekly-section {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.9), rgba(15, 23, 42, 0.9));
            backdrop-filter: blur(20px);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 60px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }
        
        .weekly-section h3 {
            margin-bottom: 30px;
            font-size: 24px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #3b82f6;
        }
        
        .weekly-section h3::before {
            content: '📅';
            font-size: 28px;
        }
        
        .weekly-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .day-checkbox {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            padding: 20px;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.6), rgba(30, 41, 59, 0.6));
            border-radius: 16px;
            transition: all 0.3s ease;
            border: 1px solid rgba(59, 130, 246, 0.1);
        }
        
        .day-checkbox:hover {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(139, 92, 246, 0.1));
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.2);
        }
        
        .day-checkbox input[type="checkbox"] {
            width: 28px;
            height: 28px;
            accent-color: #3b82f6;
            cursor: pointer;
            border-radius: 6px;
        }
        
        .day-label {
            font-size: 16px;
            color: #cbd5e1;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 1px;
        }
        
        .progress-container {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.6), rgba(30, 41, 59, 0.6));
            border-radius: 16px;
            padding: 25px;
            margin-top: 30px;
            border: 1px solid rgba(59, 130, 246, 0.1);
        }
        
        .progress-bar {
            width: 100%;
            height: 12px;
            background: rgba(51, 65, 85, 0.6);
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 20px;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6, #ec4899);
            width: 0%;
            transition: width 0.5s ease;
            border-radius: 6px;
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.5);
        }
        
        .progress-text {
            text-align: center;
            font-size: 18px;
            color: #cbd5e1;
            font-weight: 700;
        }
        
        .task-input-section {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.9), rgba(15, 23, 42, 0.9));
            backdrop-filter: blur(20px);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 60px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }
        
        .task-input-section h3 {
            margin-bottom: 30px;
            font-size: 24px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #3b82f6;
        }
        
        .task-input-section h3::before {
            content: '✨';
            font-size: 28px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 25px;
            align-items: flex-end;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 12px;
            font-size: 16px;
            color: #cbd5e1;
            font-weight: 600;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 15px 20px;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.6), rgba(30, 41, 59, 0.6));
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 12px;
            color: #e2e8f0;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.8), rgba(30, 41, 59, 0.8));
            transform: translateY(-2px);
        }
        
        .btn-add {
            background: linear-gradient(135deg, #3b82f6, #8b5cf6, #ec4899);
            color: white;
            padding: 15px 35px;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            height: 50px;
            font-size: 16px;
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .btn-add:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(59, 130, 246, 0.6);
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 40px;
        }
        
        .tasks-section {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.9), rgba(15, 23, 42, 0.9));
            backdrop-filter: blur(20px);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }
        
        .section-header {
            padding: 30px 35px;
            border-bottom: 1px solid rgba(59, 130, 246, 0.2);
            font-size: 22px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #3b82f6;
        }
        
        .section-header::before {
            content: '📋';
            font-size: 26px;
        }
        
        .empty-state {
            padding: 60px;
            text-align: center;
            color: #64748b;
        }
        
        .empty-state p {
            font-size: 20px;
            margin: 0;
            font-weight: 600;
        }
        
        .tasks-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .tasks-table thead {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.6), rgba(30, 41, 59, 0.6));
        }
        
        .tasks-table th {
            padding: 20px 30px;
            text-align: left;
            font-size: 14px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            border-bottom: 1px solid rgba(59, 130, 246, 0.2);
            font-weight: 700;
        }
        
        .tasks-table td {
            padding: 20px 30px;
            border-bottom: 1px solid rgba(59, 130, 246, 0.1);
        }
        
        .tasks-table tr:hover {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(139, 92, 246, 0.05));
        }
        
        .tasks-table tr.completed {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(22, 163, 74, 0.1));
            border-left: 4px solid #22c55e;
        }
        
        .priority-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            border: 2px solid;
        }
        
        .priority-High {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(220, 38, 38, 0.2));
            color: #fca5a5;
            border-color: #ef4444;
        }
        
        .priority-Medium {
            background: linear-gradient(135deg, rgba(251, 146, 60, 0.2), rgba(234, 88, 12, 0.2));
            color: #fdba74;
            border-color: #fb923c;
        }
        
        .priority-Low {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.2), rgba(22, 163, 74, 0.2));
            color: #86efac;
            border-color: #22c55e;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 700;
            border: 2px solid;
        }
        
        .status-done {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.2), rgba(22, 163, 74, 0.2));
            color: #86efac;
            border-color: #22c55e;
        }
        
        .status-pending {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.2), rgba(147, 51, 234, 0.2));
            color: #d8b4fe;
            border-color: #a855f7;
        }
        
        .action-buttons {
            display: flex;
            gap: 12px;
        }
        
        .btn-action {
            background: linear-gradient(135deg, rgba(51, 65, 85, 0.6), rgba(30, 41, 59, 0.6));
            border: 1px solid rgba(59, 130, 246, 0.2);
            color: #64748b;
            cursor: pointer;
            padding: 10px 16px;
            border-radius: 10px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .btn-action:hover {
            color: #e2e8f0;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(139, 92, 246, 0.2));
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
        }
        
        .analytics-section {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.9), rgba(15, 23, 42, 0.9));
            backdrop-filter: blur(20px);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 20px;
            padding: 35px;
            display: flex;
            flex-direction: column;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }
        
        .chart-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #3b82f6;
        }
        
        .chart-title::before {
            content: '📊';
            font-size: 26px;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 30px;
        }
        
        .analytics-progress {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.6), rgba(30, 41, 59, 0.6));
            border-radius: 16px;
            padding: 25px;
            border: 1px solid rgba(59, 130, 246, 0.1);
        }
        
        .analytics-progress .progress-bar {
            width: 100%;
            height: 14px;
            background: rgba(51, 65, 85, 0.6);
            border-radius: 7px;
            overflow: hidden;
            margin-bottom: 20px;
            box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.2);
        }
        
        .analytics-progress .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6, #ec4899);
            width: <?php echo $total > 0 ? round(($completed / $total) * 100) : 0; ?>%;
            border-radius: 7px;
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.6);
        }
        
        .analytics-progress .progress-text {
            text-align: center;
            font-size: 18px;
            color: #cbd5e1;
            font-weight: 700;
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
            
            .weekly-grid {
                grid-template-columns: repeat(4, 1fr);
            }
            
            .navbar-content {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            
            .navbar-logo {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <div class="navbar-logo">🚀 TaskFlow</div>
            <div class="navbar-subtitle">Productivity Dashboard</div>
        </div>
    </nav>

    <div class="sidebar">
        <ul class="sidebar-menu">
            <li class="active">📊 Dashboard</li>
            <li>✓ Tasks</li>
            <li>📈 Analytics</li>
            <li>⚙️ Settings</li>
        </ul>
    </div>

    <div class="main-content">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📈</div>
                <div class="stat-label">Total Tasks</div>
                <div class="stat-value"><?php echo $total; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-label">Completed</div>
                <div class="stat-value"><?php echo $completed; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⏳</div>
                <div class="stat-label">Pending</div>
                <div class="stat-value"><?php echo $pending; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🎯</div>
                <div class="stat-label">Success Rate</div>
                <div class="stat-value"><?php echo $total > 0 ? round(($completed / $total) * 100) : 0; ?>%</div>
            </div>
        </div>

        <div class="weekly-section">
            <h3>Weekly Progress Tracker</h3>
            <div class="weekly-grid">
                <div class="day-checkbox">
                    <input type="checkbox" id="mon" onchange="updateProgress()">
                    <label for="mon" class="day-label">Mon</label>
                </div>
                <div class="day-checkbox">
                    <input type="checkbox" id="tue" onchange="updateProgress()">
                    <label for="tue" class="day-label">Tue</label>
                </div>
                <div class="day-checkbox">
                    <input type="checkbox" id="wed" onchange="updateProgress()">
                    <label for="wed" class="day-label">Wed</label>
                </div>
                <div class="day-checkbox">
                    <input type="checkbox" id="thu" onchange="updateProgress()">
                    <label for="thu" class="day-label">Thu</label>
                </div>
                <div class="day-checkbox">
                    <input type="checkbox" id="fri" onchange="updateProgress()">
                    <label for="fri" class="day-label">Fri</label>
                </div>
                <div class="day-checkbox">
                    <input type="checkbox" id="sat" onchange="updateProgress()">
                    <label for="sat" class="day-label">Sat</label>
                </div>
                <div class="day-checkbox">
                    <input type="checkbox" id="sun" onchange="updateProgress()">
                    <label for="sun" class="day-label">Sun</label>
                </div>
            </div>
            <div class="progress-container">
                <div class="progress-bar">
                    <div class="progress-fill" id="weeklyProgress"></div>
                </div>
                <div class="progress-text" id="weeklyProgressText">Weekly Progress: 0/7 days</div>
            </div>
        </div>

        <div class="task-input-section">
            <h3>Create New Task</h3>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Task Description</label>
                        <input type="text" name="task" placeholder="Enter detailed task description" required>
                    </div>
                    <div class="form-group">
                        <label>Priority Level</label>
                        <select name="priority" required>
                            <option value="">Select Priority</option>
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Due Date</label>
                        <input type="date" name="due_date" required>
                    </div>
                    <button type="submit" name="add" class="btn-add">Create Task</button>
                </div>
            </form>
        </div>

        <div class="content-grid">
            <div class="tasks-section">
                <div class="section-header">Task Management</div>
                <?php if ($total == 0): ?>
                <div class="empty-state">
                    <p>No tasks found. Start by creating your first task above!</p>
                </div>
                <?php else: ?>
                <table class="tasks-table">
                    <thead>
                        <tr>
                            <th>Task</th>
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
                            $status_text = $row['status'] ? '✅ Done' : '⏳ Pending';
                            $row_class = $row['status'] ? 'completed' : '';
                        ?>
                        <tr class="<?php echo $row_class; ?>">
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
                                    <a href="?complete=<?php echo $row['id']; ?>" class="btn-action" title="Mark Complete">✓ Complete</a>
                                    <?php endif; ?>
                                    <a href="?delete=<?php echo $row['id']; ?>" class="btn-action" title="Delete Task" onclick="return confirm('Are you sure you want to delete this task?')">🗑️ Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <div class="analytics-section">
                <div class="chart-title">Performance Analytics</div>
                <div class="chart-container">
                    <canvas id="taskChart"></canvas>
                </div>
                <div class="analytics-progress">
                    <div class="progress-bar">
                        <div class="progress-fill"></div>
                    </div>
                    <div class="progress-text">Completion Rate: <?php echo $total > 0 ? round(($completed / $total) * 100) : 0; ?>%</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Load weekly progress from localStorage
        document.addEventListener('DOMContentLoaded', function() {
            const days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
            days.forEach(day => {
                const checked = localStorage.getItem('weekly-' + day) === 'true';
                document.getElementById(day).checked = checked;
            });
            updateProgress();
        });

        function updateProgress() {
            const days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
            let checkedCount = 0;
            days.forEach(day => {
                const checkbox = document.getElementById(day);
                if (checkbox.checked) {
                    checkedCount++;
                    localStorage.setItem('weekly-' + day, 'true');
                } else {
                    localStorage.setItem('weekly-' + day, 'false');
                }
            });
            const progress = (checkedCount / 7) * 100;
            document.getElementById('weeklyProgress').style.width = progress + '%';
            document.getElementById('weeklyProgressText').textContent = 'Weekly Progress: ' + checkedCount + '/7 days';
        }

        const ctx = document.getElementById('taskChart').getContext('2d');
        const taskChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'Pending'],
                datasets: [{
                    data: [<?php echo $completed; ?>, <?php echo $pending; ?>],
                    backgroundColor: [
                        '#22c55e',
                        '#a855f7'
                    ],
                    borderColor: 'rgba(30, 41, 59, 0.8)',
                    borderWidth: 4,
                    hoverBorderWidth: 6
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
                            padding: 30,
                            font: {
                                size: 18,
                                weight: '700'
                            }
                        }
                    }
                },
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        });
    </script>
</body>
</html>