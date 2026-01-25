<?php

declare(strict_types=1);

namespace OCA\DomainManager\Provider;

use OCP\Http\Client\IClientService;

class RemoteDomainRepository implements IDomainProviderRepository
{
    private $httpClient;
    private $apiUrl;

    public function __construct(IClientService $httpClient, string $apiUrl)
    {
        $this->httpClient = $httpClient->newClient();
        $this->apiUrl = $apiUrl;
    }

    public function findAll(): array
    {
        $response = $this->httpClient->get($this->apiUrl . '/domains');
        return json_decode($response->getBody(), true);
    }

    public function findByDomain(string $domain): ?array
    {
        $response = $this->httpClient->get($this->apiUrl . '/domains/' . urlencode($domain));
        $data = json_decode($response->getBody(), true);
        return (isset($data['id']) || isset($data['domain'])) ? $data : null;
    }

    public function insert(string $domain, array $configuration = []): void
    {
        $this->httpClient->post($this->apiUrl . '/domains', [
            'body' => json_encode(['domain' => $domain, 'configuration' => $configuration]),
            'headers' => ['Content-Type' => 'application/json']
        ]);
    }

    public function update(int $id, string $domain, array $configuration = []): void
    {
        $this->httpClient->put($this->apiUrl . '/domains/' . $id, [
            'body' => json_encode(['domain' => $domain, 'configuration' => $configuration]),
            'headers' => ['Content-Type' => 'application/json']
        ]);
    }

    public function delete(int $id): void
    {
        $this->httpClient->delete($this->apiUrl . '/domains/' . $id);
    }

    public function findById(int $id): ?array
    {
        try {
            $response = $this->httpClient->get($this->apiUrl . '/domains/' . $id);
            $data = json_decode($response->getBody(), true);
            return (isset($data['id']) || isset($data['domain'])) ? $data : null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
