<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/navbar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: reg-in-out/login.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY due_date");
$stmt->execute([$_SESSION['user_id']]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        #taskForm {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
            width: 90%;
            max-width: 500px;
        }
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 999;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-6">My Tasks</h1>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <div class="mb-6">
            <button onclick="openTaskForm()" 
                    class="bg-green-500 text-white py-2 px-4 rounded hover:bg-green-600">
                Add New Task
            </button>
        </div>
        
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($tasks as $task): ?>
            <div id="task-<?= $task['id'] ?>" class="bg-white p-4 rounded shadow transition-colors">
                <div class="flex items-center mb-2">
                    <input type="checkbox" id="checkbox-<?= $task['id'] ?>" 
                           class="mr-2" <?= $task['status'] == 'completed' ? 'checked' : '' ?>
                           onclick="toggleTaskStatus(<?= $task['id'] ?>)">
                    <h3 class="text-xl font-bold" id="title-<?= $task['id'] ?>">
                        <?= htmlspecialchars($task['title']) ?>
                    </h3>
                </div>
                <p class="text-gray-600 mb-2" id="desc-<?= $task['id'] ?>">
                    <?= htmlspecialchars($task['description']) ?>
                </p>
                <div class="flex justify-between items-center">
                    <span class="text-sm <?= 
                        $task['priority'] == 'high' ? 'text-red-500' : 
                        ($task['priority'] == 'medium' ? 'text-yellow-500' : 'text-green-500')
                    ?>">
                        <?= ucfirst($task['priority']) ?>
                    </span>
                    <span class="text-sm text-gray-500">Due: <?= $task['due_date'] ?></span>
                </div>
                <div class="mt-4 flex justify-between">
                    <a href="cruds/edit_task.php?id=<?= $task['id'] ?>" 
                       class="text-blue-500 hover:text-blue-700">Edit</a>
                    <button onclick="confirmDelete(<?= $task['id'] ?>)" 
                            class="text-red-500 hover:text-red-700">Delete</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div id="overlay" class="overlay" onclick="closeTaskForm()"></div>
        
        <div id="taskForm" class="bg-white p-6 rounded shadow">
            <h2 class="text-2xl font-bold mb-4">Add New Task</h2>
            <form method="POST" action="cruds/add_task.php">
                <div class="mb-4">
                    <label for="title" class="block text-gray-700">Title*</label>
                    <input type="text" name="title" required 
                           class="w-full px-3 py-2 border rounded">
                </div>
                <div class="mb-4">
                    <label for="description" class="block text-gray-700">Description*</label>
                    <textarea name="description"  
                              class="w-full px-3 py-2 border rounded"></textarea>
                </div>
                <div class="mb-4">
                    <label for="due_date" class="block text-gray-700">Due Date*</label>
                    <input type="date" name="due_date" required 
                           class="w-full px-3 py-2 border rounded">
                </div>
                <div class="mb-6">
                    <label for="priority" class="block text-gray-700">Priority*</label>
                    <select name="priority" required 
                            class="w-full px-3 py-2 border rounded">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="closeTaskForm()" 
                            class="bg-gray-500 text-white py-2 px-4 rounded hover:bg-gray-600">
                        Cancel
                    </button>
                    <button type="submit" name="confirm" 
                            class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">
                        Add Task
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Check all tasks on page load
            updateTaskBackgrounds();

            // Add event listeners to checkboxes
            document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                checkbox.addEventListener('change', () => {
                    const taskId = checkbox.id.split('-')[1];
                    toggleTaskStatus(taskId);
                    updateTaskBackground(taskId);
                });
            });
        });

        function updateTaskBackgrounds() {
            const tasks = document.querySelectorAll('[id^="task-"]');
            tasks.forEach(task => {
                const taskId = task.id.split('-')[1];
                updateTaskBackground(taskId);
            });
        }

        function updateTaskBackground(taskId) {
    const taskElement = document.getElementById(`task-${taskId}`);
    const checkbox = document.getElementById(`checkbox-${taskId}`);
    const dueDateText = taskElement.querySelector('.text-gray-500').textContent.replace('Due: ', '');
    const dueDate = new Date(dueDateText);
    const today = new Date();

    if (checkbox.checked) {
        if (dueDate < today) {
            // Task is completed but overdue
            taskElement.style.backgroundImage = "url('../Task_Manager/LATE.png')";
            taskElement.style.backgroundSize = "130px 80px"; // Smaller size
            taskElement.style.backgroundRepeat = "no-repeat";
            taskElement.style.backgroundPosition = "center";
            // taskElement.style.opacity = "0.3"; // Set opacity

            taskElement.classList.add('bg-green-100');
            taskElement.classList.remove('bg-red-100');
        } else {
            // Task is completed on time
            taskElement.style.backgroundImage = "none";
            taskElement.classList.add('bg-green-100');
            taskElement.classList.remove('bg-red-100');
        }
    } else {
        if (dueDate < today) {
            // Task is overdue but not completed
            taskElement.style.backgroundImage = "none";
            taskElement.classList.add('bg-red-100');
            taskElement.classList.remove('bg-green-100');
        } else {
            // Task is neither overdue nor completed
            taskElement.style.backgroundImage = "none";
            taskElement.classList.remove('bg-green-100', 'bg-red-100');
        }
    }
}

        function toggleTaskStatus(taskId) {
            const checkbox = document.getElementById(`checkbox-${taskId}`);
            const status = checkbox.checked ? 'completed' : 'pending';
            
            fetch(`cruds/update_status.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `task_id=${taskId}&status=${status}`
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('Error updating task status');
                    checkbox.checked = !checkbox.checked;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                checkbox.checked = !checkbox.checked;
            });
        }
        
        function openTaskForm() {
            document.getElementById('overlay').style.display = 'block';
            document.getElementById('taskForm').style.display = 'block';
        }
        
        function closeTaskForm() {
            document.getElementById('overlay').style.display = 'none';
            document.getElementById('taskForm').style.display = 'none';
        }
        
        function confirmDelete(taskId) {
            if (confirm('Are you sure you want to delete this task?')) {
                deleteTask(taskId);
            }
        }

        function deleteTask(taskId) {
            fetch('cruds/delete_task.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `task_id=${taskId}`
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    document.getElementById(`task-${taskId}`).remove();
                    showMessage('Task deleted successfully!', 'green');
                } else {
                    showMessage('Error: ' + data.message, 'red');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('An error occurred while deleting the task', 'red');
            });
        }

        function showMessage(message, color) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `bg-${color}-100 border border-${color}-400 text-${color}-700 px-4 py-3 rounded mb-4`;
            messageDiv.textContent = message;
            
            const container = document.querySelector('.container');
            if (container.firstChild) {
                container.insertBefore(messageDiv, container.firstChild.nextSibling);
            } else {
                container.appendChild(messageDiv);
            }
            
            setTimeout(() => {
                messageDiv.remove();
            }, 5000);
        }
    </script>
</body>
</html>