$(document).ready(function () {
    const tableBody = $('#unowned-domains-table tbody');

    function handleError(xhr, msg) {
        let err = msg || 'Error';
        if (xhr && xhr.responseJSON && xhr.responseJSON.error) err = xhr.responseJSON.error;
        if (typeof OC.Notification !== 'undefined') {
            OC.Notification.showTemporary(err);
        } else {
            alert(err);
        }
    }

    function fetchOrphaned() {
        $.getJSON(OC.generateUrl('/apps/domain_manager/api/domains/orphaned'), function (data) {
            tableBody.empty();
            data.forEach(function (d) {
                const row = $('<tr data-id="' + d.id + '">');
                row.append('<td>' + (d.domain || '') + '</td>');
                row.append('<td>' + (d.provider || '') + '</td>');
                row.append('<td>' + (d.created_at || '') + '</td>');
                row.append('<td><button class="assign-me">Assign to me</button> <button class="delete-unowned">Delete</button></td>');
                tableBody.append(row);
            });
        }).fail(function (xhr) {
            handleError(xhr, 'Could not fetch orphaned domains');
        });
    }

    tableBody.on('click', '.assign-me', function () {
        const row = $(this).closest('tr');
        const id = row.data('id');
        $.post(OC.generateUrl('/apps/domain_manager/api/domains/' + id + '/assign'), {owner: OC.getCurrentUser ? OC.getCurrentUser() : ''}, function () {
            fetchOrphaned();
            if (typeof OC.Notification !== 'undefined') {
                OC.Notification.showTemporary('Domain assigned to you');
            }
        }).fail(function (xhr) {
            handleError(xhr, 'Could not assign owner');
        });
    });

    tableBody.on('click', '.delete-unowned', function () {
        const row = $(this).closest('tr');
        const id = row.data('id');
        if (!confirm('Delete this orphaned domain?')) return;
        $.ajax({
            url: OC.generateUrl('/apps/domain_manager/api/domains/' + id),
            type: 'DELETE',
            data: {requesttoken: OC.requestToken},
            success: function () {
                fetchOrphaned();
                if (typeof OC.Notification !== 'undefined') OC.Notification.showTemporary('Domain deleted');
            },
            error: function (xhr) {
                handleError(xhr, 'Could not delete domain');
            }
        });
    });

    fetchOrphaned();
});
