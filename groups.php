<?php
require_once 'check-session.php';
checkAuth();
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Groups - Cash Book</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .groups-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .groups-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .group-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 2px solid var(--border-color);
        }

        .group-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border-color: var(--primary-color);
        }

        .group-card-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }

        .group-info h3 {
            font-size: 1.25rem;
            color: var(--text-primary);
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .group-role {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .role-admin {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.2), rgba(118, 75, 162, 0.2));
            color: var(--primary-color);
        }

        .role-member {
            background: rgba(107, 114, 128, 0.2);
            color: var(--text-secondary);
        }

        .group-description {
            color: var(--text-secondary);
            font-size: 0.875rem;
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .group-members {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 15px;
            padding: 10px;
            background: var(--light-color);
            border-radius: 8px;
        }

        .group-members i {
            color: var(--primary-color);
        }

        .group-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-small {
            padding: 8px 16px;
            font-size: 0.875rem;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-invite {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .btn-view {
            background: var(--light-color);
            color: var(--text-primary);
            border: 2px solid var(--border-color);
        }

        .btn-delete {
            background: var(--danger-color);
            color: white;
        }

        .create-group-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
            z-index: 100;
        }

        .create-group-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.6);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            padding: 30px;
            max-width: 500px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            animation: slideIn 0.3s ease;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--border-color);
        }

        .modal-header h2 {
            font-size: 1.5rem;
            color: var(--text-primary);
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-secondary);
            transition: color 0.3s ease;
        }

        .modal-close:hover {
            color: var(--danger-color);
        }

        .user-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-height: 400px;
            overflow-y: auto;
        }

        .user-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            background: var(--light-color);
            border-radius: 10px;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .user-item:hover {
            border-color: var(--primary-color);
        }

        .user-item.selected {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            border-color: var(--primary-color);
        }

        .user-item.pending {
            opacity: 0.6;
        }

        .user-item.member {
            background: rgba(16, 185, 129, 0.1);
            border-color: var(--success-color);
        }

        .user-item-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .user-status {
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 8px;
            font-weight: 600;
        }

        .status-member {
            background: rgba(16, 185, 129, 0.2);
            color: var(--success-color);
        }

        .status-pending {
            background: rgba(245, 158, 11, 0.2);
            color: var(--warning-color);
        }

        .empty-groups {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }

        .empty-groups i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .pending-requests-card {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(245, 158, 11, 0.05));
            border: 2px solid var(--warning-color);
        }

        .request-actions {
            display: flex;
            gap: 8px;
        }

        .btn-accept {
            background: var(--success-color);
            color: white;
        }

        .btn-reject {
            background: var(--danger-color);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container groups-container">
        <header class="header">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-users"></i>
                    <h1>My Groups</h1>
                </div>
                <div class="header-actions">
                    <div class="user-info">
                        <i class="fas fa-user-circle"></i>
                        <span><?php echo htmlspecialchars($user['name']); ?></span>
                    </div>
                    <a href="dashboard.php" class="manage-users-link">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </header>

        <!-- Pending Invitations Section -->
        <section class="dashboard-section" id="pendingSection" style="display: none;">
            <div class="dashboard-header">
                <h2><i class="fas fa-bell"></i> Pending Group Invitations</h2>
            </div>
            <div id="pendingInvitations" class="groups-grid">
                <!-- Pending invitations will load here -->
            </div>
        </section>

        <!-- My Groups Section -->
        <section class="dashboard-section">
            <div id="myGroupsList" class="groups-grid">
                <div class="empty-groups">
                    <i class="fas fa-users"></i>
                    <p>Loading your groups...</p>
                </div>
            </div>
        </section>

        <!-- Create Group Button -->
        <button class="create-group-btn" id="createGroupBtn" title="Create New Group">
            <i class="fas fa-plus"></i>
        </button>
    </div>

    <!-- Create Group Modal -->
    <div class="modal" id="createGroupModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-plus-circle"></i> Create New Group</h2>
                <button class="modal-close" onclick="closeModal('createGroupModal')">&times;</button>
            </div>
            <form id="createGroupForm" class="auth-form">
                <div class="form-group">
                    <label for="groupName">
                        <i class="fas fa-users"></i> Group Name
                    </label>
                    <input type="text" id="groupName" placeholder="Enter group name" required>
                </div>
                <div class="form-group">
                    <label for="groupDescription">
                        <i class="fas fa-align-left"></i> Description (Optional)
                    </label>
                    <textarea id="groupDescription" placeholder="Enter group description" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check"></i> Create Group
                </button>
            </form>
        </div>
    </div>

    <!-- Invite Users Modal -->
    <div class="modal" id="inviteUsersModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-user-plus"></i> Invite Users to Group</h2>
                <button class="modal-close" onclick="closeModal('inviteUsersModal')">&times;</button>
            </div>
            <div class="form-group">
                <label><i class="fas fa-search"></i> Search Users</label>
                <input type="text" id="searchUsers" placeholder="Search by name or email..." style="width: 100%; padding: 12px; border: 2px solid var(--border-color); border-radius: 10px;">
            </div>
            <div id="usersList" class="user-list">
                <p style="text-align: center; color: var(--text-secondary);">Loading users...</p>
            </div>
            <button class="btn btn-primary" id="sendInvitesBtn" style="margin-top: 20px; width: 100%;">
                <i class="fas fa-paper-plane"></i> Send Invitations
            </button>
        </div>
    </div>

    <!-- View Group Modal -->
    <div class="modal" id="viewGroupModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-users"></i> Group Details</h2>
                <button class="modal-close" onclick="closeModal('viewGroupModal')">&times;</button>
            </div>
            <div id="groupDetails">
                <!-- Group details will load here -->
            </div>
        </div>
    </div>

    <div id="toast" class="toast"></div>

    <script>
        const CURRENT_USER = <?php echo json_encode($user); ?>;
    </script>
    <script src="groups.js"></script>
</body>
</html>

