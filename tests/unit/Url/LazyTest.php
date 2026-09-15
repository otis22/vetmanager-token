<?php

declare(strict_types=1);

namespace Otis22\VetmanagerToken\Url;

use Otis22\VetmanagerUrl\Url;
use Otis22\VetmanagerUrl\Url\Concrete;
use PHPUnit\Framework\TestCase;

class LazyTest extends TestCase
{
    public function testResolvesOnlyWhenUsedAndReusesUrl(): void
    {
        $calls = 0;
        $url = new Lazy(function () use (&$calls): Url {
            $calls++;
            return new Concrete('https://clinic.example');
        });

        $this->assertSame(0, $calls);
        $this->assertSame('https://clinic.example', $url->asString());
        $this->assertSame('https://clinic.example', $url->asString());
        $this->assertSame(1, $calls);
    }

    public function testFailedResolutionCanBeRetried(): void
    {
        $calls = 0;
        $url = new Lazy(function () use (&$calls): Url {
            if (++$calls === 1) {
                throw new \RuntimeException('Temporary failure');
            }
            return new Concrete('https://clinic.example');
        });

        try {
            $url->asString();
            $this->fail('Expected resolution to fail');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Temporary failure', $exception->getMessage());
        }
        $this->assertSame('https://clinic.example', $url->asString());
        $this->assertSame(2, $calls);
    }
}
