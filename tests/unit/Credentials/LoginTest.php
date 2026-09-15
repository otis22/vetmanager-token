<?php

declare(strict_types=1);

namespace Otis22\VetmanagerToken\Credentials;

use PHPUnit\Framework\TestCase;

class LoginTest extends TestCase
{
    public function testValueIsPassedThroughUnchanged(): void
    {
        foreach (['', 'a', 'user@example.com', 'Abc12345!', 'гриша', " leading and trailing "] as $value) {
            $this->assertSame($value, (new Login($value))->asString());
        }
    }
}
