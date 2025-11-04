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
    
    // Search and filters
    document.getElementById('searchInput').addEventListener('input', debounce(handleSearch, 500));
    document.getElementById('filterDateFrom').addEventListener('change', loadTransactions);
    document.getElementById('filterDateTo').addEventListener('change', loadTransactions);
    document.getElementById('filterGroup').addEventListener('change', loadTransactions);
    document.getElementById('filterType').addEventListener('change', loadTransactions);
    document.getElementById('sortBy').addEventListener('change', loadTransactions);
    document.getElementById('clearFilters').addEventListener('click', clearFilters);
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
                window.location.href = 'login.html';
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
            
            // Clear existing options (except first)
            entrySelect.innerHTML = '<option value="">Select Group</option>';
            filterSelect.innerHTML = '<option value="">All Groups</option>';
            
            // Add group options
            data.groups.forEach(group => {
                const option1 = new Option(group.name, group.id);
                const option2 = new Option(group.name, group.id);
                
                entrySelect.add(option1);
                filterSelect.add(option2);
            });
        }
    } catch (error) {
        console.error('Error loading groups:', error);
        showToast('Error loading groups', 'error');
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
    
    const formData = {
        action: 'addEntry',
        type: type,
        group_id: groupId,
        amount: document.getElementById('entryAmount').value,
        datetime: document.getElementById('entryDate').value,
        message: document.getElementById('entryMessage').value
    };
    
    await submitEntry(formData, form);
}

// Submit entry to database
async function submitEntry(formData, form) {
    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(formData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            const typeText = formData.type === 'in' ? 'In' : 'Out';
            showToast(`Cash ${typeText} entry added successfully!`, 'success');
            
            // Reset only input fields, not the entire form
            document.getElementById('entryAmount').value = '';
            document.getElementById('entryGroup').value = '';
            document.getElementById('entryMessage').value = '';
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
        const type = document.getElementById('filterType').value;
        const sortBy = document.getElementById('sortBy').value;
        
        const params = new URLSearchParams({
            action: 'getEntries',
            search: searchQuery,
            date_from: dateFrom,
            date_to: dateTo,
            group_id: groupId,
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
        
        return `
            <div class="transaction-item ${typeClass}">
                <div class="transaction-icon">
                    <i class="fas ${icon}"></i>
                </div>
                <div class="transaction-details">
                    <div class="transaction-user">${escapeHtml(entry.group_name || 'No Group')}</div>
                    <div class="transaction-date">
                        <i class="fas fa-calendar"></i> ${formattedDate}
                        <i class="fas fa-clock"></i> ${formattedTime}
                    </div>
                    ${entry.message ? `<div class="transaction-message">${escapeHtml(entry.message)}</div>` : ''}
                    <span class="transaction-type">${typeText}</span>
                </div>
                <div class="transaction-amount">
                    ${sign} ₹ ${parseFloat(entry.amount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                </div>
            </div>
        `;
    }).join('');
}

// Update statistics
function updateStatistics(stats) {
    const totalBalance = stats.total_in - stats.total_out;
    
    document.getElementById('totalBalance').textContent = `₹ ${totalBalance.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
    document.getElementById('totalBalance').className = `balance-amount ${totalBalance < 0 ? 'negative' : ''}`;
    
    document.getElementById('totalCashIn').textContent = `₹ ${parseFloat(stats.total_in).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
    document.getElementById('totalCashOut').textContent = `₹ ${parseFloat(stats.total_out).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
    document.getElementById('totalEntries').textContent = stats.total_entries;
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
    document.getElementById('filterType').value = '';
    document.getElementById('sortBy').value = 'date_desc';
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

