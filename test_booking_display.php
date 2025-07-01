<?php
session_start();

// 临时设置admin session用于测试
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_username'] = 'admin';

require_once 'db.php';

// 获取今天的bookings
$stmt = $pdo->prepare("
    SELECT 
        b.booking_id,
        b.facility_id,
        b.start_time,
        b.end_time,
        b.status,
        u.username,
        u.email,
        f.name as facility_name
    FROM bookings b
    LEFT JOIN users u ON b.user_id = u.user_id
    LEFT JOIN facilities f ON b.facility_id = f.facility_id
    WHERE b.booking_date = CURDATE()
    ORDER BY b.start_time
");
$stmt->execute();
$bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Booking Display Test</title>
    <style>
        .test-cell {
            border: 1px solid #ccc;
            padding: 10px;
            margin: 5px;
            display: inline-block;
            width: 200px;
            height: 120px;
            vertical-align: top;
        }
        .confirmed { background-color: #d4edda; }
        .cancelled { background-color: #f8d7da; }
    </style>
</head>
<body>
    <h2>Booking Display Test</h2>
    
    <h3>Raw Database Data:</h3>
    <pre><?php print_r($bookings); ?></pre>
    
    <h3>Time Format Conversion Test:</h3>
    <?php foreach ($bookings as $booking): ?>
        <div>
            Original: <?php echo $booking['start_time']; ?> → 
            Converted: <?php echo substr($booking['start_time'], 0, 5); ?>
            (Facility ID: <?php echo $booking['facility_id']; ?>)
        </div>
    <?php endforeach; ?>
    
    <h3>Simulated Table Cells:</h3>
    <!-- 模拟时间表格单元格 -->
    <div data-time="09:00" data-facility="1" class="test-cell">
        09:00 - Discussion Room A<br>
        <span id="cell-09-1">Available</span>
    </div>
    
    <div data-time="10:00" data-facility="1" class="test-cell">
        10:00 - Discussion Room A<br>
        <span id="cell-10-1">Available</span>
    </div>
    
    <div data-time="15:00" data-facility="4" class="test-cell">
        15:00 - Basketball Court<br>
        <span id="cell-15-4">Available</span>
    </div>
    
    <script>
        // 测试数据 (从PHP传递)
        const bookings = <?php echo json_encode($bookings); ?>;
        
        console.log('Test bookings:', bookings);
        
        // 应用booking显示逻辑
        bookings.forEach(booking => {
            // Convert time format from HH:MM:SS to HH:MM
            const startTime = booking.start_time.substring(0, 5);
            console.log(`Looking for cell: data-time="${startTime}" data-facility="${booking.facility_id}"`);
            
            const cell = document.querySelector(`[data-time="${startTime}"][data-facility="${booking.facility_id}"]`);
            console.log(`Found cell:`, cell);
            
            if (cell) {
                const statusClass = booking.status === 'confirmed' ? 'confirmed' : 'cancelled';
                cell.className = `test-cell ${statusClass}`;
                
                const span = cell.querySelector('span');
                span.innerHTML = `
                    <strong>${booking.username}</strong><br>
                    ${booking.email}<br>
                    Status: ${booking.status.toUpperCase()}
                `;
                
                console.log(`✅ Successfully updated cell for booking ${booking.booking_id}`);
            } else {
                console.error(`❌ Cell not found for booking ${booking.booking_id} - data-time="${startTime}" data-facility="${booking.facility_id}"`);
            }
        });
        
        console.log('Test completed. Check the cells above to see if bookings are displayed correctly.');
    </script>
</body>
</html> 