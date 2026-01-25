<?php
style('domain_manager', 'style');
script('domain_manager', 'domainmanager');
?>


<div id="app-content">
    <div id="controls">
        <div id="add-domain-wrapper">
            <form id="add-domain-form">
                <input type="text" name="domain" id="domain-input-field" placeholder="Enter domain" required>
                <select name="providerId" id="provider-select"></select>
                <div id="provider-config-fields"></div>
                <button type="submit" class="button primary">Add Domain</button>
            </form>
        </div>
    </div>

    <div id="domain-manager-content">
        <table id="domains-table" class="with-filter">
            <thead>
            <tr>
                <th id="headerDomain" class="column-name"><span>Domain</span></th>
                <th id="headerProvider"><span>Provider</span></th>
                <th id="headerExpiration"><span>Expiration</span></th>
                <th id="headerCreated"><span>Created At</span></th>
                <th id="headerActions"><span>Actions</span></th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Right-side details drawer -->
<div id="domain-details-drawer" class="drawer hidden" aria-hidden="true" role="dialog" aria-label="Domain details">
    <div class="drawer-header">
        <h2 id="drawer-domain-name">Domain details</h2>
        <button id="drawer-close" class="button">Close</button>
    </div>
    <div class="drawer-content">
        <section class="drawer-section">
            <h3>Overview</h3>
            <dl>
                <dt>Domain</dt><dd id="detail-domain">-</dd>
                <dt>Provider</dt><dd id="detail-provider">-</dd>
                <dt>Created At</dt><dd id="detail-created">-</dd>
                <dt>Expiration</dt><dd id="detail-expiration">-</dd>
            </dl>
        </section>
        <section class="drawer-section">
            <h3>Configuration</h3>
            <pre id="detail-configuration">{}</pre>
        </section>
        <section class="drawer-section">
            <h3>Lookup Events</h3>
            <div id="detail-lookup-events">Loading...</div>
        </section>
        <section class="drawer-section drawer-actions">
            <button id="detail-refresh-lookup" class="button">Refresh Lookup</button>
            <button id="detail-update" class="button primary">Update</button>
            <button id="detail-delete" class="button destructive">Delete</button>
        </section>
    </div>
</div>
