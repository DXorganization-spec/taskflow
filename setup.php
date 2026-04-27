<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';

$sql = "CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_name VARCHAR(255) NOT NULL,
    due_date DATE,
    days_to_complete INT,
    status BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'tasks' created successfully!";
} else {
    echo "Error: " . $conn->error;
}
?>