<?php

declare(strict_types=1);

namespace OCA\DomainManager\Controller;

use OCA\DomainManager\Service\DomainService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;

class PageController extends Controller
{
    private $domainService;
    private $rdapRepo;

    public function __construct($appName, IRequest $request, DomainService $domainService, $rdapRepo = null)
    {
        parent::__construct($appName, $request);
        $this->domainService = $domainService;
        $this->rdapRepo = $rdapRepo;
    }

    /**
     * @NoCSRFRequired
     * @NoAdminRequired
     */
    public function index()
    {
        return new TemplateResponse('domain_manager', 'main');
    }

    public function listDomains()
    {
        try {
            $domains = $this->domainService->getAll();
            return new DataResponse($domains);
        } catch (\Exception $e) {
            return new DataResponse(['error' => 'Could not list domains: ' . $e->getMessage()], 500);
        }
    }

    public function listProviders()
    {
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

    public function addDomain()
    {
        $data = $this->request->getParams();
        $domain = isset($data['domain']) ? trim($data['domain']) : '';
        $providerId = isset($data['providerId']) ? $data['providerId'] : 'none';
        $configuration = isset($data['configuration']) ? $data['configuration'] : [];

        if (!$this->isValidDomain($domain)) {
            return new DataResponse(['error' => 'Invalid domain name or TLD missing'], 400);
        }
        try {
            if ($this->domainService->findByDomain($domain) !== null) {
                return new DataResponse(['error' => 'Domain already exists'], 400);
            }
            $this->domainService->add($domain, $providerId, $configuration);
            return new DataResponse(['status' => 'Domain added']);
        } catch (\Exception $e) {
            return new DataResponse(['error' => 'Could not add domain: ' . $e->getMessage()], 500);
        }
    }

    public function updateDomain($id)
    {
        $domain = $this->request->getParam('domain');
        $providerId = $this->request->getParam('providerId', 'none');
        $configuration = $this->request->getParam('configuration', []);
        $domain = $domain ? trim($domain) : '';
        if (!$this->isValidDomain($domain)) {
            return new DataResponse(['error' => 'Invalid domain name or TLD missing'], 400);
        }
        try {
            $existing = $this->domainService->findByDomain($domain);
            if ($existing !== null && (int)$existing['id'] !== (int)$id) {
                return new DataResponse(['error' => 'Domain already exists'], 400);
            }
            $this->domainService->update((int)$id, $domain, $providerId, $configuration);
            return new DataResponse(['status' => 'Domain updated']);
        } catch (\Exception $e) {
            return new DataResponse(['error' => 'Could not update domain: ' . $e->getMessage()], 500);
        }
    }

    public function deleteDomain($id)
    {
        $providerId = $this->request->getParam('providerId', 'none');
        try {
            $this->domainService->delete((int)$id, $providerId);
            return new DataResponse(['status' => 'Domain deleted']);
        } catch (\Exception $e) {
            return new DataResponse(['error' => 'Could not delete domain: ' . $e->getMessage()], 500);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function rdapLookup($domain)
    {
        if (!$this->rdapRepo) {
            return new DataResponse(['error' => 'RDAP lookup is disabled'], 404);
        }
        try {
            $data = $this->rdapRepo->lookup($domain);
            return new DataResponse($data);
        } catch (\Exception $e) {
            return new DataResponse(['error' => 'RDAP lookup failed: ' . $e->getMessage()], 500);
        }
    }
}
