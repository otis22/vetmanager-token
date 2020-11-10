<?php

declare(strict_types=1);

namespace Otis22\VetmanagerToken;

use Otis22\VetmanagerUrl\Url\Concrete;
use PHPUnit\Framework\TestCase;

class FunctionsTest extends TestCase
{
    public function testCredentialsReturnInstanceOfCredentials(): void
    {
        $this->assertTrue(credentials('test', 'test', 'test') instanceof Credentials);
    }

    public function testTokenReturnInstanceOfToken(): void
    {
        $this->assertTrue(
            token(
                credentials('test', 'test', 'test'),
                'test'
            ) instanceof Token
        );
    }
}
