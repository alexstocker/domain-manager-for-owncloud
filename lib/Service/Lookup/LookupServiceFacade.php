<?php

declare(strict_types=1);

namespace OCA\DomainManager\Service\Lookup;

class LookupServiceFacade
{
    /** @var ILookupService[] */
    private $services = [];

    public function registerService(string $name, ILookupService $service): void
    {
        $this->services[$name] = $service;
    }

    public function lookup(string $domain): array
    {
        foreach ($this->services as $service) {
            if ($service->supports($domain)) {
                return $service->lookup($domain);
            }
        }

        throw new \Exception("No suitable lookup service found for '{$domain}'.");
    }
}
