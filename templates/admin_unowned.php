<?php
\OCP\Util::addScript('domain_manager', 'admin_unowned');
?>

<div id="domain-manager-unowned" class="section">
    <h2>Unowned Domains</h2>
    <p>Admins can assign or delete domains that have no owner.</p>
    <table id="unowned-domains-table" class="grid">
        <thead>
            <tr>
                <th>Domain</th>
                <th>Provider</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
