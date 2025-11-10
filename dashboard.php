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
    <title>Cash Book Dashboard</title>
    <?php include 'pwa-meta.php'; ?>
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/style1.css?v=<?php echo defined('ASSET_VERSION') ? ASSET_VERSION : '1.0'; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        <header class="header">
            <div class="header-content">
                <div class="logo">
                    <img src="icons/Black and Green Simple Organic Cosmetic Product Logo (4).png" alt="bookify" height="50px">
                    <h1>BOOKIFY</h1>
                </div>
                <div class="header-actions">
                    <div class="user-info">
                        <?php if (!empty($user['profile_picture'])): ?>
                            <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" 
                                 alt="Profile" 
                                 class="user-avatar"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                            <i class="fas fa-user-circle" style="display:none;"></i>
                        <?php else: ?>
                            <i class="fas fa-user-circle"></i>
                        <?php endif; ?>
                        <span><?php echo htmlspecialchars($user['name']); ?></span>
                    </div>
                    <a href="profile" class="manage-users-link">
                        <i class="fas fa-user-edit"></i> My Profile
                    </a>
                    <a href="groups" class="manage-users-link">
                        <i class="fas fa-users"></i> My Groups
                    </a>
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
                        <input type="datetime-local" id="entryDate" required>
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
                <div class="stat-card stat-in">
                    <div class="stat-icon">
                        <i class="fas fa-arrow-trend-up"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-label">Total Cash In</span>
                        <span class="stat-value" id="totalCashIn">₹ 0</span>
                    </div>
                </div>
                <div class="stat-card stat-out">
                    <div class="stat-icon">
                        <i class="fas fa-arrow-trend-down"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-label">Total Cash Out</span>
                        <span class="stat-value" id="totalCashOut">₹ 0</span>
                    </div>
                </div>
                <div class="stat-card stat-balance">
                    <div class="stat-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-label">Total Balance</span>
                        <span class="stat-value" id="totalBalance">₹ 0</span>
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
                            <input type="date" id="filterDateFrom" placeholder="From">
                            <span class="to-label">to</span>
                            <input type="date" id="filterDateTo" placeholder="To">
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
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>No transactions yet. Add your first entry!</p>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Notification Toast -->
    <div id="toast" class="toast"></div>

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
                    <input type="datetime-local" id="editEntryDate" required>
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

    <!-- PWA Install Prompt Banner -->
    <div id="pwaInstallBanner" class="pwa-install-banner" style="display: none;">
        <button class="pwa-banner-close" id="closePWABanner">&times;</button>
        <div class="pwa-banner-content">
            <div class="pwa-banner-icon">
                <i class="fas fa-mobile-alt"></i>
            </div>
            <div class="pwa-banner-text">
                <h4>Install Cash Book App</h4>
                <p>Add to your home screen for quick access and offline use!</p>
            </div>
            <button class="pwa-banner-install" id="installPWABanner">
                <i class="fas fa-download"></i> Install
            </button>
        </div>
    </div>

    <script>
        // Pass PHP data to JavaScript
        const BASE_PATH = '<?php echo BASE_PATH; ?>';
        const CURRENT_USER = <?php echo json_encode($user); ?>;
    </script>
    <script src="<?php echo BASE_PATH; ?>/pwa1.js?v=<?php echo defined('ASSET_VERSION') ? ASSET_VERSION : '1.0'; ?>"></script>
    <script src="<?php echo BASE_PATH; ?>/dashboard1.js?v=<?php echo defined('ASSET_VERSION') ? ASSET_VERSION : '1.0'; ?>"></script>
</body>
</html>

