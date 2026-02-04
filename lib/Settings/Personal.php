<?php

declare(strict_types=1);

namespace OCA\DomainManager\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IUserSession;
use OCP\Settings\ISettings;

class Personal implements ISettings
{
    private IConfig $config;
    private IUserSession $userSession;

    public function __construct(IConfig $config, IUserSession $userSession)
    {
        $this->config = $config;
        $this->userSession = $userSession;
    }

    public function getPanel(): TemplateResponse
    {
        $userId = $this->userSession->getUser()->getUID();
        $appName = 'domain_manager';

        $settings = [
            'backend' => $this->config->getUserValue($userId, $appName, 'backend', 'local'),
            'remote_url' => $this->config->getUserValue($userId, $appName, 'remote_url', ''),
            'cloudflare_token' => $this->config->getUserValue($userId, $appName, 'cloudflare_token', ''),
            'ispconfig_enabled' => $this->config->getUserValue($userId, $appName, 'ispconfig_enabled', 'no'),
            'ispconfig_url' => $this->config->getUserValue($userId, $appName, 'ispconfig_url', ''),
            'ispconfig_user' => $this->config->getUserValue($userId, $appName, 'ispconfig_user', ''),
            'robot_enabled' => $this->config->getUserValue($userId, $appName, 'robot_enabled', 'no'),
            'robot_url' => $this->config->getUserValue($userId, $appName, 'robot_url', ''),
            'robot_user' => $this->config->getUserValue($userId, $appName, 'robot_user', ''),
            'rdap_enabled' => $this->config->getUserValue($userId, $appName, 'rdap_enabled', 'no'),
            'cctld_lookup_enabled' => $this->config->getUserValue($userId, $appName, 'cctld_lookup_enabled', 'no'),
            'easyname_enabled' => $this->config->getUserValue($userId, $appName, 'easyname_enabled', 'no'),
            'easyname_url' => $this->config->getUserValue($userId, $appName, 'easyname_url', ''),
            'easyname_user' => $this->config->getUserValue($userId, $appName, 'easyname_user', ''),
            'easyname_key' => $this->config->getUserValue($userId, $appName, 'easyname_key', ''),
            'lookup_cache_ttl' => $this->config->getUserValue($userId, $appName, 'lookup_cache_ttl', '86400'),
            'tax_rates' => $this->config->getUserValue($userId, $appName, 'tax_rates', '0,10,20'),
            'payment_periods' => $this->config->getUserValue($userId, $appName, 'payment_periods', 'monthly,yearly'),
        ];

        return new TemplateResponse($appName, 'personal', $settings);
    }

    public function getSectionID(): string
    {
        return 'additional';
    }

    public function getPriority(): int
    {
        return 10;
    }
}