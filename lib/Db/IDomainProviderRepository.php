<?php

declare(strict_types=1);

namespace OCA\DomainManager\Db;

// Backwards-compat shim: make Db\IDomainProviderRepository extend the new Provider interface
interface IDomainProviderRepository extends \OCA\DomainManager\Provider\IDomainProviderRepository
{
}
