<?php
// Configure session for subdirectory support
if (session_status() === PHP_SESSION_NONE) {
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    $cookiePath = $basePath ? strtolower($basePath) : '/';
    $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    
    // Use SameSite=None with Secure=true for HTTPS (works for BOTH regular browsers and WebView)
    // Use SameSite=Lax for HTTP (works for regular browsers, WebView on HTTP has limitations)
    $sameSite = $isSecure ? 'None' : 'Lax';
    
    session_set_cookie_params([
        'lifetime' => 604800, // 1 week
        'path' => $cookiePath,
        'domain' => '', // Empty domain works better with WebView
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => $sameSite
    ]);
    
    session_start();
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Access-Control-Allow-Credentials: true'); // Important for cookies in WebView

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

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
    case 'getGroupMembers':
        getGroupMembers($conn, $user_id);
        break;
    case 'addEntry':
        addEntry($conn, $user_id);
        break;
    case 'updateEntry':
        updateEntry($conn, $user_id);
        break;
    case 'getEntry':
        getEntry($conn, $user_id);
        break;
    case 'getEntries':
        getEntries($conn, $user_id);
        break;
    case 'deleteEntry':
        deleteEntry($conn, $user_id);
        break;
    case 'getEntryEditHistory':
        getEntryEditHistory($conn, $user_id);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

// Get user's groups
function getUserGroups($conn, $user_id) {
    try {
        $sql = "SELECT g.id, g.name 
                FROM `groups` g
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

// Get members of a specific group
function getGroupMembers($conn, $user_id) {
    try {
        $group_id = $_GET['group_id'] ?? '';
        
        if (empty($group_id)) {
            echo json_encode(['success' => false, 'message' => 'Group ID is required']);
            return;
        }
        
        // Check if user is member of this group
        $checkStmt = $conn->prepare("SELECT id FROM group_members WHERE group_id = ? AND user_id = ?");
        $checkStmt->bind_param("ii", $group_id, $user_id);
        $checkStmt->execute();
        
        if ($checkStmt->get_result()->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'You are not a member of this group']);
            return;
        }
        
        // Get all members of the group
        $sql = "SELECT u.id, u.name 
                FROM users u
                INNER JOIN group_members gm ON u.id = gm.user_id
                WHERE gm.group_id = ?
                ORDER BY u.name ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $group_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $members = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $members[] = $row;
            }
        }
        
        echo json_encode(['success' => true, 'members' => $members]);
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
        
        // Handle attachment upload
        $attachment = null;
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadEntryAttachment($_FILES['attachment']);
            if ($uploadResult['success']) {
                $attachment = $uploadResult['filename'];
            } else {
                echo json_encode(['success' => false, 'message' => $uploadResult['message']]);
                return;
            }
        }
        
        // Insert entry
        $stmt = $conn->prepare("INSERT INTO entries (user_id, group_id, type, amount, datetime, message, attachment, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisdsssi", $user_id, $group_id, $type, $amount, $datetime, $message, $attachment, $user_id);
        
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

// Upload entry attachment
function uploadEntryAttachment($file) {
    $uploadDir = __DIR__ . '/uploads/entry_attachments/';
    
    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.'];
    }
    
    // Validate file size (max 10MB)
    $maxSize = 10 * 1024 * 1024; // 10MB
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File size too large. Maximum 10MB allowed.'];
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'entry_' . uniqid() . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => 'uploads/entry_attachments/' . $filename];
    } else {
        return ['success' => false, 'message' => 'Failed to upload file'];
    }
}

