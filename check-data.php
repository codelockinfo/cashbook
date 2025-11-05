<?php
session_start();
require_once 'config.php';

// Enable error display
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>Data Check</title>";
echo "<style>
body{font-family:Arial,sans-serif;padding:20px;background:#f5f5f5;}
.container{max-width:1000px;margin:0 auto;background:white;padding:30px;border-radius:8px;}
h1{color:#667eea;border-bottom:2px solid #667eea;padding-bottom:10px;}
h2{color:#333;margin-top:30px;}
table{width:100%;border-collapse:collapse;margin:15px 0;}
th,td{border:1px solid #ddd;padding:12px;text-align:left;}
th{background:#667eea;color:white;}
tr:nth-child(even){background:#f9f9f9;}
.success{color:#22c55e;font-weight:bold;}
.error{color:#ef4444;font-weight:bold;}
.warning{color:#f59e0b;font-weight:bold;}
.info{background:#e0e7ff;padding:15px;border-left:4px solid #667eea;margin:15px 0;}
</style></head><body><div class='container'>";

echo "<h1>🔍 Cash Book Data Check</h1>";

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo "<p class='error'>You are not logged in! <a href='login.php'>Go to Login</a></p>";
    echo "</div></body></html>";
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$conn = getDBConnection();

echo "<div class='info'><strong>Logged in as:</strong> $user_name (ID: $user_id)</div>";

// 1. Check all users
echo "<h2>1. All Users</h2>";
$result = $conn->query("SELECT id, name, email FROM users ORDER BY id");
if ($result->num_rows > 0) {
    echo "<table><tr><th>ID</th><th>Name</th><th>Email</th></tr>";
    while ($row = $result->fetch_assoc()) {
        $highlight = $row['id'] == $user_id ? " style='background:#fffbeb;'" : "";
        echo "<tr$highlight><td>{$row['id']}</td><td>{$row['name']}</td><td>{$row['email']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p class='warning'>No users found</p>";
}

// 2. Check all groups
echo "<h2>2. All Groups</h2>";
$result = $conn->query("SELECT id, name, created_by FROM `groups` ORDER BY id");
if ($result->num_rows > 0) {
    echo "<table><tr><th>ID</th><th>Name</th><th>Created By (User ID)</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['id']}</td><td>{$row['name']}</td><td>{$row['created_by']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p class='warning'>⚠️ No groups found! You need to create a group first.</p>";
}

// 3. Check group memberships
echo "<h2>3. Group Memberships</h2>";
$result = $conn->query("SELECT gm.id, gm.group_id, g.name as group_name, gm.user_id, u.name as user_name 
                        FROM group_members gm 
                        JOIN `groups` g ON gm.group_id = g.id 
                        JOIN users u ON gm.user_id = u.id 
                        ORDER BY gm.group_id, gm.user_id");
if ($result->num_rows > 0) {
    echo "<table><tr><th>Membership ID</th><th>Group ID</th><th>Group Name</th><th>User ID</th><th>User Name</th></tr>";
    while ($row = $result->fetch_assoc()) {
        $highlight = $row['user_id'] == $user_id ? " style='background:#fffbeb;'" : "";
        echo "<tr$highlight><td>{$row['id']}</td><td>{$row['group_id']}</td><td>{$row['group_name']}</td><td>{$row['user_id']}</td><td>{$row['user_name']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p class='warning'>⚠️ No group memberships found!</p>";
}

// 4. Check YOUR group memberships specifically
echo "<h2>4. Your Group Memberships</h2>";
$stmt = $conn->prepare("SELECT g.id, g.name FROM `groups` g 
                        INNER JOIN group_members gm ON g.id = gm.group_id 
                        WHERE gm.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    echo "<p class='success'>✓ You are member of {$result->num_rows} group(s):</p>";
    echo "<table><tr><th>Group ID</th><th>Group Name</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['id']}</td><td>{$row['name']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>✗ You are NOT a member of any groups!</p>";
    echo "<div class='info'><strong>SOLUTION:</strong> Go to <a href='groups.php'>Groups Page</a> and create or join a group first!</div>";
}

// 5. Check all entries
echo "<h2>5. All Entries in Database</h2>";
$result = $conn->query("SELECT e.id, e.user_id, u.name as user_name, e.group_id, g.name as group_name, 
                        e.type, e.amount, e.datetime, e.message 
                        FROM entries e 
                        LEFT JOIN users u ON e.user_id = u.id 
                        LEFT JOIN `groups` g ON e.group_id = g.id 
                        ORDER BY e.datetime DESC");
if ($result->num_rows > 0) {
    echo "<p class='success'>✓ Found {$result->num_rows} total entries:</p>";
    echo "<table><tr><th>ID</th><th>User</th><th>Group</th><th>Type</th><th>Amount</th><th>Date</th><th>Message</th></tr>";
    while ($row = $result->fetch_assoc()) {
        $typeClass = $row['type'] == 'in' ? 'success' : 'error';
        $typeSymbol = $row['type'] == 'in' ? '↓' : '↑';
        echo "<tr><td>{$row['id']}</td><td>{$row['user_name']} (ID:{$row['user_id']})</td>";
        echo "<td>{$row['group_name']} (ID:{$row['group_id']})</td>";
        echo "<td class='$typeClass'>$typeSymbol {$row['type']}</td>";
        echo "<td>₹{$row['amount']}</td><td>{$row['datetime']}</td><td>{$row['message']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p class='warning'>⚠️ No entries found in database!</p>";
    echo "<div class='info'>No transactions have been added yet. Add your first entry from the dashboard!</div>";
}

// 6. Check entries visible to YOU
echo "<h2>6. Entries Visible to You (Based on Group Membership)</h2>";
$stmt = $conn->prepare("SELECT e.id, e.user_id, u.name as user_name, e.group_id, g.name as group_name, 
                        e.type, e.amount, e.datetime, e.message 
                        FROM entries e 
                        INNER JOIN `groups` g ON e.group_id = g.id
                        INNER JOIN users u ON e.user_id = u.id
                        INNER JOIN group_members gm ON g.id = gm.group_id
                        WHERE gm.user_id = ?
                        GROUP BY e.id
                        ORDER BY e.datetime DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    echo "<p class='success'>✓ You can see {$result->num_rows} entries:</p>";
    echo "<table><tr><th>ID</th><th>User</th><th>Group</th><th>Type</th><th>Amount</th><th>Date</th><th>Message</th></tr>";
    while ($row = $result->fetch_assoc()) {
        $typeClass = $row['type'] == 'in' ? 'success' : 'error';
        $typeSymbol = $row['type'] == 'in' ? '↓ IN' : '↑ OUT';
        echo "<tr><td>{$row['id']}</td><td>{$row['user_name']}</td><td>{$row['group_name']}</td>";
        echo "<td class='$typeClass'>$typeSymbol</td><td>₹{$row['amount']}</td>";
        echo "<td>{$row['datetime']}</td><td>{$row['message']}</td></tr>";
    }
    echo "</table>";
    echo "<div class='info'><strong>✓ These entries should appear on your dashboard!</strong> If they don't, there may be a JavaScript error. Check browser console (F12).</div>";
} else {
    echo "<p class='error'>✗ You cannot see any entries!</p>";
    echo "<div class='info'><strong>REASON:</strong> Either no entries exist in your groups, OR you're not a member of any groups that have entries.</div>";
}

echo "<hr><h2>📋 Summary & Action Items</h2>";
echo "<div class='info'>";
echo "<ol>";
echo "<li>If you're not a member of any groups → <strong><a href='groups.php'>Go to Groups</a></strong> and create/join one</li>";
echo "<li>If you're in groups but no entries exist → <strong><a href='dashboard.php'>Go to Dashboard</a></strong> and add entries</li>";
echo "<li>If entries exist but dashboard is empty → Check browser console (F12) for JavaScript errors</li>";
echo "</ol>";
echo "</div>";

echo "<p style='margin-top:30px;'><a href='dashboard.php' style='background:#667eea;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;'>← Back to Dashboard</a></p>";

$conn->close();
echo "</div></body></html>";
?>

