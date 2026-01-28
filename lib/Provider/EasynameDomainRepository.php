<?php

declare(strict_types=1);

namespace OCA\DomainManager\Provider;

use OCP\Http\Client\IClientService;

class EasynameDomainRepository implements IDomainProviderRepository
{
    private $httpClient;
    private $apiUrl;
    private $apiUser;
    private $apiKey;
    private $authSalt;
    private $signingSalt;

    public function __construct(IClientService $clientService, string $apiUrl, string $apiUser, string $apiKey, string $authSalt = null, string $signingSalt = null)
    {
        $this->httpClient = $clientService->newClient();
        $this->apiUrl = rtrim($apiUrl, '/');
        $this->apiUser = $apiUser;
        $this->apiKey = $apiKey;
        $this->authSalt = $authSalt;
        $this->signingSalt = $signingSalt;
        // Note: According to api-docs.easyname.com the API requires headers X-User-ApiKey and X-User-Authentication
        // X-User-ApiKey => $this->apiKey
        // X-User-Authentication => generated from authSalt + user id + email (base64(md5(..., true)))
        // If no salts are provided, the repository will still send X-User-ApiKey and the raw apiUser as X-User-Authentication.
    }

    /**
     * Try to produce X-User-Authentication value according to Easyname docs.
     * If an authSalt is available and apiUser contains id and email, the salt pattern
     * (containing two %s placeholders) will be filled and MD5(base64) encoded accordingly.
     * If no authSalt or missing pieces, return the raw apiUser value as fallback.
     */
    private function buildAuthenticationHeader(): string
    {
        if ($this->authSalt !== null) {
            $user = $this->parseUser($this->apiUser);
            if ($user['id'] !== null && $user['email'] !== null) {
                $pattern = $this->authSalt;
                // Replace first %s with id and second with email
                $filled = $this->safeSprintf($pattern, $user['id'], $user['email']);
                // MD5 raw bytes then base64 encode
                $md5raw = md5($filled, true);
                return base64_encode($md5raw);
            }
        }
        // Fallback: assume apiUser already contains authentication string
        return $this->apiUser;
    }

    /**
     * Parse the provided apiUser into id and email if possible.
     * Accepts formats:
     * - JSON string like '{"id":123,"email":"a@b"}'
     * - "id:email" or "id|email" or "id,email"
     * - numeric id or an email-only string
     */
    private function parseUser(string $input): array
    {
        $result = ['id' => null, 'email' => null];

        // JSON?
        $trim = trim($input);
        if (($trim[0] ?? '') === '{') {
            $decoded = json_decode($trim, true);
            if (is_array($decoded)) {
                $result['id'] = isset($decoded['id']) ? (string)$decoded['id'] : null;
                $result['email'] = isset($decoded['email']) ? (string)$decoded['email'] : null;
                return $result;
            }
        }

        // separators
        $seps = [":", "|", ","];
        foreach ($seps as $s) {
            if (strpos($input, $s) !== false) {
                [$id, $email] = explode($s, $input, 2) + [null, null];
                $result['id'] = $id !== null ? trim($id) : null;
                $result['email'] = $email !== null ? trim($email) : null;
                return $result;
            }
        }

        // single token: decide if email or id
        if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
            $result['email'] = $input;
        } elseif (is_numeric($input)) {
            $result['id'] = (string)$input;
        }

        // unknown content will be handled by returning nulls and using raw apiUser as fallback

