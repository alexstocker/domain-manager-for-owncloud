<?php
\OCP\Util::addScript('domain_manager', 'settings');
?>

    <div id="domain-manager-settings" class="section">
        <h2>DomainManager User Settings</h2>
        <p class="settings-hint">
            Configure how MyApp behaves for your account.
        </p>

        <form id="domain-manager-personal-form">
            <p>
                <label for="backend-select">Default Backend</label>
                <select id="backend-select" name="backend">
                    <option value="local" <?php if($_['backend'] === 'local') print_unescaped('selected="selected"'); ?>>Local</option>
                    <option value="remote" <?php if($_['backend'] === 'remote') print_unescaped('selected="selected"'); ?>>Remote</option>
                </select>
            </p>
            <p>
                <label for="remote-url">Remote API URL</label>
                <input type="text" id="remote-url" name="remote_url" value="<?php p($_['remote_url']) ?>" placeholder="https://api.example.com/v1" />
            </p>

            <h3>Cloudflare</h3>
            <p>
                <label for="cloudflare-token">Global API Token</label>
                <input type="password" id="cloudflare-token" name="cloudflare_token" value="<?php p($_['cloudflare_token']) ?>" />
            </p>

            <h3>ISPConfig</h3>
            <p>
                <input type="checkbox" id="ispconfig-enabled" name="ispconfig_enabled" value="yes" <?php if($_['ispconfig_enabled'] === 'yes') print_unescaped('checked="checked"'); ?> />
                <label for="ispconfig-enabled">Enable ISPConfig Provider</label>
            </p>
            <div class="ispconfig-details">
                <p>
                    <label for="ispconfig-url">API URL</label>
                    <input type="text" id="ispconfig-url" name="ispconfig_url" value="<?php p($_['ispconfig_url']) ?>" />
                </p>
                <p>
                    <label for="ispconfig-user">Username</label>
                    <input type="text" id="ispconfig-user" name="ispconfig_user" value="<?php p($_['ispconfig_user']) ?>" />
                </p>
                <p>
                    <label for="ispconfig-pass">Password</label>
                    <input type="password" id="ispconfig-pass" name="ispconfig_pass" />
                </p>
            </div>

            <h3>Robot API (Webtropia/WIIT)</h3>
            <p>
                <input type="checkbox" id="robot-enabled" name="robot_enabled" value="yes" <?php if($_['robot_enabled'] === 'yes') print_unescaped('checked="checked"'); ?> />
                <label for="robot-enabled">Enable Robot API Provider</label>
            </p>
            <div class="robot-details">
                <p>
                    <label for="robot-url">API URL</label>
                    <input type="text" id="robot-url" name="robot_url" value="<?php p($_['robot_url']) ?>" />
                </p>
                <p>
                    <label for="robot-user">Username</label>
                    <input type="text" id="robot-user" name="robot_user" value="<?php p($_['robot_user']) ?>" />
                </p>
                <p>
                    <label for="robot-pass">Password</label>
                    <input type="password" id="robot-pass" name="robot_pass" />
                </p>
            </div>

            <h3>Easyname</h3>
            <p>
                <input type="checkbox" id="easyname-enabled" name="easyname_enabled" value="yes" <?php if($_['easyname_enabled'] === 'yes') print_unescaped('checked="checked"'); ?> />
                <label for="easyname-enabled">Enable Easyname Provider</label>
            </p>
            <div class="easyname-details">
                <p>
                    <label for="easyname-url">API URL</label>
                    <input type="text" id="easyname-url" name="easyname_url" value="<?php p($_['easyname_url']) ?>" />
                </p>
                <p>
                    <label for="easyname-user">Username</label>
                    <input type="text" id="easyname-user" name="easyname_user" value="<?php p($_['easyname_user']) ?>" />
                </p>
                <p>
                    <label for="easyname-key">API Key</label>
                    <input type="password" id="easyname-key" name="easyname_key" />
                </p>
            </div>

            <h3>Lookups</h3>
            <p>
                <input type="checkbox" id="rdap-enabled" name="rdap_enabled" value="yes" <?php if($_['rdap_enabled'] === 'yes') print_unescaped('checked="checked"'); ?> />
                <label for="rdap-enabled">Enable generic RDAP lookup</label>
            </p>
            <p>
                <input type="checkbox" id="cctld-lookup-enabled" name="cctld_lookup_enabled" value="yes" <?php if($_['cctld_lookup_enabled'] === 'yes') print_unescaped('checked="checked"'); ?> />
                <label for="cctld-lookup-enabled">Enable ccTLD-specific lookup (.at, .de)</label>
            </p>
            <p>
                <label for="lookup-cache-ttl">Lookup cache TTL (seconds, default 86400)</label>
                <input type="number" id="lookup-cache-ttl" name="lookup_cache_ttl" min="1" value="<?php p($_['lookup_cache_ttl']) ?>" placeholder="86400" />
            </p>

            <h3>Billing</h3>
            <p>
                <label for="tax-rates">Tax Rates (comma-separated, e.g. 0,10,20)</label>
                <input type="text" id="tax-rates" name="tax_rates" value="<?php p($_['tax_rates']) ?>" placeholder="0,10,20" />
            </p>
            <p>
                <label for="payment-periods">Payment Periods (comma-separated, e.g. monthly,yearly)</label>
                <input type="text" id="payment-periods" name="payment_periods" value="<?php p($_['payment_periods']) ?>" placeholder="monthly,yearly" />
            </p>

            <p>
                <button id="save-settings-btn" type="submit">Save Settings</button>
                <span id="save-status"></span>
            </p>
        </form>
    </div>
