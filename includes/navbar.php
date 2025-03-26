<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure database connection
if (!isset($pdo)) {
    require_once __DIR__ . '/../includes/db.php';
    if (!isset($pdo)) {
        die("Database connection not established. Check db.php!");
    }
}

$user = null;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    try {
        $stmt = $pdo->prepare("SELECT username, profile_image FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching user data: " . $e->getMessage());
    }
}
?>

<nav class="bg-gray-800 py-5 px-6 flex justify-between items-center shadow-lg">
    <a href="../index.php" class="text-white text-2xl font-bold hover:text-gray-300 transition duration-300">Task Manager</a>
    
    <?php if (isset($_SESSION['user_id']) && $user): ?>
        <div class="flex items-center space-x-6">
            <div class="flex items-center space-x-4">
                <img src="<?= !empty($user['profile_image']) ? htmlspecialchars($user['profile_image']) : '../assets/default-profile.png'; ?>" 
                     alt="Profile Picture" class="w-10 h-10 rounded-full border-2 border-white object-cover">
                
                <span class="text-white font-medium"><?= htmlspecialchars($user['username']) ?></span>
            </div>
            
            <a href="../../Task_Manager/reg-in-out/logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition duration-300">
                Logout
            </a>
        </div>
    <?php else: ?>
        <a href="../reg-in-out/login.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">
            Login
        </a>
    <?php endif; ?>
</nav>
