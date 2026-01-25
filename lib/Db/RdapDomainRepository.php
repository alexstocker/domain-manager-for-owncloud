<?php

declare(strict_types=1);

namespace OCA\DomainManager\Provider;

use OCP\Http\Client\IClientService;

class RdapDomainRepository implements \OCA\DomainManager\Provider\IDomainProviderRepository, \OCA\DomainManager\Db\IDomainProviderRepository
{
    private $httpClient;
    private $bootstrapUrl = 'https://rdap.org/domain/';

    public function __construct(IClientService $httpClient)
    {
        $this->httpClient = $httpClient->newClient();
    }

    public function findAll(): array
    {
        return [];
    }

    public function findByDomain(string $domain): ?array
    {
        return $this->lookup($domain);
    }

    public function insert(string $domain, array $configuration = []): void
    {
        throw new \Exception("RDAP is a read-only protocol.");
    }

    public function update(int $id, string $domain, array $configuration = []): void
    {
        throw new \Exception("RDAP is a read-only protocol.");
    }

    public function delete(int $id): void
    {
        throw new \Exception("RDAP is a read-only protocol.");
    }

    public function lookup(string $domain): array
    {
        try {
            $response = $this->httpClient->get($this->bootstrapUrl . $domain);
            $data = json_decode($response->getBody(), true);

            if ($data) {
                $data['identifiedProvider'] = $this->identifyProvider($data);
            }

            return $data;
        } catch (\Exception $e) {
            return [];
        }
    }

    private function identifyProvider(array $data): string
    {
        $nameservers = [];
        if (isset($data['nameservers'])) {
            foreach ($data['nameservers'] as $ns) {
                if (isset($ns['ldhName'])) {
                    $nameservers[] = strtolower($ns['ldhName']);
                }
            }
        }

        foreach ($nameservers as $ns) {
            if (strpos($ns, 'cloudflare.com') !== false) {
                return 'cloudflare';
            }
            if (strpos($ns, 'ispconfig') !== false) {
                return 'ispconfig';
            }
            if (strpos($ns, 'webtropia.com') !== false ||
                strpos($ns, 'it-wiit.com') !== false ||
                strpos($ns, 'webtropia-ops.de') !== false
            ) {
                return 'robot';
            }
        }

        return 'none';
    }
}
