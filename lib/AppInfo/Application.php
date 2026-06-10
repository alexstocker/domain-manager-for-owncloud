<?php

declare(strict_types=1);

namespace OCA\DomainManager\AppInfo;

use OCA\DomainManager\Controller\SettingsController;
use OCA\DomainManager\Service\Lookup\CountryCodeTldLookupService;
use OCA\DomainManager\Settings\Personal;
use OCA\DomainManager\Settings\PersonalSection;
use OCP\AppFramework\App;
use OCA\DomainManager\Controller\PageController;
use OCA\DomainManager\Service\DomainService;
use OCA\DomainManager\Service\Lookup\LookupServiceFacade;
use OCA\DomainManager\Service\Lookup\RdapLookupService;
use OCA\DomainManager\Db\DbDomainRepository;
use OCA\DomainManager\Provider\EasynameDomainRepository;
use OCA\DomainManager\Provider\RemoteDomainRepository;
use OCA\DomainManager\Provider\ISPConfigDomainRepository;
use OCA\DomainManager\Provider\CloudflareDomainRepository;
use OCA\DomainManager\Provider\RobotApiDomainRepository;
use OCA\DomainManager\Db\DomainProviderManager;
use OCP\Settings\ISettings;

class Application extends App
{
    public function __construct(array $urlParams = [])
    {
        parent::__construct('domain_manager', $urlParams);

        $container = $this->getContainer();
        $server = $container->getServer();

        $container->registerService('PersonalSettings', function ($c) use ($server) {
            return new Personal(
                $server->getConfig(),
                $server->getUserSession()
            );
        });

        $container->registerAlias(ISettings::class, 'PersonalSettings');

        $container->registerService(PersonalSection::class, function ($c) use ($server) {
            return new PersonalSection(
                $server->getL10N('domain_manager')
            );
        });

        $container->registerService('DomainProviderManager', function ($c) use ($server) {
            $config = $server->getConfig();
            $manager = new DomainProviderManager();
            $user = $server->getUserSession()->getUser();
            $userId = $user ? $user->getUID() : '';

            $backendType = $config->getUserValue($userId, 'domain_manager', 'backend', 'local');
            if ($backendType === 'remote') {
                $apiUrl = $config->getUserValue($userId, 'domain_manager', 'remote_url', 'http://localhost:8080');
                $storageRepo = new RemoteDomainRepository(
                    $server->getHTTPClientService(),
                    $apiUrl
                );
            } else {
                $storageRepo = new DbDomainRepository(
                    $server->getDatabaseConnection()
                );
            }
            $manager->setStorageRepository($storageRepo);

            $cloudflareToken = $config->getUserValue($userId, 'domain_manager', 'cloudflare_token', '');
            if ($cloudflareToken !== '') {
                $manager->registerProvider('cloudflare', 'Cloudflare', new CloudflareDomainRepository(
                    $server->getHTTPClientService(),
                    $cloudflareToken
                ), [
                    ['name' => 'api_token', 'label' => 'API Token', 'type' => 'password', 'default' => $cloudflareToken]
                ]);
            }

            $robotEnabled = $config->getUserValue($userId, 'domain_manager', 'robot_enabled', 'no');
            if ($robotEnabled === 'yes') {
                $apiUrl = $config->getUserValue($userId, 'domain_manager', 'robot_url', '');
                $user = $config->getUserValue($userId, 'domain_manager', 'robot_user', '');
                $pass = $config->getUserValue($userId, 'domain_manager', 'robot_pass', '');
                $manager->registerProvider('robot', 'Robot API (Webtropia/WIIT)', new RobotApiDomainRepository(
                    $server->getHTTPClientService(),
                    $apiUrl,
                    $user,
                    $pass
                ), [
                    ['name' => 'api_url', 'label' => 'API URL', 'type' => 'text', 'default' => $apiUrl],
                    ['name' => 'username', 'label' => 'Username', 'type' => 'text', 'default' => $user],
                    ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'default' => $pass]
                ]);
            }

            $ispconfigEnabled = $config->getUserValue($userId, 'domain_manager', 'ispconfig_enabled', 'no');
            if ($ispconfigEnabled === 'yes') {
                $apiUrl = $config->getUserValue($userId, 'domain_manager', 'ispconfig_url', '');
                $user = $config->getUserValue($userId, 'domain_manager', 'ispconfig_user', '');
                $pass = $config->getUserValue($userId, 'domain_manager', 'ispconfig_pass', '');
                $manager->registerProvider('ispconfig', 'ISPConfig', new ISPConfigDomainRepository(
                    $server->getHTTPClientService(),
                    $apiUrl,
                    $user,
                    $pass
                ), [
                    ['name' => 'api_url', 'label' => 'API URL', 'type' => 'text', 'default' => $apiUrl],
                    ['name' => 'username', 'label' => 'Username', 'type' => 'text', 'default' => $user],
                    ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'default' => $pass]
                ]);
            }

            $easynameEnabled = $config->getUserValue($userId, 'domain_manager', 'easyname_enabled', 'no');
            if ($easynameEnabled === 'yes') {
                $apiUrl = $config->getUserValue($userId, 'domain_manager', 'easyname_url', 'https://api.easyname.com');
                $user = $config->getUserValue($userId, 'domain_manager', 'easyname_user', '');
                $key = $config->getUserValue($userId, 'domain_manager', 'easyname_key', '');
                $authSalt = $config->getUserValue($userId, 'domain_manager', 'easyname_auth_salt', '');
                $signingSalt = $config->getUserValue($userId, 'domain_manager', 'easyname_signing_salt', '');
                $manager->registerProvider('easyname', 'Easyname', new EasynameDomainRepository(
                    $server->getHTTPClientService(),
                    $apiUrl,
                    $user,
                    $key,
                    $authSalt !== '' ? $authSalt : null,
                    $signingSalt !== '' ? $signingSalt : null
                ), [
                    ['name' => 'api_url', 'label' => 'API URL', 'type' => 'text', 'default' => $apiUrl],
                    ['name' => 'username', 'label' => 'Username', 'type' => 'text', 'default' => $user],
                    ['name' => 'api_key', 'label' => 'API Key', 'type' => 'password', 'default' => $key],
                    ['name' => 'auth_salt', 'label' => 'Authentication Salt', 'type' => 'password', 'default' => $authSalt],
                    ['name' => 'signing_salt', 'label' => 'Signing Salt', 'type' => 'password', 'default' => $signingSalt]
                ]);
            }

            return $manager;
        });

        $container->registerService('LookupServiceFacade', function ($c) use ($server) {
            $facade = new LookupServiceFacade();
            $config = $server->getConfig();
            $user = $server->getUserSession()->getUser();
            $userId = $user ? $user->getUID() : '';

            // Register specific services first
            $cctldEnabled = $config->getUserValue($userId, 'domain_manager', 'cctld_lookup_enabled', 'no');
            if ($cctldEnabled === 'yes') {
                $facade->registerService('cctld', new CountryCodeTldLookupService(
                    $server->getHTTPClientService()
                ));
            }

            // Register generic fallback service last
            $rdapEnabled = $config->getUserValue($userId,'domain_manager', 'rdap_enabled', 'no');
            if ($rdapEnabled === 'yes') {
                $facade->registerService('rdap', new RdapLookupService(
                    $server->getHTTPClientService()
                ));
            }

            return $facade;
        });

        $container->registerService('OCA\DomainManager\Db\IDomainRepository', function ($c) {
            return $c->query('DomainProviderManager')->getStorageRepository();
        });

        $container->registerService('DomainService', function ($c) {
            return new DomainService(
                $c->query('DomainProviderManager')
            );
        });

        $container->registerService('PageController', function ($c) {
            return new PageController(
                $c->query('AppName'),
                $c->query('Request'),
                $c->query('DomainService'),
                $c->query('LookupServiceFacade'),
                $this->getContainer()->getServer()->getConfig(),
                $this->getContainer()->getServer()->getUserSession(),
                $this->getContainer()->getServer()->getGroupManager()
            );
        });

        $container->registerService('SettingsController', function ($c) use ($server) {
            return new SettingsController(
                $c->query('AppName'),
                $c->query('Request'),
                $server->getConfig(),
                $server->getUserSession()
            );
        });
    }
}
