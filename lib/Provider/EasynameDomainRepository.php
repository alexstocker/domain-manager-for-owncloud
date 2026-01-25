<?php

declare(strict_types=1);

namespace OCA\DomainManager\Provider;

use OCP\Http\Client\IClientService;

class EasynameDomainRepository implements IDomainProviderRepository
{
    private $httpClient;
    private $apiUrl;
    private $apiUser;
    private $apiKey;

    public function __construct(IClientService $clientService, string $apiUrl, string $apiUser, string $apiKey)
    {
        $this->httpClient = $clientService->newClient();
        $this->apiUrl = $apiUrl;
        $this->apiUser = $apiUser;
        $this->apiKey = $apiKey;
        // TODO: Set up necessary headers, e.g., for authentication
    }

    public function findAll(): array
    {
        // TODO: Implement API call to fetch all domains from easyname.eu
        throw new \Exception("EasynameDomainRepository::findAll() is not implemented.");
    }

    public function findByDomain(string $domain): ?array
    {
        // TODO: Implement API call to find a specific domain
        throw new \Exception("EasynameDomainRepository::findByDomain() is not implemented.");
    }

    public function insert(string $domain, array $configuration = []): void
    {
        // TODO: Implement API call to create a domain
        throw new \Exception("EasynameDomainRepository::insert() is not implemented.");
    }

    public function update(int $id, string $domain, array $configuration = []): void
    {
        // TODO: Implement API call to update a domain
        throw new \Exception("EasynameDomainRepository::update() is not implemented.");
    }

    public function delete(int $id): void
    {
        // TODO: Implement API call to delete a domain
        throw new \Exception("EasynameDomainRepository::delete() is not implemented.");
    }

    public function findById(int $id): ?array
    {
        return null;
    }
}
