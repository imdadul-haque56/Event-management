<?php
// Automated Platform Verification Script
require_once __DIR__ . '/../includes/db.php';

echo "=== Running Platform Verification & Automated Testing ===\n";

$tests_passed = 0;
$total_tests = 0;

function assertTest($condition, $message) {
    global $tests_passed, $total_tests;
    $total_tests++;
    if ($condition) {
        $tests_passed++;
        echo "[PASS] $message\n";
    } else {
        echo "[FAIL] $message\n";
    }
}

try {
    // Test 1: DB connection sanity
    assertTest($pdo instanceof PDO, "Database PDO instance successfully initialized.");

    // Test 2: Check tables existence
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    assertTest(in_array('users', $tables), "Table 'users' exists in the database.");
    assertTest(in_array('events', $tables), "Table 'events' exists in the database.");
    assertTest(in_array('bookings', $tables), "Table 'bookings' exists in the database.");

    // Test 3: Insert test user, verify password hashing
    $test_email = 'test_verify_' . time() . '@verify.local';
    $test_pass = 'secret123';
    $hashed = password_hash($test_pass, PASSWORD_BCRYPT);
    
    $insUser = $pdo->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, 'user')");
    $insUser->execute(['Test Verification User', $test_email, $hashed]);
    $user_id = $pdo->lastInsertId();
    
    // Verify user retrieval
    $getUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $getUser->execute([$user_id]);
    $user_row = $getUser->fetch();
    
    assertTest($user_row && password_verify($test_pass, $user_row['password']), "Hashed password encryption and authentication validation holds true.");

    // Test 4: Insert test event with capacities
    $insEvent = $pdo->prepare("
        INSERT INTO events (title, description, event_date, event_time, venue, ticket_price, total_seats, available_seats) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $insEvent->execute([
        'Test automated capacity event',
        'Verification unit test description details.',
        '2026-12-31',
        '18:00:00',
        'Testing Arena Venue',
        45.00,
        100,
        100
    ]);
    $event_id = $pdo->lastInsertId();
    
    assertTest($event_id > 0, "Test event successfully inserted with capacity 100.");

    // Test 5: Transaction booking allocation check
    $booking_qty = 3;
    $total_price = 45.00 * $booking_qty;

    $pdo->beginTransaction();
    // lock and check
    $lockStmt = $pdo->prepare("SELECT available_seats FROM events WHERE id = ? FOR UPDATE");
    $lockStmt->execute([$event_id]);
    $avail = $lockStmt->fetchColumn();
    
    if ($avail >= $booking_qty) {
        $up = $pdo->prepare("UPDATE events SET available_seats = available_seats - ? WHERE id = ?");
        $up->execute([$booking_qty, $event_id]);
        
        $insBook = $pdo->prepare("INSERT INTO bookings (user_id, event_id, quantity, total_price) VALUES (?, ?, ?, ?)");
        $insBook->execute([$user_id, $event_id, $booking_qty, $total_price]);
        $booking_id = $pdo->lastInsertId();
        
        $pdo->commit();
    } else {
        $pdo->rollBack();
        $booking_id = 0;
    }
    
    assertTest($booking_id > 0, "Ticket booking transaction committed successfully.");
    
    // Verify seats decremented
    $checkSeats = $pdo->prepare("SELECT available_seats FROM events WHERE id = ?");
    $checkSeats->execute([$event_id]);
    $avail_after = $checkSeats->fetchColumn();
    
    assertTest($avail_after == 97, "Event seat allocation correctly decremented by $booking_qty (100 -> $avail_after).");

    // Test 6: Booking Cancellation capacity restoration check
    $pdo->beginTransaction();
    $cancelBook = $pdo->prepare("UPDATE bookings SET booking_status = 'cancelled' WHERE id = ?");
    $cancelBook->execute([$booking_id]);
    
    $restoreSeats = $pdo->prepare("UPDATE events SET available_seats = available_seats + ? WHERE id = ?");
    $restoreSeats->execute([$booking_qty, $event_id]);
    $pdo->commit();
    
    $checkSeats->execute([$event_id]);
    $avail_restored = $checkSeats->fetchColumn();
    assertTest($avail_restored == 100, "Booking cancellation successfully restored seats (97 -> $avail_restored).");

    // Clean up test records
    $pdo->prepare("DELETE FROM bookings WHERE id = ?")->execute([$booking_id]);
    $pdo->prepare("DELETE FROM events WHERE id = ?")->execute([$event_id]);
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
    
    echo "=== Cleaned up all temporary test rows ===\n";
    
    assertTest(true, "Cleanup operations completed.");

} catch (\Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "[FAIL] Verification crashed with exception: " . $e->getMessage() . "\n";
}

echo "\nVerification Summary: Passed $tests_passed / $total_tests tests.\n";
if ($tests_passed === $total_tests) {
    echo "SUCCESS: All core components verified correctly.\n";
} else {
    echo "FAILURE: One or more checks failed. Verify configuration values.\n";
}
?>
