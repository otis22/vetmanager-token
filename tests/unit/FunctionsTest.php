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

    public function testTokenDefersInvalidDomainErrorUntilUsed(): void
    {
        $token = token(credentials('test', 'TestPassword123', 'test'), 'http://clinic.example:invalid');
        $this->assertInstanceOf(Token::class, $token);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Can't parse url or host string");
        $token->asString();
    }
}
