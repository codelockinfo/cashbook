// API Configuration
const API_URL = 'api.php';
const AUTH_API_URL = 'auth-api.php';

// Initialize the application
document.addEventListener('DOMContentLoaded', function() {
    initializeDateTimeInputs();
    loadGroups();
    loadTransactions();
    setupEventListeners();
});

// Initialize datetime inputs with current date/time
function initializeDateTimeInputs() {
    const now = new Date();
    const localDateTime = new Date(now - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    document.getElementById('entryDate').value = localDateTime;
}

// Setup all event listeners
function setupEventListeners() {
    // Button clicks
    document.getElementById('btnCashIn').addEventListener('click', () => handleEntry('in'));
    document.getElementById('btnCashOut').addEventListener('click', () => handleEntry('out'));
    
    // Logout button
    document.getElementById('logoutBtn').addEventListener('click', handleLogout);
    
    // File upload handlers
    const attachmentInput = document.getElementById('entryAttachment');
    const removeAttachmentBtn = document.getElementById('removeAttachment');
    
    if (attachmentInput) {
        attachmentInput.addEventListener('change', handleAttachmentChange);
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
async function handleLogout() {
    if (!confirm('Are you sure you want to logout?')) {
        return;
    }
    
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
        console.error('Logout error:', error);
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
            
            // Auto-select first group by default
            if (data.groups.length > 0) {
                defaultSelect.value = data.groups[0].id;
                // Trigger the change event to hide/show appropriate fields
                handleDefaultGroupChange();
            }
        }
    } catch (error) {
        console.error('Error loading groups:', error);
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
    
    if (defaultGroupId) {
        // A specific group is selected
        // Hide the group selectors
        entryGroupContainer.style.display = 'none';
        filterGroupContainer.style.display = 'none';
        
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
        entryGroupContainer.style.display = 'block';
        filterGroupContainer.style.display = 'block';
        
        // Reset selections
        entryGroupSelect.value = '';
        filterGroupSelect.value = '';
        
        // Add back required attribute
        entryGroupSelect.setAttribute('required', 'required');
        
        // Reload transactions without filter
        loadTransactions();
    }
}

// Handle group filter change
async function handleGroupChange() {
    const groupId = document.getElementById('filterGroup').value;
    const memberFilterContainer = document.getElementById('memberFilterContainer');
    const memberSelect = document.getElementById('filterMember');
    
    if (groupId) {
        // Show member filter and load members
        memberFilterContainer.style.display = 'block';
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
        console.error('Error loading group members:', error);
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
    
    // Use FormData to handle file uploads
    const formData = new FormData();
    formData.append('action', 'addEntry');
    formData.append('type', type);
    formData.append('group_id', groupId);
    formData.append('amount', document.getElementById('entryAmount').value);
    formData.append('datetime', document.getElementById('entryDate').value);
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
            
            // Reset only input fields, not the entire form
            document.getElementById('entryAmount').value = '';
            document.getElementById('entryGroup').value = '';
            document.getElementById('entryMessage').value = '';
            removeAttachment(); // Clear attachment
            initializeDateTimeInputs();
            
            loadTransactions();
        } else {
            showToast(data.message || 'Error adding entry', 'error');
        }
    } catch (error) {
        console.error('Error submitting entry:', error);
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
            displayTransactions(data.entries);
            updateStatistics(data.statistics);
        } else {
            showToast(data.message || 'Error loading transactions', 'error');
        }
    } catch (error) {
        console.error('Error loading transactions:', error);
        showToast('Error loading transactions', 'error');
    }
}

// Display transactions in the list
function displayTransactions(entries) {
    const container = document.getElementById('transactionsList');
    
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
        
        // Format date and time
        const date = new Date(entry.datetime);
        const formattedDate = date.toLocaleDateString('en-IN', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
        const formattedTime = date.toLocaleTimeString('en-IN', {
            hour: '2-digit',
            minute: '2-digit'
        });
        
        // User profile picture or icon
        const userAvatar = entry.profile_picture 
            ? `<img src="${escapeHtml(entry.profile_picture)}" alt="Profile" class="transaction-user-avatar" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
               <i class="fas fa-user" style="display:none;"></i>`
            : `<i class="fas fa-user"></i>`;
        
        // Attachment photo icon
        const attachmentIcon = entry.attachment 
            ? `<button class="attachment-icon" onclick="openPhotoModal('${escapeHtml(entry.attachment)}', '${escapeHtml(entry.group_name || 'Transaction')} - ${formattedDate}')" title="View payment proof">
                   <i class="fas fa-image"></i>
               </button>`
            : '';
        
        return `
            <div class="transaction-item ${typeClass}">
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
                        <div class="transaction-user">
                            ${userAvatar} ${escapeHtml(entry.user_name || 'Unknown User')}
                            ${attachmentIcon}
                        </div>
                    </div>
                    <div class="transaction-actions">
                        <button class="btn-edit-entry" onclick="openEditModal(${entry.id})" title="Edit entry">
                            <i class="fas fa-edit"></i>
                        </button>
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

// Clear all filters
function clearFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('filterDateFrom').value = '';
    document.getElementById('filterDateTo').value = '';
    document.getElementById('filterGroup').value = '';
    document.getElementById('filterMember').value = '';
    document.getElementById('filterType').value = '';
    document.getElementById('sortBy').value = 'date_desc';
    
    // Hide member filter
    document.getElementById('memberFilterContainer').style.display = 'none';
    
    loadTransactions();
}

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
    
    // Set values
    document.getElementById('editEntryId').value = entry.id;
    document.getElementById('editEntryAmount').value = entry.amount;
    document.getElementById('editEntryType').value = entry.type;
    document.getElementById('editEntryMessage').value = entry.message || '';
    
    // Format datetime for datetime-local input
    const date = new Date(entry.datetime);
    const localDateTime = new Date(date - date.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    document.getElementById('editEntryDate').value = localDateTime;
    
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
    
    // Remove new attachment button
    const removeNewBtn = document.getElementById('removeEditAttachment');
    removeNewBtn.removeEventListener('click', removeEditAttachment);
    removeNewBtn.addEventListener('click', removeEditAttachment);
    
    // Remove current attachment button
    const removeCurrentBtn = document.getElementById('removeCurrentAttachment');
    removeCurrentBtn.removeEventListener('click', removeCurrentAttachment);
    removeCurrentBtn.addEventListener('click', removeCurrentAttachment);
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

// Handle edit form submission
async function handleEditSubmit(e) {
    e.preventDefault();
    
    const entryId = document.getElementById('editEntryId').value;
    const amount = document.getElementById('editEntryAmount').value;
    const type = document.getElementById('editEntryType').value;
    const groupId = document.getElementById('editEntryGroup').value;
    const datetime = document.getElementById('editEntryDate').value;
    const message = document.getElementById('editEntryMessage').value;
    const attachmentInput = document.getElementById('editEntryAttachment');
    
    // Validate
    if (!entryId || !amount || !type || !groupId || !datetime) {
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
    formData.append('datetime', datetime);
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
});

