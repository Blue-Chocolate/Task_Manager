<?php 
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $cpassword = trim($_POST['cpassword']);
    
    $profile_image = 'uploads/default.png'; // Default profile image

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true); // Ensure folder exists
        }

        $fileExt = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array(strtolower($fileExt), $allowedExt)) {
            $profile_image = 'uploads/' . time() . '_' . basename($_FILES['profile_image']['name']);
            $targetPath = '../' . $profile_image; // Ensure correct path

            if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetPath)) {
                $_SESSION['error'] = "Failed to upload image.";
                header("Location: reg.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Invalid file type. Only JPG, PNG, and GIF allowed.";
            header("Location: reg.php");
            exit();
        }
    }

    if (empty($username) || empty($password) || empty($cpassword)) {
        $_SESSION['error'] = "Please fill all fields.";
        header("Location: reg.php");
        exit();
    }

    if ($password !== $cpassword) {
        $_SESSION['error'] = "Passwords do not match!";
        header("Location: reg.php");
        exit();
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = "Username already exists.";
        header("Location: reg.php");
        exit();
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, profile_image) VALUES (?, ?, ?)");
        $stmt->execute([$username, $hash, $profile_image]);
        
        $_SESSION['success'] = "Registration successful! Please login.";
        header("Location: login.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Registration failed: " . $e->getMessage();
        header("Location: reg.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container { max-width: 500px; margin-top: 50px; }
        .profile-preview { width: 100px; height: 100px; object-fit: cover; border-radius: 50%; }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="card shadow">
            <div class="card-body">
                <h2 class="card-title text-center mb-4">Register</h2>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>

                <form action="reg.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3 text-center">
                        <img src="../uploads/default.png" alt="Profile Preview" class="profile-preview" id="profilePreview">
                        <input type="file" class="form-control mt-2" name="profile_image" id="profileImage" 
                               accept="image/*" style="display: none;">
                        <button type="button" class="btn btn-secondary btn-sm mt-2" 
                                onclick="document.getElementById('profileImage').click()">
                            Upload Profile Image
                        </button>
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" required 
                               placeholder="Enter username">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required 
                               placeholder="Enter password">
                    </div>

                    <div class="mb-3">
                        <label for="cpassword" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" name="cpassword" required 
                               placeholder="Confirm password">
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Register</button>
                        <a href="login.php" class="btn btn-link">Already have an account? Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Profile image preview
        document.getElementById('profileImage').addEventListener('change', function(e) {
            const reader = new FileReader();
            reader.onload = function() {
                document.getElementById('profilePreview').src = reader.result;
            }
            reader.readAsDataURL(e.target.files[0]);
        });
    </script>
</body>
</html>