<?php

namespace Backend\Modules\Compression\Tests\Http;

use Backend\Modules\Compression\Exception\ValidateResponseErrorException;
use Backend\Modules\Compression\Http\TinyPngApiClient;
use Common\ModulesSettings;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
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

    public function testCanGetMonthlyCompressionCount(): void
    {
        $response = new Response(200, ['Compression-Count' => '187']);
        $guzzleClient = $this->createMock(Client::class);
        $guzzleClient->method('request')->with('POST', '/shrink')->willReturn($response);

        $key = random_bytes(32);
        $client = new TinyPngApiClient($key, [], $guzzleClient);

        $this->assertEquals(187, $client->getMonthlyCompressionCount());
    }

    public function testShouldThrowExceptionIfNoMonthlyCompressionCountHeaderAvailable(): void
    {
        $response = new Response(200, []);
        $guzzleClient = $this->createMock(Client::class);
        $guzzleClient->method('request')->with('POST', '/shrink')->willReturn($response);

        $key = random_bytes(32);
        $client = new TinyPngApiClient($key, [], $guzzleClient);

        $this->expectException(ValidateResponseErrorException::class);
        $client->getMonthlyCompressionCount();
    }

    public function testShouldThrowExceptionIfBadResponseForMonthlyCompressionCount(): void
    {
        $response = new Response(500, ['Compression-Count' => '187']);
        $guzzleClient = $this->createMock(Client::class);
        $guzzleClient->method('request')->with('POST', '/shrink')->willReturn($response);

        $key = random_bytes(32);
        $client = new TinyPngApiClient($key, [], $guzzleClient);

        $this->expectException(ValidateResponseErrorException::class);
        $client->getMonthlyCompressionCount();
    }
}
