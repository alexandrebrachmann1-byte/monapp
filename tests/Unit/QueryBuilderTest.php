<?php

declare(strict_types=1);

namespace Tests\Unit;

use Core\QueryBuilder;
use PDO;
use PHPUnit\Framework\TestCase;

final class QueryBuilderTest extends TestCase
{
    public function testToSqlWithWhereAndOrderBy(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $query = (new QueryBuilder($pdo, 'users'))
            ->select('id', 'email')
            ->where('email', '=', 'ada@example.com')
            ->orderBy('id', 'DESC');

        $this->assertSame(
            'SELECT id, email FROM `users` WHERE `email` = ? ORDER BY `id` DESC',
            $query->toSql()
        );
        $this->assertSame(['ada@example.com'], $query->getBindings());
    }

    public function testHavingBindingsStayAfterWhere(): void
    {
        $pdo = new PDO('sqlite::memory:');
        $query = (new QueryBuilder($pdo, 'users'))
            ->select('email', 'COUNT(*) AS total')
            ->where('id', '>', 1)
            ->groupBy('email')
            ->having('total', '>=', 2);

        $this->assertSame(
            'SELECT email, COUNT(*) AS total FROM `users` WHERE `id` > ? GROUP BY `email` HAVING `total` >= ?',
            $query->toSql()
        );
        $this->assertSame([1, 2], $query->getBindings());
    }
}
