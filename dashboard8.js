// API Configuration  
const API_URL = ((typeof BASE_PATH !== 'undefined' && BASE_PATH) ? BASE_PATH : '') + '/api.php';
const AUTH_API_URL = ((typeof BASE_PATH !== 'undefined' && BASE_PATH) ? BASE_PATH : '') + '/auth-api.php';
const GROUP_API_URL = ((typeof BASE_PATH !== 'undefined' && BASE_PATH) ? BASE_PATH : '') + '/group-api.php';

// Initialize the application
document.addEventListener('DOMContentLoaded', function() {
    initializeDateTimeInputs();
    loadGroups(); // This will call loadTransactions() after setting default group
    // Don't call loadTransactions() here - it will be called by handleDefaultGroupChange()
    setupEventListeners();
    updatePendingRequestsBadge(); // Load pending requests count
    // Update badge every 30 seconds
    setInterval(updatePendingRequestsBadge, 30000);
});

// Initialize datetime inputs with current date/time
function initializeDateTimeInputs() {
    const now = new Date();
    const localDateTime = new Date(now - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    document.getElementById('entryDate').value = localDateTime;
}

// Scroll to form function (for mobile)
function scrollToForm() {
    const entrySection = document.querySelector('.entry-section');
    if (entrySection) {
        const offset = 20;
        const elementPosition = entrySection.getBoundingClientRect().top;
        const offsetPosition = elementPosition + window.pageYOffset - offset;
        
        window.scrollTo({
            top: Math.max(0, offsetPosition),
            behavior: 'smooth'
        });
        
        // Focus on amount input after scrolling
        setTimeout(() => {
            const amountInput = document.getElementById('entryAmount');
            if (amountInput) {
                amountInput.focus();
            }
        }, 500);
    }
}

// Scroll to latest entry function
function scrollToLatestEntry(entryId) {
    // Wait a bit for DOM to update after loadTransactions
    setTimeout(() => {
        const entryElement = document.querySelector(`[data-entry-id="${entryId}"]`);
        if (entryElement) {
            // Calculate offset considering fixed buttons at bottom
            const buttonGroupHeight = isMobileView() ? 80 : 0;
            const offset = 100 + buttonGroupHeight; // Offset from top + button height
            
            // Get element position
            const elementPosition = entryElement.getBoundingClientRect().top;
            const offsetPosition = elementPosition + window.pageYOffset - offset;
            
            // Scroll to entry
            window.scrollTo({
                top: Math.max(0, offsetPosition),
                behavior: 'smooth'
            });
            
            // Highlight the entry briefly
            entryElement.style.transition = 'background-color 0.3s';
            entryElement.style.backgroundColor = 'rgba(102, 126, 234, 0.1)';
            setTimeout(() => {
                entryElement.style.backgroundColor = '';
            }, 2000);
        } else {
            // If entry not found by ID, try to scroll to first entry
            setTimeout(() => {
                const firstEntry = document.querySelector('.transaction-item[data-entry-id]');
                if (firstEntry) {
                    const buttonGroupHeight = isMobileView() ? 80 : 0;
                    const offset = 100 + buttonGroupHeight;
                    const elementPosition = firstEntry.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - offset;
                    
                    window.scrollTo({
                        top: Math.max(0, offsetPosition),
                        behavior: 'smooth'
                    });
                }
            }, 500);
        }
    }, 500); // Increased timeout to ensure DOM is fully updated
}

// Check if form has data (amount and message)
function formHasData() {
    const amount = document.getElementById('entryAmount').value.trim();
    const message = document.getElementById('entryMessage').value.trim();
    return amount !== '' && message !== '';
}

// Check if mobile view
function isMobileView() {
    return window.innerWidth <= 768;
}

// Check if user is near bottom of page
function isUserNearBottom() {
    const scrollPosition = window.pageYOffset || document.documentElement.scrollTop;
    const windowHeight = window.innerHeight;
    const documentHeight = document.documentElement.scrollHeight;
    // Consider user "near bottom" if within 200px of bottom
    return (documentHeight - scrollPosition - windowHeight) < 200;
}

// Setup handlers to keep buttons visible when keyboard appears
function setupKeyboardHandlers() {
    const stickyButtonGroup = document.querySelector('.button-group-sticky-mobile');
    if (!stickyButtonGroup) return;
    
    // Ensure sticky buttons are always fixed and visible
    stickyButtonGroup.style.position = 'fixed';
    stickyButtonGroup.style.bottom = '0';
    stickyButtonGroup.style.zIndex = '10000';
    
    let initialViewportHeight = window.visualViewport ? window.visualViewport.height : window.innerHeight;
    
    // Handle viewport resize (keyboard appearing/disappearing)
    function handleViewportResize() {
        if (window.visualViewport) {
            const currentHeight = window.visualViewport.height;
            const heightDifference = initialViewportHeight - currentHeight;
            
            // Always ensure sticky buttons stay visible above keyboard
            stickyButtonGroup.style.position = 'fixed';
            stickyButtonGroup.style.bottom = '0';
            stickyButtonGroup.style.zIndex = '10000';
            
            // If keyboard is visible, ensure buttons are above it
            if (heightDifference > 150) {
                // Keyboard is visible - buttons should stay at bottom of viewport
                stickyButtonGroup.style.bottom = '0';
            }
        } else {
            // Fallback - always keep buttons fixed
            stickyButtonGroup.style.position = 'fixed';
            stickyButtonGroup.style.bottom = '0';
            stickyButtonGroup.style.zIndex = '10000';
        }
    }
    
    // Use visualViewport API if available (better for mobile keyboards)
    if (window.visualViewport) {
        window.visualViewport.addEventListener('resize', handleViewportResize);
        window.visualViewport.addEventListener('scroll', handleViewportResize);
    } else {
        // Fallback for browsers without visualViewport API
        window.addEventListener('resize', handleViewportResize);
    }
    
    // Handle input focus to ensure buttons stay visible
    // Use event delegation to catch dynamically added inputs
    document.addEventListener('focusin', function(e) {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
            // Ensure sticky buttons stay visible
            if (stickyButtonGroup) {
                stickyButtonGroup.style.position = 'fixed';
                stickyButtonGroup.style.bottom = '0';
                stickyButtonGroup.style.zIndex = '10000';
            }
        }
    }, true);
    
    // Also handle existing inputs explicitly
    const inputs = document.querySelectorAll('input, textarea');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            // Ensure sticky buttons stay visible
            if (stickyButtonGroup) {
                stickyButtonGroup.style.position = 'fixed';
                stickyButtonGroup.style.bottom = '0';
                stickyButtonGroup.style.zIndex = '10000';
            }
        });
    });
}

