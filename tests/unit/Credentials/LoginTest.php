<?php

declare(strict_types=1);

namespace Otis22\VetmanagerToken\Credentials;

use PHPUnit\Framework\TestCase;

class LoginTest extends TestCase
{
    public function testValid(): void
    {
        $this->assertEquals(
            (new Login('test'))->asString(),
            'test'
        );
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function invalidData(): array
    {
        return [
            [''],
            ['.'],
            ['Y'],
            ['yyy'],
            ['гриша']
        ];
    }

    /**
     * @dataProvider invalidData
     */
    public function testInvalid(string $invalidLogin): void
    {
        $this->expectException(\Exception::class);
        (new Login($invalidLogin))->asString();
    }
}
