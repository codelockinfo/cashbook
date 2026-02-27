// API Configuration
const API_URL =
  (typeof BASE_PATH !== "undefined" && BASE_PATH ? BASE_PATH : "") +
  "/group-api.php";

let currentGroupId = null;
let currentGroupName = '';
let currentGroupCreator = '';
let selectedUsers = [];

// Initialize
document.addEventListener("DOMContentLoaded", function () {
  loadMyGroups();
  loadPendingInvitations();
  setupEventListeners();

  // Check if we need to auto-open the create group modal
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get("action") === "create") {
    openModal("createGroupModal");

    // Clean up URL to prevent reopen on refresh
    window.history.replaceState({}, document.title, window.location.pathname);
  }
});

// Setup Event Listeners
function setupEventListeners() {
  document
    .getElementById("createGroupBtnHeader")
    .addEventListener("click", () => openModal("createGroupModal"));
  document
    .getElementById("createGroupForm")
    .addEventListener("submit", handleCreateGroup);
  document
    .getElementById("sendInvitesBtn")
    .addEventListener("click", handleSendInvites);
  document
    .getElementById("searchUsers")
    .addEventListener("input", handleSearchUsers);
}

// Load My Groups
async function loadMyGroups() {
  try {
    const response = await fetch(`${API_URL}?action=getMyGroups`);
    const data = await response.json();

    if (data.success) {
      displayMyGroups(data.groups);
    } else {
      showToast(data.message || "Error loading groups", "error");
    }
  } catch (error) {
    console.error("Error loading groups:", error);
    showToast("Error loading groups", "error");
  }
}

