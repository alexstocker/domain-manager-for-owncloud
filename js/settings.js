$(document).ready(function() {
    const form = $('#domain-manager-admin-form');
    const status = $('#save-status');

    function loadSettings() {
        $.getJSON(OC.generateUrl('/apps/domain_manager/api/settings'), function(data) {
            $('#backend-select').val(data.backend);
            $('#remote-url').val(data.remote_url);
            $('#cloudflare-token').val(data.cloudflare_token);
            $('#ispconfig-enabled').prop('checked', data.ispconfig_enabled === 'yes');
            $('#ispconfig-url').val(data.ispconfig_url);
            $('#ispconfig-user').val(data.ispconfig_user);
            $('#robot-enabled').prop('checked', data.robot_enabled === 'yes');
            $('#robot-url').val(data.robot_url);
            $('#robot-user').val(data.robot_user);
            $('#rdap-enabled').prop('checked', data.rdap_enabled === 'yes');
        });
    }

    form.on('submit', function(e) {
        e.preventDefault();
        status.text('Saving...');

        const data = {
            backend: $('#backend-select').val(),
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
            rdap_enabled: $('#rdap-enabled').is(':checked') ? 'yes' : 'no',
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

    loadSettings();
});
