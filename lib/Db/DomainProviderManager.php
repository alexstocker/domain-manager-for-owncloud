<?php

declare(strict_types=1);

namespace OCA\DomainManager\Db;

use OCA\DomainManager\Provider\IDomainProviderRepository;

class DomainProviderManager
{
    private $providers = [];
    private $storageRepository;

    public function setStorageRepository(IDomainRepository $repository)
    {
        $this->storageRepository = $repository;
    }

    public function getStorageRepository(): IDomainRepository
    {
        return $this->storageRepository;
    }

    public function registerProvider(string $id, string $name, IDomainProviderRepository $repository, array $configFields = [])
    {
        $this->providers[$id] = [
            'name' => $name,
            'repository' => $repository,
            'configFields' => $configFields
        ];
    }

    public function getProviders(): array
    {
        $result = [];
        foreach ($this->providers as $id => $data) {
            $result[] = [
                'id' => $id,
                'name' => $data['name'],
                'configFields' => $data['configFields'] ?? []
            ];
        }
        return $result;
    }

    public function getRepository(string $id): ?IDomainProviderRepository
    {
        return isset($this->providers[$id]) ? $this->providers[$id]['repository'] : null;
    }
}