        return $result;
    }

    /**
     * Safe sprintf replacement for patterns with two %s placeholders.
     */
    private function safeSprintf(string $pattern, $a, $b): string
    {
        // If pattern doesn't contain %s placeholders, append in-between
        if (substr_count($pattern, '%s') >= 2) {
            return sprintf($pattern, $a, $b);
        }
        return str_replace('%s', $a, $pattern) . $b;
    }

    /**
     * Compute Easyname signature for POST/PUT payloads according to docs.
     * Build a body: { data: <payload>, timestamp: <ts>, signature: <base64(md5)> }
     */
    private function buildSignedBody(array $data): string
    {
        $timestamp = time();
        $merged = $data; // data keys
        // signature requires keys from data plus timestamp
        $mergedWithTimestamp = $merged;
        $mergedWithTimestamp['timestamp'] = $timestamp;

        $flattened = $this->flattenValues($mergedWithTimestamp);
        // concatenate values with a single space as separator (docs examples show spaces)
        $concat = implode(' ', $flattened);

        $len = strlen($concat);
        $half = intdiv($len + 1, 2); // ceil for odd lengths
        $first = substr($concat, 0, $half);
        $second = substr($concat, $half);

        $salt = $this->signingSalt ?? '';
        $toHash = $first . $salt . $second;
        $md5raw = md5($toHash, true);
        $signature = base64_encode($md5raw);

        $envelope = ['data' => $data, 'timestamp' => $timestamp, 'signature' => $signature];
        $json = json_encode($envelope);
        if ($json === false) {
            throw new \Exception('Failed to encode signed JSON body: ' . json_last_error_msg());
        }
        return $json;
    }

    /**
     * Flatten data values in a deterministic order: for associative arrays sort keys
     * ascending and recurse. For lists (numeric keys) iterate in numeric order.
     * Convert booleans: false -> '' (empty string), true -> '1' as per docs.
     */
    private function flattenValues($data): array
    {
        $out = [];
        if (is_array($data)) {
            // detect list vs assoc
            $isList = array_values($data) === $data;
            if ($isList) {
                foreach ($data as $v) {
                    $out = array_merge($out, $this->flattenValues($v));
                }
            } else {
                $keys = array_keys($data);
                sort($keys, SORT_STRING);
                foreach ($keys as $k) {
                    $v = $data[$k];
                    if (is_array($v)) {
                        $out = array_merge($out, $this->flattenValues($v));
                    } else {
                        if ($v === false) {
                            $out[] = '';
                        } elseif ($v === true) {
                            $out[] = '1';
                        } elseif ($v === null) {
                            $out[] = '';
                        } else {
                            $out[] = (string)$v;
                        }
                    }
                }
            }
        } else {
            if ($data === false) {
                $out[] = '';
            } elseif ($data === true) {
                $out[] = '1';
            } elseif ($data === null) {
                $out[] = '';
            } else {
                $out[] = (string)$data;
            }
        }
        return $out;
    }

    /**
     * Centralised request helper that injects auth headers and decodes JSON responses.
     * This helper will throw an Exception on HTTP client exceptions or invalid JSON.
     * It also supports signing POST/PUT bodies when signingSalt is configured.
     *
     * @param string $method GET|POST|PUT|DELETE
     * @param string $path API path relative to base URL (leading slash optional)
     * @param array $opts Additional options: 'body' => string, 'headers' => array, 'data' => array (for signing)
     * @return array Decoded JSON as associative array
     * @throws \Exception
     */
    private function request(string $method, string $path, array $opts = []): array
    {
        $url = $this->apiUrl . '/' . ltrim($path, '/');

        $defaultHeaders = [
            'X-User-ApiKey' => $this->apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ];

        // Build authentication header according to available salts/user info
        $defaultHeaders['X-User-Authentication'] = $this->buildAuthenticationHeader();

        $headers = $opts['headers'] ?? [];
        $headers = array_merge($defaultHeaders, $headers);

        $options = [];

        // Support signing payloads for POST/PUT when signingSalt is set and 'data' provided
        if ((strtoupper($method) === 'POST' || strtoupper($method) === 'PUT') && isset($opts['data']) && $this->signingSalt !== null) {
            $options['body'] = $this->buildSignedBody($opts['data']);
        } elseif (isset($opts['body'])) {
            $options['body'] = $opts['body'];
        }

        $options['headers'] = $headers;

        try {
            switch (strtoupper($method)) {
                case 'GET':
                    $response = $this->httpClient->get($url, $options);
                    break;
                case 'POST':
                    $response = $this->httpClient->post($url, $options);
                    break;
                case 'PUT':
                    $response = $this->httpClient->put($url, $options);
                    break;
                case 'DELETE':
                    $response = $this->httpClient->delete($url, $options);
                    break;
                default:
                    throw new \Exception('Unsupported HTTP method: ' . $method);
            }
        } catch (\Throwable $e) {
            throw new \Exception('HTTP request failed: ' . $e->getMessage());
        }

        $body = (string)$response->getBody();
        $decoded = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON response from Easyname API: ' . json_last_error_msg());
        }

        return $decoded ?? [];
    }

    /**
     * Fetch all domains from the provider.
     * Assumption: Easyname exposes a collection endpoint at /api/domains that returns
     * a JSON object with either a top-level array or a 'data' key containing domain objects.
     * Normalise items to an array of ['id' => int, 'domain' => string, 'created_at' => string].
     */
    public function findAll(): array
    {
        // Try the documented resource path /domain first
        $pathCandidates = [
            '/domain',
            '/domain/',
            '/api/domains',
            '/api/items',
            '/api/list',
            '/api/domain',
            '/api/item'
        ];

        $raw = [];
        foreach ($pathCandidates as $path) {
            try {
                $decoded = $this->request('GET', $path);
                // Easyname responses usually have 'data' as payload
                if (isset($decoded['data']) && is_array($decoded['data'])) {
                    $raw = $decoded['data'];
                } elseif (isset($decoded['result']) && is_array($decoded['result'])) {
                    $raw = $decoded['result'];
                } elseif (is_array($decoded) && $this->looksLikeList($decoded)) {
                    $raw = $decoded;
                } else {
                    continue;
                }
                break;
            } catch (\Exception $e) {
                continue;
            }
        }

        $domains = [];
        foreach ($raw as $item) {
            if (is_array($item)) {
                $id = isset($item['id']) ? (int)$item['id'] : (isset($item['domain_id']) ? (int)$item['domain_id'] : null);
                $name = $item['domain'] ?? $item['name'] ?? $item['hostname'] ?? null;
                $created = $item['created_at'] ?? $item['created'] ?? date('Y-m-d H:i:s');

                if ($name !== null) {
                    $domains[] = [
                        'id' => $id,
                        'domain' => rtrim($name, '.'),
                        'created_at' => $created
                    ];
                }
            }
        }

        return $domains;
    }

    private function looksLikeList(array $decoded): bool
    {
        // If the array has numeric keys starting at 0, it's a list
        if (array_values($decoded) === $decoded) {
            return true;
        }
        return false;
    }

    public function findByDomain(string $domain): ?array
    {
        $all = $this->findAll();
        foreach ($all as $d) {
            if (isset($d['domain']) && $d['domain'] === $domain) {
                return $d;
            }
        }

        // As fallback try direct query endpoint with domain param
        try {
            $decoded = $this->request('GET', '/domain?domain=' . urlencode($domain));
            if (isset($decoded['data']) && is_array($decoded['data'])) {
                $item = is_array($decoded['data']) && array_values($decoded['data']) === $decoded['data'] ? ($decoded['data'][0] ?? null) : $decoded['data'];
            } elseif (is_array($decoded) && $this->looksLikeList($decoded)) {
                $item = $decoded[0] ?? null;
            } else {
                $item = $decoded;
            }

            if (is_array($item) && ($item['domain'] ?? $item['name'] ?? null)) {
                return [
                    'id' => isset($item['id']) ? (int)$item['id'] : null,
                    'domain' => $item['domain'] ?? $item['name'],
                    'created_at' => $item['created_at'] ?? ($item['created'] ?? date('Y-m-d H:i:s'))
                ];
            }
        } catch (\Exception $e) {
        }

        return null;
    }

    public function insert(string $domain, array $configuration = []): void
    {
        $payload = array_merge(['domain' => $domain], $configuration);
        // If signing salt present, request() will sign automatically when passing 'data'
        if ($this->signingSalt !== null) {
            $this->request('POST', '/domain', ['data' => $payload]);
            return;
        }

        $body = json_encode($payload);
        if ($body === false) {
            throw new \Exception('Failed to encode JSON payload for insert');
        }

        $this->request('POST', '/domain', ['body' => $body]);
    }

    public function update(int $id, string $domain, array $configuration = []): void
    {
        $payload = array_merge(['domain' => $domain], $configuration);
        if ($this->signingSalt !== null) {
            $this->request('POST', '/domain/' . $id, ['data' => $payload]);
            return;
        }

        $body = json_encode($payload);
        if ($body === false) {
            throw new \Exception('Failed to encode JSON payload for update');
        }

        // The docs allow POST /domain/<id> for updates; also accept PUT
        $this->request('POST', '/domain/' . $id, ['body' => $body]);
    }

    public function delete(int $id): void
    {
        // Easyname uses POST operations for many domain commands; they also have specific /domain/<id>/delete
        // Try the documented delete endpoint first
        try {
            $this->request('POST', '/domain/' . $id . '/delete');
            return;
        } catch (\Exception $e) {
            // Fallback to DELETE if supported
            $this->request('DELETE', '/domain/' . $id);
        }
    }

    /**
     * Optional convenience: find by provider id. Not part of interface; kept for callers that expect it.
     */
    public function findById(int $id): ?array
    {
        try {
            $decoded = $this->request('GET', '/domain/' . $id);
            if (isset($decoded['data']) && is_array($decoded['data'])) {
                $item = $decoded['data'];
            } else {
                $item = $decoded;
            }

            if (is_array($item) && ($item['name'] ?? $item['domain'] ?? null)) {
                return [
                    'id' => isset($item['id']) ? (int)$item['id'] : $id,
                    'domain' => $item['domain'] ?? $item['name'],
                    'created_at' => $item['created_at'] ?? ($item['created'] ?? date('Y-m-d H:i:s'))
                ];
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }
}
