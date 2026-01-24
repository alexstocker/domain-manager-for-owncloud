$(document).ready(function() {
    const tableBody = $('#domains-table tbody');

    function isValidDomain(domain) {
        if (!domain || domain.trim() === '') {
            return false;
        }
        const domainRegex = /^[a-zA-Z0-9][a-zA-Z0-9-]{0,61}[a-zA-Z0-9](?:\.[a-zA-Z]{2,})+$/;
        return domainRegex.test(domain);
    }

    function handleError(xhr, defaultMsg) {
        let errorMsg = defaultMsg || 'An error occurred';
        if (xhr.responseJSON && xhr.responseJSON.error) {
            errorMsg = xhr.responseJSON.error;
        } else if (xhr.responseText) {
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
                    '<input type="text" class="domain-input" value="' + domain.domain + '">' +
                    configStr +
                    '</td>' +
                    '<td class="provider-cell">' + providerName + '</td>' +
                    '<td class="expiration-cell">Loading...</td>' +
                    '<td>' + domain.created_at + '</td>' +
                    '<td class="actions-cell">' +
                    '<button class="update-btn">Update</button>' +
                    '<button class="delete-btn">Delete</button>' +
                    '</td>' +
                    '</tr>'
                );
                tableBody.append(row);
                fetchRdapInfo(row, domain.domain);
            });
        }).fail(function(xhr) {
            handleError(xhr, 'Error fetching domains');
        });
    }

    function fetchRdapInfo(row, domain) {
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
            const providerSelect = $('#provider-select');
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
        const providerId = $('#provider-select').val();
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

    $('#provider-select').on('change', renderConfigFields);

    tableBody.on('click', '.update-btn', function() {
        const row = $(this).closest('tr');
        const id = row.data('id');
        const providerId = row.data('provider');
        const domain = row.find('.domain-input').val().trim();
        
        if (!isValidDomain(domain)) {
            const errorMsg = 'Invalid domain name or TLD missing';
            if (typeof OC.Notification !== 'undefined') {
                OC.Notification.showTemporary(errorMsg);
            } else {
                alert(errorMsg);
            }
            return;
        }

        $.ajax({
            url: OC.generateUrl('/apps/domain_manager/api/domains/' + id),
            type: 'PUT',
            data: { domain: domain, providerId: providerId, requesttoken: OC.requestToken },
            success: function() {
                fetchDomains();
                if (typeof OC.Notification !== 'undefined') {
                    OC.Notification.showTemporary('Domain updated');
                }
            },
            error: function(xhr) {
                handleError(xhr, 'Error updating domain');
            }
        });
    });

    tableBody.on('click', '.delete-btn', function() {
        const row = $(this).closest('tr');
        const id = row.data('id');
        const providerId = row.data('provider');

        if (confirm('Are you sure you want to delete this domain?')) {
            $.ajax({
                url: OC.generateUrl('/apps/domain_manager/api/domains/' + id),
                type: 'DELETE',
                data: { providerId: providerId, requesttoken: OC.requestToken },
                success: function() {
                    fetchDomains();
                    if (typeof OC.Notification !== 'undefined') {
                        OC.Notification.showTemporary('Domain deleted');
                    }
                },
                error: function(xhr) {
                    handleError(xhr, 'Error deleting domain');
                }
            });
        }
    });

    $('#add-domain-form input[name="domain"]').on('blur', function() {
        const domain = $(this).val().trim();
        if (isValidDomain(domain)) {
            const domainInput = $(this);
            domainInput.addClass('loading');

            $.getJSON(OC.generateUrl('/apps/domain_manager/api/lookup/' + domain), function(data) {
                domainInput.removeClass('loading');
                if (data && data.identifiedProvider && data.identifiedProvider !== 'none') {
                    $('#provider-select').val(data.identifiedProvider).trigger('change');
                    if (typeof OC.Notification !== 'undefined') {
                        OC.Notification.showTemporary('Provider identified as ' + data.identifiedProvider + ' via RDAP');
                    }
                }
            }).fail(function() {
                domainInput.removeClass('loading');
            });
        }
    });

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
            .done(function() {
                fetchDomains();
                $('#add-domain-form')[0].reset();
                if (typeof OC.Notification !== 'undefined') {
                    OC.Notification.showTemporary('Domain added');
                }
            })
            .fail(function(xhr) {
                handleError(xhr, 'Error adding domain');
            });
    });

    fetchDomains();
    fetchProviders();
});