<?php

namespace Backend\Modules\Compression\Tests\Http;

use Backend\Modules\Compression\Http\TinyPngApiClient;
use Common\ModulesSettings;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

/**
 * Class TinyPngApiClientTest
 * @package Backend\Modules\Compression\Tests\Http
 */
class TinyPngApiClientTest extends TestCase
{
    /**
     * @var Client
     */
    public $guzzle;

    public function setUp(): void
    {
        $this->guzzle = $this->createMock(Client::class);
    }

    public function testSetDefaultClient(): void
    {
        $guzzle = $this->guzzle;
        $client = new TinyPngApiClient('', [], $guzzle);
        (function () use ($guzzle) {
            TinyPngApiClientTest::assertSame($guzzle, $this->client);
        })->call($client);
    }

    public function testSetApiKeyIsStored(): void
    {
        $key = random_bytes(32);
        $client = new TinyPngApiClient($key);
        (function () use ($key) {
            TinyPngApiClientTest::assertSame($key, $this->apiKey);
        })->call($client);
    }

    public function testClientIsCreatedFromModuleSettings(): void
    {
        $key = random_bytes(32);
        $modulesSettings = $this->createMock(ModulesSettings::class);
        $modulesSettings->method('get')->with('Compression', 'api_key')->willReturn($key);

        $client = TinyPngApiClient::createFromModuleSettings($modulesSettings);
        (function () use ($key) {
            TinyPngApiClientTest::assertSame($key, $this->apiKey);
        })->call($client);
    }
}
