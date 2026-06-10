<?php

declare(strict_types=1);

namespace OCA\DomainManager\Controller;

use OCA\DomainManager\Service\DomainService;
use OCA\DomainManager\Service\Lookup\LookupServiceFacade;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IUserSession;

class PageController extends Controller
{
    private $domainService;
    private $lookupServiceFacade;
    private $config;
    private $userSession;
    private $groupManager;
    private $isAdmin;

    public function __construct(
        $appName,
        IRequest $request,
        DomainService $domainService,
        LookupServiceFacade $lookupServiceFacade,
        IConfig $config,
        IUserSession $userSession,
        IGroupManager $groupManager
    ) {
        parent::__construct($appName, $request);
        $this->domainService = $domainService;
        $this->lookupServiceFacade = $lookupServiceFacade;
        $this->config = $config;
        $this->userSession = $userSession;
        $this->groupManager = $groupManager;
        $this->isAdmin = $this->groupManager->isAdmin(($this->userSession->getUser())->getUID()) ?? false;
    }

    /**
     * @NoCSRFRequired
     * @NoAdminRequired
     */
    public function index()
    {
        return new TemplateResponse('domain_manager', 'main');
    }

    /**
     * @NoAdminRequired
     */
    public function listDomains()
    {
        if (!$this->checkAccess()) {
            return new DataResponse(['error' => 'Access denied'], 403);
        }
        try {
            $user = $this->userSession->getUser();
            $uid = $user ? $user->getUID() : null;

            $domains = $this->domainService->getAllForUser($uid);
            return new DataResponse($domains);
        } catch (\Exception $e) {
            return new DataResponse(['error' => 'Could not list domains: ' . $e->getMessage()], 500);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function listProviders()
    {
        if (!$this->checkAccess()) {
            return new DataResponse(['error' => 'Access denied'], 403);
        }
        try {
            $providers = $this->domainService->getProviders();
            return new DataResponse($providers);
        } catch (\Exception $e) {
            return new DataResponse(['error' => 'Could not list providers: ' . $e->getMessage()], 500);
        }
    }

    private function isValidDomain($domain)
    {
        if (!isset($domain) || trim($domain) === '') {
            return false;
        }
        if (strpos($domain, '.') === false || substr($domain, -1) === '.') {
            return false;
        }
        return filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false;
    }

    /**
     * @NoAdminRequired
     */
    public function addDomain()
    {
        if (!$this->checkAccess()) {
            return new DataResponse(['error' => 'Access denied'], 403);
        }
        $data = $this->request->getParams();
        $domain = isset($data['domain']) ? trim($data['domain']) : '';
        $providerId = $data['providerId'] ?? 'none';
        $configuration = $data['configuration'] ?? [];

        if (!$this->isValidDomain($domain)) {
            return new DataResponse(['error' => 'Invalid domain name or TLD missing'], 400);
        }
        try {
            $user = $this->userSession->getUser();
            $uid = $user ? $user->getUID() : null;

            $existingForOwner = $this->domainService->findByDomainForOwner($domain, $uid);
            if ($existingForOwner !== null) {
                return new DataResponse(['error' => 'Domain already exists'], 400);
            }

            $existingOrphaned = $this->domainService->findByDomainForOwner($domain, null);
            if ($existingOrphaned !== null && !$this->isAdmin) {
                return new DataResponse(['error' => 'Domain already exists (orphaned) - contact admin'], 400);
            }

            $user = $this->userSession->getUser();
            if ($user !== null) {
                $configuration['owner'] = $user->getUID();
            }
            $this->domainService->add($domain, $providerId, $configuration);
            
            $newDomain = $this->domainService->findByDomainForOwner($domain, $uid);
            return new DataResponse($newDomain);
        } catch (\Exception $e) {
            return new DataResponse(['error' => 'Could not add domain: ' . $e->getMessage()], 500);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function updateDomain($id)
    {
        if (!$this->checkAccess()) {
            return new DataResponse(['error' => 'Access denied'], 403);
        }
        $domain = $this->request->getParam('domain');
        $providerId = $this->request->getParam('providerId', 'none');
        $configuration = $this->request->getParam('configuration', []);
        $domain = $domain ? trim($domain) : '';
        if (!$this->isValidDomain($domain)) {
            return new DataResponse(['error' => 'Invalid domain name or TLD missing'], 400);
        }

        $existingRec = $this->domainService->findById((int)$id);
        $user = $this->userSession->getUser();
        $uid = $user ? $user->getUID() : null;

        $owner = $existingRec['owner'] ?? null;
        if ($owner === null && $existingRec && isset($existingRec['configuration']) && is_array($existingRec['configuration'])) {
            $owner = $existingRec['configuration']['owner'] ?? null;
        }
        if (!$this->isAdmin && $owner !== $uid) {
            return new DataResponse(['error' => 'Forbidden'], 403);
        }
        try {

            $existingForOwner = $this->domainService->findByDomainForOwner($domain, $uid);
            if ($existingForOwner !== null && (int)$existingForOwner['id'] !== (int)$id) {
                return new DataResponse(['error' => 'Domain already exists'], 400);
            }

            $existingOrphaned = $this->domainService->findByDomainForOwner($domain, null);
            if ($existingOrphaned !== null && !$this->isAdmin && (int)$existingOrphaned['id'] !== (int)$id) {
                return new DataResponse(['error' => 'Domain already exists (orphaned) - contact admin'], 400);
            }

            if (isset($configuration['owner'])) {
                unset($configuration['owner']);
            }
            $this->domainService->update((int)$id, $domain, $providerId, $configuration);
            return new DataResponse(['status' => 'Domain updated']);
        } catch (\Exception $e) {
            return new DataResponse(['error' => 'Could not update domain: ' . $e->getMessage()], 500);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function deleteDomain($id)
    {
        if (!$this->checkAccess()) {
            return new DataResponse(['error' => 'Access denied'], 403);
        }
        $providerId = $this->request->getParam('providerId', 'none');

        $existingRec = $this->domainService->findById((int)$id);
        $user = $this->userSession->getUser();
        $uid = $user ? $user->getUID() : null;

        $owner = $existingRec['owner'] ?? null;
        if ($owner === null
            && $existingRec
            && isset($existingRec['configuration'])
            && is_array($existingRec['configuration'])
        ) {
            $owner = $existingRec['configuration']['owner'] ?? null;
        }
        if (!$this->isAdmin && $owner !== $uid) {
            return new DataResponse(['error' => 'Forbidden'], 403);
        }
        try {
            $this->domainService->delete((int)$id, $providerId);
            return new DataResponse(['status' => 'Domain deleted']);
        } catch (\Exception $e) {
            return new DataResponse(['error' => 'Could not delete domain: ' . $e->getMessage()], 500);
        }
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function lookupForce($domain, $force = null)
    {
        return $this->lookup($domain, $force);
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function lookup($domain, $force = null)
    {
        if (!$this->checkAccess()) {
            return new DataResponse(['error' => 'Access denied'], 403);
        }
        try {
            $existingDomain = $this->domainService->findByDomain($domain);

            if ($this->forceLookUp($existingDomain, $force)) {
                $data = $this->lookupServiceFacade->lookup($domain);

                if ($existingDomain) {
                    $this->domainService->updateLookupData((int)$existingDomain['id'], $data);
                }

                return new DataResponse($data);
            }

            return new DataResponse($existingDomain['last_lookup_data']);
        } catch (\Exception $e) {
            return new DataResponse(['error' => 'Lookup failed: ' . $e->getMessage()], 500);
        }
    }

    private function forceLookUp($existingDomain, $force): bool
    {
        if ($existingDomain
            && array_key_exists('last_lookup_data', $existingDomain)
            && array_key_exists('last_lookup_time', $existingDomain)
        ) {
            if ($this->isDomainDataCacheExpired($existingDomain) || $force) {
                return true;
            }
        }
        return false;
    }

    private function isDomainDataCacheExpired($existingDomain): bool
    {
        $cacheAge = time() - (int)$existingDomain['last_lookup_time'];
        $ttl = (int)$this->config->getAppValue($this->appName, 'lookup_cache_ttl', '86400');
        if ($ttl < 1) {
            $ttl = 86400;
        }
        if ($cacheAge > $ttl) {
            return true;
        }
        return false;
    }


    /**
     * GET domain details by id
     * @NoAdminRequired
     */
    public function getDomain($id): DataResponse
    {
        if (!$this->checkAccess()) {
            return new DataResponse(['error' => 'Access denied'], 403);
        }
        try {
            $domain = $this->domainService->findById((int)$id);
            if ($domain === null) {
                return new DataResponse(['error' => 'Domain not found'], 404);
            }

            $user = $this->userSession->getUser();
            $uid = $user ? $user->getUID() : null;

            $owner = $domain['owner'] ?? null;
            if ($owner === null && isset($domain['configuration']['owner'])) {
                $owner = $domain['configuration']['owner'];
            }
            if (!$this->isAdmin && $owner !== null && $owner !== $uid) {
                return new DataResponse(['error' => 'Forbidden'], 403);
            }

            $providers = $this->domainService->getProviders();
            $providerMeta = null;
            foreach ($providers as $p) {
                if (isset($p['id']) && $p['id'] === ($domain['provider'] ?? 'none')) {
                    $providerMeta = $p;
                    break;
                }
            }

            $taxRates = $this->config->getAppValue($this->appName, 'tax_rates', '0,10,20');
            $paymentPeriods = $this->config->getAppValue($this->appName, 'payment_periods', 'monthly,yearly');

            $result = [
                'domain' => $domain,
                'providerMeta' => $providerMeta,
                'taxRates' => explode(',', $taxRates),
                'paymentPeriods' => explode(',', $paymentPeriods)
            ];

            return new DataResponse($result);
        } catch (\Exception $e) {
            return new DataResponse(['error' => 'Could not fetch domain details: ' . $e->getMessage()], 500);
        }
    }

    /**
     * List orphaned domains (admin only)
     *
     * @NoCSRFRequired
     * @NoAdminRequired
     */
    public function listOrphanedDomains()
    {
//        $user = $this->userSession->getUser();
//        $isAdmin = ($user && method_exists($user, 'isAdmin') && $user->isAdmin());
//        $isAdmin = $this->groupManager->isAdmin($user->getUID());

        if (!$this->isAdmin) {
            return new DataResponse(['error' => 'Access denied'], 403);
        }
        try {
            $rows = $this->domainService->getOrphaned();
            return new DataResponse($rows);
        } catch (\Exception $e) {
            return new DataResponse(['error' => 'Could not list orphaned domains: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Assign an owner to a domain (admin only)
     */
    public function assignOwner($id)
    {
//        $user = $this->userSession->getUser();
//        $isAdmin = ($user && method_exists($user, 'isAdmin') && $user->isAdmin());
        if (!$this->isAdmin) {
            return new DataResponse(['error' => 'Access denied'], 403);
        }
        $owner = $this->request->getParam('owner');
        if ($owner === null) {
            return new DataResponse(['error' => 'Owner parameter missing'], 400);
        }
        try {
            // validate target user exists? We'll rely on ownCloud users API in a future step. For now set owner.
            $this->domainService->setOwner((int)$id, $owner === '' ? null : $owner);
            return new DataResponse(['status' => 'Owner assigned']);
        } catch (\Exception $e) {
            return new DataResponse(['error' => 'Could not assign owner: ' . $e->getMessage()], 500);
        }
    }

    private function checkAccess(): bool
    {
        // If no group restriction configured, allow access
        $allowed = (string)$this->config->getAppValue($this->appName, 'allowed_groups', '');
        $allowed = trim($allowed);
        if ($allowed === '') {
            return true;
        }

        $user = $this->userSession->getUser();
        if ($user === null) {
            return false;
        }
        $uid = $user->getUID();

        $groups = array_map('trim', explode(',', $allowed));
        foreach ($groups as $g) {
            if ($g === '') {
                continue;
            }
            // Try several possible group manager APIs
            if (method_exists($this->groupManager, 'isInGroup')) {
                try {
                    if ($this->groupManager->isInGroup($uid, $g)) {
                        return true;
                    }
                } catch (\Throwable $e) {
                    // ignore and try other methods
                }
            }
            if (method_exists($this->groupManager, 'getUserGroups')) {
                try {
                    $userGroups = $this->groupManager->getUserGroups($user);
                     if (is_array($userGroups) && in_array($g, $userGroups, true)) {
                         return true;
                     }
                 } catch (\Throwable $e) {
                 }
             }
             // fallback - attempt to get the group and check membership via members list
             if (method_exists($this->groupManager, 'get')) {
                 try {
                     $groupObj = $this->groupManager->get($g);
                     if ($groupObj !== null && method_exists($groupObj, 'inGroup')) {
                        if ($groupObj->inGroup($user)) {
                             return true;
                         }
                     }
                 } catch (\Throwable $e) {
                 }
             }
        }
        return false;
    }
}
