<?php

declare(strict_types=1);

namespace Otis22\VetmanagerToken\Url;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use Otis22\VetmanagerUrl\Url;
use Otis22\VetmanagerUrl\Url\FromJson;
use PHPUnit\Framework\TestCase;

class BillingUrlTest extends TestCase
{
    public function testLegacyResolverReturnsUrl(): void
    {
        if (class_exists(FromJson::class) && is_callable([FromJson::class, 'fromDomainAndBillingApiUsingClient'])) {
            $this->markTestSkipped('Legacy URL factory is only available with URL 0.x');
        }
        $url = BillingUrl::resolve('compatibility-test', new Client([
            'handler' => HandlerStack::create(new MockHandler([]))
        ]));
        $this->assertInstanceOf(Url::class, $url);
    }
}
