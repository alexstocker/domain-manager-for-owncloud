<?php
style('domain_manager', 'style');
script('domain_manager', 'domainmanager');
?>

<div id="app-navigation">
    <ul id="navigation-stats"></ul>
</div>

<div id="app-content">
    <div id="controls">
        <div id="add-domain-wrapper">
            <form id="add-domain-form">
                <input type="text" name="domain" id="domain-input-field" placeholder="Enter domain" required>
                <select name="providerId" id="provider-select"></select>
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
                <th id="headerPrice"><span>Price</span></th>
                <th id="headerExpiration"><span>Expiration</span></th>
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
            <h3>Billing</h3>
            <dl>
                <dt>Price</dt>
                <dd><input type="number" id="detail-price" step="0.01" min="0" placeholder="0.00"></dd>
                <dt>Tax Rate</dt>
                <dd>
                    <select id="detail-tax-rate">
                        <option value="0">0%</option>
                    </select>
                </dd>
                <dt>Payment Period</dt>
                <dd>
                    <select id="detail-payment-period">
                        <option value="">Select...</option>
                    </select>
                </dd>
            </dl>
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
        <!-- Inline confirmation UI shown when user clicks Delete -->
        <section id="detail-delete-confirm" class="drawer-section delete-confirm hidden" aria-hidden="true">
            <p>Are you sure you want to permanently delete this domain?</p>
            <div class="delete-confirm-actions">
                <button id="detail-delete-confirm-button" class="button destructive">Yes, delete</button>
                <button id="detail-delete-cancel-button" class="button">Cancel</button>
            </div>
        </section>
    </div>
</div>
