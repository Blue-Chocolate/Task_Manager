<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/navbar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $due_date = $_POST['due_date'];
    $priority = $_POST['priority'];
    
    if (empty($title) ||  empty($due_date)) {
        $_SESSION['error'] = "Please fill all required fields.";
        header("Location: add_task.php");
        exit();
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description, due_date, priority) 
                              VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'],
            $title,
            $description,
            $due_date,
            $priority
        ]);
        
        $_SESSION['success'] = "Task added successfully!";
        header("Location: ../index.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error adding task: " . $e->getMessage();
        header("Location: add_task.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Task</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-6">Add New Task</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <form action="add_task.php" method="POST" class="max-w-md mx-auto bg-white p-6 rounded shadow">
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
            <button type="submit" 
                    class="w-full bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">
                Add Task
            </button>
        </form>
    </div>
</body>
</html>