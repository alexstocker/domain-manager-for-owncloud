<?php

declare(strict_types=1);

namespace OCA\DomainManager\Service;

use OCA\DomainManager\Db\DomainProviderManager;

class DomainService
{
    private $providerManager;

    public function __construct(DomainProviderManager $providerManager)
    {
        $this->providerManager = $providerManager;
    }

    public function getProviders(): array
    {
        return $this->providerManager->getProviders();
    }

    public function getAll(): array
    {
        return $this->providerManager->getStorageRepository()->findAll();
    }

    public function findByDomain(string $domain): ?array
    {
        return $this->providerManager->getStorageRepository()->findByDomain($domain);
    }

    public function add(string $domain, string $providerId, array $configuration = []): void
    {
        $configuration['provider'] = $providerId;
        $this->providerManager->getStorageRepository()->insert($domain, $configuration);
    }

    public function update(int $id, string $domain, string $providerId, array $configuration = []): void
    {
        $configuration['provider'] = $providerId;
        $this->providerManager->getStorageRepository()->update($id, $domain, $configuration);
    }

    public function delete(int $id, string $providerId): void
    {
        $this->providerManager->getStorageRepository()->delete($id);
    }
}