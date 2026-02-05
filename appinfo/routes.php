<?php

declare(strict_types=1);

return [
    'routes' => [
        ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
        ['name' => 'page#listDomains', 'url' => '/api/domains', 'verb' => 'GET'],
        ['name' => 'page#listProviders', 'url' => '/api/providers', 'verb' => 'GET'],
        ['name' => 'page#addDomain', 'url' => '/api/domains/add', 'verb' => 'POST'],
        ['name' => 'page#listOrphanedDomains', 'url' => '/api/domains/orphaned', 'verb' => 'GET'],
        ['name' => 'page#getDomain', 'url' => '/api/domains/{id}', 'verb' => 'GET'],
        ['name' => 'page#updateDomain', 'url' => '/api/domains/{id}', 'verb' => 'PUT'],
        ['name' => 'page#deleteDomain', 'url' => '/api/domains/{id}', 'verb' => 'DELETE'],
        ['name' => 'page#lookup', 'url' => '/api/lookup/{domain}', 'verb' => 'GET'],
        ['name' => 'page#lookupForce', 'url' => '/api/lookup/{domain}/{force}', 'verb' => 'GET'],
        ['name' => 'page#assignOwner', 'url' => '/api/domains/{id}/assign', 'verb' => 'POST'],
//        ['name' => 'settings#getSettings', 'url' => '/api/settings', 'verb' => 'GET'],
        ['name' => 'settings#setSettings', 'url' => '/api/settings', 'verb' => 'POST']
    ]
];
