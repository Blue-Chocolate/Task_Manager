<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/navbar.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../reg-in-out/login.php");
    exit();
}

// Check if task ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Task ID not provided";
    header("Location: ../index.php");
    exit();
}

$task_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch the task to edit (only if it belongs to the current user)
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
$stmt->execute([$task_id, $user_id]);
$task = $stmt->fetch();

if (!$task) {
    $_SESSION['error'] = "Task not found or you don't have permission";
    header("Location: ../index.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $due_date = $_POST['due_date'];
    $priority = $_POST['priority'];
    $status = $_POST['status'];

    if (empty($title) || empty($description) || empty($due_date)) {
        $_SESSION['error'] = "Please fill all required fields";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE tasks SET 
                title = ?, 
                description = ?, 
                due_date = ?, 
                priority = ?, 
                status = ?,
                updated_at = NOW()
                WHERE id = ? AND user_id = ?");

            $stmt->execute([
                $title,
                $description,
                $due_date,
                $priority,
                $status,
                $task_id,
                $user_id
            ]);

            $_SESSION['success'] = "Task updated successfully!";
            header("Location: ../index.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error updating task: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-6">Edit Task</h1>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="max-w-md mx-auto bg-white p-6 rounded shadow">
            <div class="mb-4">
                <label for="title" class="block text-gray-700">Title*</label>
                <input type="text" name="title" required 
                       value="<?= htmlspecialchars($task['title']) ?>" 
                       class="w-full px-3 py-2 border rounded">
            </div>
            <div class="mb-4">
                <label for="description" class="block text-gray-700">Description*</label>
                <textarea name="description" required 
                          class="w-full px-3 py-2 border rounded"><?= htmlspecialchars($task['description']) ?></textarea>
            </div>
            <div class="mb-4">
                <label for="due_date" class="block text-gray-700">Due Date*</label>
                <input type="date" name="due_date" required 
                       value="<?= htmlspecialchars($task['due_date']) ?>" 
                       class="w-full px-3 py-2 border rounded">
            </div>
            <div class="mb-4">
                <label for="priority" class="block text-gray-700">Priority*</label>
                <select name="priority" required class="w-full px-3 py-2 border rounded">
                    <option value="low" <?= $task['priority'] == 'low' ? 'selected' : '' ?>>Low</option>
                    <option value="medium" <?= $task['priority'] == 'medium' ? 'selected' : '' ?>>Medium</option>
                    <option value="high" <?= $task['priority'] == 'high' ? 'selected' : '' ?>>High</option>
                </select>
            </div>
            <div class="mb-6">
                <label for="status" class="block text-gray-700">Status*</label>
                <select name="status" required class="w-full px-3 py-2 border rounded">
                    <option value="pending" <?= $task['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="completed" <?= $task['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                </select>
            </div>
            <div class="flex justify-between">
                <a href="../index.php" class="bg-gray-500 text-white py-2 px-4 rounded hover:bg-gray-600">Cancel</a>
                <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Update Task</button>
            </div>
        </form>
    </div>
</body>
</html>