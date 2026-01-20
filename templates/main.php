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