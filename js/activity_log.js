// ============================================================
// LQMS — activity_log.js
// Initialises DataTables on the activity log table.
// jQuery and DataTables are loaded by activity_log.php.
// ============================================================
$(document).ready(function () {
    $('#activityTable').DataTable({
        pageLength:  25,
        lengthMenu:  [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
        order:       [[8, 'desc']],   // Date & Time column descending
        columnDefs: [
            { orderable: false, targets: [1, 5] },   // User cell, Page URL
            { searchable: false, targets: [0] },      // Row # not searchable
            { width: '40px',  targets: [0] },
            { width: '130px', targets: [1] },
            { width: '110px', targets: [2] },
            { width: '120px', targets: [3] },
            { width: '90px',  targets: [6] },
            { width: '80px',  targets: [7] },
            { width: '130px', targets: [8] },
        ],
        language: {
            search:          'Search logs:',
            lengthMenu:      'Show _MENU_ entries',
            info:            'Showing _START_ to _END_ of _TOTAL_ entries',
            infoFiltered:    '(filtered from _MAX_ total)',
            emptyTable:      'No activity logs found.',
            zeroRecords:     'No matching logs found.',
            paginate: {
                first:    '«',
                last:     '»',
                next:     '›',
                previous: '‹',
            }
        },
        // Auto-refresh every 30s — re-fetch the page so DataTables
        // picks up new rows from the server
        initComplete: function () {
            setTimeout(() => location.reload(), 30_000);
        }
    });
});
