<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please login.']);
    exit;
}

// Database configuration
require_once 'config.php';

// Get database connection
$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Get action from request
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Route to appropriate handler
switch ($action) {
    case 'getUserGroups':
        getUserGroups($conn, $user_id);
        break;
    case 'addEntry':
        addEntry($conn, $user_id);
        break;
    case 'getEntries':
        getEntries($conn, $user_id);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

// Get user's groups
function getUserGroups($conn, $user_id) {
    try {
        $sql = "SELECT g.id, g.name 
                FROM groups g
                INNER JOIN group_members gm ON g.id = gm.group_id
                WHERE gm.user_id = ?
                ORDER BY g.name ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $groups = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $groups[] = $row;
            }
        }
        
        echo json_encode(['success' => true, 'groups' => $groups]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Add new cash entry
function addEntry($conn, $user_id) {
    try {
        $type = $_POST['type'] ?? '';
        $group_id = $_POST['group_id'] ?? '';
        $amount = $_POST['amount'] ?? 0;
        $datetime = $_POST['datetime'] ?? '';
        $message = $_POST['message'] ?? '';
        
        // Validate inputs
        if (empty($type) || empty($group_id) || empty($amount) || empty($datetime)) {
            echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
            return;
        }
        
        if (!in_array($type, ['in', 'out'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid transaction type']);
            return;
        }
        
        if ($amount <= 0) {
            echo json_encode(['success' => false, 'message' => 'Amount must be greater than 0']);
            return;
        }
        
        // Check if user is member of the group
        $stmt = $conn->prepare("SELECT id FROM group_members WHERE group_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $group_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'You are not a member of this group']);
            return;
        }
        
        // Insert entry
        $stmt = $conn->prepare("INSERT INTO entries (user_id, group_id, type, amount, datetime, message, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisdssi", $user_id, $group_id, $type, $amount, $datetime, $message, $user_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Entry added successfully', 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error adding entry: ' . $conn->error]);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Get all entries with filters
function getEntries($conn, $user_id) {
    try {
        $search = $_GET['search'] ?? '';
        $date_from = $_GET['date_from'] ?? '';
        $date_to = $_GET['date_to'] ?? '';
        $group_id = $_GET['group_id'] ?? '';
        $type = $_GET['type'] ?? '';
        $sort = $_GET['sort'] ?? 'date_desc';
        
        // Build query - only show entries from user's groups
        $sql = "SELECT e.id, e.user_id, e.group_id, e.type, e.amount, e.datetime, e.message, 
                g.name as group_name, u.name as user_name
                FROM entries e 
                INNER JOIN groups g ON e.group_id = g.id
                INNER JOIN users u ON e.user_id = u.id
                INNER JOIN group_members gm ON g.id = gm.group_id
                WHERE gm.user_id = ?";
        
        $params = [$user_id];
        $types = 'i';
        
        // Add search filter
        if (!empty($search) && strlen($search) >= 3) {
            $sql .= " AND (g.name LIKE ? OR e.message LIKE ? OR u.name LIKE ?)";
            $searchParam = "%$search%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $types .= 'sss';
        }
        
        // Add date filters
        if (!empty($date_from)) {
            $sql .= " AND DATE(e.datetime) >= ?";
            $params[] = $date_from;
            $types .= 's';
        }
        
        if (!empty($date_to)) {
            $sql .= " AND DATE(e.datetime) <= ?";
            $params[] = $date_to;
            $types .= 's';
        }
        
        // Add group filter
        if (!empty($group_id)) {
            $sql .= " AND e.group_id = ?";
            $params[] = $group_id;
            $types .= 'i';
        }
        
        // Add type filter
        if (!empty($type)) {
            $sql .= " AND e.type = ?";
            $params[] = $type;
            $types .= 's';
        }
        
        // Group by entry id to avoid duplicates
        $sql .= " GROUP BY e.id";
        
        // Add sorting
        switch ($sort) {
            case 'date_asc':
                $sql .= " ORDER BY e.datetime ASC";
                break;
            case 'amount_desc':
                $sql .= " ORDER BY e.amount DESC";
                break;
            case 'amount_asc':
                $sql .= " ORDER BY e.amount ASC";
                break;
            case 'date_desc':
            default:
                $sql .= " ORDER BY e.datetime DESC";
                break;
        }
        
        // Prepare and execute
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $entries = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $entries[] = $row;
            }
        }
        
        // Get statistics for user's groups only
        $stats_sql = "SELECT 
                        COALESCE(SUM(CASE WHEN e.type = 'in' THEN e.amount ELSE 0 END), 0) as total_in,
                        COALESCE(SUM(CASE WHEN e.type = 'out' THEN e.amount ELSE 0 END), 0) as total_out,
                        COUNT(DISTINCT e.id) as total_entries
                      FROM entries e
                      INNER JOIN group_members gm ON e.group_id = gm.group_id
                      WHERE gm.user_id = ?";
        
        // Apply same filters to statistics
        $stats_params = [$user_id];
        $stats_types = 'i';
        
        if (!empty($search) && strlen($search) >= 3) {
            $stats_sql .= " AND (EXISTS (SELECT 1 FROM groups g WHERE g.id = e.group_id AND g.name LIKE ?) 
                            OR e.message LIKE ? 
                            OR EXISTS (SELECT 1 FROM users u WHERE u.id = e.user_id AND u.name LIKE ?))";
            $searchParam = "%$search%";
            $stats_params[] = $searchParam;
            $stats_params[] = $searchParam;
            $stats_params[] = $searchParam;
            $stats_types .= 'sss';
        }
        
        if (!empty($date_from)) {
            $stats_sql .= " AND DATE(e.datetime) >= ?";
            $stats_params[] = $date_from;
            $stats_types .= 's';
        }
        
        if (!empty($date_to)) {
            $stats_sql .= " AND DATE(e.datetime) <= ?";
            $stats_params[] = $date_to;
            $stats_types .= 's';
        }
        
        if (!empty($group_id)) {
            $stats_sql .= " AND e.group_id = ?";
            $stats_params[] = $group_id;
            $stats_types .= 'i';
        }
        
        if (!empty($type)) {
            $stats_sql .= " AND e.type = ?";
            $stats_params[] = $type;
            $stats_types .= 's';
        }
        
        $stats_stmt = $conn->prepare($stats_sql);
        $stats_stmt->bind_param($stats_types, ...$stats_params);
        $stats_stmt->execute();
        $stats_result = $stats_stmt->get_result();
        $statistics = $stats_result->fetch_assoc();
        
        echo json_encode([
            'success' => true,
            'entries' => $entries,
            'statistics' => $statistics
        ]);
        
        $stmt->close();
        $stats_stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

$conn->close();
?>
