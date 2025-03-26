<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$task_id = filter_input(INPUT_POST, 'task_id', FILTER_VALIDATE_INT);
if (!$task_id || $task_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid task ID']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $checkStmt = $pdo->prepare("SELECT id FROM tasks WHERE id = ? AND user_id = ?");
    $checkStmt->execute([$task_id, $user_id]);

    if ($checkStmt->rowCount() === 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Task not found or unauthorized']);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$task_id, $user_id]);

    if ($stmt->rowCount() > 0) {
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Task deleted successfully']);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Failed to delete task']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error',
        'error' => $e->getMessage()
    ]);
    exit;
}
?>