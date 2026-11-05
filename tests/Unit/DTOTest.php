<?php

declare(strict_types=1);

namespace Tests\Unit;

use Core\DTO;
use Core\ValidationException;
use PHPUnit\Framework\TestCase;

final class SampleDTO extends DTO
{
    public string $name;
    public int $age;
}

final class DTOTest extends TestCase
{
    public function testFromArrayAndToArray(): void
    {
        $dto = SampleDTO::fromArray(['name' => 'Ada', 'age' => '36']);

        $this->assertSame('Ada', $dto->name);
        $this->assertSame(36, $dto->age);
        $this->assertSame(['name' => 'Ada', 'age' => 36], $dto->toArray());
        $this->assertSame(['name' => 'Ada', 'age' => 36], $dto->jsonSerialize());
        $this->assertSame(
            '{"name":"Ada","age":36}',
            json_encode($dto, JSON_UNESCAPED_UNICODE)
        );
    }

    public function testFromArrayIgnoresExtraKeys(): void
    {
        $dto = SampleDTO::fromArray(['name' => 'Ada', 'age' => 36, 'secret' => 'nope']);

        $this->assertSame(['name' => 'Ada', 'age' => 36], $dto->toArray());
    }

    public function testMissingRequiredFieldThrows(): void
    {
        $this->expectException(ValidationException::class);
        SampleDTO::fromArray(['name' => 'Ada']);
    }
}
