<?php

declare(strict_types=1);

namespace OCA\DomainManager\Db;

use OCP\Http\Client\IClientService;

class RobotApiDomainRepository implements IDomainRepository
{
    private $httpClient;
    private $apiUrl;
    private $username;
    private $password;

    public function __construct(IClientService $httpClient, string $apiUrl, string $username, string $password)
    {
        $this->httpClient = $httpClient->newClient();
        $this->apiUrl = rtrim($apiUrl, '/');
        $this->username = $username;
        $this->password = $password;
    }

    public function findAll(): array
    {
        $response = $this->httpClient->get($this->apiUrl . '/domain', [
            'auth' => [$this->username, $this->password]
        ]);

        $data = json_decode($response->getBody(), true);
        $domains = [];

        if (is_array($data)) {
            foreach ($data as $entry) {
                $domains[] = [
                    'id' => $entry['domain_name'] ?? $entry['id'],
                    'domain' => $entry['domain_name'] ?? $entry['name'],
                    'created_at' => $entry['created_at'] ?? date('Y-m-d H:i:s')
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
        throw new \Exception("Domain registration via Robot API requires additional parameters (contacts, nameservers, etc.)");
    }

    public function update(int $id, string $domain, array $configuration = []): void
    {
        throw new \Exception("Update not implemented for Robot API");
    }

    public function delete(int $id): void
    {
        throw new \Exception("Delete via Robot API is restricted for safety.");
    }
}
