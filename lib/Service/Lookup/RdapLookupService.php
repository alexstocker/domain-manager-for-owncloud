<?php

declare(strict_types=1);

namespace OCA\DomainManager\Service\Lookup;

use OCP\Http\Client\IClientService;

class RdapLookupService implements ILookupService
{
    private $httpClient;
    private $bootstrapUrl = 'https://rdap.org/domain/';

    public function __construct(IClientService $httpClient)
    {
        $this->httpClient = $httpClient->newClient();
    }

    public function supports(string $domain): bool
    {
        // This is the generic fallback service.
        return true;
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
