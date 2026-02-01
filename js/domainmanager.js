$(document).ready(function() {
    const tableBody = $('#domains-table tbody');
    const providerSelect = $('#provider-select');
    const navigationStats = $('#navigation-stats');
    let selectedDomainId = null;
    // Drawer DOM references (must exist before using them)
    const drawer = $('#domain-details-drawer');
    const drawerDomainName = $('#drawer-domain-name');
    const detailDomain = $('#detail-domain');
    const detailProvider = $('#detail-provider');
    const detailCreated = $('#detail-created');
    const detailExpiration = $('#detail-expiration');
    const detailLookupEvents = $('#detail-lookup-events');
    const detailPrice = $('#detail-price');
    const detailTaxRate = $('#detail-tax-rate');
    const detailPaymentPeriod = $('#detail-payment-period');

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
            updateNavigationStats(data);
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

    function updateNavigationStats(domains) {
        navigationStats.empty();

        // Total domains
        const totalDomains = domains.length;
        navigationStats.append('<li><a href="#">Total Domains<span class="utils">' + totalDomains + '</span></a></li>');

        // Domains by TLD
        const tldCounts = {};
        domains.forEach(d => {
            const parts = d.domain.split('.');
            if (parts.length > 1) {
                const tld = parts[parts.length - 1];
                tldCounts[tld] = (tldCounts[tld] || 0) + 1;
            }
        });
        navigationStats.append('<li class="app-navigation-entry-header">By TLD</li>');
        for (const [tld, count] of Object.entries(tldCounts)) {
            navigationStats.append('<li><a href="#">.' + tld + '<span class="utils">' + count + '</span></a></li>');
        }

        // Domains by Provider
        const providerCounts = {};
        const providerPrices = {};
        let totalPrice = 0;
        let totalPriceGross = 0;

        domains.forEach(d => {
            const providerId = d.provider || 'none';
            let providerName = providerId;
            if (providerId !== 'none') {
                const providerObj = providers.find(p => p.id === providerId);
                if (providerObj) providerName = providerObj.name;
            }
            providerCounts[providerName] = (providerCounts[providerName] || 0) + 1;

            // Price calculation
            if (d.configuration && d.configuration.price) {
                const price = parseFloat(d.configuration.price);
                if (!isNaN(price)) {
                    providerPrices[providerName] = (providerPrices[providerName] || 0) + price;
                    totalPrice += price;

                    let taxRate = 0;
                    if (d.configuration.tax_rate) {
                        taxRate = parseFloat(d.configuration.tax_rate);
                    }
                    if (!isNaN(taxRate)) {
                        totalPriceGross += price * (1 + taxRate / 100);
                    } else {
                        totalPriceGross += price;
                    }
                }
            }
        });

        navigationStats.append('<li class="app-navigation-entry-header">By Provider</li>');
        for (const [provider, count] of Object.entries(providerCounts)) {
            navigationStats.append('<li><a href="#">' + provider + '<span class="utils">' + count + '</span></a></li>');
        }

        navigationStats.append('<li class="app-navigation-entry-header">Costs</li>');
        navigationStats.append('<li><a href="#">Total Net<span class="utils">' + totalPrice.toFixed(2) + '</span></a></li>');
        navigationStats.append('<li><a href="#">Total Gross<span class="utils">' + totalPriceGross.toFixed(2) + '</span></a></li>');
        
        navigationStats.append('<li class="app-navigation-entry-header">Costs by Provider</li>');
        for (const [provider, price] of Object.entries(providerPrices)) {
            navigationStats.append('<li><a href="#">' + provider + '<span class="utils">' + price.toFixed(2) + '</span></a></li>');
        }
    }

    function appendDomainToTable(domain) {
        let providerName = 'none';
        if (domain.provider && domain.provider !== 'none') {
            const provider = providers.find(p => p.id === domain.provider);
            providerName = provider ? provider.name : domain.provider;
        }

        let priceDisplay = '-';
        if (domain.configuration && domain.configuration.price) {
            priceDisplay = parseFloat(domain.configuration.price).toFixed(2);
            if (domain.configuration.payment_period) {
                priceDisplay += ' / ' + domain.configuration.payment_period;
            }
        }

        const row = $(
            '<tr data-id="' + domain.id + '" data-provider="' + (domain.provider || 'none') + '" data-domain="' + domain.domain + '">' +
            '<td class="column-name">' +
            '<span class="domain-text">' + domain.domain + '</span>' +
            '</td>' +
            '<td class="provider-cell">' + providerName + '</td>' +
            '<td class="price-cell">' + priceDisplay + '</td>' +
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
            // Re-fetch domains to ensure provider names are available for stats
            fetchDomains();
        }).fail(function(xhr) {
            handleError(xhr, 'Error fetching providers');
        });
    }

    // Add Domain form submit handler (reintroduced)
    $('#add-domain-form').on('submit', function(e) {
        e.preventDefault();
        const domain = $(this).find('input[name="domain"]').val().trim();
        const providerId = $(this).find('select[name="providerId"]').val();

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
            // Refresh domains to update stats
            fetchDomains();
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
        detailLookupEvents.text('Loading...');
        detailPrice.val('');
        detailTaxRate.empty();
        detailPaymentPeriod.empty();
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

                // Billing
                detailPrice.val(d.configuration.price || '');
                
                // Populate tax rates
                detailTaxRate.append('<option value="">Select...</option>');
                if (data.taxRates) {
                    data.taxRates.forEach(function(rate) {
                        let isSelected = false;
                        const dbValue = d.configuration.tax_rate;
                        if (dbValue !== undefined && dbValue !== null && dbValue !== '') {
                            if (parseFloat(dbValue) === parseFloat(rate)) {
                                isSelected = true;
                            }
                        }
                        const selected = isSelected ? 'selected' : '';
                        detailTaxRate.append('<option value="' + rate + '" ' + selected + '>' + rate + '%</option>');
                    });
                }

                // Populate payment periods
                detailPaymentPeriod.append('<option value="">Select...</option>');
                if (data.paymentPeriods) {
                    data.paymentPeriods.forEach(function(period) {
                        const selected = (d.configuration.payment_period == period) ? 'selected' : '';
                        detailPaymentPeriod.append('<option value="' + period + '" ' + selected + '>' + period + '</option>');
                    });
                }

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
                // Refresh domains to update stats
                fetchDomains();
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

        const configuration = {
            price: detailPrice.val(),
            tax_rate: detailTaxRate.val(),
            payment_period: detailPaymentPeriod.val()
        };

        $.ajax({
            url: OC.generateUrl('/apps/domain_manager/api/domains/' + selectedDomainId),
            type: 'PUT',
            data: { 
                domain: domainVal, 
                providerId: providerId, 
                configuration: configuration,
                requesttoken: OC.requestToken 
            },
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

    // Initial fetch is now triggered inside fetchProviders to ensure providers are loaded first
    fetchProviders();
});
