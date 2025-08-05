
let currentPage = 1;
let currentLimit = 10;
let editingRecord = null;
let allRecords = [];


const modal = document.getElementById('modal');
const modalTitle = document.getElementById('modalTitle');
const transactionForm = document.getElementById('transactionForm');
const recordsBody = document.getElementById('recordsBody');
const loading = document.getElementById('loading');
const searchInput = document.getElementById('searchInput');
const statusFilter = document.getElementById('statusFilter');
const prevBtn = document.getElementById('prevBtn');
const nextBtn = document.getElementById('nextBtn');
const pageInfo = document.getElementById('pageInfo');


document.addEventListener('DOMContentLoaded', function() {
    loadRecords();
    
    // Modal events
    document.getElementById('addBtn').addEventListener('click', openAddModal);
    document.querySelector('.close').addEventListener('click', closeModal);
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeModal();
        }
    });
    

    transactionForm.addEventListener('submit', handleFormSubmit);
    
    searchInput.addEventListener('input', debounce(handleSearch, 300));
    statusFilter.addEventListener('change', handleFilter);
    
    // Pagination
    prevBtn.addEventListener('click', () => changePage(currentPage - 1));
    nextBtn.addEventListener('click', () => changePage(currentPage + 1));
});

async function loadRecords() {
    showLoading(true);
    try {
        const response = await fetch(`fetch_transactions.php?page=${currentPage}&limit=${currentLimit}`);
        const data = await response.json();
        
        if (data.error) {
            throw new Error(data.error);
        }
        
        allRecords = data.data;
        displayRecords(data.data);
        updatePagination(data.pagination);
    } catch (error) {
        showMessage('Error loading records: ' + error.message, 'error');
    } finally {
        showLoading(false);
    }
}

// Display records in table
function displayRecords(records) {
    recordsBody.innerHTML = '';
    
    if (records.length === 0) {
        recordsBody.innerHTML = `
            <tr>
                <td colspan="7" style="text-align: center; padding: 40px; color: #a0aec0;">
                    <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 15px; display: block;"></i>
                    No records found
                </td>
            </tr>
        `;
        return;
    }
    
    records.forEach(record => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><strong>${record.serial_number}</strong></td>
            <td>${parseFloat(record.amount).toFixed(2)}</td>
            <td>${formatDate(record.received_date)}</td>
            <td>${record.received_through}</td>
            <td>${record.description}</td>
            <td><span class="status-badge status-${record.status}">${record.status}</span></td>
            <td class="actions">
                <button class="btn btn-secondary" onclick="editRecord('${record.serial_number}')">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="btn btn-danger" onclick="deleteRecord('${record.serial_number}')">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </td>
        `;
        recordsBody.appendChild(row);
    });
}

// Update pagination controls
function updatePagination(pagination) {
    pageInfo.textContent = `Page ${pagination.current_page} of ${pagination.total_pages}`;
    prevBtn.disabled = !pagination.has_prev_page;
    nextBtn.disabled = !pagination.has_next_page;
}

// Change page
function changePage(page) {
    currentPage = page;
    loadRecords();
}

// Open add 
function openAddModal() {
    editingRecord = null;
    modalTitle.textContent = 'Add New Transaction';
    transactionForm.reset();
    modal.style.display = 'block';
}

// Open edit 
function editRecord(serialNumber) {
    const record = allRecords.find(r => r.serial_number === serialNumber);
    if (!record) {
        showMessage('Record not found', 'error');
        return;
    }
    
    editingRecord = record;
    modalTitle.textContent = 'Edit Transaction';
    
    document.getElementById('serial_number').value = record.serial_number;
    document.getElementById('amount').value = record.amount;
    document.getElementById('received_through').value = record.received_through;
    document.getElementById('description').value = record.description;
    document.getElementById('status').value = record.status;
    
    modal.style.display = 'block';
}

// Close 
function closeModal() {
    modal.style.display = 'none';
    editingRecord = null;
    transactionForm.reset();
}

// Handle form submission
async function handleFormSubmit(event) {
    event.preventDefault();
    
    const formData = new FormData(transactionForm);
    const data = Object.fromEntries(formData.entries());
    
    try {
        showLoading(true);
        
        if (editingRecord) {
            // Update existing record
            const response = await fetch('update_transaction.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    original_serial: editingRecord.serial_number,
                    ...data
                })
            });
            
            const result = await response.json();
            if (result.error) {
                throw new Error(result.error);
            }
            
            showMessage('Record updated successfully!', 'success');
        } else {
            // Add new record
            const response = await fetch('add_transaction.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            if (result.error) {
                throw new Error(result.error);
            }
            
            showMessage('Record added successfully!', 'success');
        }
        
        closeModal();
        loadRecords();
    } catch (error) {
        showMessage('Error: ' + error.message, 'error');
    } finally {
        showLoading(false);
    }
}

// Delete record
async function deleteRecord(serialNumber) {
    if (!confirm('Are you sure you want to delete this record?')) {
        return;
    }
    
    try {
        showLoading(true);
        
        const response = await fetch('delete_transaction.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ serial_number: serialNumber })
        });
        
        const result = await response.json();
        if (result.error) {
            throw new Error(result.error);
        }
        
        showMessage('Record deleted successfully!', 'success');
        loadRecords();
    } catch (error) {
        showMessage('Error: ' + error.message, 'error');
    } finally {
        showLoading(false);
    }
}

//search
function handleSearch() {
    const searchTerm = searchInput.value.toLowerCase();
    const filteredRecords = allRecords.filter(record => 
        record.serial_number.toLowerCase().includes(searchTerm) ||
        record.description.toLowerCase().includes(searchTerm) ||
        record.received_through.toLowerCase().includes(searchTerm)
    );
    displayRecords(filteredRecords);
}

//filter
function handleFilter() {
    const statusFilterValue = statusFilter.value;
    let filteredRecords = allRecords;
    
    if (statusFilterValue) {
        filteredRecords = allRecords.filter(record => 
            record.status === statusFilterValue
        );
    }
    
    displayRecords(filteredRecords);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function showLoading(show) {
    loading.classList.toggle('hidden', !show);
}

function showMessage(message, type) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${type}`;
    messageDiv.textContent = message;
    document.body.appendChild(messageDiv);
    
    setTimeout(() => {
        messageDiv.remove();
    }, 3000);
}

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