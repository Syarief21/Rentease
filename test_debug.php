<?php
// test_api.php - File untuk testing API auth

header('Content-Type: application/json');

// Test 1: Cek database connection
echo "=== TEST 1: DATABASE CONNECTION ===\n";
try {
    require_once __DIR__ . '/backend/config/db.php';
    echo "✓ Database connected successfully\n";
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    exit;
}

// Test 2: Cek Session class
echo "\n=== TEST 2: SESSION CLASS ===\n";
try {
    require_once __DIR__ . '/backend/session.php';
    echo "✓ Session class loaded successfully\n";
} catch (Exception $e) {
    echo "✗ Session class failed: " . $e->getMessage() . "\n";
    exit;
}

// Test 3: Cek users table
echo "\n=== TEST 3: USERS TABLE ===\n";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Users table exists\n";
        
        // Tampilkan struktur tabel
        $columns = $pdo->query("DESCRIBE users");
        echo "Table structure:\n";
        foreach ($columns as $col) {
            echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
        }
    } else {
        echo "✗ Users table does not exist\n";
    }
} catch (Exception $e) {
    echo "✗ Error checking users table: " . $e->getMessage() . "\n";
}

// Test 4: Cek data users yang ada
echo "\n=== TEST 4: EXISTING USERS ===\n";
try {
    $count = $pdo->query("SELECT COUNT(*) as cnt FROM users")->fetch()['cnt'];
    echo "✓ Total users in database: " . $count . "\n";
    
    $users = $pdo->query("SELECT id, name, email, role FROM users");
    foreach ($users as $user) {
        echo "  - ID: " . $user['id'] . ", Name: " . $user['name'] . ", Email: " . $user['email'] . ", Role: " . $user['role'] . "\n";
    }
} catch (Exception $e) {
    echo "✗ Error fetching users: " . $e->getMessage() . "\n";
}

// Test 5: Simulasi register
echo "\n=== TEST 5: SIMULATE REGISTER ===\n";
try {
    $name = "Test User";
    $email = "testuser@example.com";
    $password = "password123";
    
    // Hapus user test jika sudah ada
    $pdo->prepare("DELETE FROM users WHERE email = ?")->execute([$email]);
    
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    
    if ($stmt->execute([$name, $email, $hashedPassword, 'user'])) {
        echo "✓ Register simulation successful\n";
        echo "  - Name: " . $name . "\n";
        echo "  - Email: " . $email . "\n";
        echo "  - Password hash created\n";
    } else {
        echo "✗ Register simulation failed\n";
    }
} catch (Exception $e) {
    echo "✗ Error in register simulation: " . $e->getMessage() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
?>
