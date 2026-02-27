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
    <title>Help & Support - Bookify</title>
    <?php include 'pwa-meta.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/style16.css?v=<?php echo defined('ASSET_VERSION') ? ASSET_VERSION : '1.0'; ?>">
</head>
<body>
    <div class="container" style="max-width: 1200px;">
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
                </div>
            </div>
        </header>

        <div style="max-width: 800px; margin: 0 auto;">
            <div class="auth-card">
                <div class="auth-header">
                    <h1 style="color: var(--light-color);"> Help & Support</h1>
                    <p class="subtitle" style="color: var(--light-color);">Get help and find answers to your questions</p>
                </div>

                <div class="help-content" style="padding: 20px 0;">
                    <!-- FAQ Section -->
                    <div class="help-section" style="margin-bottom: 30px;">                        
                        <div class="faq-container">
                            <div class="faq-item">
                                <div class="faq-question">
                                    <div class="faq-question-content">                                            
                                        <span>How do I add a new entry?</span>
                                    </div>
                                    <i class="fas fa-chevron-down faq-arrow"></i>
                                </div>
                                <div class="faq-answer">
                                    <!-- Video Example - Replace with your video URL -->
                                    <div class="faq-video">
                                        <iframe 
                                        src="https://www.youtube.com/embed/YOUR_VIDEO_ID" 
                                        frameborder="0" 
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                            allowfullscreen>
                                        </iframe>
                                    </div>
                                    <!-- OR use local video file -->
                                    <!-- 
                                        <div class="faq-video">
                                        <video controls>
                                            <source src="videos/how-to-add-entry.mp4" type="video/mp4">
                                            Your browser does not support the video tag.
                                        </video>
                                    </div>
                                    -->
                                    <p>Go to Dashboard, fill in the entry form with date, amount, type (Cash In/Cash Out), and description. Click "Add Entry" to save.</p>
                                </div>
                            </div>

                            <div class="faq-item">
                                <div class="faq-question">
                                    <div class="faq-question-content">                                        
                                        <span>How do I create or join a group?</span>
                                    </div>
                                    <i class="fas fa-chevron-down faq-arrow"></i>
                                </div>
                                <div class="faq-answer">
                                    <!-- Video Example -->
                                    <div class="faq-video">
                                        <iframe 
                                            src="https://www.youtube.com/embed/YOUR_VIDEO_ID" 
                                            frameborder="0" 
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                            allowfullscreen>
                                        </iframe>
                                    </div>
                                    <p>Go to "My Groups" page. Click "Create Group" to create a new group, or accept invitations to join existing groups.</p>
                                </div>
                            </div>

                            <div class="faq-item">
                                <div class="faq-question">
                                    <div class="faq-question-content">                                        
                                        <span>How do I change my password?</span>
                                    </div>
                                    <i class="fas fa-chevron-down faq-arrow"></i>
                                </div>
                                <div class="faq-answer">
                                    <p>Go to "My Profile", enter your current password and new password, then click "Update Profile".</p>
                                </div>
                            </div>

                            <div class="faq-item">
                                <div class="faq-question">
                                    <div class="faq-question-content">                                        
                                        <span>Can I attach files to entries?</span>
                                    </div>
                                    <i class="fas fa-chevron-down faq-arrow"></i>
                                </div>
                                <div class="faq-answer">
                                    <p>Yes! When adding an entry, you can attach images or documents using the attachment option in the form.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Section -->
                    <div class="help-section" style="margin-bottom: 30px;">
                        <h2 style="color: var(--light-color); margin-bottom: 20px;">
                            <i class="fas fa-envelope"></i> Contact Support
                        </h2>
                        
                        <div style="padding: 20px; background: white; border-radius: 12px; ">
                            <p style="color: var(--text-primary); margin-bottom: 15px; font-weight: 600;">
                                Need more help? Contact our support team:
                            </p>
                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <i class="fas fa-envelope" style="color: var(--primary-color);"></i>
                                    <span style="color: var(--text-secondary);">Email: codelockinfo@gmail.com</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
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

    <div id="toast" class="toast"></div>
    
    <script>
        const BASE_PATH = '<?php echo BASE_PATH; ?>';
    </script>
    <script src="<?php echo BASE_PATH; ?>/pwa10.js?v=<?php echo ASSET_VERSION; ?>"></script>
    <script src="<?php echo BASE_PATH; ?>/auth9.js?v=<?php echo defined('ASSET_VERSION') ? ASSET_VERSION : '1.0'; ?>"></script>
    <script src="<?php echo BASE_PATH; ?>/dashboard9.js?v=<?php echo defined('ASSET_VERSION') ? ASSET_VERSION : '1.0'; ?>"></script>
    <style>
        /* FAQ Accordion Styles */
        .faq-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .faq-item {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .faq-item:hover {
            border-color: var(--primary-color);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
        }

        .faq-item.active {
            border-color: var(--primary-color);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        }

        .faq-question {
            padding: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f9fafb;
            transition: background 0.3s ease;
            user-select: none;
        }

        .faq-question:hover {
            background: #f3f4f6;
        }

        .faq-item.active .faq-question {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        }

        .faq-question-content {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
        }

        .faq-question-content i {
            color: var(--primary-color);
            font-size: 20px;
        }

        .faq-question-content span {
            color: var(--text-primary);
            font-weight: 600;
            font-size: 16px;
        }

        .faq-arrow {
            color: var(--text-secondary);
            font-size: 14px;
            transition: transform 0.3s ease;
        }

        .faq-item.active .faq-arrow {
            transform: rotate(180deg);
            color: var(--primary-color);
        }

        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease, padding 0.4s ease;
            padding: 0 20px;
        }

        .faq-item.active .faq-answer {
            max-height: 1000px; /* Increased to accommodate videos */
            padding: 0 20px 20px 20px;
        }

        .faq-answer p {
            color: var(--text-secondary);
            line-height: 1.6;
            margin: 0;
            padding-top: 15px;
            margin-bottom: 15px;
        }

        /* Video Container Styles */
        .faq-video {
            margin-top: 20px;
            width: 100%;
            position: relative;
            padding-bottom: 56.25%; /* 16:9 Aspect Ratio */
            height: 0;
            overflow: hidden;
            border-radius: 8px;
            background: #000;
        }

        .faq-video iframe,
        .faq-video video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
            border-radius: 8px;
        }

        .faq-video video {
            object-fit: contain;
        }

        /* Responsive video on mobile */
        @media (max-width: 768px) {
            .faq-video {
                padding-bottom: 56.25%;
                margin-top: 15px;
            }
        }

        .help-link:hover {
            border-color: var(--primary-color) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        }
    </style>
    <script>
        // FAQ Accordion Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const faqItems = document.querySelectorAll('.faq-item');
            
            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question');
                
                question.addEventListener('click', function() {
                    // Close other items (optional - remove if you want multiple open)
                    const isActive = item.classList.contains('active');
                    
                    // Close all items first
                    faqItems.forEach(otherItem => {
                        if (otherItem !== item) {
                            otherItem.classList.remove('active');
                        }
                    });
                    
                    // Toggle current item
                    if (isActive) {
                        item.classList.remove('active');
                    } else {
                        item.classList.add('active');
                    }
                });
            });
        });
    </script>
</body>
</html>

