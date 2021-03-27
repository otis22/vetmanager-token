<?php

declare(strict_types=1);

namespace Otis22\VetmanagerToken\Token\FromGateway;

use PHPUnit\Framework\TestCase;

class ErrorMessageTest extends TestCase
{
    public function testErrorMessage(): void
    {
        $this->assertStringContainsString(
            'title',
            (
                new ErrorMessage(
                    '{
                        "status": 401,
                        "title": "Wrong authentification.",
                        "detail": "Неправильный логин или пароль."
                    }'
                )
            )->asString()
        );
    }
    public function testErrorMessageWithEmptyDetails(): void
    {
        $this->assertStringContainsString(
            "Can't parse response errors",
            (
            new ErrorMessage(
                '{
                        "status": 401,
                        "title": "Wrong authentification.",
                    }'
            )
            )->asString()
        );
    }
}