// Button click handler (shared function)
function handleButtonClick(type) {
    if (formHasData()) {
        // Form has data - save entry (will scroll to latest entry after save)
        handleEntry(type);
    } else {
        // Form is empty - check if user is at bottom, then scroll to form
        if (isUserNearBottom()) {
            scrollToForm();
        } else if (isMobileView()) {
            scrollToForm();
        }
    }
}

// Setup all event listeners
function setupEventListeners() {
    // Original button clicks with smart behavior
    const btnCashIn = document.getElementById('btnCashIn');
    const btnCashOut = document.getElementById('btnCashOut');
    
    if (btnCashIn) {
        btnCashIn.addEventListener('click', () => handleButtonClick('in'));
    }
    
    if (btnCashOut) {
        btnCashOut.addEventListener('click', () => handleButtonClick('out'));
    }
    
    // Sticky mobile button clicks (same functionality)
    const btnCashInSticky = document.getElementById('btnCashInSticky');
    const btnCashOutSticky = document.getElementById('btnCashOutSticky');
    
    if (btnCashInSticky) {
        btnCashInSticky.addEventListener('click', () => handleButtonClick('in'));
    }
    
    if (btnCashOutSticky) {
        btnCashOutSticky.addEventListener('click', () => handleButtonClick('out'));
    }
    
    // Keep buttons visible when keyboard appears (mobile)
    if (isMobileView()) {
        setupKeyboardHandlers();
    }
    
    // Logout button
    document.getElementById('logoutBtn').addEventListener('click', handleLogout);
    
    // File upload handlers
    const attachmentInput = document.getElementById('entryAttachment');
    const removeAttachmentBtn = document.getElementById('removeAttachment');
    
    if (attachmentInput) {
        attachmentInput.addEventListener('change', handleAttachmentChange);
        
        // Explicit click handler for mobile compatibility
        const attachmentLabel = document.querySelector('label[for="entryAttachment"].file-upload-label');
        if (attachmentLabel) {
            attachmentLabel.addEventListener('click', function(e) {
                e.preventDefault();
                attachmentInput.click();
            });
        }
    }
    
    if (removeAttachmentBtn) {
        removeAttachmentBtn.addEventListener('click', removeAttachment);
    }
    
    // Photo modal close
    const photoModalClose = document.querySelector('.photo-modal-close');
    const photoModal = document.getElementById('photoModal');
    
    if (photoModalClose) {
        photoModalClose.addEventListener('click', closePhotoModal);
    }
    
    if (photoModal) {
        photoModal.addEventListener('click', function(e) {
            if (e.target === photoModal) {
                closePhotoModal();
            }
        });
    }
    
    // Search and filters
    document.getElementById('searchInput').addEventListener('input', debounce(handleSearch, 500));
    document.getElementById('filterDateFrom').addEventListener('change', loadTransactions);
    document.getElementById('filterDateTo').addEventListener('change', loadTransactions);
    document.getElementById('filterGroup').addEventListener('change', handleGroupChange);
    document.getElementById('filterMember').addEventListener('change', loadTransactions);
    document.getElementById('filterType').addEventListener('change', loadTransactions);
    document.getElementById('sortBy').addEventListener('change', loadTransactions);
    document.getElementById('clearFilters').addEventListener('click', clearFilters);
    
    // Default group selector
    document.getElementById('defaultGroupSelector').addEventListener('change', handleDefaultGroupChange);
    
    // Delete entry modal handlers
    setupDeleteEntryModalListeners();
}

// Setup delete entry modal event listeners
function setupDeleteEntryModalListeners() {
    const deleteModal = document.getElementById('deleteEntryModal');
    const deleteConfirmBtn = document.getElementById('deleteEntryConfirmBtn');
    const deleteCancelBtn = document.getElementById('deleteEntryCancelBtn');
    
    if (deleteConfirmBtn) {
        deleteConfirmBtn.addEventListener('click', confirmDeleteEntry);
    }
    
    if (deleteCancelBtn) {
        deleteCancelBtn.addEventListener('click', hideDeleteEntryModal);
    }
    
    // Close modal when clicking overlay
    if (deleteModal) {
        deleteModal.addEventListener('click', function(e) {
            if (e.target === deleteModal || e.target.classList.contains('confirm-modal-overlay')) {
                hideDeleteEntryModal();
            }
        });
    }
    
    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const deleteModal = document.getElementById('deleteEntryModal');
            if (deleteModal && deleteModal.style.display === 'flex') {
                hideDeleteEntryModal();
            }
        }
    });
}

// Handle attachment file selection
function handleAttachmentChange(e) {
    const file = e.target.files[0];
    const fileNameSpan = document.getElementById('attachmentFileName');
    const removeBtn = document.getElementById('removeAttachment');
    const preview = document.getElementById('attachmentPreview');
    const previewImg = document.getElementById('attachmentPreviewImg');
    
    if (file) {
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            showToast('Invalid file type. Only images are allowed.', 'error');
            e.target.value = '';
            return;
        }
        
        // Validate file size (10MB max)
        const maxSize = 10 * 1024 * 1024;
        if (file.size > maxSize) {
            showToast('File size too large. Maximum 10MB allowed.', 'error');
            e.target.value = '';
            return;
        }
        
        fileNameSpan.textContent = file.name;
        removeBtn.style.display = 'inline-block';
        
        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}

// Remove attachment
function removeAttachment() {
    const attachmentInput = document.getElementById('entryAttachment');
    const fileNameSpan = document.getElementById('attachmentFileName');
    const removeBtn = document.getElementById('removeAttachment');
    const preview = document.getElementById('attachmentPreview');
    
    attachmentInput.value = '';
    fileNameSpan.textContent = 'No file chosen';
    removeBtn.style.display = 'none';
    preview.style.display = 'none';
}

// Open photo modal
function openPhotoModal(imageSrc, caption) {
    const modal = document.getElementById('photoModal');
    const modalImg = document.getElementById('photoModalImg');
    const modalCaption = document.getElementById('photoModalCaption');
    
    modal.style.display = 'flex';
    modalImg.src = imageSrc;
    modalCaption.textContent = caption;
}

// Close photo modal
function closePhotoModal() {
    const modal = document.getElementById('photoModal');
    modal.style.display = 'none';
}

// Handle Logout
function handleLogout() {
    showLogoutModal();
}

