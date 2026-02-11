<?php
// test_update_profile.php - Test update profil functionality

require_once __DIR__ . '/backend/session.php';
require_once __DIR__ . '/backend/config/db.php';

Session::start();

echo "<h2>Test Update Profile</h2>";

// Check if user is logged in
if (!Session::isLoggedIn()) {
    echo "<p style='color:red'>❌ User tidak login. Session tidak ditemukan.</p>";
    echo "<p><a href='views/auth/login.php'>Login di sini</a></p>";
    exit;
}

$user_id = Session::get('user_id');
echo "<p>✓ User logged in. ID: " . htmlspecialchars($user_id) . "</p>";

// Get user data
$stmt = $pdo->prepare("SELECT id, name, email, profile_picture FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo "<p style='color:red'>❌ User data tidak ditemukan di database</p>";
    exit;
}

echo "<p>✓ User data found:</p>";
echo "<ul>";
echo "<li>Name: " . htmlspecialchars($user['name']) . "</li>";
echo "<li>Email: " . htmlspecialchars($user['email']) . "</li>";
echo "<li>Profile Picture: " . htmlspecialchars(isset($user['profile_picture']) ? $user['profile_picture'] : 'default.jpg') . "</li>";
echo "</ul>";

// Check upload directory
$uploadDir = __DIR__ . '/uploads/profiles/';
echo "<p><strong>Upload Directory Check:</strong></p>";
if (is_dir($uploadDir)) {
    echo "<p>✓ Directory exists: " . htmlspecialchars($uploadDir) . "</p>";
    if (is_writable($uploadDir)) {
        echo "<p>✓ Directory is writable</p>";
    } else {
        echo "<p style='color:red'>❌ Directory is NOT writable</p>";
    }
} else {
    echo "<p style='color:red'>❌ Directory does not exist</p>";
}

// Test form
echo "<p><strong>Test Update Form:</strong></p>";
echo "<form method='POST' enctype='multipart/form-data'>";
echo "<label>Name:</label>";
echo "<input type='text' name='name' value='" . htmlspecialchars($user['name']) . "' required>";
echo "<br><br>";
echo "<label>Email:</label>";
echo "<input type='email' name='email' value='" . htmlspecialchars($user['email']) . "' required>";
echo "<br><br>";
echo "<label>Profile Picture:</label>";
echo "<input type='file' name='profile_picture' accept='image/*'>";
echo "<br><br>";
echo "<button type='submit'>Test Update</button>";
echo "</form>";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : null;
    $email = isset($_POST['email']) ? trim($_POST['email']) : null;
    
    echo "<p><strong>Form Data Received:</strong></p>";
    echo "<ul>";
    echo "<li>Name: " . htmlspecialchars($name) . "</li>";
    echo "<li>Email: " . htmlspecialchars($email) . "</li>";
    echo "<li>File: " . (isset($_FILES['profile_picture']) ? $_FILES['profile_picture']['name'] : 'No file') . "</li>";
    echo "</ul>";
    
    if (!$name || !$email) {
        echo "<p style='color:red'>❌ Name or email is empty</p>";
    } else {
        // Try to update
        $image_url = null;
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
            $file = $_FILES['profile_picture'];
            $filename = uniqid() . '-' . basename($file['name']);
            $destination = $uploadDir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $image_url = 'uploads/profiles/' . $filename;
                echo "<p>✓ File uploaded: " . htmlspecialchars($filename) . "</p>";
            } else {
                echo "<p style='color:red'>❌ File upload failed</p>";
            }
        }
        
        // Update database
        try {
            if ($image_url) {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, profile_picture = ? WHERE id = ?");
                $result = $stmt->execute([$name, $email, $image_url, $user_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
                $result = $stmt->execute([$name, $email, $user_id]);
            }
            
            if ($result) {
                echo "<p style='color:green'>✓ Profile updated successfully!</p>";
                Session::set('user_name', $name);
                
                // Reload user data to display updated info
                $stmt = $pdo->prepare("SELECT id, name, email, profile_picture FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $updated_user = $stmt->fetch();
                
                if ($updated_user) {
                    echo "<p><strong>Updated Profile Data:</strong></p>";
                    echo "<ul>";
                    echo "<li>Name: " . htmlspecialchars($updated_user['name']) . "</li>";
                    echo "<li>Email: " . htmlspecialchars($updated_user['email']) . "</li>";
                    echo "<li>Profile Picture: " . htmlspecialchars(isset($updated_user['profile_picture']) ? $updated_user['profile_picture'] : 'default.jpg') . "</li>";
                    echo "</ul>";
                    
                    // Display profile picture
                    if (isset($updated_user['profile_picture']) && $updated_user['profile_picture']) {
                        $pic_path = '../../' . $updated_user['profile_picture'];
                        echo "<p><strong>Profile Picture Preview:</strong></p>";
                        echo "<img src='" . htmlspecialchars($pic_path) . "' alt='Profile' style='width:150px; height:150px; border-radius:50%; object-fit:cover; border:3px solid #4CAF50;'>";
                    }
                }
            } else {
                echo "<p style='color:red'>❌ Update failed</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color:red'>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
}
?>

<style>
    body { font-family: Arial; margin: 20px; }
    input, button { padding: 8px; margin: 5px 0; }
    button { background: #4CAF50; color: white; border: none; cursor: pointer; }
    p { line-height: 1.6; }
    img { display: block; margin: 10px 0; }
    .success { background: #d4edda; padding: 10px; border-radius: 4px; margin: 10px 0; }
    .error { background: #f8d7da; padding: 10px; border-radius: 4px; margin: 10px 0; }
</style>
