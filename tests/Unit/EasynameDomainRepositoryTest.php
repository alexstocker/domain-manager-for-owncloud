<?php

declare(strict_types=1);

use OCA\DomainManager\Provider\EasynameDomainRepository;
use OCP\Http\Client\IClientService;
use PHPUnit\Framework\TestCase;

class EasynameDomainRepositoryTest extends TestCase
{
    private function newRepo(string $authSalt = null, string $signingSalt = null): EasynameDomainRepository
    {
        // Create a dummy client service mock that returns a simple http client object with minimal methods
        $httpClientMock = $this->createMock(IClientService::class);
        $dummyClient = new class {
            public function get($url, $opts = []) { return new class { public function getBody() { return json_encode(['data' => []]); } }; }
            public function post($url, $opts = []) { return new class { public function getBody() { return json_encode(['data' => []]); } }; }
            public function put($url, $opts = []) { return new class { public function getBody() { return json_encode(['data' => []]); } }; }
            public function delete($url, $opts = []) { return new class { public function getBody() { return json_encode(['data' => []]); } }; }
        };
        $httpClientMock->method('newClient')->willReturn($dummyClient);

        return new EasynameDomainRepository($httpClientMock, 'https://api.easyname.com', '{"id":123,"email":"me@example.com"}', 'TESTKEY', $authSalt, $signingSalt);
    }

    public function testBuildAuthenticationHeaderWithSalt()
    {
        $repo = $this->newRepo('Signing%sTopSecret%sAuth00', null);
        $ref = new \ReflectionClass($repo);
        $m = $ref->getMethod('buildAuthenticationHeader');
        $m->setAccessible(true);
        $val = $m->invoke($repo);
        // Should be base64-encoded md5 raw bytes of sprintf of the pattern
        $expectedRaw = md5(sprintf('Signing%sTopSecret%sAuth00', '123', 'me@example.com'), true);
        $expected = base64_encode($expectedRaw);
        $this->assertEquals($expected, $val);
    }

    public function testBuildSignedBodyProducesSignature()
    {
        $repo = $this->newRepo(null, 'AaaBbbCcc');
        $ref = new \ReflectionClass($repo);
        $m = $ref->getMethod('buildSignedBody');
        $m->setAccessible(true);

        $payload = ['domain' => 'example.com', 'expire' => '2015-03-05', 'id' => 1234, 'trustee' => false, 'purchased' => '2010-03-05'];
        $json = $m->invoke($repo, $payload);
        $this->assertNotFalse(json_decode($json, true));

        $arr = json_decode($json, true);
        $this->assertArrayHasKey('data', $arr);
        $this->assertArrayHasKey('timestamp', $arr);
        $this->assertArrayHasKey('signature', $arr);
        $this->assertEquals($payload, $arr['data']);
    }
}
