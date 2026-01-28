$(document).ready(function() {
    const tableBody = $('#domains-table tbody');
    const providerSelect = $('#provider-select');
    let selectedDomainId = null;
    // Drawer DOM references (must exist before using them)
    const drawer = $('#domain-details-drawer');
    const drawerDomainName = $('#drawer-domain-name');
    const detailDomain = $('#detail-domain');
    const detailProvider = $('#detail-provider');
    const detailCreated = $('#detail-created');
    const detailExpiration = $('#detail-expiration');
    const detailConfiguration = $('#detail-configuration');
    const detailLookupEvents = $('#detail-lookup-events');

    function openDrawer() {
        drawer.removeClass('hidden').addClass('visible').attr('aria-hidden', 'false');
    }
    function closeDrawer() {
        drawer.addClass('hidden').removeClass('visible').attr('aria-hidden', 'true');
    }

    function isValidDomain(domain) {
        if (!domain || domain.trim() === '') {
            return false;
        }
        const domainRegex = /^[a-zA-Z0-9][a-zA-Z0-9-]{0,61}[a-zA-Z0-9](?:\.[a-zA-Z]{2,})+$/;
        return domainRegex.test(domain);
    }

    function handleError(xhr, defaultMsg) {
        let errorMsg = defaultMsg || 'An error occurred';
        if (xhr && xhr.responseJSON && xhr.responseJSON.error) {
            errorMsg = xhr.responseJSON.error;
        } else if (xhr && xhr.responseText) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.error) {
                    errorMsg = response.error;
                }
            } catch (e) {
            }
        }
        if (typeof OC.Notification !== 'undefined') {
            OC.Notification.showTemporary(errorMsg);
        } else {
            alert(errorMsg);
        }
    }

    function fetchDomains() {
        $.getJSON(OC.generateUrl('/apps/domain_manager/api/domains'), function(data) {
            tableBody.empty();
            data.forEach(function(domain) {
                appendDomainToTable(domain);
            });
            // Trigger lookup for all domains after they are in the DOM
            tableBody.find('tr').each(function() {
                const row = $(this);
                const domainName = row.data('domain');
                fetchLookupInfo(row, domainName);
            });
        }).fail(function(xhr) {
            handleError(xhr, 'Error fetching domains');
        });
    }

    function appendDomainToTable(domain) {
        let configStr = '';
        if (domain.configuration && Object.keys(domain.configuration).length > 0) {
            const filteredConfig = Object.assign({}, domain.configuration);
            delete filteredConfig.provider;
            if (Object.keys(filteredConfig).length > 0) {
                configStr = '<br><small>' + JSON.stringify(filteredConfig) + '</small>';
            }
        }

        let providerName = 'none';
        if (domain.provider && domain.provider !== 'none') {
            const provider = providers.find(p => p.id === domain.provider);
            providerName = provider ? provider.name : domain.provider;
        }

        const row = $(
            '<tr data-id="' + domain.id + '" data-provider="' + (domain.provider || 'none') + '" data-domain="' + domain.domain + '">' +
            '<td class="column-name">' +
            '<span class="domain-text">' + domain.domain + '</span>' +
            configStr +
            '</td>' +
            '<td class="provider-cell">' + providerName + '</td>' +
            '<td class="expiration-cell">Loading...</td>' +
            '<td class="actions-cell">' +
            '<a class="action action-menu permanent" href="#" data-action="menu" data-original-title="" title="">' +
            '<span class="icon icon-more details-btn" title="Details"></span>' +
            '<span class="hidden-visually">Actions</span>' +
            '</a>' +
            '</td>' +
            '</tr>'
        );
        tableBody.append(row);
        return row;
    }

    function fetchLookupInfo(row, domain) {
        const expirationCell = row.find('.expiration-cell');
        const providerCell = row.find('.provider-cell');
        
        $.getJSON(OC.generateUrl('/apps/domain_manager/api/lookup/' + domain), function(data) {
            if (data) {
                if (data.events) {
                    const expirationEvent = data.events.find(e => e.eventAction === 'expiration');
                    if (expirationEvent) {
                        expirationCell.text(new Date(expirationEvent.eventDate).toLocaleDateString());
                    } else {
                        expirationCell.text('Unknown');
                    }
                } else {
                    expirationCell.text('N/A');
                }

                if (row.data('provider') === 'none' && data.identifiedProvider && data.identifiedProvider !== 'none') {
                    const provider = providers.find(p => p.id === data.identifiedProvider);
                    const identifiedName = provider ? provider.name : data.identifiedProvider;
                    providerCell.html('<i>' + identifiedName + ' (guess)</i>');
                }
            }
        }).fail(function() {
            expirationCell.text('N/A');
        });
    }

    let providers = [];

    function fetchProviders() {
        $.getJSON(OC.generateUrl('/apps/domain_manager/api/providers'), function(data) {
            providers = data;
            providerSelect.empty();
            
            providerSelect.append('<option value="none">none</option>');
            
            data.forEach(function(provider) {
                providerSelect.append('<option value="' + provider.id + '">' + provider.name + '</option>');
            });
            renderConfigFields();
        }).fail(function(xhr) {
            handleError(xhr, 'Error fetching providers');
        });
    }

    function renderConfigFields() {
        const providerId = providerSelect.val();
        const provider = providers.find(p => p.id === providerId);
        const container = $('#provider-config-fields');
        container.empty();

        if (provider && provider.configFields) {
            provider.configFields.forEach(field => {
                const input = $('<input>', {
                    type: field.type,
                    name: 'config_' + field.name,
                    placeholder: field.label,
                    value: field.default || ''
                });
                container.append(input);
            });
        }
    }

    providerSelect.on('change', renderConfigFields);

    // Add Domain form submit handler (reintroduced)
    $('#add-domain-form').on('submit', function(e) {
        e.preventDefault();
        const domain = $(this).find('input[name="domain"]').val().trim();
        const providerId = $(this).find('select[name="providerId"]').val();

        const configuration = {};
        $(this).find('input[name^="config_"]').each(function() {
            const name = $(this).attr('name').replace('config_', '');
            configuration[name] = $(this).val();
        });

        if (!isValidDomain(domain)) {
            const errorMsg = 'Invalid domain name or TLD missing';
            if (typeof OC.Notification !== 'undefined') {
                OC.Notification.showTemporary(errorMsg);
            } else {
                alert(errorMsg);
            }
            return;
        }

        $.post(OC.generateUrl('/apps/domain_manager/api/domains/add'), {
            domain: domain,
            providerId: providerId,
            configuration: configuration,
            requesttoken: OC.requestToken
        })
        .done(function(newDomain) {
            // Add new row and trigger lookup
            const newRow = appendDomainToTable(newDomain);
            fetchLookupInfo(newRow, newDomain.domain);
            $('#add-domain-form')[0].reset();
            if (typeof OC.Notification !== 'undefined') {
                OC.Notification.showTemporary('Domain added');
            }
        })
        .fail(function(xhr) {
            handleError(xhr, 'Error adding domain');
        });
    });

    tableBody.on('click', '.details-btn', function() {
        const row = $(this).closest('tr');
        const id = row.data('id');
        selectedDomainId = id;
        // show loading state
        drawerDomainName.text('Loading...');
        detailDomain.text('-');
        detailProvider.text('-');
        detailCreated.text('-');
        detailExpiration.text('-');
        detailConfiguration.text('{}');
        detailLookupEvents.text('Loading...');
        openDrawer();

        $.getJSON(OC.generateUrl('/apps/domain_manager/api/domains/' + id), function(data) {
            if (data && data.domain) {
                const d = data.domain;
                drawerDomainName.text(d.domain);
                detailDomain.text(d.domain);
                const providerId = d.provider || 'none';
                let providerName = providerId;
                if (data.providerMeta && data.providerMeta.name) {
                    providerName = data.providerMeta.name;
                } else {
                    const providerObj = providers.find(p => p.id === providerId);
                    if (providerObj) providerName = providerObj.name;
                }
                detailProvider.text(providerName);
                detailCreated.text(d.created_at || '-');
                // expiration will be fetched via lookup (frontend-initiated)
                detailExpiration.text('N/A');
                detailConfiguration.text(JSON.stringify(d.configuration || {}, null, 2));

                // perform lookup request (frontend-initiated)
                fetchDetailsLookup(d.domain);
            } else {
                detailLookupEvents.text('No details available');
            }
        }).fail(function(xhr) {
            handleError(xhr, 'Error fetching domain details');
        });
    });

    $('#drawer-close').on('click', function() {
        closeDrawer();
    });

    function fetchDetailsLookup(domainName, force) {
        let url = OC.generateUrl('/apps/domain_manager/api/lookup/' + domainName);
        if (force === true) {
            url = url + `/force`;
        }
        detailLookupEvents.text('Loading...');
        $.getJSON(url, function(data) {
            if (data && data.events) {
                const ul = $('<ul/>');
                data.events.forEach(function(e) {
                    ul.append('<li>' + (e.eventAction || '') + ': ' + (e.eventDate || '') + '</li>');
                });
                detailLookupEvents.empty();
                detailLookupEvents.append(ul);
                const expirationEvent = data.events.find(e => e.eventAction === 'expiration');
                if (expirationEvent) {
                    detailExpiration.text(new Date(expirationEvent.eventDate).toLocaleDateString());
                } else {
                    detailExpiration.text('Unknown');
                }
            } else {
                detailLookupEvents.text('N/A');
                detailExpiration.text('N/A');
            }
        }).fail(function() {
            detailLookupEvents.text('N/A');
            detailExpiration.text('N/A');
        });
    }

    $('#detail-refresh-lookup').on('click', function() {
        const domainName = detailDomain.text();
        if (domainName && domainName !== '-') {
            fetchDetailsLookup(domainName, true);
        }
    });

    $('#detail-delete').on('click', function() {
        if (!selectedDomainId) {
            handleError(null, 'Could not determine domain id to delete');
            return;
        }
        // Show inline confirmation UI in the drawer
        $('#detail-delete-confirm').removeClass('hidden').attr('aria-hidden', 'false');
        // hide primary drawer actions to avoid accidental clicks
        $('.drawer-actions').addClass('hidden');
    });

    // Cancel delete in drawer confirmation
    $('#detail-delete-cancel-button').on('click', function() {
        $('#detail-delete-confirm').addClass('hidden').attr('aria-hidden', 'true');
        $('.drawer-actions').removeClass('hidden');
    });

    // Confirm delete (perform API call)
    $('#detail-delete-confirm-button').on('click', function() {
        if (!selectedDomainId) {
            handleError(null, 'Could not determine domain id to delete');
            return;
        }
        const row = tableBody.find('tr[data-id="' + selectedDomainId + '"]');
        const providerId = row.data('provider');
        $.ajax({
            url: OC.generateUrl('/apps/domain_manager/api/domains/' + selectedDomainId),
            type: 'DELETE',
            data: { providerId: providerId, requesttoken: OC.requestToken },
            success: function() {
                row.remove();
                $('#detail-delete-confirm').addClass('hidden').attr('aria-hidden', 'true');
                $('.drawer-actions').removeClass('hidden');
                closeDrawer();
                selectedDomainId = null;
                if (typeof OC.Notification !== 'undefined') {
                    OC.Notification.showTemporary('Domain deleted');
                }
            },
            error: function(xhr) {
                handleError(xhr, 'Error deleting domain');
            }
        });
    });

    $('#detail-update').on('click', function() {
        if (!selectedDomainId) {
            handleError(null, 'Could not determine domain id to update');
            return;
        }
        const row = tableBody.find('tr[data-id="' + selectedDomainId + '"]');
        const providerId = row.data('provider');
        // Domain is not editable in the table. Use stored data-domain value for update.
        const domainVal = String(row.data('domain') || '').trim();
        if (!isValidDomain(domainVal)) {
            handleError(null, 'Invalid domain name or TLD missing');
            return;
        }
        $.ajax({
            url: OC.generateUrl('/apps/domain_manager/api/domains/' + selectedDomainId),
            type: 'PUT',
            data: { domain: domainVal, providerId: providerId, requesttoken: OC.requestToken },
            success: function() {
                fetchDomains();
                closeDrawer();
                selectedDomainId = null;
                if (typeof OC.Notification !== 'undefined') {
                    OC.Notification.showTemporary('Domain updated');
                }
            },
            error: function(xhr) {
                handleError(xhr, 'Error updating domain');
            }
        });
    });

    fetchDomains();
    fetchProviders();
});
