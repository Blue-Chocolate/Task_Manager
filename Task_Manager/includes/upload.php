<?php 
$localhost = 'localhost'; 
$dbname = 'task_manager'; 
$username = 'root'; 
$password = ''; 

try {
    $pdo = new PDO("mysql:host=$localhost;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if (isset($_POST['upload'])) {
    if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] !== 0) {
        echo "No file uploaded or an error occurred.";
        exit();
    }

    $uploadFolder = '../uploads/';
    if (!is_dir($uploadFolder)) {
        mkdir($uploadFolder, 0775, true);
    }

    $imageName = time() . '_' . basename($_FILES['profile_image']['name']);
    $imageTmpName = $_FILES['profile_image']['tmp_name'];
    $imagePath = 'uploads/' . $imageName; // Relative path for database

    if (move_uploaded_file($imageTmpName, $uploadFolder . $imageName)) {
        $query = "INSERT INTO users (profile_image) VALUES (?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$imagePath]);

        echo "Profile picture uploaded successfully!";
    } else {
        echo "Failed to upload image.";
    }
}
?>
