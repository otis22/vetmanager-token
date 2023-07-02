<?php

declare(strict_types=1);

namespace Otis22\VetmanagerToken\Credentials;

use PHPUnit\Framework\TestCase;

class PasswordTest extends TestCase
{
    /**
     * @return array<int, array<int, string>>
     */
    public function validData(): array
    {
        return [
            ['Ghghsh7373'],
            ['Ma1hh1qui'],
            ['GhthsTsjsg1Wh'],
        ];
    }

    /**
     * @dataProvider validData
     */
    public function testValid(string $validPassword): void
    {
        $this->assertEquals(
            (new Password($validPassword))->asString(),
            $validPassword
        );
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function invalidData(): array
    {
        return [
            [''],
            ['Ghghghskas332.'],
            [',,,.....,,---'],
            ['гриша324234']
        ];
    }

    /**
     * @dataProvider invalidData
     */
    public function testInvalid(string $invalidPassword): void
    {
        $this->expectException(\Exception::class);
        (new Password($invalidPassword))->asString();
    }
}
