<?php
require_once 'config.php';
require_once 'check-session.php';
checkAuth();
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Groups - Bookify</title>
    <?php include 'pwa-meta.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/style13.css?v=<?php echo defined('ASSET_VERSION') ? ASSET_VERSION : '1.0'; ?>">
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
            padding: 18px;
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
            margin-bottom: 10px;
        }

        .group-info h3 {
            font-size: 1.25rem;
            color: var(--text-primary);
            margin-bottom: 3px;
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
            margin-bottom: 10px;
            line-height: 1.5;
        }

        .group-members {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
            padding: 8px;
            background: var(--light-color);
            border-radius: 8px;
        }

        .group-members i {
            color: var(--primary-color);
        }

        .group-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-small {
            padding: 8px 14px;
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
            color: white;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: white;
            transition: color 0.3s ease;
        }

        .modal-close:hover {
            color: #211f1f;
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
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
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

        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            .dashboard-section {
                padding: 20px 12px 12px 12px;
            }
            .groups-grid {
                grid-template-columns: 1fr;
                gap: 15px;
                padding: 0px;
            }

            .group-card {
                padding: 15px;
                margin-bottom: 0;
            }

            .group-info h3 {
                font-size: 1.1rem;
                flex-wrap: wrap;
                gap: 8px;
            }

            .group-role {
                font-size: 0.7rem;
                padding: 3px 8px;
            }

            .group-description {
                font-size: 0.8rem;
            }

            .group-actions {
                display: flex;
                flex-direction: column;
                gap: 8px;
                width: 100%;
            }

            .btn-small {
                width: 100%;
                justify-content: center;
                padding: 9px 14px;
                font-size: 0.875rem;
            }

            .btn-invite,
            .btn-view,
            .btn-delete {
                width: 100%;
            }

            /* Header mobile adjustments */
            .header-content {
                flex-direction: column;
                gap: 15px;
            }

            .header-actions {
                flex-direction: column;
                width: 100%;
                align-items: stretch !important;
            }

            .manage-users-link {
                width: 100%;
                justify-content: center;
                text-align: center;
            }

            /* Modal adjustments */
            .modal-content {
                padding: 20px;
                margin: 10px;
            }

            .modal-header h2 {
                font-size: 1.25rem;
            }

            /* Empty state */
            .empty-groups {
                padding: 40px 20px;
            }

            .empty-groups i {
                font-size: 3rem;
            }

            /* User list in modal */
            .user-item {
                padding: 10px;
            }

            .user-avatar {
                width: 35px;
                height: 35px;
                font-size: 0.875rem;
            }

            .request-actions {
                flex-direction: column;
                width: 100%;
                gap: 8px;
            }

            .btn-accept,
            .btn-reject {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .group-card {
                padding: 12px;
                border-radius: 12px;
            }

            .group-info h3 {
                font-size: 1rem;
            }

            .group-description {
                font-size: 0.75rem;
            }

            .group-members {
                font-size: 0.875rem;
                padding: 6px;
            }

            .btn-small {
                padding: 8px 12px;
                font-size: 0.8rem;
            }

            .modal-content {
                padding: 15px;
            }

            .user-item-info {
                gap: 8px;
            }
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
                        <?php if (!empty($user['profile_picture'])): ?>
                            <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" 
                                 alt="Profile" 
                                 class="user-avatar"
                                 onerror="this.style.display='none'; if(this.nextElementSibling) { this.nextElementSibling.style.display='inline-flex'; }">
                            <i class="fas fa-user-circle"></i>
                        <?php else: ?>
                            <i class="fas fa-user-circle" style="display:inline-flex;"></i>
                        <?php endif; ?>
                        <span><?php echo htmlspecialchars($user['name']); ?></span>
                    </div>
                    <button id="createGroupBtnHeader" class="manage-users-link">
                        <i class="fas fa-plus-circle"></i> Create Group
                    </button>
                    <a href="profile" class="manage-users-link">
                        <i class="fas fa-circle-user"></i> My Profile
                    </a>
                    <a href="dashboard" class="manage-users-link">
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

    </div>

    <!-- Create Group Modal -->
    <div class="modal" id="createGroupModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-plus-circle"></i> Create Group</h2>
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
                <div style="display: flex; justify-content: center;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Create Group
                    </button>
                </div>
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

    <!-- Delete Group Confirmation Modal -->
    <div id="deleteGroupModal" class="confirm-modal" style="display: none;">
        <div class="confirm-modal-overlay"></div>
        <div class="confirm-modal-content">
            <div class="confirm-modal-header confirm-modal-header-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Delete Group</h3>
            </div>
            <div class="confirm-modal-body">
                <p id="deleteGroupMessage">Are you sure you want to delete this group? This will delete all associated entries and cannot be undone.</p>
            </div>
            <div class="confirm-modal-footer">
                <button class="btn-cancel" id="deleteGroupCancelBtn">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button class="btn-confirm" id="deleteGroupConfirmBtn">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>

    <div id="toast" class="toast"></div>

    <!-- Floating Install Button (Bottom Right) -->
    <!-- <button id="floatingInstallBtn" class="floating-install-btn" onclick="if(typeof installPWA === 'function') { installPWA(); } else { alert('Install not available'); }" title="Install App">
        <i class="fas fa-download"></i>
    </button> -->

    <script>
        // Pass PHP data to JavaScript
        const BASE_PATH = '<?php echo BASE_PATH; ?>';
        const CURRENT_USER = <?php echo json_encode($user); ?>;
        
        // PWA popup functionality removed
    </script>
    <script src="<?php echo BASE_PATH; ?>/pwa10.js?v=<?php echo defined('ASSET_VERSION') ? ASSET_VERSION : '1.0'; ?>"></script>
    <script src="<?php echo BASE_PATH; ?>/groups4.js?v=<?php echo defined('ASSET_VERSION') ? ASSET_VERSION : '1.0'; ?>"></script>
</body>
</html>