// Get single entry by ID
function getEntry($conn, $user_id) {
    try {
        $entry_id = $_GET['id'] ?? '';
        
        if (empty($entry_id)) {
            echo json_encode(['success' => false, 'message' => 'Entry ID is required']);
            return;
        }
        
        // Get entry with permission check (user must be member of the group)
        // Only allow editing active entries (status = 1)
        $sql = "SELECT e.* FROM entries e 
                INNER JOIN `groups` g ON e.group_id = g.id
                INNER JOIN group_members gm ON g.id = gm.group_id
                WHERE e.id = ? AND gm.user_id = ? AND e.status = 1";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $entry_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Entry not found, deleted, or access denied']);
            return;
        }
        
        $entry = $result->fetch_assoc();
        echo json_encode(['success' => true, 'entry' => $entry]);
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Update existing entry
function updateEntry($conn, $user_id) {
    try {
        $entry_id = $_POST['id'] ?? '';
        $type = $_POST['type'] ?? '';
        $group_id = $_POST['group_id'] ?? '';
        $amount = $_POST['amount'] ?? 0;
        $message = $_POST['message'] ?? '';
        $remove_attachment = $_POST['remove_attachment'] ?? '';
        
        // Validate inputs (datetime is not editable, so we don't need it from POST)
        if (empty($entry_id) || empty($type) || empty($group_id) || empty($amount)) {
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
        
        // Check if entry exists and user has permission (must be member of the group)
        // Get all current values to compare for edit history (datetime is preserved from original entry)
        $checkStmt = $conn->prepare("SELECT e.id, e.group_id, e.type, e.amount, e.datetime, e.message, e.attachment, e.status 
                                      FROM entries e 
                                      INNER JOIN group_members gm ON e.group_id = gm.group_id
                                      WHERE e.id = ? AND gm.user_id = ?");
        $checkStmt->bind_param("ii", $entry_id, $user_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Entry not found or access denied']);
            return;
        }
        
        $currentEntry = $checkResult->fetch_assoc();
        
        // Check if entry is inactive (status=0)
        if ($currentEntry['status'] == 0) {
            echo json_encode(['success' => false, 'message' => 'Cannot edit a deleted entry']);
            return;
        }
        
        $currentAttachment = $currentEntry['attachment'];
        
        // Check if user is member of the new group
        $groupCheckStmt = $conn->prepare("SELECT id FROM group_members WHERE group_id = ? AND user_id = ?");
        $groupCheckStmt->bind_param("ii", $group_id, $user_id);
        $groupCheckStmt->execute();
        
        if ($groupCheckStmt->get_result()->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'You are not a member of the selected group']);
            return;
        }
        
        // Handle attachment
        $attachment = $currentAttachment; // Keep current by default
        
        // Remove current attachment if requested
        if ($remove_attachment === 'true' && !empty($currentAttachment)) {
            $filePath = __DIR__ . '/' . $currentAttachment;
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
            $attachment = null;
        }
        
        // Upload new attachment if provided
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            // Delete old attachment if exists
            if (!empty($currentAttachment)) {
                $filePath = __DIR__ . '/' . $currentAttachment;
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
            
            $uploadResult = uploadEntryAttachment($_FILES['attachment']);
            if ($uploadResult['success']) {
                $attachment = $uploadResult['filename'];
            } else {
                echo json_encode(['success' => false, 'message' => $uploadResult['message']]);
                return;
            }
        }
        
        // Update entry (datetime is not editable, keep original value)
        $stmt = $conn->prepare("UPDATE entries SET group_id = ?, type = ?, amount = ?, message = ?, attachment = ? WHERE id = ?");
        $stmt->bind_param("isdssi", $group_id, $type, $amount, $message, $attachment, $entry_id);
        
        if ($stmt->execute()) {
            // Log edit history - track what fields were changed
            $now = date('Y-m-d H:i:s');
            $historyStmt = $conn->prepare("INSERT INTO entry_edit_history (entry_id, edited_by, edited_at, field_name, old_value, new_value) VALUES (?, ?, ?, ?, ?, ?)");
            
            // Track changes for each field (datetime is not editable, so don't track it)
            $fieldsToTrack = [
                'group_id' => ['old' => $currentEntry['group_id'], 'new' => $group_id],
                'type' => ['old' => $currentEntry['type'], 'new' => $type],
                'amount' => ['old' => $currentEntry['amount'], 'new' => $amount],
                'message' => ['old' => $currentEntry['message'] ?? '', 'new' => $message ?? ''],
                'attachment' => ['old' => $currentEntry['attachment'] ?? '', 'new' => $attachment ?? '']
            ];
            
            foreach ($fieldsToTrack as $fieldName => $values) {
                $oldVal = $values['old'];
                $newVal = $values['new'];
                
                // Only log if value actually changed
                if ($oldVal != $newVal) {
                    // Format values for display
                    $oldDisplay = is_null($oldVal) || $oldVal === '' ? '(empty)' : (string)$oldVal;
                    $newDisplay = is_null($newVal) || $newVal === '' ? '(empty)' : (string)$newVal;
                    
                    // Special handling for group_id - get group names
                    if ($fieldName === 'group_id') {
                        if (!empty($oldVal)) {
                            $groupStmt = $conn->prepare("SELECT name FROM `groups` WHERE id = ?");
                            $groupStmt->bind_param("i", $oldVal);
                            $groupStmt->execute();
                            $groupResult = $groupStmt->get_result();
                            if ($groupResult->num_rows > 0) {
                                $oldDisplay = $groupResult->fetch_assoc()['name'];
                            }
                            $groupStmt->close();
                        }
                        if (!empty($newVal)) {
                            $groupStmt = $conn->prepare("SELECT name FROM `groups` WHERE id = ?");
                            $groupStmt->bind_param("i", $newVal);
                            $groupStmt->execute();
                            $groupResult = $groupStmt->get_result();
                            if ($groupResult->num_rows > 0) {
                                $newDisplay = $groupResult->fetch_assoc()['name'];
                            }
                            $groupStmt->close();
                        }
                    }
                    
                    $historyStmt->bind_param("isssss", $entry_id, $user_id, $now, $fieldName, $oldDisplay, $newDisplay);
                    $historyStmt->execute();
                }
            }
            
            $historyStmt->close();
            echo json_encode(['success' => true, 'message' => 'Entry updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating entry: ' . $conn->error]);
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
        
        // Build query - show all entries from user's groups (including deleted ones with status=0)
        // Include deletion info for deleted entries
        $sql = "SELECT e.id, e.user_id, e.group_id, e.type, e.amount, e.datetime, e.message, e.attachment,
                e.status, e.deleted_at, e.deleted_by,
                g.name as group_name, u.name as user_name, u.profile_picture,
                du.name as deleted_by_name, du.profile_picture as deleted_by_picture
                FROM entries e 
                INNER JOIN `groups` g ON e.group_id = g.id
                INNER JOIN users u ON e.user_id = u.id
                INNER JOIN group_members gm ON g.id = gm.group_id
                LEFT JOIN users du ON e.deleted_by = du.id
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
        
        // Ensure only active entries (status=1) are included
        // This is already in WHERE clause, but keeping for safety
        
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
        
        // Add member filter (user who created the entry)
        $member_id = $_GET['member_id'] ?? '';
        if (!empty($member_id)) {
            $sql .= " AND e.user_id = ?";
            $params[] = $member_id;
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
                // Debug: Log deleted entries to check data
                if (isset($row['status']) && $row['status'] == 0) {
                    error_log("Deleted entry ID {$row['id']}: deleted_at={$row['deleted_at']}, deleted_by={$row['deleted_by']}, deleted_by_name=" . ($row['deleted_by_name'] ?? 'NULL'));
                    
                    // If deleted_by_name is NULL but deleted_by exists, try to get it directly
                    if (empty($row['deleted_by_name']) && !empty($row['deleted_by'])) {
                        $nameStmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
                        $nameStmt->bind_param("i", $row['deleted_by']);
                        $nameStmt->execute();
                        $nameResult = $nameStmt->get_result();
                        if ($nameResult->num_rows > 0) {
                            $nameRow = $nameResult->fetch_assoc();
                            $row['deleted_by_name'] = $nameRow['name'];
                        }
                        $nameStmt->close();
                    }
                }
                $entries[] = $row;
            }
        }
        
        // Get statistics for user's groups only (exclude entries with status=0)
        $stats_sql = "SELECT 
                        COALESCE(SUM(CASE WHEN e.type = 'in' AND e.status = 1 THEN e.amount ELSE 0 END), 0) as total_in,
                        COALESCE(SUM(CASE WHEN e.type = 'out' AND e.status = 1 THEN e.amount ELSE 0 END), 0) as total_out,
                        COUNT(DISTINCT CASE WHEN e.status = 1 THEN e.id END) as total_entries
                      FROM entries e
                      INNER JOIN group_members gm ON e.group_id = gm.group_id
                      WHERE gm.user_id = ? AND e.status = 1";
        
        // Apply same filters to statistics
        $stats_params = [$user_id];
        $stats_types = 'i';
        
        if (!empty($search) && strlen($search) >= 3) {
            $stats_sql .= " AND (EXISTS (SELECT 1 FROM `groups` g WHERE g.id = e.group_id AND g.name LIKE ?) 
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
        
        if (!empty($member_id)) {
            $stats_sql .= " AND e.user_id = ?";
            $stats_params[] = $member_id;
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

// Delete entry (set status to 0 and record deletion info)
function deleteEntry($conn, $user_id) {
    try {
        $entry_id = $_POST['id'] ?? '';
        
        if (empty($entry_id)) {
            echo json_encode(['success' => false, 'message' => 'Entry ID is required']);
            return;
        }
        
        // Check if entry exists and user has permission (must be member of the group)
        $checkStmt = $conn->prepare("SELECT e.id, e.status FROM entries e 
                                      INNER JOIN group_members gm ON e.group_id = gm.group_id
                                      WHERE e.id = ? AND gm.user_id = ?");
        $checkStmt->bind_param("ii", $entry_id, $user_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Entry not found or access denied']);
            return;
        }
        
        $entry = $checkResult->fetch_assoc();
        
        // Check if already deleted
        if ($entry['status'] == 0) {
            echo json_encode(['success' => false, 'message' => 'Entry is already deleted']);
            return;
        }
        
        // Set status to 0 and record deletion info
        $now = date('Y-m-d H:i:s');
        $updateStmt = $conn->prepare("UPDATE entries SET status = 0, deleted_at = ?, deleted_by = ? WHERE id = ?");
        $updateStmt->bind_param("sii", $now, $user_id, $entry_id);
        
        if ($updateStmt->execute()) {
            echo json_encode([
                'success' => true, 
                'message' => 'Entry deleted successfully',
                'deleted_at' => $now,
                'deleted_by' => $user_id
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting entry: ' . $conn->error]);
        }
        
        $updateStmt->close();
        $checkStmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Get entry edit history
function getEntryEditHistory($conn, $user_id) {
    try {
        $entry_id = $_GET['entry_id'] ?? '';
        
        if (empty($entry_id)) {
            echo json_encode(['success' => false, 'message' => 'Entry ID is required']);
            return;
        }
        
        // Check if user has permission to view this entry's history
        $checkStmt = $conn->prepare("SELECT e.id FROM entries e 
                                      INNER JOIN group_members gm ON e.group_id = gm.group_id
                                      WHERE e.id = ? AND gm.user_id = ?");
        $checkStmt->bind_param("ii", $entry_id, $user_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Entry not found or access denied']);
            return;
        }
        
        // Get edit history with user names, grouped by edit session (same edited_at)
        $sql = "SELECT eh.id, eh.field_name, eh.old_value, eh.new_value, eh.edited_at,
                u.name as edited_by_name, u.profile_picture as edited_by_picture
                FROM entry_edit_history eh
                INNER JOIN users u ON eh.edited_by = u.id
                WHERE eh.entry_id = ?
                ORDER BY eh.edited_at DESC, eh.id DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $entry_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $history = [];
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $history[] = $row;
            }
        }
        
        echo json_encode([
            'success' => true,
            'history' => $history
        ]);
        
        $stmt->close();
        $checkStmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

$conn->close();
?>
