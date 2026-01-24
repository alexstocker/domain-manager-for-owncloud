<?php

declare(strict_types=1);

namespace OCA\DomainManager\Service\Lookup;

use OCP\Http\Client\IClientService;

class CountryCodeTldLookupService implements ILookupService
{
    private $httpClient;

    private $tldEndpoints = [
        'at' => 'https://rdap.nic.at/domain/',
        'de' => 'https://rdap.denic.de/domain/',
    ];

    public function __construct(IClientService $httpClient)
    {
        $this->httpClient = $httpClient->newClient();
    }

    public function supports(string $domain): bool
    {
        $tld = substr($domain, strrpos($domain, '.') + 1);
        return isset($this->tldEndpoints[$tld]);
    }

    public function lookup(string $domain): array
    {
        $tld = substr($domain, strrpos($domain, '.') + 1);
        $endpoint = $this->tldEndpoints[$tld];

        try {
            if ($tld !== 'at') {
                $response = $this->httpClient->get($endpoint . $domain);
                return json_decode($response->getBody(), true);
            }
            $response = $this->parseWhoisTextDataToJson(
                $this->queryWhois('whois.nic.at', $domain),
                $domain
            );
            return json_decode($response, true);
        } catch (\Exception $e) {
            return ['error' => "Lookup for '{$domain}' failed: " . $e->getMessage()];
        }
    }

    protected function queryWhois($targetServer, $domain)
    {
        // 1. Clean the domain input
        $domain = strtolower(trim($domain));
        $port = 43;
        $timeout = 10;

        // 2. Open socket connection
        $fp = @fsockopen($targetServer, $port, $errno, $errstr, $timeout);

        if (!$fp) {
            return ["error" => "Could not connect: $errstr ($errno)"];
        }

        // 3. Send query (nic.at expects domain + CRLF)
        fwrite($fp, $domain . "\r\n");

        // 4. Read response
        $response = "";
        while (!feof($fp)) {
            $response .= fgets($fp, 128);
        }
        fclose($fp);

        return $response;
    }

    protected function parseWhoisTextDataToJson($whoisTextData, $domain) {
        $data = [];

        // Simple regex to catch lines like "domain: nic.at" or "registrar: ..."
        $lines = explode("\n", $whoisTextData);
        foreach ($lines as $line) {
            if (strpos($line, ':') !== false) {
                list($key, $value) = explode(':', $line, 2);
                $data[trim($key)] = trim($value);
            }
        }

        return json_encode([
            "rdapConformance" => ["pseudo_rdap_v1"],
            "ldhName" => $domain,
            "raw_text" => $whoisTextData,
            "parsed" => $data
        ], JSON_PRETTY_PRINT);
    }
}
