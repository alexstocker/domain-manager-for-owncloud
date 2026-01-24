<?php
\OCP\Util::addScript('domain_manager', 'settings');
?>

<div id="domain-manager-settings" class="section">
<h2>Domain Manager Settings</h2>

    <form id="domain-manager-admin-form">
        <p>
            <label for="backend-select">Default Backend</label>
            <select id="backend-select" name="backend">
                <option value="local">Local</option>
                <option value="remote">Remote</option>
            </select>
        </p>
        <p>
            <label for="remote-url">Remote API URL</label>
            <input type="text" id="remote-url" name="remote_url" placeholder="https://api.example.com/v1" />
        </p>

        <h3>Cloudflare</h3>
        <p>
            <label for="cloudflare-token">Global API Token</label>
            <input type="password" id="cloudflare-token" name="cloudflare_token" />
        </p>

        <h3>ISPConfig</h3>
        <p>
            <input type="checkbox" id="ispconfig-enabled" name="ispconfig_enabled" value="yes" />
            <label for="ispconfig-enabled">Enable ISPConfig Provider</label>
        </p>
        <div class="ispconfig-details">
            <p>
                <label for="ispconfig-url">API URL</label>
                <input type="text" id="ispconfig-url" name="ispconfig_url" />
            </p>
            <p>
                <label for="ispconfig-user">Username</label>
                <input type="text" id="ispconfig-user" name="ispconfig_user" />
            </p>
            <p>
                <label for="ispconfig-pass">Password</label>
                <input type="password" id="ispconfig-pass" name="ispconfig_pass" />
            </p>
        </div>

        <h3>Robot API (Webtropia/WIIT)</h3>
        <p>
            <input type="checkbox" id="robot-enabled" name="robot_enabled" value="yes" />
            <label for="robot-enabled">Enable Robot API Provider</label>
        </p>
        <div class="robot-details">
            <p>
                <label for="robot-url">API URL</label>
                <input type="text" id="robot-url" name="robot_url" />
            </p>
            <p>
                <label for="robot-user">Username</label>
                <input type="text" id="robot-user" name="robot_user" />
            </p>
            <p>
                <label for="robot-pass">Password</label>
                <input type="password" id="robot-pass" name="robot_pass" />
            </p>
        </div>

        <h3>Easyname</h3>
        <p>
            <input type="checkbox" id="easyname-enabled" name="easyname_enabled" value="yes" />
            <label for="easyname-enabled">Enable Easyname Provider</label>
        </p>
        <div class="easyname-details">
            <p>
                <label for="easyname-url">API URL</label>
                <input type="text" id="easyname-url" name="easyname_url" />
            </p>
            <p>
                <label for="easyname-user">Username</label>
                <input type="text" id="easyname-user" name="easyname_user" />
            </p>
            <p>
                <label for="easyname-key">API Key</label>
                <input type="password" id="easyname-key" name="easyname_key" />
            </p>
        </div>

        <h3>Lookups</h3>
        <p>
            <input type="checkbox" id="rdap-enabled" name="rdap_enabled" value="yes" />
            <label for="rdap-enabled">Enable generic RDAP lookup</label>
        </p>
        <p>
            <input type="checkbox" id="cctld-lookup-enabled" name="cctld_lookup_enabled" value="yes" />
            <label for="cctld-lookup-enabled">Enable ccTLD-specific lookup (.at, .de)</label>
        </p>

        <p>
            <button id="save-settings-btn" type="submit">Save Settings</button>
            <span id="save-status"></span>
        </p>
    </form>
</div>