// Display My Groups
function displayMyGroups(groups) {
  const container = document.getElementById("myGroupsList");

  if (!groups || groups.length === 0) {
    container.innerHTML = `
            <div class="empty-groups">
                <i class="fas fa-users"></i>
                <p>You haven't joined any groups yet.</p>
                <p style="font-size: 0.875rem; margin-top: 10px;">Click the <span id="createGroupTextLink" style="font-weight: 600; cursor: pointer;">"Create Group"</span> button to get started!</p>
            </div>
        `;
    // Add click event listener to the Create Group text
    setTimeout(() => {
      const createGroupLink = document.getElementById("createGroupTextLink");
      if (createGroupLink) {
        createGroupLink.addEventListener("click", () =>
          openModal("createGroupModal"),
        );
      }
    }, 0);
    return;
  }

  container.innerHTML = groups
    .map(
      (group) => `
        <div class="group-card">
            <div class="group-card-header">
                <div class="group-info" style="flex: 1;">
                    <h3>
                        <i class="fas fa-users"></i>
                        ${escapeHtml(group.name)}
                        <span class="group-role role-${group.role}">${group.role}</span>
                    </h3>
                </div>
                <button onclick="shareApp('${escapeHtml(group.name).replace(/'/g, "\\'")}', '${escapeHtml(group.created_by_name || '').replace(/'/g, "\\'")}')" style="background: none; border: none; color: var(--primary-color); cursor: pointer; padding: 0 0 0 10px; font-size: 1.25rem; transition: transform 0.2s;" title="Share App" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                    <i class="fas fa-share-nodes"></i>
                </button>
            </div>
            ${group.description ? `<p class="group-description">${escapeHtml(group.description)}</p>` : ""}
            <div class="group-members">
                <i class="fas fa-user-friends"></i>
                <span><strong>${group.member_count}</strong> member${group.member_count !== 1 ? "s" : ""}</span>
                ${group.pending_count > 0 ? `<span style="color: var(--warning-color);"> • ${group.pending_count} pending</span>` : ""}
            </div>
            <div class="group-actions">
                ${
                  group.role === "admin"
                    ? `
                    <button class="btn-small btn-invite" onclick="openInviteModal(${group.id}, '${escapeHtml(group.name).replace(/'/g, "\\'")}', '${escapeHtml(group.created_by_name || '').replace(/'/g, "\\'")}', this)">
                        <i class="fas fa-user-plus"></i> Invite Users
                    </button>
                `
                    : ""
                }
                <button class="btn-small btn-view" onclick="viewGroup(${group.id}, this)">
                    <i class="fas fa-eye"></i> View Details
                </button>
                ${
                  group.role === "admin"
                    ? `
                    <button class="btn-small btn-delete" onclick="deleteGroup(${group.id}, '${escapeHtml(group.name).replace(/'/g, "\\'")}')">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                `
                    : ""
                }
            </div>
        </div>
    `,
    )
    .join("");
}

// Load Pending Invitations
async function loadPendingInvitations() {
  try {
    const response = await fetch(`${API_URL}?action=getPendingInvitations`);
    const data = await response.json();

    if (data.success && data.invitations.length > 0) {
      displayPendingInvitations(data.invitations);
      document.getElementById("pendingSection").style.display = "block";
    } else {
      document.getElementById("pendingSection").style.display = "none";
    }

    // Update badge on dashboard if function exists
    if (typeof window.updatePendingRequestsBadge === "function") {
      window.updatePendingRequestsBadge();
    }
  } catch (error) {
    console.error("Error loading invitations:", error);
  }
}

// Display Pending Invitations
function displayPendingInvitations(invitations) {
  const container = document.getElementById("pendingInvitations");

  container.innerHTML = invitations
    .map(
      (inv) => `
        <div class="group-card pending-requests-card">
            <div class="group-card-header">
                <div class="group-info">
                    <h3>
                        <i class="fas fa-bell"></i>
                        ${escapeHtml(inv.group_name)}
                    </h3>
                    <p style="font-size: 0.875rem; color: var(--text-secondary); margin-top: 5px;">
                        Invited by <strong>${escapeHtml(inv.invited_by_name)}</strong>
                    </p>
                </div>
            </div>
            ${inv.message ? `<p class="group-description">${escapeHtml(inv.message)}</p>` : ""}
            <div class="request-actions">
                <button class="btn-small btn-accept" onclick="respondToInvitation(${inv.id}, 'approved')">
                    <i class="fas fa-check"></i> Accept
                </button>
                <button class="btn-small btn-reject" onclick="respondToInvitation(${inv.id}, 'rejected')">
                    <i class="fas fa-times"></i> Decline
                </button>
            </div>
        </div>
    `,
    )
    .join("");
}

// Handle Create Group
async function handleCreateGroup(e) {
  e.preventDefault();

  const name = document.getElementById("groupName").value.trim();
  const description = document.getElementById("groupDescription").value.trim();

  if (!name) {
    showToast("Group name is required", "error");
    return;
  }

  try {
    const response = await fetch(API_URL, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: new URLSearchParams({
        action: "createGroup",
        name: name,
        description: description,
      }),
    });

    const data = await response.json();

    if (data.success) {
      showToast("Group created successfully!", "success");
      closeModal("createGroupModal");
      document.getElementById("createGroupForm").reset();
      loadMyGroups();
    } else {
      showToast(data.message || "Error creating group", "error");
    }
  } catch (error) {
    console.error("Error creating group:", error);
    showToast("Error creating group", "error");
  }
}

// Open Invite Modal
async function openInviteModal(groupId, groupName, groupCreator, buttonElement) {
  // Show loader on button
  const button = buttonElement || event?.target?.closest(".btn-invite");
  if (button) {
    const originalContent = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
    button.dataset.originalContent = originalContent;
  }

  currentGroupId = groupId;
  currentGroupName = groupName;
  currentGroupCreator = groupCreator;
  selectedUsers = [];
  openModal("inviteUsersModal");

  try {
    await loadAvailableUsers(groupId);
  } finally {
    // Reset button after loading
    if (button) {
      button.disabled = false;
      button.innerHTML =
        button.dataset.originalContent ||
        '<i class="fas fa-user-plus"></i> Invite Users';
    }
  }
}

// Load Available Users
async function loadAvailableUsers(groupId) {
  try {
    const response = await fetch(
      `${API_URL}?action=getAvailableUsers&group_id=${groupId}`,
    );
    const data = await response.json();

    if (data.success) {
      displayUsersList(data.users);
    } else {
      showToast(data.message || "Error loading users", "error");
    }
  } catch (error) {
    console.error("Error loading users:", error);
    showToast("Error loading users", "error");
  }
}

// Display Users List
function displayUsersList(users) {
  const container = document.getElementById("usersList");

  if (!users || users.length === 0) {
    container.innerHTML =
      '<p style="text-align: center; color: var(--text-secondary);">All users have been invited or are already members.</p>';
    return;
  }

  container.innerHTML = users
    .map((user) => {
      let statusHtml = "";
      let extraClass = "";

      if (user.status === "member") {
        statusHtml = '<span class="user-status status-member">Member</span>';
        extraClass = "member";
      } else if (user.status === "pending") {
        statusHtml = '<span class="user-status status-pending">Pending</span>';
        extraClass = "pending";
      }

      const disabled = user.status === "member" || user.status === "pending";

      return `
            <div class="user-item ${extraClass}" onclick="${!disabled ? `toggleUserSelection(${user.id}, this)` : ""}" style="${disabled ? "cursor: not-allowed;" : "cursor: pointer;"}">
                <div class="user-item-info">
                    <div class="user-avatar">${user.name.charAt(0).toUpperCase()}</div>
                    <div>
                        <div style="font-weight: 600; color: var(--text-primary);">${escapeHtml(user.name)}</div>
                        <div style="font-size: 0.75rem; color: var(--text-secondary);">${escapeHtml(user.email)}</div>
                    </div>
                </div>
                ${statusHtml}
            </div>
        `;
    })
    .join("");
}

// Toggle User Selection
function toggleUserSelection(userId, element) {
  if (
    element.classList.contains("member") ||
    element.classList.contains("pending")
  ) {
    return;
  }

  const index = selectedUsers.indexOf(userId);

  if (index > -1) {
    selectedUsers.splice(index, 1);
    element.classList.remove("selected");
  } else {
    selectedUsers.push(userId);
    element.classList.add("selected");
  }
}

// Handle Search Users
function handleSearchUsers(e) {
  const searchTerm = e.target.value.toLowerCase();
  const userItems = document.querySelectorAll(".user-item");

  userItems.forEach((item) => {
    const text = item.textContent.toLowerCase();
    if (text.includes(searchTerm)) {
      item.style.display = "flex";
    } else {
      item.style.display = "none";
    }
  });
}

// Handle Send Invites
async function handleSendInvites() {
  if (selectedUsers.length === 0) {
    showToast("Please select at least one user to invite", "error");
    return;
  }

  try {
    const response = await fetch(API_URL, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: new URLSearchParams({
        action: "sendInvitations",
        group_id: currentGroupId,
        user_ids: selectedUsers.join(","),
      }),
    });

    const data = await response.json();

    if (data.success) {
      showToast(
        `Invitations sent to ${selectedUsers.length} user(s)!`,
        "success",
      );
      closeModal("inviteUsersModal");
      selectedUsers = [];
      loadMyGroups();
    } else {
      showToast(data.message || "Error sending invitations", "error");
    }
  } catch (error) {
    console.error("Error sending invitations:", error);
    showToast("Error sending invitations", "error");
  }
}

// Respond to Invitation
async function respondToInvitation(invitationId, status) {
  try {
    const response = await fetch(API_URL, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: new URLSearchParams({
        action: "respondToInvitation",
        invitation_id: invitationId,
        status: status,
      }),
    });

    const data = await response.json();

    if (data.success) {
      showToast(
        status === "approved" ? "Invitation accepted!" : "Invitation declined",
        "success",
      );
      loadPendingInvitations();
      // Update badge on dashboard if function exists
      if (typeof window.updatePendingRequestsBadge === "function") {
        window.updatePendingRequestsBadge();
      }
      loadMyGroups();
    } else {
      showToast(data.message || "Error responding to invitation", "error");
    }
  } catch (error) {
    console.error("Error:", error);
    showToast("Error responding to invitation", "error");
  }
}

// View Group
async function viewGroup(groupId, buttonElement) {
  // Show loader on button
  const button = buttonElement || event?.target?.closest(".btn-view");
  if (button) {
    const originalContent = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
    button.dataset.originalContent = originalContent;
  }

  try {
    const response = await fetch(
      `${API_URL}?action=getGroupDetails&group_id=${groupId}`,
    );
    const data = await response.json();

    if (data.success) {
      displayGroupDetails(data.group);
      openModal("viewGroupModal");
    } else {
      showToast(data.message || "Error loading group details", "error");
    }
  } catch (error) {
    console.error("Error:", error);
    showToast("Error loading group details", "error");
  } finally {
    // Reset button after loading
    if (button) {
      button.disabled = false;
      button.innerHTML =
        button.dataset.originalContent ||
        '<i class="fas fa-eye"></i> View Details';
    }
  }
}

// Display Group Details
function displayGroupDetails(group) {
  const container = document.getElementById("groupDetails");

  container.innerHTML = `
        <div style="margin-bottom: 20px;">
            <h3 style="font-size: 1.25rem; margin-bottom: 10px;">${escapeHtml(group.name)}</h3>
            ${group.description ? `<p style="color: var(--text-secondary); margin-bottom: 15px;">${escapeHtml(group.description)}</p>` : ""}
            <p style="font-size: 0.875rem; color: var(--text-secondary);">Created by ${escapeHtml(group.created_by_name)}</p>
        </div>
        
        <div style="margin-bottom: 20px;">
            <h4 style="font-size: 1rem; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-users"></i> Members (${group.members.length})
            </h4>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                ${group.members
                  .map(
                    (member) => `
                    <div style="padding: 12px; background: var(--light-color); border-radius: 10px; display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 35px; height: 35px; border-radius: 50%; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                ${member.name.charAt(0).toUpperCase()}
                            </div>
                            <div>
                                <div style="font-weight: 600;">${escapeHtml(member.name)}</div>
                                <div style="font-size: 0.75rem; color: var(--text-secondary);">${escapeHtml(member.email)}</div>
                            </div>
                        </div>
                        <span class="group-role role-${member.role}">${member.role}</span>
                    </div>
                `,
                  )
                  .join("")}
            </div>
        </div>
        
        ${
          group.pending_invitations && group.pending_invitations.length > 0
            ? `
            <div>
                <h4 style="font-size: 1rem; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; color: var(--warning-color);">
                    <i class="fas fa-clock"></i> Pending Invitations (${group.pending_invitations.length})
                </h4>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    ${group.pending_invitations
                      .map(
                        (inv) => `
                        <div style="padding: 12px; background: rgba(245, 158, 11, 0.1); border-radius: 10px; display: flex; align-items: center; gap: 12px;">
                            <div style="width: 35px; height: 35px; border-radius: 50%; background: linear-gradient(135deg, var(--warning-color), #f97316); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                ${inv.name.charAt(0).toUpperCase()}
                            </div>
                            <div style="flex: 1;">
                                <div style="font-weight: 600;">${escapeHtml(inv.name)}</div>
                                <div style="font-size: 0.75rem; color: var(--text-secondary);">Invitation sent</div>
                            </div>
                        </div>
                    `,
                      )
                      .join("")}
                </div>
            </div>
        `
            : ""
        }
    `;
}

// Delete Group - Show confirmation modal
function deleteGroup(groupId, groupName) {
  showDeleteGroupModal(groupId, groupName);
}

// Show delete group confirmation modal
function showDeleteGroupModal(groupId, groupName) {
  const modal = document.getElementById("deleteGroupModal");
  const messageEl = document.getElementById("deleteGroupMessage");

  // Update message with group name
  messageEl.innerHTML = `Are you sure you want to delete the group <strong>"${escapeHtml(groupName)}"</strong>? This will delete all associated entries and cannot be undone.`;

  modal.style.display = "flex";
  document.body.style.overflow = "hidden";

  // Setup button handlers
  const confirmBtn = document.getElementById("deleteGroupConfirmBtn");
  const cancelBtn = document.getElementById("deleteGroupCancelBtn");
  const overlay = modal.querySelector(".confirm-modal-overlay");

  // Remove old listeners
  const newConfirmBtn = confirmBtn.cloneNode(true);
  const newCancelBtn = cancelBtn.cloneNode(true);
  confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
  cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);

  // Add new listeners
  newConfirmBtn.addEventListener("click", () => performDeleteGroup(groupId));
  newCancelBtn.addEventListener("click", hideDeleteGroupModal);
  overlay.addEventListener("click", hideDeleteGroupModal);
}

// Hide delete group modal
function hideDeleteGroupModal() {
  const modal = document.getElementById("deleteGroupModal");
  modal.style.display = "none";
  document.body.style.overflow = "auto";
}

// Perform actual group deletion
async function performDeleteGroup(groupId) {
  hideDeleteGroupModal();

  try {
    const response = await fetch(API_URL, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: new URLSearchParams({
        action: "deleteGroup",
        group_id: groupId,
      }),
    });

    const data = await response.json();

    if (data.success) {
      showToast("Group deleted successfully", "success");
      loadMyGroups();
    } else {
      showToast(data.message || "Error deleting group", "error");
    }
  } catch (error) {
    console.error("Error:", error);
    showToast("Error deleting group", "error");
  }
}

// Modal Functions
function openModal(modalId) {
  document.getElementById(modalId).classList.add("show");
}

function closeModal(modalId) {
  document.getElementById(modalId).classList.remove("show");
}

// Close modal when clicking outside
document.addEventListener("click", function (event) {
  if (event.target.classList.contains("modal")) {
    const modals = ["createGroupModal", "inviteUsersModal", "viewGroupModal"];
    modals.forEach((modalId) => {
      if (event.target.id === modalId) {
        closeModal(modalId);
      }
    });
  }
});

// Show Toast
function showToast(message, type = "success") {
  const toast = document.getElementById("toast");
  const icon = type === "success" ? "fa-check-circle" : "fa-exclamation-circle";

  toast.innerHTML = `<i class="fas ${icon}"></i> ${message}`;
  toast.className = `toast ${type} show`;

  setTimeout(() => {
    toast.classList.remove("show");
  }, 3000);
}

// Share App
function shareApp(groupName, creatorName) {
  const shareUrl = "https://play.google.com/store/apps/details?id=com.codelock.bookifyapp";
  const shareTitle = "Bookify - Cashbook App";
  const displayName = creatorName ? creatorName : "Admin";
  const shareMessage = `${displayName} invites you in this group: "${groupName}". Download the app or login here:`;

  if (navigator.share) {
    navigator
      .share({
        title: shareTitle,
        text: `${shareMessage}\n\n${shareUrl}`
      })
      .catch((error) => {
        console.log("Error sharing:", error);
        if (error.name !== "AbortError") {
          showToast("Unable to share at this time", "error");
        }
      });
  } else {
    // Fallback for browsers that do not support Web Share API
    const fullText = `${shareMessage}\n\n${shareUrl}`;
    
    // First safely save to clipboard as a backup
    const dummy = document.createElement("input");
    document.body.appendChild(dummy);
    dummy.value = fullText;
    dummy.select();
    document.execCommand("copy");
    document.body.removeChild(dummy);
    showToast("Share text copied to clipboard!", "success");
  }
}

// Escape HTML
function escapeHtml(text) {
  const map = {
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#039;",
  };
  return text.replace(/[&<>"']/g, (m) => map[m]);
}
