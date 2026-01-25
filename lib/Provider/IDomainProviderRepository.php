<?php

declare(strict_types=1);

namespace OCA\DomainManager\Provider;

interface IDomainProviderRepository
{
    /**
     * Return a list of provider-level domains (read-only operations expected)
     * @return array
     */
    public function findAll(): array;

    /**
     * Find a single domain from provider data
     * @param string $domain
     * @return array|null
     */
    public function findByDomain(string $domain): ?array;

    /**
     * Optional provider-side create; not all providers support this
     */
    public function insert(string $domain, array $configuration = []): void;

    /**
     * Optional provider-side update
     */
    public function update(int $id, string $domain, array $configuration = []): void;

    /**
     * Optional provider-side delete
     */
    public function delete(int $id): void;
}
