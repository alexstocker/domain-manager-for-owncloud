<?php

declare(strict_types=1);

namespace OCA\DomainManager\Db;

interface IDomainRepository
{
    /**
     * @return array
     */
    public function findAll(): array;

    /**
     * @param string $domain
     * @return array|null
     */
    public function findByDomain(string $domain): ?array;

    /**
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array;

    /**
     * Find all domains for a specific owner. If $owner is null, returns all domains.
     *
     * @param string|null $owner
     * @return array
     */
    public function findAllForOwner(?string $owner): array;

    /**
     * Find a domain record for a given domain and owner. If $owner is null, find an unowned record.
     *
     * @param string $domain
     * @param string|null $owner
     * @return array|null
     */
    public function findByDomainAndOwner(string $domain, ?string $owner): ?array;

    /**
     * Find domains that have no owner assigned.
     * @return array
     */
    public function findUnowned(): array;

    /**
     * Set or clear the owner for a domain record.
     * @param int $id
     * @param string|null $owner
     */
    public function setOwner(int $id, ?string $owner): void;

    /**
     * @param string $domain
     * @param array $configuration
     * @return void
     */
    public function insert(string $domain, array $configuration = []): void;

    /**
     * @param int $id
     * @param string $domain
     * @param array $configuration
     * @return void
     */
    public function update(int $id, string $domain, array $configuration = []): void;

    /**
     * @param int $id
     * @return void
     */
    public function delete(int $id): void;
}
