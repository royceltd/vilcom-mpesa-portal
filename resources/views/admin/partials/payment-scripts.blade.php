{{-- resources/views/admin/partials/payment-scripts.blade.php --}}

<script>
$(document).ready(function() {
    // Initialize DataTables with modern styling and enhanced features
    const table = $('#paymentsTable').DataTable({
        "pageLength": 25,
        "responsive": true,
        "order": [[0, "desc"]],
        "columnDefs": [
            { 
                "targets": [9], // Action column
                "orderable": false,
                "searchable": false
            }
        ],
        "language": {
            "search": "Search payments:",
            "searchPlaceholder": "Type to search...",
            "lengthMenu": "Show _MENU_ payments per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ payments",
            "infoEmpty": "No payments found",
            "infoFiltered": "(filtered from _MAX_ total payments)",
            "emptyTable": "No payment data available",
            "zeroRecords": "No matching payments found",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            }
        },
        "dom": '<"flex flex-col sm:flex-row justify-between items-center mb-4"<"flex items-center space-x-2"l><"flex items-center"f>>rtip',
        "initComplete": function() {
            // Style the search input
            $('.dataTables_filter input').addClass('px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent');
            $('.dataTables_filter label').addClass('text-sm font-medium text-gray-700');
            
            // Style the length select
            $('.dataTables_length select').addClass('px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent');
            $('.dataTables_length label').addClass('text-sm font-medium text-gray-700');
            
            // Style pagination
            $('.dataTables_paginate .paginate_button').addClass('px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-transparent');
            $('.dataTables_paginate .paginate_button.current').addClass('bg-blue-50 border-blue-500 text-blue-600');
            $('.dataTables_paginate .paginate_button.disabled').addClass('text-gray-400 cursor-not-allowed');
            
            // Style info text
            $('.dataTables_info').addClass('text-sm text-gray-700');
            
            // Show export buttons after DataTable is initialized
            $('#export-buttons').removeClass('hidden');
        }
    });
});

// Modal Functions
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Dropdown Functions
function toggleDropdown(dropdownId) {
    const dropdown = document.getElementById(dropdownId);
    dropdown.classList.toggle('hidden');
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function closeDropdown(e) {
        if (!e.target.closest(`#${dropdownId}`) && !e.target.closest(`button[onclick="toggleDropdown('${dropdownId}')"]`)) {
            dropdown.classList.add('hidden');
            document.removeEventListener('click', closeDropdown);
        }
    });
}

// Export Functions
function exportTableToCSV() {
    const table = $('#paymentsTable').DataTable();
    const data = table.rows({ search: 'applied' }).data().toArray();
    
    // Create CSV content
    const headers = ['#', 'Sender', 'Ref', 'Amount', 'Type', 'Account No', 'Date', 'Billing Status', 'Message'];
    let csvContent = headers.join(',') + '\n';
    
    data.forEach(function(row, index) {
        // Extract text content from HTML elements
        const cleanRow = [
            index + 1,
            extractText(row[1]),
            extractText(row[2]),
            extractText(row[3]),
            extractText(row[4]),
            extractText(row[5]),
            extractText(row[6]),
            extractText(row[7]),
            extractText(row[8]).replace(/,/g, ';') // Replace commas in message
        ];
        csvContent += cleanRow.map(field => `"${field}"`).join(',') + '\n';
    });
    
    // Download CSV
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'payments_' + new Date().toISOString().slice(0, 10) + '.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function printTable() {
    const table = $('#paymentsTable').DataTable();
    
    let printHTML = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Payment Report</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                h1 { color: #3B82F6; margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px; }
                th { background-color: #f8f9fa; font-weight: bold; }
                tr:nth-child(even) { background-color: #f9f9f9; }
                .print-date { text-align: right; margin-bottom: 20px; color: #666; }
            </style>
        </head>
        <body>
            <h1>Payment Report</h1>
            <div class="print-date">Generated on: ${new Date().toLocaleDateString()}</div>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Sender</th>
                        <th>Ref</th>
                        <th>Amount</th>
                        <th>Type</th>
                        <th>Account No</th>
                        <th>Date</th>
                        <th>Billing Status</th>
                        <th>Message</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    table.rows({ search: 'applied' }).data().each(function(row, index) {
        printHTML += '<tr>';
        for (let i = 0; i < 9; i++) { // Exclude action column
            printHTML += `<td>${extractText(row[i])}</td>`;
        }
        printHTML += '</tr>';
    });
    
    printHTML += `
                </tbody>
            </table>
        </body>
        </html>
    `;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(printHTML);
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
}

function extractText(html) {
    const div = document.createElement('div');
    div.innerHTML = html;
    return div.textContent || div.innerText || '';
}

// Close modals when pressing Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modals = ['addModal', 'searchModal', 'exportModal', 'filterModal'];
        modals.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (!modal.classList.contains('hidden')) {
                closeModal(modalId);
            }
        });
    }
});

// Auto-hide success messages after 5 seconds
setTimeout(function() {
    const alerts = document.querySelectorAll('.bg-green-50, .bg-red-50');
    alerts.forEach(function(alert) {
        alert.style.transition = 'opacity 0.5s ease-out';
        alert.style.opacity = '0';
        setTimeout(function() {
            alert.remove();
        }, 500);
    });
}, 5000);

// Add loading states to forms
document.querySelectorAll('.payment-form').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            const originalText = submitButton.innerHTML;
            submitButton.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white inline-block" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Processing...
            `;
            
            // Reset button after 10 seconds if form doesn't submit
            setTimeout(function() {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalText;
                }
            }, 10000);
        }
    });
});

// Add form validation feedback
document.querySelectorAll('input[required], select[required]').forEach(function(field) {
    field.addEventListener('invalid', function(e) {
        e.target.classList.add('border-red-500', 'focus:ring-red-500');
        e.target.classList.remove('border-gray-300', 'focus:ring-blue-500');
    });
    
    field.addEventListener('input', function(e) {
        if (e.target.validity.valid) {
            e.target.classList.remove('border-red-500', 'focus:ring-red-500');
            e.target.classList.add('border-gray-300', 'focus:ring-blue-500');
        }
    });
});

// Format number inputs in real-time
document.querySelectorAll('input[type="number"]').forEach(function(input) {
    input.addEventListener('input', function(e) {
        if (e.target.name === 'amount') {
            // Format amount with 2 decimal places
            const value = parseFloat(e.target.value);
            if (!isNaN(value)) {
                e.target.value = value.toFixed(2);
            }
        }
    });
});

// Add tooltips for truncated messages
document.querySelectorAll('[title]').forEach(function(element) {
    element.addEventListener('mouseenter', function(e) {
        // Create tooltip
        const tooltip = document.createElement('div');
        tooltip.className = 'absolute z-50 px-3 py-2 text-sm text-white bg-gray-900 rounded-lg shadow-lg pointer-events-none';
        tooltip.textContent = e.target.getAttribute('title');
        tooltip.style.left = e.pageX + 'px';
        tooltip.style.top = (e.pageY - 40) + 'px';
        document.body.appendChild(tooltip);
        
        e.target.addEventListener('mouseleave', function() {
            tooltip.remove();
        }, { once: true });
    });
});
</script>