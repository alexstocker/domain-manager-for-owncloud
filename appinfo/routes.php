<?php

declare(strict_types=1);

return [
    'routes' => [
        ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
        ['name' => 'page#listDomains', 'url' => '/api/domains', 'verb' => 'GET'],
        ['name' => 'page#listProviders', 'url' => '/api/providers', 'verb' => 'GET'],
        ['name' => 'page#addDomain', 'url' => '/api/domains/add', 'verb' => 'POST'],
        ['name' => 'page#updateDomain', 'url' => '/api/domains/{id}', 'verb' => 'PUT'],
        ['name' => 'page#deleteDomain', 'url' => '/api/domains/{id}', 'verb' => 'DELETE'],
        ['name' => 'page#rdapLookup', 'url' => '/api/rdap/{domain}', 'verb' => 'GET'],
        ['name' => 'settings#getSettings', 'url' => '/api/settings', 'verb' => 'GET'],
        ['name' => 'settings#setSettings', 'url' => '/api/settings', 'verb' => 'POST']
    ]
];
