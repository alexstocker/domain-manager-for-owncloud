<?php

declare(strict_types=1);

/**
 * Admin settings page for domain_manager
 */

if (!defined('PHPUNIT_MAIN_METHOD')) {
    $template = new \OCP\Template('domain_manager', 'admin');
    return $template->fetchPage();
}
