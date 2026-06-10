<?php

declare(strict_types=1);

namespace OCA\DomainManager\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IUserSession;

class SettingsController extends Controller
{
    private $config;

    private $userSession;

    public function __construct($appName, IRequest $request, IConfig $config, IUserSession $userSession)
    {
        parent::__construct($appName, $request);
        $this->config = $config;
        $this->userSession = $userSession;
    }

    /**
     * @NoAdminRequired
     */
    public function getSettings()
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
        return new DataResponse($settings);
    }

    /**
     * @NoAdminRequired
     */
    public function setSettings()
    {
        $userId = $this->userSession->getUser()->getUID();

        $backend = $this->request->getParam('backend');
        $remote_url = $this->request->getParam('remote_url');
        $cloudflare_token = $this->request->getParam('cloudflare_token');
        $ispconfig_enabled = $this->request->getParam('ispconfig_enabled');
        $ispconfig_url = $this->request->getParam('ispconfig_url');
        $ispconfig_user = $this->request->getParam('ispconfig_user');
        $ispconfig_pass = $this->request->getParam('ispconfig_pass');
        $robot_enabled = $this->request->getParam('robot_enabled');
        $robot_url = $this->request->getParam('robot_url');
        $robot_user = $this->request->getParam('robot_user');
        $robot_pass = $this->request->getParam('robot_pass');
        $rdap_enabled = $this->request->getParam('rdap_enabled');
        $cctld_lookup_enabled = $this->request->getParam('cctld_lookup_enabled');
        $easyname_enabled = $this->request->getParam('easyname_enabled');
        $easyname_url = $this->request->getParam('easyname_url');
        $easyname_user = $this->request->getParam('easyname_user');
        $easyname_key = $this->request->getParam('easyname_key');
        $allowed_groups = $this->request->getParam('allowed_groups');
        $lookup_cache_ttl = $this->request->getParam('lookup_cache_ttl');
        $tax_rates = $this->request->getParam('tax_rates');
        $payment_periods = $this->request->getParam('payment_periods');

        if ($backend !== null) {
            $this->config->setUserValue($userId, $this->appName, 'backend', $backend);
        }
        if ($remote_url !== null) {
            $this->config->setUserValue($userId,$this->appName, 'remote_url', $remote_url);
        }
        if ($cloudflare_token !== null) {
            $this->config->setUserValue($userId,$this->appName, 'cloudflare_token', $cloudflare_token);
        }
        if ($ispconfig_enabled !== null) {
            $this->config->setUserValue($userId,$this->appName, 'ispconfig_enabled', $ispconfig_enabled);
        }
        if ($ispconfig_url !== null) {
            $this->config->setUserValue($userId,$this->appName, 'ispconfig_url', $ispconfig_url);
        }
        if ($ispconfig_user !== null) {
            $this->config->setUserValue($userId,$this->appName, 'ispconfig_user', $ispconfig_user);
        }
        if ($ispconfig_pass !== null && $ispconfig_pass !== '') {
            $this->config->setUserValue($userId,$this->appName, 'ispconfig_pass', $ispconfig_pass);
        }
        if ($robot_enabled !== null) {
            $this->config->setUserValue($userId,$this->appName, 'robot_enabled', $robot_enabled);
        }
        if ($robot_url !== null) {
            $this->config->setUserValue($userId,$this->appName, 'robot_url', $robot_url);
        }
        if ($robot_user !== null) {
            $this->config->setUserValue($userId,$this->appName, 'robot_user', $robot_user);
        }
        if ($robot_pass !== null && $robot_pass !== '') {
            $this->config->setUserValue($userId,$this->appName, 'robot_pass', $robot_pass);
        }
        if ($rdap_enabled !== null) {
            $this->config->setUserValue($userId,$this->appName, 'rdap_enabled', $rdap_enabled);
        }
        if ($cctld_lookup_enabled !== null) {
            $this->config->setUserValue($userId,$this->appName, 'cctld_lookup_enabled', $cctld_lookup_enabled);
        }
        if ($easyname_enabled !== null) {
            $this->config->setUserValue($userId,$this->appName, 'easyname_enabled', $easyname_enabled);
        }
        if ($easyname_url !== null) {
            $this->config->setUserValue($userId,$this->appName, 'easyname_url', $easyname_url);
        }
        if ($easyname_user !== null) {
            $this->config->setUserValue($userId,$this->appName, 'easyname_user', $easyname_user);
        }
        if ($easyname_key !== null && $easyname_key !== '') {
            $this->config->setUserValue($userId,$this->appName, 'easyname_key', $easyname_key);
        }
        if ($allowed_groups !== null) {
            $this->config->setUserValue($userId,$this->appName, 'allowed_groups', trim((string)$allowed_groups));
        }
        if ($lookup_cache_ttl !== null) {
            // sanitize: store as integer string, fallback will be applied when reading
            $this->config->setUserValue($userId,$this->appName, 'lookup_cache_ttl', (string)(int)$lookup_cache_ttl);
        }
        if ($tax_rates !== null) {
            $this->config->setUserValue($userId,$this->appName, 'tax_rates', $tax_rates);
        }
        if ($payment_periods !== null) {
            $this->config->setUserValue($userId,$this->appName, 'payment_periods', $payment_periods);
        }

        return new DataResponse(['status' => 'Settings saved']);
    }
}
