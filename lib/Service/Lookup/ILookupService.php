<?php

declare(strict_types=1);

namespace OCA\DomainManager\Service\Lookup;

interface ILookupService
{
    public function supports(string $domain): bool;
    public function lookup(string $domain): array;
}
