<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTO\UserDTO;
use PHPUnit\Framework\TestCase;

final class UserDTOTest extends TestCase
{
    public function testUserDtoAcceptsMissingOptionalProfileFields(): void
    {
        $dto = UserDTO::fromArray([
            'id' => 1,
            'username' => 'fmdev',
            'email' => 'fmdeveloppeur@gmail.com',
        ]);

        $this->assertSame([
            'id' => 1,
            'username' => 'fmdev',
            'email' => 'fmdeveloppeur@gmail.com',
            'role' => null,
            'job' => null,
        ], $dto->toArray());
    }
}
