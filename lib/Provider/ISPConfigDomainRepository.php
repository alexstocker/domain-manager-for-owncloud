<?php

declare(strict_types=1);

namespace OCA\DomainManager\Provider;

use OCP\Http\Client\IClientService;

class ISPConfigDomainRepository implements IDomainProviderRepository
{
    private $httpClient;
    private $apiUrl;
    private $username;
    private $password;
    private $sessionId = null;

    public function __construct(IClientService $httpClient, string $apiUrl, string $username, string $password)
    {
        $this->httpClient = $httpClient->newClient();
        $this->apiUrl = rtrim($apiUrl, '/');
        $this->username = $username;
        $this->password = $password;
    }

    private function login()
    {
        if ($this->sessionId) {
            return $this->sessionId;
        }

        $response = $this->httpClient->post($this->apiUrl . '/login', [
            'body' => json_encode([
                'username' => $this->username,
                'password' => $this->password
            ]),
            'headers' => ['Content-Type' => 'application/json']
        ]);

        $data = json_decode($response->getBody(), true);
        if (isset($data['response'])) {
            $this->sessionId = $data['response'];
            return $this->sessionId;
        }

        throw new \Exception("ISPConfig login failed: " . ($data['message'] ?? 'Unknown error'));
    }

    public function findAll(): array
    {
        $session_id = $this->login();
        $response = $this->httpClient->get($this->apiUrl . '/dns_zone_get_all?session_id=' . $session_id);
        $data = json_decode($response->getBody(), true);

        $domains = [];
        if (isset($data['response']) && is_array($data['response'])) {
            foreach ($data['response'] as $zone) {
                $domains[] = [
                    'id' => (int)$zone['id'],
                    'domain' => rtrim($zone['origin'], '.'),
                    'created_at' => date('Y-m-d H:i:s')
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
        throw new \Exception("Insert not implemented for ISPConfig yet - requires additional parameters (server_id, etc.)");
    }

    public function update(int $id, string $domain, array $configuration = []): void
    {
        throw new \Exception("Update not implemented for ISPConfig");
    }

    public function delete(int $id): void
    {
        throw new \Exception("Delete not implemented for ISPConfig");
    }
}
