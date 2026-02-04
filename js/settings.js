$(document).ready(function() {
    const form = $('#domain-manager-personal-form');
    const status = $('#save-status');

    form.on('submit', function(e) {
        e.preventDefault();
        status.text('Saving...');

        const data = {
            backend: $('#backend-select').val(),
            allowed_groups: $('#allowed-groups').val(),
            remote_url: $('#remote-url').val(),
            cloudflare_token: $('#cloudflare-token').val(),
            ispconfig_enabled: $('#ispconfig-enabled').is(':checked') ? 'yes' : 'no',
            ispconfig_url: $('#ispconfig-url').val(),
            ispconfig_user: $('#ispconfig-user').val(),
            ispconfig_pass: $('#ispconfig-pass').val(),
            robot_enabled: $('#robot-enabled').is(':checked') ? 'yes' : 'no',
            robot_url: $('#robot-url').val(),
            robot_user: $('#robot-user').val(),
            robot_pass: $('#robot-pass').val(),
            easyname_enabled: $('#easyname-enabled').is(':checked') ? 'yes' : 'no',
            easyname_url: $('#easyname-url').val(),
            easyname_user: $('#easyname-user').val(),
            easyname_key: $('#easyname-key').val(),
            rdap_enabled: $('#rdap-enabled').is(':checked') ? 'yes' : 'no',
            cctld_lookup_enabled: $('#cctld-lookup-enabled').is(':checked') ? 'yes' : 'no',
            lookup_cache_ttl: $('#lookup-cache-ttl').val(),
            tax_rates: $('#tax-rates').val(),
            payment_periods: $('#payment-periods').val(),
            requesttoken: OC.requestToken
        };

        $.post(OC.generateUrl('/apps/domain_manager/api/settings'), data)
            .done(function() {
                status.text('Settings saved successfully').css('color', 'green');
                setTimeout(() => status.text(''), 3000);
            })
            .fail(function() {
                status.text('Error saving settings').css('color', 'red');
            });
    });
});