// Show logout confirmation modal
function showLogoutModal() {
    const modal = document.getElementById('logoutModal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    // Setup button handlers
    const confirmBtn = document.getElementById('logoutConfirmBtn');
    const cancelBtn = document.getElementById('logoutCancelBtn');
    const overlay = modal.querySelector('.confirm-modal-overlay');
    
    // Remove old listeners
    const newConfirmBtn = confirmBtn.cloneNode(true);
    const newCancelBtn = cancelBtn.cloneNode(true);
    confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
    cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);
    
    // Add new listeners
    newConfirmBtn.addEventListener('click', performLogout);
    newCancelBtn.addEventListener('click', hideLogoutModal);
    overlay.addEventListener('click', hideLogoutModal);
}

// Hide logout modal
function hideLogoutModal() {
    const modal = document.getElementById('logoutModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Perform actual logout
async function performLogout() {
    hideLogoutModal();
    
    try {
        const response = await fetch(AUTH_API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'logout'
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Logged out successfully', 'success');
            setTimeout(() => {
                window.location.href = 'login';
            }, 500);
        } else {
            showToast('Error logging out', 'error');
        }
    } catch (error) {
        showToast('Error logging out', 'error');
    }
}

// Load groups for dropdown
async function loadGroups() {
    try {
        const response = await fetch(`${API_URL}?action=getUserGroups`);
        const data = await response.json();
        
        if (data.success) {
            const entrySelect = document.getElementById('entryGroup');
            const filterSelect = document.getElementById('filterGroup');
            const defaultSelect = document.getElementById('defaultGroupSelector');
            
            // Clear existing options (except first)
            entrySelect.innerHTML = '<option value="">Select Group</option>';
            filterSelect.innerHTML = '<option value="">All Groups</option>';
            defaultSelect.innerHTML = '<option value="">All Groups</option>';
            
            // Add group options
            data.groups.forEach(group => {
                const option1 = new Option(group.name, group.id);
                const option2 = new Option(group.name, group.id);
                const option3 = new Option(group.name, group.id);
                
                entrySelect.add(option1);
                filterSelect.add(option2);
                defaultSelect.add(option3);
            });
            
            // Get most frequently accessed group
            if (data.groups.length > 0) {
                try {
                    const mostAccessedResponse = await fetch(`${API_URL}?action=getMostAccessedGroup`);
                    const mostAccessedData = await mostAccessedResponse.json();
                    
                    let defaultGroupId = null;
                    
                    if (mostAccessedData.success && mostAccessedData.group_id) {
                        // Check if the most accessed group is still in user's groups
                        const groupExists = data.groups.some(g => g.id == mostAccessedData.group_id);
                        if (groupExists) {
                            defaultGroupId = mostAccessedData.group_id;
                        }
                    }
                    
                    // If no most accessed group or it doesn't exist anymore, use first group
                    if (!defaultGroupId && data.groups.length > 0) {
                        defaultGroupId = data.groups[0].id;
                    }
                    
                    if (defaultGroupId) {
                        defaultSelect.value = defaultGroupId;
                        // Trigger the change event to hide/show appropriate fields
                        // Note: handleDefaultGroupChange() will track the access, so we don't track here
                        handleDefaultGroupChange();
                    } else {
                        // No groups - group filter will be visible, add class
                        const filtersSection = document.querySelector('.filters-section');
                        if (filtersSection) {
                            filtersSection.classList.add('has-group-filter');
                        }
                        // Still load transactions (will show empty state)
                        loadTransactions();
                    }
                } catch (error) {
                    console.error('Error loading most accessed group:', error);
                    // Fallback to first group if error
                    if (data.groups.length > 0) {
                        defaultSelect.value = data.groups[0].id;
                        // Note: handleDefaultGroupChange() will track the access, so we don't track here
                        handleDefaultGroupChange();
                    }
                }
            } else {
                // No groups - group filter will be visible, add class
                const filtersSection = document.querySelector('.filters-section');
                if (filtersSection) {
                    filtersSection.classList.add('has-group-filter');
                }
                // Still load transactions (will show empty state)
                loadTransactions();
            }
        }
    } catch (error) {
        showToast('Error loading groups', 'error');
    }
}

// Handle default group selector change
function handleDefaultGroupChange() {
    const defaultGroupId = document.getElementById('defaultGroupSelector').value;
    const entryGroupContainer = document.getElementById('entryGroupContainer');
    const filterGroupContainer = document.getElementById('filterGroupContainer');
    const entryGroupSelect = document.getElementById('entryGroup');
    const filterGroupSelect = document.getElementById('filterGroup');
    const filtersSection = document.querySelector('.filters-section');
    
    if (defaultGroupId) {
        // Track group access when a specific group is selected
        trackGroupAccess(defaultGroupId);
        
        // A specific group is selected
        // Hide the group selectors
        entryGroupContainer.style.display = 'none';
        filterGroupContainer.style.display = 'none';
        
        // Remove class to disable horizontal scroll
        if (filtersSection) {
            filtersSection.classList.remove('has-group-filter');
        }
        
        // Set the entry group to the selected default group
        entryGroupSelect.value = defaultGroupId;
        
        // Set the filter group to the selected default group
        filterGroupSelect.value = defaultGroupId;
        
        // Remove required attribute from entry group when hidden
        entryGroupSelect.removeAttribute('required');
        
        // Trigger filter change to load filtered transactions
        handleGroupChange();
    } else {
        // "All Groups" is selected
        // Show the group selectors
        entryGroupContainer.style.display = 'flex';
        filterGroupContainer.style.display = 'flex';
        
        // Add class to enable horizontal scroll on medium screens
        if (filtersSection) {
            filtersSection.classList.add('has-group-filter');
        }
        
        // Reset selections
        entryGroupSelect.value = '';
        filterGroupSelect.value = '';
        
        // Add back required attribute
        entryGroupSelect.setAttribute('required', 'required');
        
        // Reload transactions without filter
        loadTransactions();
    }
}

// Track group access (call API to increment access count)
async function trackGroupAccess(groupId) {
    try {
        // Don't track if groupId is empty
        if (!groupId) {
            return;
        }
        
        // Call API to track group access
        await fetch(`${API_URL}?action=trackGroupAccess&group_id=${groupId}`, {
            method: 'GET'
        });
        // Silently fail if tracking fails - don't show error to user
    } catch (error) {
        console.error('Error tracking group access:', error);
        // Silently fail - don't interrupt user experience
    }
}

// Handle group filter change
async function handleGroupChange() {
    const groupId = document.getElementById('filterGroup').value;
    const memberFilterContainer = document.getElementById('memberFilterContainer');
    const memberSelect = document.getElementById('filterMember');
    
    if (groupId) {
        // Show member filter and load members
        memberFilterContainer.style.display = 'flex';
        
        await loadGroupMembers(groupId);
    } else {
        // Hide member filter
        memberFilterContainer.style.display = 'none';
        memberSelect.innerHTML = '<option value="">All Members</option>';
    }
    
    // Reload transactions
    loadTransactions();
}

// Load members of selected group
async function loadGroupMembers(groupId) {
    try {
        const response = await fetch(`${API_URL}?action=getGroupMembers&group_id=${groupId}`);
        const data = await response.json();
        
        if (data.success) {
            const memberSelect = document.getElementById('filterMember');
            
            // Clear existing options
            memberSelect.innerHTML = '<option value="">All Members</option>';
            
            // Add member options
            data.members.forEach(member => {
                const option = new Option(member.name, member.id);
                memberSelect.add(option);
            });
        } else {
            showToast(data.message || 'Error loading members', 'error');
        }
    } catch (error) {
        showToast('Error loading members', 'error');
    }
}

// Handle entry submission (unified for Cash In and Cash Out)
async function handleEntry(type) {
    const form = document.getElementById('entryForm');
    
    // Validate form
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const groupId = document.getElementById('entryGroup').value;
    
    if (!groupId) {
        showToast('Please select a group', 'error');
        return;
    }
    
    // Update datetime to current time right before submitting to ensure accurate timestamp
    const now = new Date();
    const localDateTime = new Date(now - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    document.getElementById('entryDate').value = localDateTime;
    
    // Use FormData to handle file uploads
    const formData = new FormData();
    formData.append('action', 'addEntry');
    formData.append('type', type);
    formData.append('group_id', groupId);
    formData.append('amount', document.getElementById('entryAmount').value);
    formData.append('datetime', localDateTime); // Use the updated datetime
    formData.append('message', document.getElementById('entryMessage').value);
    
    // Add attachment if selected
    const attachmentFile = document.getElementById('entryAttachment').files[0];
    if (attachmentFile) {
        formData.append('attachment', attachmentFile);
    }
    
    await submitEntry(formData, form);
}

// Submit entry to database
async function submitEntry(formData, form) {
    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            body: formData  // FormData automatically sets correct Content-Type with boundary
        });
        
        const data = await response.json();
        
        if (data.success) {
            const typeText = formData.get('type') === 'in' ? 'In' : 'Out';
            showToast(`Cash ${typeText} entry added successfully!`, 'success');
            
            // Get the entry ID from response for scrolling
            const entryId = data.id || null;
            
            // Get the default group value BEFORE resetting
            const defaultGroupId = document.getElementById('defaultGroupSelector').value;
            
            // Reset only input fields, not the entire form
            document.getElementById('entryAmount').value = '';
            document.getElementById('entryMessage').value = '';
            removeAttachment(); // Clear attachment
            initializeDateTimeInputs();
            
            // Restore group selection from default selector
            if (defaultGroupId) {
                // If a default group is selected, restore it to entryGroup
                document.getElementById('entryGroup').value = defaultGroupId;
            } else {
                // If "All Groups" is selected, clear the entryGroup
                document.getElementById('entryGroup').value = '';
            }
            
            // Ensure sort is set to newest first to show latest entry at top
            const sortSelect = document.getElementById('sortBy');
            if (sortSelect && sortSelect.value !== 'date_desc') {
                sortSelect.value = 'date_desc';
            }
            
            // Load transactions and scroll to latest entry
            await loadTransactions();
            
            // Scroll to latest entry after transactions are loaded (mobile & desktop)
            if (entryId) {
                scrollToLatestEntry(entryId);
            } else {
                // If entry ID not available, scroll to first transaction item
                setTimeout(() => {
                    const firstEntry = document.querySelector('.transaction-item[data-entry-id]');
                    if (firstEntry) {
                        const entryId = firstEntry.getAttribute('data-entry-id');
                        scrollToLatestEntry(entryId);
                    }
                }, 500);
            }
        } else {
            showToast(data.message || 'Error adding entry', 'error');
        }
    } catch (error) {
        showToast('Error submitting entry', 'error');
    }
}

