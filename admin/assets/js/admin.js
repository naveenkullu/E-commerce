// Admin Panel JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize MDB Dropdowns
    const dropdowns = document.querySelectorAll('[data-mdb-toggle="dropdown"]');
    dropdowns.forEach(dropdown => {
        try {
            new mdb.Dropdown(dropdown);
        } catch (e) {
            console.log('Dropdown init error:', e);
            // Fallback: manual toggle
            dropdown.addEventListener('click', function(e) {
                e.preventDefault();
                const menu = this.nextElementSibling;
                if (menu && menu.classList.contains('dropdown-menu')) {
                    menu.classList.toggle('show');
                }
            });
        }
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    });
    
    // Confirm delete actions
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
    
    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new mdb.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});

// Delete item with AJAX
function deleteItem(id, type, button) {
    if (!confirm('Are you sure you want to delete this item?')) {
        return;
    }
    
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    fetch(`api/delete-${type}.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the row
            const row = button.closest('tr');
            if (row) {
                row.style.transition = 'opacity 0.3s ease';
                row.style.opacity = '0';
                setTimeout(() => row.remove(), 300);
            }
            showToast('Success', 'Item deleted successfully', 'success');
        } else {
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-trash"></i>';
            showToast('Error', data.message || 'Failed to delete item', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-trash"></i>';
        showToast('Error', 'An error occurred', 'danger');
    });
}

// Toggle status
function toggleStatus(id, type, currentStatus, button) {
    const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
    
    button.disabled = true;
    
    fetch(`api/toggle-status.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            id: id, 
            type: type, 
            status: newStatus 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            button.disabled = false;
            showToast('Error', data.message || 'Failed to update status', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        button.disabled = false;
        showToast('Error', 'An error occurred', 'danger');
    });
}

// Preview image before upload
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Handle multiple file uploads
function handleMultipleFiles(input, containerId) {
    const container = document.getElementById(containerId);
    container.innerHTML = '';
    
    if (input.files) {
        Array.from(input.files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'col-md-3 mb-3';
                div.innerHTML = `
                    <div class="position-relative">
                        <img src="${e.target.result}" class="img-fluid rounded" alt="Preview ${index + 1}">
                        <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2" 
                                onclick="removePreview(this)">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
                container.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }
}

// Remove preview image
function removePreview(button) {
    button.closest('.col-md-3').remove();
}

// Export data
function exportData(type, format) {
    window.location.href = `api/export.php?type=${type}&format=${format}`;
}

// Print report
function printReport() {
    window.print();
}

// Filter table
function filterTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const filter = input.value.toUpperCase();
    const table = document.getElementById(tableId);
    const tr = table.getElementsByTagName('tr');
    
    for (let i = 1; i < tr.length; i++) {
        let found = false;
        const td = tr[i].getElementsByTagName('td');
        
        for (let j = 0; j < td.length; j++) {
            if (td[j]) {
                const txtValue = td[j].textContent || td[j].innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
        }
        
        tr[i].style.display = found ? '' : 'none';
    }
}

// Sort table
function sortTable(tableId, columnIndex) {
    const table = document.getElementById(tableId);
    let switching = true;
    let dir = 'asc';
    let switchcount = 0;
    
    while (switching) {
        switching = false;
        const rows = table.rows;
        
        for (let i = 1; i < (rows.length - 1); i++) {
            let shouldSwitch = false;
            const x = rows[i].getElementsByTagName('TD')[columnIndex];
            const y = rows[i + 1].getElementsByTagName('TD')[columnIndex];
            
            if (dir === 'asc') {
                if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                    shouldSwitch = true;
                    break;
                }
            } else if (dir === 'desc') {
                if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                    shouldSwitch = true;
                    break;
                }
            }
        }
        
        if (shouldSwitch) {
            rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
            switching = true;
            switchcount++;
        } else {
            if (switchcount === 0 && dir === 'asc') {
                dir = 'desc';
                switching = true;
            }
        }
    }
}

// Bulk actions
function handleBulkAction(action, type) {
    const checkboxes = document.querySelectorAll('.item-checkbox:checked');
    const ids = Array.from(checkboxes).map(cb => cb.value);
    
    if (ids.length === 0) {
        showToast('Warning', 'Please select at least one item', 'warning');
        return;
    }
    
    if (!confirm(`Are you sure you want to ${action} ${ids.length} item(s)?`)) {
        return;
    }
    
    fetch(`api/bulk-action.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            ids: ids, 
            action: action, 
            type: type 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', `${action} completed successfully`, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast('Error', data.message || 'Failed to complete action', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error', 'An error occurred', 'danger');
    });
}

// Select all checkboxes
function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
}

// Update order status
function updateOrderStatus(orderId, status) {
    if (!confirm(`Change order status to ${status}?`)) {
        return;
    }
    
    fetch('api/update-order-status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            order_id: orderId, 
            status: status 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', 'Order status updated', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast('Error', data.message || 'Failed to update status', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error', 'An error occurred', 'danger');
    });
}

// Show loading overlay
function showLoading() {
    const overlay = document.createElement('div');
    overlay.id = 'loadingOverlay';
    overlay.className = 'loading-overlay';
    overlay.innerHTML = '<div class="loading-spinner-large"></div>';
    document.body.appendChild(overlay);
}

// Hide loading overlay
function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.remove();
    }
}

// Approve/Reject Payment
function approvePayment(orderId, action) {
    const actionText = action === 'approve' ? 'approve' : 'reject';
    
    if (!confirm(`Are you sure you want to ${actionText} this payment?`)) {
        return;
    }
    
    showLoading();
    
    fetch('api/approve-payment.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            order_id: orderId,
            action: action
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            showToast('Success', data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast('Error', data.message || 'Failed to process request', 'danger');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        showToast('Error', 'An error occurred', 'danger');
    });
}
