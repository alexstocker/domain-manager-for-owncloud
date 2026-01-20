<?php

declare(strict_types=1);

namespace OCA\DomainManager\Db;

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

    public function registerProvider(string $id, string $name, IDomainRepository $repository, array $configFields = [])
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

    public function getRepository(string $id): ?IDomainRepository
    {
        return isset($this->providers[$id]) ? $this->providers[$id]['repository'] : null;
    }
}
