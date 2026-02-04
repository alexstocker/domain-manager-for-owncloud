<?php

declare(strict_types=1);

namespace OCA\DomainManager;

use OCP\AppFramework\App;
use OCP\Util;

\OC::$server->getNavigationManager()->add(function () {
    $urlGenerator = \OC::$server->getURLGenerator();
    $l = \OC::$server->getL10N('domain_manager');
    return [
        'id' => 'domain_manager',
        'order' => 10,
        'href' => $urlGenerator->linkToRoute('domain_manager.page.index'),
        'icon' => $urlGenerator->imagePath('domain_manager', 'app.svg'),
        'name' => $l->t('Domain Manager'),
    ];
});
