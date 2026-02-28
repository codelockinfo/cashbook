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
    <title>Bookify Dashboard</title>
    <?php include 'pwa-meta.php'; ?>
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/style22.css?v=<?php echo defined('ASSET_VERSION') ? ASSET_VERSION : '1.0'; ?>">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/datetime-picker1.css?v=<?php echo defined('ASSET_VERSION') ? ASSET_VERSION : '1.0'; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        <header class="header">
            <div class="header-content">
                <div class="logo">
                    <img src="icons/bookify logo.png" alt="bookify" height="50px">
                    <h1>BOOKIFY</h1>
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
                    <a href="profile" class="manage-users-link">
                        <i class="fas fa-circle-user"></i> My Profile
                    </a>
                    <?php if (userHasCreatedGroup()): ?>
                    <a href="groups" class="manage-users-link" id="myGroupsLink">
                        <i class="fas fa-users"></i> My Groups
                        <span class="notification-badge" id="pendingRequestsBadge" style="display: none;">0</span>
                    </a>
                    <?php else: ?>
                    <button onclick="window.location.href='groups?action=create'" id="createGroupBtnHeader" class="manage-users-link">
                        <i class="fas fa-plus-circle"></i> Create Group
                    </button>
                    <?php endif; ?>
                    <button id="logoutBtn" class="manage-users-link logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </div>
            </div>
        </header>

        <!-- Entry Input Section -->
        <section class="entry-section">
            <div class="entry-card unified-entry-card">
                <div class="card-header">
                    <div class="card-header-left">
                        <i class="fas fa-exchange-alt"></i>
                        <h2>Add Entry</h2>
                    </div>
                    <div class="card-header-right">
                        <select id="defaultGroupSelector" class="default-group-selector">
                            <option value="">All Groups</option>
                        </select>
                    </div>
                </div>
                <form id="entryForm" class="entry-form" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="entryDate">
                            <i class="fas fa-calendar"></i> Date & Time
                        </label>
                        <input type="datetime-local" id="entryDate" required style="display: none;">
                        <div id="entryDateDisplay" style="width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; background: white; cursor: pointer; display: flex; align-items: center; justify-content: space-between;">
                            <span id="entryDateText">Select Date & Time</span>
                            <i class="fas fa-calendar-alt" style="color: #6b7280;"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="entryAmount">
                            <i class="fas fa-rupee-sign"></i> Amount
                        </label>
                        <input type="number" id="entryAmount" placeholder="Enter amount" required min="0" step="0.01">
                    </div>
                    <div class="form-group" id="entryGroupContainer">
                        <label for="entryGroup">
                            <i class="fas fa-users"></i> Group
                        </label>
                        <select id="entryGroup" required>
                            <option value="">Select Group</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="entryMessage">
                            <i class="fas fa-message"></i> Message
                        </label>
                        <textarea id="entryMessage" placeholder="Enter description or message" rows="3"></textarea>
                    </div>
                    <div class="form-group full-width">
                        <label for="entryAttachment">
                            <i class="fas fa-paperclip"></i> Payment Proof (Optional)
                        </label>
                        <div class="file-upload-wrapper">
                            <input type="file" id="entryAttachment" name="entryAttachment" accept="image/*" class="file-input">
                            <label for="entryAttachment" class="file-upload-label">
                                <i class="fas fa-camera"></i> Choose Photo
                            </label>
                            <span class="file-upload-name" id="attachmentFileName">No file chosen</span>
                            <button type="button" class="btn-remove-file" id="removeAttachment" style="display: none;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div id="attachmentPreview" class="attachment-preview" style="display: none;">
                            <img id="attachmentPreviewImg" src="" alt="Preview">
                        </div>
                    </div>
                    <div class="button-group">
                        <button type="button" id="btnCashIn" class="btn btn-cash-in">
                            <i class="fas fa-arrow-down"></i> Cash In
                        </button>
                        <button type="button" id="btnCashOut" class="btn btn-cash-out">
                            <i class="fas fa-arrow-up"></i> Cash Out
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <!-- Dashboard Section -->
        <section class="dashboard-section">
            <div class="dashboard-header">
                <h2><i class="fas fa-chart-line"></i> Transactions</h2>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card stat-balance">
                    <div class="stat-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-label">Total Balance</span>
                        <span class="stat-value" id="totalBalance">
                            <span class="wave-loader">
                                <span></span><span></span><span></span><span></span><span></span>
                            </span>
                        </span>
                    </div>
                </div>
                <div class="stat-card stat-in">
                    <div class="stat-icon">
                        <i class="fas fa-arrow-trend-down"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-label"> Cash In</span>
                        <span class="stat-value" id="totalCashIn">
                            <span class="wave-loader">
                                <span></span><span></span><span></span><span></span><span></span>
                            </span>
                        </span>
                    </div>
                </div>
                <div class="stat-card stat-out">
                    <div class="stat-icon">
                        <i class="fas fa-arrow-trend-up"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-label">Cash Out</span>
                        <span class="stat-value" id="totalCashOut">
                            <span class="wave-loader">
                                <span></span><span></span><span></span><span></span><span></span>
                            </span>
                        </span>
                    </div>
                </div>
                
            </div>

            <!-- Filters -->
            <div class="filters-section">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search transactions (min 3 characters)...">
                </div>
                <div class="filter-group">
                    <div class="filter-item date-range">
                        <label><i class="fas fa-calendar"></i> Date Range</label>
                        <div class="date-inputs">
                            <input type="date" id="filterDateFrom" placeholder="dd-mm-yyyy" style="display: none;">
                            <span class="to-label">to</span>
                            <input type="date" id="filterDateTo" placeholder="dd-mm-yyyy" style="display: none;">
                        </div>
                    </div>
                    <div class="filter-item" id="filterGroupContainer">
                        <label><i class="fas fa-users"></i> Group</label>
                        <select id="filterGroup">
                            <option value="">All Groups</option>
                        </select>
                    </div>
                    <div class="filter-item" id="memberFilterContainer" style="display: none;">
                        <label><i class="fas fa-user"></i> Member</label>
                        <select id="filterMember">
                            <option value="">All Members</option>
                        </select>
                    </div>
                    <div class="filter-item">
                        <label><i class="fas fa-exchange-alt"></i> Type</label>
                        <select id="filterType">
                            <option value="">All Types</option>
                            <option value="in">Cash In</option>
                            <option value="out">Cash Out</option>
                        </select>
                    </div>
                    <button class="btn btn-clear" id="clearFilters">
                        <i class="fas fa-times"></i> Clear Filters
                    </button>
                </div>
            </div>

            <!-- Transactions List -->
            <div class="transactions-section">
                <div class="transactions-header">
                    <h3>Recent Transactions</h3>
                    <div class="sort-options">
                        <label>Sort by:</label>
                        <select id="sortBy">
                            <option value="date_desc">Date (Newest First)</option>
                            <option value="date_asc">Date (Oldest First)</option>
                            <option value="amount_desc">Amount (High to Low)</option>
                            <option value="amount_asc">Amount (Low to High)</option>
                        </select>
                    </div>
                </div>
                <div id="transactionsList" class="transactions-list">
                    <!-- Transactions will be loaded here dynamically -->
                    <div class="transactions-loader" id="transactionsLoader">
                        <div class="wave-loader">
                            <span></span><span></span><span></span><span></span><span></span>
                        </div>
                        <p>Loading transactions...</p>
                    </div>
                    <div class="empty-state" id="emptyState" style="display: none;">
                        <i class="fas fa-inbox"></i>
                        <p>No transactions yet. Add your first entry!</p>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Notification Toast -->
    <div id="toast" class="toast"></div>

    <!-- Sticky Mobile Buttons (Always Visible) -->
    <div class="button-group-sticky-mobile">
        <button type="button" id="btnCashInSticky" class="btn btn-cash-in">
            <i class="fas fa-arrow-down"></i> Cash In
        </button>
        <button type="button" id="btnCashOutSticky" class="btn btn-cash-out">
            <i class="fas fa-arrow-up"></i> Cash Out
        </button>
    </div>

    <!-- Photo Viewer Modal -->
    <div id="photoModal" class="photo-modal" style="display: none;">
        <div class="photo-modal-content">
            <span class="photo-modal-close">&times;</span>
            <img id="photoModalImg" src="" alt="Payment Proof">
            <div class="photo-modal-caption" id="photoModalCaption"></div>
        </div>
    </div>

    <!-- Edit Entry Modal -->
    <div id="editEntryModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-edit"></i> Edit Entry</h2>
                <span class="modal-close" onclick="closeEditModal()">&times;</span>
            </div>
            <form id="editEntryForm" class="entry-form" enctype="multipart/form-data">
                <input type="hidden" id="editEntryId">
                
                <div class="form-group">
                    <label for="editEntryDate">
                        <i class="fas fa-calendar"></i> Date & Time
                    </label>
                    <input type="datetime-local" id="editEntryDate" required style="display: none;">
                    <div id="editEntryDateDisplay" style="width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 10px; background: white; cursor: pointer; display: flex; align-items: center; justify-content: space-between;">
                        <span id="editEntryDateText">Select Date & Time</span>
                        <i class="fas fa-calendar-alt" style="color: #6b7280;"></i>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="editEntryAmount">
                        <i class="fas fa-rupee-sign"></i> Amount
                    </label>
                    <input type="number" id="editEntryAmount" placeholder="Enter amount" required min="0" step="0.01">
                </div>
                
                <div class="form-group">
                    <label for="editEntryType">
                        <i class="fas fa-exchange-alt"></i> Type
                    </label>
                    <select id="editEntryType" required>
                        <option value="in">Cash In</option>
                        <option value="out">Cash Out</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="editEntryGroup">
                        <i class="fas fa-users"></i> Group
                    </label>
                    <select id="editEntryGroup" required>
                        <option value="">Select Group</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="editEntryMessage">
                        <i class="fas fa-message"></i> Message
                    </label>
                    <textarea id="editEntryMessage" placeholder="Enter description or message" rows="3"></textarea>
                </div>
                
                <div class="form-group full-width">
                    <label for="editEntryAttachment">
                        <i class="fas fa-paperclip"></i> Payment Proof (Optional)
                    </label>
                    <div id="currentAttachmentPreview" style="display: none; margin-bottom: 10px;">
                        <p style="font-size: 13px; color: #666;">Current attachment:</p>
                        <img id="currentAttachmentImg" src="" alt="Current" style="max-width: 150px; border-radius: 5px; cursor: pointer;" onclick="openPhotoModal(this.src, 'Current Attachment')">
                        <button type="button" class="btn-remove-file" id="removeCurrentAttachment" style="margin-left: 10px;">
                            <i class="fas fa-times"></i> Remove
                        </button>
                    </div>
                    <div class="file-upload-wrapper">
                        <input type="file" id="editEntryAttachment" name="editEntryAttachment" accept="image/*" class="file-input">
                        <label for="editEntryAttachment" class="file-upload-label">
                            <i class="fas fa-camera"></i> Choose New Photo
                        </label>
                        <span class="file-upload-name" id="editAttachmentFileName">No file chosen</span>
                        <button type="button" class="btn-remove-file" id="removeEditAttachment" style="display: none;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div id="editAttachmentPreview" class="attachment-preview" style="display: none;">
                        <img id="editAttachmentPreviewImg" src="" alt="Preview">
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button type="submit" class="btn btn-primary" id="saveEditBtn">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Entry Details Modal (Edit History) -->
    <div id="entryDetailsModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <h2><i class="fas fa-history"></i> Entry Edit History</h2>
                <span class="modal-close" onclick="closeEntryDetailsModal()">&times;</span>
            </div>
            <div class="modal-body" style="padding: 25px; max-height: 70vh; overflow-y: auto;">
                <div id="entryDetailsContent">
                    <div class="loading-state" style="text-align: center; padding: 40px;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: var(--primary-color);"></i>
                        <p style="margin-top: 15px; color: var(--text-secondary);">Loading edit history...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Entry Confirmation Modal -->
    <div id="deleteEntryModal" class="confirm-modal" style="display: none;">
        <div class="confirm-modal-overlay"></div>
        <div class="confirm-modal-content">
            <div class="confirm-modal-header confirm-modal-header-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Delete Entry</h3>
            </div>
            <div class="confirm-modal-body">
                <p>Are you sure you want to delete this entry?</p>
                <p style="margin-top: 12px; font-size: 0.95rem; color: #6b7280;">This entry will be hidden and will not be included in calculations. This action cannot be undone.</p>
            </div>
            <div class="confirm-modal-footer">
                <button class="btn-cancel" id="deleteEntryCancelBtn">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button class="btn-confirm" id="deleteEntryConfirmBtn">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="confirm-modal" style="display: none;">
        <div class="confirm-modal-overlay"></div>
        <div class="confirm-modal-content">
            <div class="confirm-modal-header">
                <i class="fas fa-exclamation-circle"></i>
                <h3>Confirm Logout</h3>
            </div>
            <div class="confirm-modal-body">
                <p>Are you sure you want to logout?</p>
            </div>
            <div class="confirm-modal-footer">
                <button class="btn-cancel" id="logoutCancelBtn">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button class="btn-confirm" id="logoutConfirmBtn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </div>
        </div>
    </div>

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
    <script src="<?php echo BASE_PATH; ?>/pwa10.js?v=<?php echo defined('ASSET_VERSION') ? ASSET_VERSION : '1.0'; ?>" onerror="console.error('❌ Failed to load pwa10.js from: <?php echo BASE_PATH; ?>/pwa10.js')"></script>
    <script src="<?php echo BASE_PATH; ?>/datetime-picker1.js?v=<?php echo defined('ASSET_VERSION') ? ASSET_VERSION : '1.0'; ?>"></script>
    <script src="<?php echo BASE_PATH; ?>/date-picker.js?v=<?php echo defined('ASSET_VERSION') ? ASSET_VERSION : '1.0'; ?>"></script>
    <script src="<?php echo BASE_PATH; ?>/dashboard9.js?v=<?php echo defined('ASSET_VERSION') ? ASSET_VERSION : '1.0'; ?>"></script>
</body>
</html>