// Load and display transactions
async function loadTransactions() {
    try {
        const searchQuery = document.getElementById('searchInput').value;
        const dateFrom = document.getElementById('filterDateFrom').value;
        const dateTo = document.getElementById('filterDateTo').value;
        const groupId = document.getElementById('filterGroup').value;
        const memberId = document.getElementById('filterMember').value;
        const type = document.getElementById('filterType').value;
        const sortBy = document.getElementById('sortBy').value;
        
        const params = new URLSearchParams({
            action: 'getEntries',
            search: searchQuery,
            date_from: dateFrom,
            date_to: dateTo,
            group_id: groupId,
            member_id: memberId,
            type: type,
            sort: sortBy
        });
        
        const response = await fetch(`${API_URL}?${params}`);
        const data = await response.json();
        
        if (data.success) {
            // Debug: Log entries to check deletion info
            if (data.entries && data.entries.length > 0) {
                const deletedEntries = data.entries.filter(e => e.status === 0 || e.status === '0');
                if (deletedEntries.length > 0) {
                    console.log('Deleted entries found:', deletedEntries);
                    console.log('First deleted entry data:', deletedEntries[0]);
                }
            }
            displayTransactions(data.entries);
            updateStatistics(data.statistics);
        } else {
            showToast(data.message || 'Error loading transactions', 'error');
        }
    } catch (error) {
        showToast('Error loading transactions', 'error');
    }
}

