<?php
// test_detail.php - Test detail.php functionality

require_once __DIR__ . '/backend/config/db.php';

echo "<h1>Detail Page Test</h1>";

// Check if we can connect to database
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM properties");
    $result = $stmt->fetch();
    echo "<p>✓ Database connected. Total properties: " . $result['total'] . "</p>";
    
    // Get first property
    $stmt = $pdo->query("SELECT p.id, p.name, p.price, p.available_rooms, p.total_rooms FROM properties p LIMIT 1");
    $prop = $stmt->fetch();
    
    if ($prop) {
        echo "<p>✓ First property found:</p>";
        echo "<ul>";
        echo "<li>ID: " . $prop['id'] . "</li>";
        echo "<li>Name: " . htmlspecialchars($prop['name']) . "</li>";
        echo "<li>Price: " . $prop['price'] . "</li>";
        echo "<li>Available: " . $prop['available_rooms'] . " / " . $prop['total_rooms'] . "</li>";
        echo "</ul>";
        
        echo "<p><a href='views/user/detail.php?id=" . $prop['id'] . "'>View Property Detail</a></p>";
    } else {
        echo "<p style='color:red'>❌ No properties found. Please add a property first.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<style>
    body { font-family: Arial; margin: 20px; }
    a { color: #4CAF50; text-decoration: none; }
    a:hover { text-decoration: underline; }
</style>
