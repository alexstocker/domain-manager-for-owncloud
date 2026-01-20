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
