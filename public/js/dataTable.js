$(document).ready(function() {
    $('#table-backoffice').DataTable({
        language: {
            url: '/js/dataTables.french.json'
        },
        "aoColumnDefs": [
            { 'bSortable': false, 'aTargets': [ 1,2,5,5 ] }
        ]
    });
});

$(document).ready(function() {
    $('#table-category').DataTable({
        language: {
            url: '/js/dataTables.french.json'
        },
        "aoColumnDefs": [
            { 'bSortable': false, 'aTargets': [ 1,3 ] }
        ]
    });
});