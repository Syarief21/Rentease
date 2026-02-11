<?php
/**
 * Test script untuk verify profile update functionality
 * Akses: http://localhost/Project/test_profile_update.php
 */

// Cek upload directory
$uploadDir = __DIR__ . '/uploads/profiles/';
echo "<h2>Upload Directory Check</h2>";
echo "Path: " . $uploadDir . "<br>";
echo "Exists: " . (is_dir($uploadDir) ? "✓ Yes" : "✗ No") . "<br>";
echo "Writable: " . (is_writable($uploadDir) ? "✓ Yes" : "✗ No") . "<br><br>";

// Cek database connection
echo "<h2>Database Connection Check</h2>";
try {
    require_once __DIR__ . '/backend/config/db.php';
    echo "✓ Database connected successfully<br>";
    
    // Check users table structure
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll();
    echo "<h3>Users Table Structure</h3>";
    echo "<ul>";
    foreach ($columns as $col) {
        echo "<li><strong>" . $col['Field'] . "</strong> - " . $col['Type'] . "</li>";
    }
    echo "</ul>";
} catch (Exception $e) {
    echo "✗ Database Error: " . $e->getMessage() . "<br>";
}

// Test API endpoint
echo "<h2>API Response Test</h2>";
echo "<p>Check browser console for the following test results:</p>";
echo "<textarea id='responseLog' style='width:100%; height:300px; font-family:monospace;'></textarea><br>";

// Check if session is active
require_once __DIR__ . '/backend/session.php';
Session::start();

if (Session::isLoggedIn()) {
    echo "<div style='background:#d4edda; padding:10px; margin:10px 0; border:1px solid #c3e6cb;'>";
    echo "<h3>✓ Session Active</h3>";
    echo "User ID: " . Session::get('user_id') . "<br>";
    echo "User Name: " . Session::get('user_name') . "<br>";
    echo "Profile Picture: " . Session::get('user_profile_picture') . "<br>";
    echo "</div>";
} else {
    echo "<div style='background:#f8d7da; padding:10px; margin:10px 0; border:1px solid #f5c6cb;'>";
    echo "<h3>✗ No Active Session</h3>";
    echo "<p>Please login first to test profile update.</p>";
    echo "</div>";
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Profile Update Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        h3 { color: #555; }
        textarea { border: 1px solid #ccc; padding: 10px; }
        .test-button { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; margin: 10px 0; }
        .test-button:hover { background: #0056b3; }
    </style>
</head>
<body>
<h1>Profile Update System Test</h1>
<p>This page verifies all components of the profile update feature.</p>

<h2>File Upload Test Form</h2>
<form id="testForm" enctype="multipart/form-data">
    <label>Name: <input type="text" id="testName" value="Test User"></label><br>
    <label>Email: <input type="email" id="testEmail" value="test@example.com"></label><br>
    <label>Profile Picture: <input type="file" id="testFile" accept="image/*"></label><br>
    <button type="button" class="test-button" onclick="testProfileUpdate()">Test Profile Update API</button>
</form>

<script>
async function testProfileUpdate() {
    const log = document.getElementById('responseLog');
    log.value = '';
    
    const appendLog = (msg) => {
        log.value += msg + '\n';
        log.scrollTop = log.scrollHeight;
    };
    
    appendLog('=== Starting Profile Update Test ===\n');
    
    try {
        const formData = new FormData();
        const name = document.getElementById('testName').value;
        const email = document.getElementById('testEmail').value;
        const file = document.getElementById('testFile').files[0];
        
        appendLog('Input Data:');
        appendLog('  Name: ' + name);
        appendLog('  Email: ' + email);
        appendLog('  File: ' + (file ? file.name + ' (' + file.type + ')' : 'No file selected'));
        appendLog('');
        
        if (!name || !email) {
            appendLog('ERROR: Name and email are required');
            return;
        }
        
        formData.append('name', name);
        formData.append('email', email);
        if (file) {
            formData.append('profile_picture', file);
        }
        
        appendLog('Sending request to backend/api/users.php...');
        const response = await fetch('../../backend/api/users.php?action=update_profile', {
            method: 'POST',
            body: formData
        });
        
        appendLog('Status: ' + response.status + ' ' + response.statusText);
        
        const text = await response.text();
        appendLog('\nRaw Response:');
        appendLog(text);
        
        try {
            const data = JSON.parse(text);
            appendLog('\n=== Parsed JSON Response ===');
            appendLog(JSON.stringify(data, null, 2));
            
            if (data.data) {
                appendLog('\n=== User Data Returned ===');
                appendLog('User ID: ' + data.data.userId);
                appendLog('User Name: ' + data.data.userName);
                appendLog('User Email: ' + data.data.userEmail);
                appendLog('Profile Picture Path: ' + data.data.profile_picture);
                appendLog('Profile Picture URL: ' + data.data.profile_picture_url);
            }
        } catch (e) {
            appendLog('\nJSON Parse Error: ' + e.message);
        }
    } catch (error) {
        appendLog('ERROR: ' + error.message);
        appendLog(error.stack);
    }
}
</script>

</body>
</html>