// Display transactions in the list
function displayTransactions(entries) {
    const container = document.getElementById('transactionsList');
    
    if (!container) {
        return;
    }
    
    if (!entries || entries.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>No transactions found. Try adjusting your filters or add a new entry!</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = entries.map(entry => {
        const typeClass = entry.type === 'in' ? 'cash-in' : 'cash-out';
        const icon = entry.type === 'in' ? 'fa-arrow-down' : 'fa-arrow-up';
        const typeText = entry.type === 'in' ? 'Cash In' : 'Cash Out';
        const sign = entry.type === 'in' ? '+' : '-';
        
        // Format date and time in Indian Standard Time (IST)
        // MySQL datetime values are stored in IST (server timezone is set to +05:30)
        // Parse MySQL datetime string and format it in IST
        let date;
        const dtStr = entry.datetime;
        
        if (dtStr.includes('T') && dtStr.includes('Z')) {
            // ISO with UTC - convert to IST
            date = new Date(dtStr);
        } else if (dtStr.includes('T') && (dtStr.includes('+') || dtStr.match(/-\d{2}:\d{2}$/))) {
            // ISO with timezone offset - parse directly
            date = new Date(dtStr);
        } else if (dtStr.match(/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/)) {
            // MySQL datetime format 'YYYY-MM-DD HH:MM:SS' - treat as IST
            // Create date object treating the string as IST time
            // Convert to ISO format: '2025-11-21 14:04:00' -> '2025-11-21T14:04:00+05:30'
            date = new Date(dtStr.replace(' ', 'T') + '+05:30');
        } else if (dtStr.includes('T')) {
            // ISO without timezone - treat as IST
            date = new Date(dtStr + '+05:30');
        } else {
            // Fallback - try to parse as-is
            date = new Date(dtStr);
        }
        
        // Format in Indian timezone (IST) - ensures correct display
        const formattedDate = date.toLocaleDateString('en-IN', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            timeZone: 'Asia/Kolkata'
        });
        const formattedTime = date.toLocaleTimeString('en-IN', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true,
            timeZone: 'Asia/Kolkata'
        });
        
        // User profile picture or icon - show only one at a time
        const userAvatar = entry.profile_picture 
            ? `<img src="${escapeHtml(entry.profile_picture)}" alt="Profile" class="transaction-user-avatar" onerror="this.style.display='none'; this.nextElementSibling && (this.nextElementSibling.style.display='inline-flex');">
               <i class="fas fa-user" style="display:none;"></i>`
            : `<i class="fas fa-user" style="display:inline-flex;"></i>`;
        
        // Determine the class based on profile picture existence
        const userClass = entry.profile_picture ? 'has-profile-pic' : 'no-profile-pic';
        
        // Attachment photo icon
        const attachmentIcon = entry.attachment 
            ? `<button class="attachment-icon" onclick="openPhotoModal('${escapeHtml(entry.attachment)}', '${escapeHtml(entry.group_name || 'Transaction')} - ${formattedDate}')" title="View payment proof">
                   <i class="fas fa-image"></i>
               </button>`
            : '';
        
        // Check if entry is deleted (status = 0)
        const isDeleted = entry.status === 0 || entry.status === '0' || entry.status === false || entry.status == 0;
        const deletedClass = isDeleted ? 'deleted-entry' : '';
        
        // Format deletion info if entry is deleted
        let deletionInfo = '';
        if (isDeleted) {
            // Debug: Log entry data to see what we're getting
            if (!entry.deleted_by_name || !entry.deleted_at) {
                console.log('Entry missing deletion info:', {
                    id: entry.id,
                    deleted_by: entry.deleted_by,
                    deleted_by_name: entry.deleted_by_name,
                    deleted_at: entry.deleted_at,
                    status: entry.status
                });
            }
            
            // Get deletion info - use deleted_at if available
            let deletedDateStr = 'Unknown date';
            let deletedTimeStr = 'Unknown time';
            
            if (entry.deleted_at && entry.deleted_at !== null && entry.deleted_at !== '' && entry.deleted_at !== 'null') {
                try {
                    // Parse MySQL datetime - explicitly treat as IST (server timezone)
                    let deletedDate;
                    const delStr = entry.deleted_at;
                    if (delStr.includes('T') && delStr.includes('Z')) {
                        deletedDate = new Date(delStr);
                    } else if (delStr.includes('T') && (delStr.includes('+') || delStr.includes('-', 10))) {
                        deletedDate = new Date(delStr);
                    } else if (delStr.includes('T')) {
                        deletedDate = new Date(delStr + '+05:30');
                    } else if (delStr.match(/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/)) {
                        // MySQL datetime format - explicitly treat as IST
                        deletedDate = new Date(delStr.replace(' ', 'T') + '+05:30');
                    } else {
                        deletedDate = new Date(delStr);
                    }
                    
                    if (!isNaN(deletedDate.getTime())) {
                        // Format in Indian timezone (IST)
                        deletedDateStr = deletedDate.toLocaleDateString('en-IN', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric',
                            timeZone: 'Asia/Kolkata'
                        });
                        deletedTimeStr = deletedDate.toLocaleTimeString('en-IN', {
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: true,
                            timeZone: 'Asia/Kolkata'
                        });
                    }
                } catch (e) {
                    console.error('Error parsing deleted_at:', e, entry.deleted_at);
                }
            }
            
            // Get deleted by name - try multiple fields
            let deletedByName = 'Unknown User';
            if (entry.deleted_by_name && entry.deleted_by_name !== null && entry.deleted_by_name !== '' && entry.deleted_by_name !== 'null') {
                deletedByName = entry.deleted_by_name;
            } else if (entry.deleted_by && entry.deleted_by !== null && entry.deleted_by !== 'null') {
                // If we have deleted_by ID but no name, show the ID
                deletedByName = 'User #' + entry.deleted_by;
            }
            
            // Always show deletion info overlay for deleted entries
            deletionInfo = `
                <div class="deletion-info-overlay">
                    <div class="deletion-info-content">
                        <i class="fas fa-trash-alt"></i>
                        <div class="deletion-info-text">
                            <strong>Deleted by ${escapeHtml(deletedByName)}</strong>
                            <span>on ${deletedDateStr} at ${deletedTimeStr}</span>
                        </div>
                    </div>
                </div>
            `;
        }
        
        return `
            <div class="transaction-item ${typeClass} ${deletedClass}" data-entry-id="${entry.id}">
                <!-- Blur overlay for deleted entries -->
                ${isDeleted ? '<div class="deleted-overlay"></div>' : ''}
                
                <!-- Row 1: Icon, Empty Space, Amount -->
                <div class="transaction-top-row">
                    <div class="transaction-icon">
                        <i class="fas ${icon}"></i>
                    </div>
                    <div class="transaction-amount">
                        ${sign} ₹ ${parseFloat(entry.amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                    </div>
                </div>
                
                <!-- Row 2: Group/User Info and Edit Button -->
                <div class="transaction-middle-row">
                    <div class="transaction-group-section">
                        <div class="transaction-group">${escapeHtml(entry.group_name || 'No Group')}</div>
                        <div class="transaction-user ${userClass}">
                            ${userAvatar} ${escapeHtml(entry.user_name || 'Unknown User')}
                            ${attachmentIcon}
                        </div>
                    </div>
                    <div class="transaction-actions">
                        ${isDeleted ? '' : `
                            <div class="transaction-actions-buttons">
                                <button class="btn-edit-entry" onclick="openEditModal(${entry.id})" title="Edit entry">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-delete-entry" onclick="deleteEntry(${entry.id})" title="Delete entry">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <div class="transaction-view-details-text" onclick="viewEntryDetails(${entry.id})" title="View edit history">
                                View Details
                            </div>
                        `}
                    </div>
                </div>
                
                <!-- Row 3: Date and Time -->
                <div class="transaction-date">
                    <span><i class="fas fa-calendar"></i> ${formattedDate}</span>
                    <span><i class="fas fa-clock"></i> ${formattedTime}</span>
                </div>
                
                <!-- Row 4: Message -->
                ${entry.message ? `<div class="transaction-message">${escapeHtml(entry.message)}</div>` : ''}
                
                <!-- Row 5: Transaction Type Badge -->
                <div>
                    <span class="transaction-type">${typeText}</span>
                </div>
                
                <!-- Deletion Info Overlay (shown on top of blur) -->
                ${deletionInfo}
            </div>
        `;
    }).join('');
}

