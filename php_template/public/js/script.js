document.addEventListener('DOMContentLoaded', function() {
    // Add confirm dialog to potentially destructive actions
    const confirmActions = document.querySelectorAll('.confirm-action');
    if (confirmActions) {
        confirmActions.forEach(function(element) {
            element.addEventListener('click', function(e) {
                const message = this.getAttribute('data-confirm') || 'Are you sure?';
                if (!confirm(message)) {
                    e.preventDefault();
                }
            });
        });
    }
    
    // Close alert messages when clicked
    const alertMessages = document.querySelectorAll('.success-message, .error-message');
    if (alertMessages) {
        alertMessages.forEach(function(element) {
            element.addEventListener('click', function() {
                this.style.display = 'none';
            });
        });
    }
    
    // Add table sorting functionality
    document.querySelectorAll('table.sortable th').forEach(headerCell => {
        headerCell.addEventListener('click', () => {
            const tableElement = headerCell.parentElement.parentElement.parentElement;
            const headerIndex = Array.prototype.indexOf.call(headerCell.parentElement.children, headerCell);
            const currentIsAscending = headerCell.classList.contains('th-sort-asc');

            sortTableByColumn(tableElement, headerIndex, !currentIsAscending);
        });
    });
});

// Function to sort tables
function sortTableByColumn(table, column, asc = true) {
    const dirModifier = asc ? 1 : -1;
    const tBody = table.tBodies[0];
    const rows = Array.from(tBody.querySelectorAll("tr"));

    // Sort each row
    const sortedRows = rows.sort((a, b) => {
        const aColText = a.querySelector(`td:nth-child(${column + 1})`).textContent.trim();
        const bColText = b.querySelector(`td:nth-child(${column + 1})`).textContent.trim();

        return aColText > bColText ? (1 * dirModifier) : (-1 * dirModifier);
    });

    // Remove all existing TRs from the table
    while (tBody.firstChild) {
        tBody.removeChild(tBody.firstChild);
    }

    // Re-add the newly sorted rows
    tBody.append(...sortedRows);

    // Remember how the column is currently sorted
    table.querySelectorAll("th").forEach(th => th.classList.remove("th-sort-asc", "th-sort-desc"));
    table.querySelector(`th:nth-child(${column + 1})`).classList.toggle("th-sort-asc", asc);
    table.querySelector(`th:nth-child(${column + 1})`).classList.toggle("th-sort-desc", !asc);
}

// Function to filter tables
function filterTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const filter = input.value.toUpperCase();
    const table = document.getElementById(tableId);
    const tr = table.getElementsByTagName("tr");

    // Loop through all table rows, and hide those who don't match the search query
    for (let i = 0; i < tr.length; i++) {
        let found = false;
        const tdArray = tr[i].getElementsByTagName("td");
        
        // Skip header row
        if (tdArray.length === 0) continue;
        
        for (let j = 0; j < tdArray.length; j++) {
            const td = tdArray[j];
            if (td) {
                const txtValue = td.textContent || td.innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
        }
        
        if (found) {
            tr[i].style.display = "";
        } else {
            tr[i].style.display = "none";
        }
    }
}