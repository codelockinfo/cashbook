<?php
// Configure session for subdirectory support
if (session_status() === PHP_SESSION_NONE) {
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    $cookiePath = $basePath ? $basePath : '/';
    
    session_set_cookie_params([
        'lifetime' => 604800, // 1 week
        'path' => $cookiePath,
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    session_start();
}

header('Content-Type: application/json');

require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'createGroup':
        createGroup($conn, $user_id);
        break;
    case 'getMyGroups':
        getMyGroups($conn, $user_id);
        break;
    case 'getAvailableUsers':
        getAvailableUsers($conn, $user_id);
        break;
    case 'sendInvitations':
        sendInvitations($conn, $user_id);
        break;
    case 'getPendingInvitations':
        getPendingInvitations($conn, $user_id);
        break;
    case 'getPendingInvitationsCount':
        getPendingInvitationsCount($conn, $user_id);
        break;
    case 'respondToInvitation':
        respondToInvitation($conn, $user_id);
        break;
    case 'getGroupDetails':
        getGroupDetails($conn, $user_id);
        break;
    case 'deleteGroup':
        deleteGroup($conn, $user_id);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

// Create new group
function createGroup($conn, $user_id) {
    try {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Group name is required']);
            return;
        }
        
        // Insert group
        $stmt = $conn->prepare("INSERT INTO `groups` (name, description, created_by) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $name, $description, $user_id);
        
        if ($stmt->execute()) {
            $group_id = $conn->insert_id;
            
            // Add creator as admin
            $stmt2 = $conn->prepare("INSERT INTO group_members (group_id, user_id, role) VALUES (?, ?, 'admin')");
            $stmt2->bind_param("ii", $group_id, $user_id);
            $stmt2->execute();
            
            echo json_encode([
                'success' => true,
                'message' => 'Group created successfully',
                'group_id' => $group_id
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error creating group: ' . $conn->error]);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Get user's groups
function getMyGroups($conn, $user_id) {
    try {
        $sql = "SELECT g.id, g.name, g.description, g.created_by, gm.role,
                (SELECT COUNT(*) FROM group_members WHERE group_id = g.id) as member_count,
                (SELECT COUNT(*) FROM group_requests WHERE group_id = g.id AND status = 'pending') as pending_count
                FROM `groups` g
                INNER JOIN group_members gm ON g.id = gm.group_id
                WHERE gm.user_id = ?
                ORDER BY g.created_at DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $groups = [];
        while ($row = $result->fetch_assoc()) {
            $groups[] = $row;
        }
        
        echo json_encode(['success' => true, 'groups' => $groups]);
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Get available users to invite
function getAvailableUsers($conn, $user_id) {
    try {
        $group_id = $_GET['group_id'] ?? 0;
        
        if ($group_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid group ID']);
            return;
        }
        
        // Check if user is admin of the group
        $stmt = $conn->prepare("SELECT role FROM group_members WHERE group_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $group_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0 || $result->fetch_assoc()['role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Only admins can invite users']);
            return;
        }
        
        // Get all users with their status for this group
        $sql = "SELECT u.id, u.name, u.email,
                CASE 
                    WHEN gm.user_id IS NOT NULL THEN 'member'
                    WHEN gr.user_id IS NOT NULL AND gr.status = 'pending' THEN 'pending'
                    ELSE 'available'
                END as status
                FROM users u
                LEFT JOIN group_members gm ON u.id = gm.user_id AND gm.group_id = ?
                LEFT JOIN group_requests gr ON u.id = gr.user_id AND gr.group_id = ? AND gr.status = 'pending'
                WHERE u.id != ?
                ORDER BY u.name ASC";
        
        $stmt2 = $conn->prepare($sql);
        $stmt2->bind_param("iii", $group_id, $group_id, $user_id);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        
        $users = [];
        while ($row = $result2->fetch_assoc()) {
            $users[] = $row;
        }
        
        echo json_encode(['success' => true, 'users' => $users]);
        $stmt2->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Send invitations to users
function sendInvitations($conn, $user_id) {
    try {
        $group_id = $_POST['group_id'] ?? 0;
        $user_ids = $_POST['user_ids'] ?? '';
        
        if ($group_id <= 0 || empty($user_ids)) {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            return;
        }
        
        // Check if user is admin
        $stmt = $conn->prepare("SELECT role FROM group_members WHERE group_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $group_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0 || $result->fetch_assoc()['role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Only admins can send invitations']);
            return;
        }
        
        $user_ids_array = explode(',', $user_ids);
        $success_count = 0;
        
        $stmt2 = $conn->prepare("INSERT INTO group_requests (group_id, user_id, invited_by, status) VALUES (?, ?, ?, 'pending')");
        
        foreach ($user_ids_array as $target_user_id) {
            $target_user_id = intval($target_user_id);
            if ($target_user_id > 0) {
                $stmt2->bind_param("iii", $group_id, $target_user_id, $user_id);
                if ($stmt2->execute()) {
                    $success_count++;
                }
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => "Invitations sent to $success_count user(s)",
            'count' => $success_count
        ]);
        
        $stmt2->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Get pending invitations for current user
function getPendingInvitations($conn, $user_id) {
    try {
        $sql = "SELECT gr.id, gr.group_id, gr.message, g.name as group_name, 
                u.name as invited_by_name, gr.created_at
                FROM group_requests gr
                INNER JOIN `groups` g ON gr.group_id = g.id
                INNER JOIN users u ON gr.invited_by = u.id
                WHERE gr.user_id = ? AND gr.status = 'pending'
                ORDER BY gr.created_at DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $invitations = [];
        while ($row = $result->fetch_assoc()) {
            $invitations[] = $row;
        }
        
        echo json_encode(['success' => true, 'invitations' => $invitations]);
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Get count of pending invitations for current user
function getPendingInvitationsCount($conn, $user_id) {
    try {
        $sql = "SELECT COUNT(*) as count
                FROM group_requests gr
                WHERE gr.user_id = ? AND gr.status = 'pending'";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        $count = intval($row['count']);
        
        echo json_encode(['success' => true, 'count' => $count]);
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage(), 'count' => 0]);
    }
}

// Respond to invitation (accept/reject)
function respondToInvitation($conn, $user_id) {
    try {
        $invitation_id = $_POST['invitation_id'] ?? 0;
        $status = $_POST['status'] ?? '';
        
        if ($invitation_id <= 0 || !in_array($status, ['approved', 'rejected'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            return;
        }
        
        // Get invitation details
        $stmt = $conn->prepare("SELECT group_id, user_id FROM group_requests WHERE id = ? AND user_id = ? AND status = 'pending'");
        $stmt->bind_param("ii", $invitation_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Invitation not found']);
            return;
        }
        
        $invitation = $result->fetch_assoc();
        $group_id = $invitation['group_id'];
        
        // Update invitation status
        $stmt2 = $conn->prepare("UPDATE group_requests SET status = ? WHERE id = ?");
        $stmt2->bind_param("si", $status, $invitation_id);
        $stmt2->execute();
        
        // If approved, add user to group
        if ($status === 'approved') {
            $stmt3 = $conn->prepare("INSERT INTO group_members (group_id, user_id, role) VALUES (?, ?, 'member')");
            $stmt3->bind_param("ii", $group_id, $user_id);
            $stmt3->execute();
        }
        
        echo json_encode([
            'success' => true,
            'message' => $status === 'approved' ? 'You have joined the group!' : 'Invitation declined'
        ]);
        
        $stmt2->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Get group details
function getGroupDetails($conn, $user_id) {
    try {
        $group_id = $_GET['group_id'] ?? 0;
        
        if ($group_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid group ID']);
            return;
        }
        
        // Check if user is member
        $stmt = $conn->prepare("SELECT role FROM group_members WHERE group_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $group_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'You are not a member of this group']);
            return;
        }
        
        // Get group info
        $stmt2 = $conn->prepare("SELECT g.*, u.name as created_by_name FROM `groups` g INNER JOIN users u ON g.created_by = u.id WHERE g.id = ?");
        $stmt2->bind_param("i", $group_id);
        $stmt2->execute();
        $group = $stmt2->get_result()->fetch_assoc();
        
        // Get members
        $stmt3 = $conn->prepare("SELECT u.id, u.name, u.email, gm.role FROM group_members gm INNER JOIN users u ON gm.user_id = u.id WHERE gm.group_id = ? ORDER BY gm.role DESC, u.name ASC");
        $stmt3->bind_param("i", $group_id);
        $stmt3->execute();
        $result3 = $stmt3->get_result();
        
        $members = [];
        while ($row = $result3->fetch_assoc()) {
            $members[] = $row;
        }
        
        // Get pending invitations
        $stmt4 = $conn->prepare("SELECT u.id, u.name, u.email FROM group_requests gr INNER JOIN users u ON gr.user_id = u.id WHERE gr.group_id = ? AND gr.status = 'pending' ORDER BY u.name ASC");
        $stmt4->bind_param("i", $group_id);
        $stmt4->execute();
        $result4 = $stmt4->get_result();
        
        $pending = [];
        while ($row = $result4->fetch_assoc()) {
            $pending[] = $row;
        }
        
        $group['members'] = $members;
        $group['pending_invitations'] = $pending;
        
        echo json_encode(['success' => true, 'group' => $group]);
        
        $stmt2->close();
        $stmt3->close();
        $stmt4->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// Delete group
function deleteGroup($conn, $user_id) {
    try {
        $group_id = $_POST['group_id'] ?? 0;
        
        if ($group_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid group ID']);
            return;
        }
        
        // Check if user is admin
        $stmt = $conn->prepare("SELECT role FROM group_members WHERE group_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $group_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0 || $result->fetch_assoc()['role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Only admins can delete groups']);
            return;
        }
        
        // Delete group (cascade will delete members and requests)
        $stmt2 = $conn->prepare("DELETE FROM `groups` WHERE id = ?");
        $stmt2->bind_param("i", $group_id);
        
        if ($stmt2->execute()) {
            echo json_encode(['success' => true, 'message' => 'Group deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error deleting group: ' . $conn->error]);
        }
        
        $stmt2->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

$conn->close();
?>