// Update statistics
function updateStatistics(stats) {
    const totalBalance = stats.total_in - stats.total_out;
    
    // Update stat cards
    document.getElementById('totalCashIn').textContent = `₹ ${parseFloat(stats.total_in).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
    document.getElementById('totalCashOut').textContent = `₹ ${parseFloat(stats.total_out).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
    
    // Update balance in stats grid
    const statBalanceElements = document.querySelectorAll('.stat-balance .stat-value');
    if (statBalanceElements.length > 0) {
        statBalanceElements.forEach(element => {
            element.textContent = `₹ ${totalBalance.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            element.className = `stat-value ${totalBalance < 0 ? 'negative' : ''}`;
        });
    }
}

// Handle search
function handleSearch() {
    const searchValue = document.getElementById('searchInput').value;
    if (searchValue.length === 0 || searchValue.length >= 3) {
        loadTransactions();
    }
}

// Clear all filters (but preserve group filter)
function clearFilters() {
    // Save the current group filter value to preserve it
    const defaultGroupSelector = document.getElementById('defaultGroupSelector');
    const filterGroup = document.getElementById('filterGroup');
    const preservedGroupValue = filterGroup.value || (defaultGroupSelector ? defaultGroupSelector.value : '');
    
    // Clear all other filters
    document.getElementById('searchInput').value = '';
    document.getElementById('filterDateFrom').value = '';
    document.getElementById('filterDateTo').value = '';
    document.getElementById('filterMember').value = '';
    document.getElementById('filterType').value = '';
    document.getElementById('sortBy').value = 'date_desc';
    
    // Restore the group filter value (preserve it)
    if (preservedGroupValue) {
        filterGroup.value = preservedGroupValue;
        // If a group is selected, trigger handleGroupChange to properly show/hide member filter
        handleGroupChange();
    } else {
        // No group selected, hide member filter
        document.getElementById('memberFilterContainer').style.display = 'none';
        loadTransactions();
    }
}

// Update pending requests badge
async function updatePendingRequestsBadge() {
    try {
        const response = await fetch(`${GROUP_API_URL}?action=getPendingInvitationsCount`);
        const data = await response.json();
        
        const badge = document.getElementById('pendingRequestsBadge');
        if (!badge) return;
        
        if (data.success && data.count > 0) {
            badge.textContent = data.count > 99 ? '99+' : data.count;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    } catch (error) {
        console.error('Error updating pending requests badge:', error);
        // Hide badge on error
        const badge = document.getElementById('pendingRequestsBadge');
        if (badge) {
            badge.style.display = 'none';
        }
    }
}

// Make function globally accessible for manual updates
window.updatePendingRequestsBadge = updatePendingRequestsBadge;

// Show toast notification
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    toast.innerHTML = `<i class="fas ${icon}"></i> ${message}`;
    toast.className = `toast ${type} show`;
    
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

// Debounce function for search
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// ============================================
// Edit Entry Functions
// ============================================

let currentEntryData = null;
let shouldRemoveCurrentAttachment = false;

// Open edit modal and load entry data
async function openEditModal(entryId) {
    try {
        const response = await fetch(`${API_URL}?action=getEntry&id=${entryId}`);
        const data = await response.json();
        
        if (data.success) {
            currentEntryData = data.entry;
            populateEditForm(data.entry);
            
            // Show modal
            document.getElementById('editEntryModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        } else {
            showToast(data.message || 'Failed to load entry', 'error');
        }
    } catch (error) {
        console.error('Error loading entry:', error);
        showToast('Error loading entry. Please try again.', 'error');
    }
}

// Populate edit form with entry data
function populateEditForm(entry) {
    // Reset form
    shouldRemoveCurrentAttachment = false;
    document.getElementById('editEntryForm').reset();
    document.getElementById('editAttachmentPreview').style.display = 'none';
    document.getElementById('editAttachmentFileName').textContent = 'No file chosen';
    document.getElementById('removeEditAttachment').style.display = 'none';
    
    // Set values (datetime is not editable)
    document.getElementById('editEntryId').value = entry.id;
    document.getElementById('editEntryAmount').value = entry.amount;
    document.getElementById('editEntryType').value = entry.type;
    document.getElementById('editEntryMessage').value = entry.message || '';
    
    // Load groups and set current group
    loadGroupsForEdit(entry.group_id);
    
    // Show current attachment if exists
    if (entry.attachment) {
        document.getElementById('currentAttachmentImg').src = entry.attachment;
        document.getElementById('currentAttachmentPreview').style.display = 'block';
    } else {
        document.getElementById('currentAttachmentPreview').style.display = 'none';
    }
    
    // Setup event listeners for edit form
    setupEditFormListeners();
}

// Load groups into edit dropdown
async function loadGroupsForEdit(selectedGroupId) {
    try {
        const response = await fetch(`${API_URL}?action=getUserGroups`);
        const data = await response.json();
        
        if (data.success) {
            const select = document.getElementById('editEntryGroup');
            select.innerHTML = '<option value="">Select Group</option>';
            
            data.groups.forEach(group => {
                const option = document.createElement('option');
                option.value = group.id;
                option.textContent = group.name;
                if (group.id == selectedGroupId) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading groups:', error);
    }
}

// Setup event listeners for edit form
function setupEditFormListeners() {
    // Form submit
    const editForm = document.getElementById('editEntryForm');
    editForm.removeEventListener('submit', handleEditSubmit); // Remove old listener
    editForm.addEventListener('submit', handleEditSubmit);
    
    // File input change
    const fileInput = document.getElementById('editEntryAttachment');
    fileInput.removeEventListener('change', handleEditAttachmentChange);
    fileInput.addEventListener('change', handleEditAttachmentChange);
    
    // Explicit click handler for mobile compatibility
    const editAttachmentLabel = document.querySelector('label[for="editEntryAttachment"].file-upload-label');
    if (editAttachmentLabel) {
        editAttachmentLabel.removeEventListener('click', handleEditLabelClick);
        editAttachmentLabel.addEventListener('click', handleEditLabelClick);
    }
    
    // Remove new attachment button
    const removeNewBtn = document.getElementById('removeEditAttachment');
    removeNewBtn.removeEventListener('click', removeEditAttachment);
    removeNewBtn.addEventListener('click', removeEditAttachment);
    
    // Remove current attachment button
    const removeCurrentBtn = document.getElementById('removeCurrentAttachment');
    removeCurrentBtn.removeEventListener('click', removeCurrentAttachment);
    removeCurrentBtn.addEventListener('click', removeCurrentAttachment);
}

// Handle edit label click for mobile
function handleEditLabelClick(e) {
    e.preventDefault();
    const fileInput = document.getElementById('editEntryAttachment');
    if (fileInput) {
        fileInput.click();
    }
}

// Handle edit attachment file selection
function handleEditAttachmentChange(e) {
    const file = e.target.files[0];
    const fileNameSpan = document.getElementById('editAttachmentFileName');
    const removeBtn = document.getElementById('removeEditAttachment');
    const preview = document.getElementById('editAttachmentPreview');
    const previewImg = document.getElementById('editAttachmentPreviewImg');
    
    if (file) {
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            showToast('Invalid file type. Only images are allowed.', 'error');
            e.target.value = '';
            return;
        }
        
        // Validate file size (10MB max)
        const maxSize = 10 * 1024 * 1024;
        if (file.size > maxSize) {
            showToast('File size too large. Maximum 10MB allowed.', 'error');
            e.target.value = '';
            return;
        }
        
        fileNameSpan.textContent = file.name;
        removeBtn.style.display = 'inline-block';
        
        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}

// Remove new attachment from edit form
function removeEditAttachment() {
    document.getElementById('editEntryAttachment').value = '';
    document.getElementById('editAttachmentFileName').textContent = 'No file chosen';
    document.getElementById('removeEditAttachment').style.display = 'none';
    document.getElementById('editAttachmentPreview').style.display = 'none';
}

// Remove current attachment
function removeCurrentAttachment() {
    shouldRemoveCurrentAttachment = true;
    document.getElementById('currentAttachmentPreview').style.display = 'none';
    showToast('Current attachment will be removed when you save', 'info');
}

// Delete entry - show confirmation modal
function deleteEntry(entryId) {
    if (!entryId) {
        showToast('Invalid entry ID', 'error');
        return;
    }
    
    // Store entry ID for deletion
    window.pendingDeleteEntryId = entryId;
    
    // Show delete confirmation modal
    showDeleteEntryModal();
}

// Show delete entry confirmation modal
function showDeleteEntryModal() {
    const modal = document.getElementById('deleteEntryModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

// Hide delete entry confirmation modal
function hideDeleteEntryModal() {
    const modal = document.getElementById('deleteEntryModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    // Clear pending delete ID
    window.pendingDeleteEntryId = null;
}

// Confirm delete entry (called from modal button)
async function confirmDeleteEntry() {
    const entryId = window.pendingDeleteEntryId;
    
    if (!entryId) {
        showToast('Invalid entry ID', 'error');
        hideDeleteEntryModal();
        return;
    }
    
    // Disable buttons during deletion
    const confirmBtn = document.getElementById('deleteEntryConfirmBtn');
    const cancelBtn = document.getElementById('deleteEntryCancelBtn');
    const originalConfirmText = confirmBtn.innerHTML;
    const originalCancelText = cancelBtn.innerHTML;
    
    confirmBtn.disabled = true;
    cancelBtn.disabled = true;
    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
    
    try {
        const formData = new URLSearchParams({
            action: 'deleteEntry',
            id: entryId
        });
        
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Entry deleted successfully', 'success');
            hideDeleteEntryModal();
            // Reload transactions and statistics to reflect changes
            loadTransactions();
        } else {
            showToast(data.message || 'Failed to delete entry', 'error');
            // Re-enable buttons on error
            confirmBtn.disabled = false;
            cancelBtn.disabled = false;
            confirmBtn.innerHTML = originalConfirmText;
            cancelBtn.innerHTML = originalCancelText;
        }
    } catch (error) {
        console.error('Delete error:', error);
        showToast('An error occurred while deleting the entry', 'error');
        // Re-enable buttons on error
        confirmBtn.disabled = false;
        cancelBtn.disabled = false;
        confirmBtn.innerHTML = originalConfirmText;
        cancelBtn.innerHTML = originalCancelText;
    }
}

// Handle edit form submission
async function handleEditSubmit(e) {
    e.preventDefault();
    
    const entryId = document.getElementById('editEntryId').value;
    const amount = document.getElementById('editEntryAmount').value;
    const type = document.getElementById('editEntryType').value;
    const groupId = document.getElementById('editEntryGroup').value;
    const message = document.getElementById('editEntryMessage').value;
    const attachmentInput = document.getElementById('editEntryAttachment');
    
    // Validate (datetime is not editable)
    if (!entryId || !amount || !type || !groupId) {
        showToast('Please fill in all required fields', 'error');
        return;
    }
    
    if (parseFloat(amount) <= 0) {
        showToast('Amount must be greater than 0', 'error');
        return;
    }
    
    // Prepare form data
    const formData = new FormData();
    formData.append('action', 'updateEntry');
    formData.append('id', entryId);
    formData.append('amount', amount);
    formData.append('type', type);
    formData.append('group_id', groupId);
    formData.append('message', message);
    formData.append('remove_attachment', shouldRemoveCurrentAttachment ? 'true' : 'false');
    
    // Add new attachment if selected
    if (attachmentInput.files.length > 0) {
        formData.append('attachment', attachmentInput.files[0]);
    }
    
    // Disable submit button
    const submitBtn = document.getElementById('saveEditBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    
    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast(data.message || 'Entry updated successfully!', 'success');
            closeEditModal();
            loadTransactions(); // Reload transactions
        } else {
            showToast(data.message || 'Failed to update entry', 'error');
        }
    } catch (error) {
        console.error('Error updating entry:', error);
        showToast('Error updating entry. Please try again.', 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

// Close edit modal
function closeEditModal() {
    document.getElementById('editEntryModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    document.getElementById('editEntryForm').reset();
    currentEntryData = null;
    shouldRemoveCurrentAttachment = false;
    document.getElementById('currentAttachmentPreview').style.display = 'none';
    document.getElementById('editAttachmentPreview').style.display = 'none';
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    const modal = document.getElementById('editEntryModal');
    if (e.target === modal) {
        closeEditModal();
    }
    
    const detailsModal = document.getElementById('entryDetailsModal');
    if (e.target === detailsModal) {
        closeEntryDetailsModal();
    }
});

// ============================================
// Entry Details (Edit History) Functions
// ============================================

// View entry details (edit history)
async function viewEntryDetails(entryId) {
    if (!entryId) {
        showToast('Invalid entry ID', 'error');
        return;
    }
    
    // Show modal
    const modal = document.getElementById('entryDetailsModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    
    // Show loading state
    const content = document.getElementById('entryDetailsContent');
    content.innerHTML = `
        <div class="loading-state" style="text-align: center; padding: 40px;">
            <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: var(--primary-color);"></i>
            <p style="margin-top: 15px; color: var(--text-secondary);">Loading edit history...</p>
        </div>
    `;
    
    try {
        const response = await fetch(`${API_URL}?action=getEntryEditHistory&entry_id=${entryId}`);
        const data = await response.json();
        
        if (data.success) {
            displayEditHistory(data.history);
        } else {
            content.innerHTML = `
                <div class="error-state" style="text-align: center; padding: 40px;">
                    <i class="fas fa-exclamation-circle" style="font-size: 2rem; color: #ef4444;"></i>
                    <p style="margin-top: 15px; color: var(--text-secondary);">${data.message || 'Failed to load edit history'}</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading edit history:', error);
        content.innerHTML = `
            <div class="error-state" style="text-align: center; padding: 40px;">
                <i class="fas fa-exclamation-circle" style="font-size: 2rem; color: #ef4444;"></i>
                <p style="margin-top: 15px; color: var(--text-secondary);">Error loading edit history. Please try again.</p>
            </div>
        `;
    }
}

// Display edit history in modal
function displayEditHistory(history) {
    const content = document.getElementById('entryDetailsContent');
    
    if (!history || history.length === 0) {
        content.innerHTML = `
            <div class="empty-state" style="text-align: center; padding: 40px;">
                <i class="fas fa-history" style="font-size: 3rem; color: var(--text-secondary); opacity: 0.5;"></i>
                <p style="margin-top: 20px; color: var(--text-secondary); font-size: 1.1rem;">No edit history found</p>
                <p style="margin-top: 10px; color: var(--text-secondary); font-size: 0.9rem;">This entry has not been edited yet.</p>
            </div>
        `;
        return;
    }
    
    // Group history by edit session (same edited_at timestamp)
    const groupedHistory = {};
    history.forEach(item => {
        const key = item.edited_at;
        if (!groupedHistory[key]) {
            groupedHistory[key] = {
                edited_at: item.edited_at,
                edited_by_name: item.edited_by_name,
                edited_by_picture: item.edited_by_picture,
                changes: []
            };
        }
        groupedHistory[key].changes.push(item);
    });
    
    // Convert to array and sort by date (newest first)
    const historyGroups = Object.values(groupedHistory).sort((a, b) => 
        new Date(b.edited_at) - new Date(a.edited_at)
    );
    
    let html = '<div class="edit-history-list">';
    
    historyGroups.forEach((group, groupIndex) => {
        // Parse date and format in Indian Standard Time (IST)
        // Parse MySQL datetime - explicitly treat as IST (server timezone)
        let editDate;
        const editStr = group.edited_at;
        if (editStr.includes('T') && editStr.includes('Z')) {
            editDate = new Date(editStr);
        } else if (editStr.includes('T') && (editStr.includes('+') || editStr.includes('-', 10))) {
            editDate = new Date(editStr);
        } else if (editStr.includes('T')) {
            editDate = new Date(editStr + '+05:30');
        } else if (editStr.match(/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/)) {
            // MySQL datetime format - explicitly treat as IST
            editDate = new Date(editStr.replace(' ', 'T') + '+05:30');
        } else {
            editDate = new Date(editStr);
        }
        
        // Format in Indian timezone (IST)
        const formattedDate = editDate.toLocaleDateString('en-IN', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            timeZone: 'Asia/Kolkata'
        });
        const formattedTime = editDate.toLocaleTimeString('en-IN', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true,
            timeZone: 'Asia/Kolkata'
        });
        
        // User avatar or icon - show only one at a time
        const userAvatar = group.edited_by_picture 
            ? `<img src="${escapeHtml(group.edited_by_picture)}" alt="Profile" class="history-user-avatar" onerror="this.style.display='none'; this.nextElementSibling && (this.nextElementSibling.style.display='inline-flex');">
               <i class="fas fa-user" style="display:none;"></i>`
            : `<i class="fas fa-user" style="display:inline-flex;"></i>`;
        
        html += `
            <div class="edit-history-group">
                <div class="edit-history-header">
                    <div class="edit-history-user">
                        ${userAvatar}
                        <div class="edit-history-user-info">
                            <strong>${escapeHtml(group.edited_by_name || 'Unknown User')}</strong>
                            <span class="edit-history-date">${formattedDate} at ${formattedTime}</span>
                        </div>
                    </div>
                </div>
                <div class="edit-history-changes">
        `;
        
        group.changes.forEach(change => {
            const fieldLabels = {
                'group_id': 'Group',
                'type': 'Type',
                'amount': 'Amount',
                'datetime': 'Date & Time',
                'message': 'Message',
                'attachment': 'Attachment'
            };
            
            const fieldName = fieldLabels[change.field_name] || change.field_name;
            const oldValue = change.old_value === '(empty)' ? '<em>Empty</em>' : escapeHtml(change.old_value);
            const newValue = change.new_value === '(empty)' ? '<em>Empty</em>' : escapeHtml(change.new_value);
            
            html += `
                <div class="edit-history-change">
                    <div class="change-field">
                        <i class="fas fa-edit"></i>
                        <strong>${fieldName}</strong>
                    </div>
                    <div class="change-values">
                        <div class="change-old">
                            <span class="change-label">From:</span>
                            <span class="change-value">${oldValue}</span>
                        </div>
                        <div class="change-arrow">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                        <div class="change-new">
                            <span class="change-label">To:</span>
                            <span class="change-value">${newValue}</span>
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += `
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    content.innerHTML = html;
}

// Close entry details modal
function closeEntryDetailsModal() {
    const modal = document.getElementById('entryDetailsModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}
