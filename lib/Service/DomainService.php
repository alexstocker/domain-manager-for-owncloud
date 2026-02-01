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

    /**
     * Get all domains for a specific user. If $isAdmin is true, return all domains.
     */
    public function getAllForUser(?string $userId, bool $isAdmin = false): array
    {
        $repo = $this->providerManager->getStorageRepository();
        if ($isAdmin) {
            return $repo->findAll();
        }
        return $repo->findAllForOwner($userId);
    }

    public function findByDomain(string $domain): ?array
    {
        return $this->providerManager->getStorageRepository()->findByDomain($domain);
    }

    /**
     * Find a domain record for a given domain and owner (owner may be null for orphaned entries).
     */
    public function findByDomainForOwner(string $domain, ?string $owner): ?array
    {
        return $this->providerManager->getStorageRepository()->findByDomainAndOwner($domain, $owner);
    }

    /**
     * Return domains without owner (admin view)
     * @return array
     */
    public function getOrphaned(): array
    {
        return $this->providerManager->getStorageRepository()->findOrphaned();
    }

    /**
     * Set owner on a domain record
     */
    public function setOwner(int $id, ?string $owner): void
    {
        $this->providerManager->getStorageRepository()->setOwner($id, $owner);
    }

    public function findById(int $id): ?array
    {
        return $this->providerManager->getStorageRepository()->findById($id);
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

    public function updateLookupData(int $id, array $data): void
    {
        $this->providerManager->getStorageRepository()->updateLookupData($id, $data, time());
    }
}
