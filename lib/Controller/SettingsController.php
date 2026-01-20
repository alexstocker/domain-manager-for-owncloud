<?php

declare(strict_types=1);

namespace OCA\DomainManager\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IConfig;
use OCP\IRequest;

class SettingsController extends Controller
{
    private $config;

    public function __construct($appName, IRequest $request, IConfig $config)
    {
        parent::__construct($appName, $request);
        $this->config = $config;
    }

    /**
     * @NoAdminRequired
     */
    public function getSettings()
    {
        $settings = [
            'backend' => $this->config->getAppValue($this->appName, 'backend', 'local'),
            'remote_url' => $this->config->getAppValue($this->appName, 'remote_url', ''),
            'cloudflare_token' => $this->config->getAppValue($this->appName, 'cloudflare_token', ''),
            'ispconfig_enabled' => $this->config->getAppValue($this->appName, 'ispconfig_enabled', 'no'),
            'ispconfig_url' => $this->config->getAppValue($this->appName, 'ispconfig_url', ''),
            'ispconfig_user' => $this->config->getAppValue($this->appName, 'ispconfig_user', ''),
            'robot_enabled' => $this->config->getAppValue($this->appName, 'robot_enabled', 'no'),
            'robot_url' => $this->config->getAppValue($this->appName, 'robot_url', ''),
            'robot_user' => $this->config->getAppValue($this->appName, 'robot_user', ''),
            'rdap_enabled' => $this->config->getAppValue($this->appName, 'rdap_enabled', 'no'),
        ];
        return new DataResponse($settings);
    }

    public function setSettings()
    {
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

        if ($backend !== null) {
            $this->config->setAppValue($this->appName, 'backend', $backend);
        }
        if ($remote_url !== null) {
            $this->config->setAppValue($this->appName, 'remote_url', $remote_url);
        }
        if ($cloudflare_token !== null) {
            $this->config->setAppValue($this->appName, 'cloudflare_token', $cloudflare_token);
        }
        if ($ispconfig_enabled !== null) {
            $this->config->setAppValue($this->appName, 'ispconfig_enabled', $ispconfig_enabled);
        }
        if ($ispconfig_url !== null) {
            $this->config->setAppValue($this->appName, 'ispconfig_url', $ispconfig_url);
        }
        if ($ispconfig_user !== null) {
            $this->config->setAppValue($this->appName, 'ispconfig_user', $ispconfig_user);
        }
        if ($ispconfig_pass !== null) {
            $this->config->setAppValue($this->appName, 'ispconfig_pass', $ispconfig_pass);
        }
        if ($robot_enabled !== null) {
            $this->config->setAppValue($this->appName, 'robot_enabled', $robot_enabled);
        }
        if ($robot_url !== null) {
            $this->config->setAppValue($this->appName, 'robot_url', $robot_url);
        }
        if ($robot_user !== null) {
            $this->config->setAppValue($this->appName, 'robot_user', $robot_user);
        }
        if ($robot_pass !== null) {
            $this->config->setAppValue($this->appName, 'robot_pass', $robot_pass);
        }
        if ($rdap_enabled !== null) {
            $this->config->setAppValue($this->appName, 'rdap_enabled', $rdap_enabled);
        }

        return new DataResponse(['status' => 'Settings saved']);
    }
}
