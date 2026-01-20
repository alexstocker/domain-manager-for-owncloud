<?php

declare(strict_types=1);

namespace OCA\DomainManager\Db;

use OCP\Http\Client\IClientService;

class CloudflareDomainRepository implements IDomainRepository
{
    private $httpClient;
    private $apiUrl = 'https://api.cloudflare.com/client/v4';
    private $apiToken;

    public function __construct(IClientService $httpClient, string $apiToken)
    {
        $this->httpClient = $httpClient->newClient();
        $this->apiToken = $apiToken;
    }

    public function findAll(): array
    {
        $response = $this->httpClient->get($this->apiUrl . '/zones', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json'
            ]
        ]);

        $data = json_decode($response->getBody(), true);
        $domains = [];

        if (isset($data['result']) && is_array($data['result'])) {
            foreach ($data['result'] as $zone) {
                $domains[] = [
                    'id' => $zone['id'],
                    'domain' => $zone['name'],
                    'created_at' => $zone['created_on']
                ];
            }
        }

        return $domains;
    }

    public function findByDomain(string $domain): ?array
    {
        $domains = $this->findAll();
        foreach ($domains as $d) {
            if ($d['domain'] === $domain) {
                return $d;
            }
        }
        return null;
    }

    public function insert(string $domain, array $configuration = []): void
    {
        $token = $configuration['api_token'] ?? $this->apiToken;
        if (empty($token)) {
            throw new \Exception("Cloudflare API token is required.");
        }
        throw new \Exception("Inserting Cloudflare zones requires an Account ID which is not yet configured.");
    }

    public function update(int $id, string $domain, array $configuration = []): void
    {
        throw new \Exception("Direct domain name update is not supported by Cloudflare API; zones are tied to specific names.");
    }

    public function delete(int $id): void
    {
        $this->httpClient->delete($this->apiUrl . '/zones/' . $id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json'
            ]
        ]);
    }
}
