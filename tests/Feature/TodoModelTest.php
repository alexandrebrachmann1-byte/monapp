<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Todo;
use Core\Database;
use PDOException;
use PHPUnit\Framework\TestCase;

final class TodoModelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Database::reset();

        try {
            $pdo = Database::connection();
            $pdo->exec('
                CREATE TABLE IF NOT EXISTS `todos` (
                    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `title` VARCHAR(255) NOT NULL,
                    `is_done` TINYINT(1) NOT NULL DEFAULT 0,
                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ');
        } catch (PDOException $e) {
            $this->markTestSkipped('Base MySQL de test indisponible : ' . $e->getMessage());
        }
    }

    public function testInsertFindUpdateDelete(): void
    {
        $id = Todo::insert([
            'title' => 'Réviser le SQL',
            'is_done' => 0,
        ]);

        $this->assertGreaterThan(0, $id);

        $todo = Todo::find($id);
        $this->assertNotNull($todo);
        $this->assertSame('Réviser le SQL', $todo['title']);

        Todo::update($id, ['title' => 'Réviser PDO', 'is_done' => 1]);
        $todo = Todo::find($id);
        $this->assertSame('Réviser PDO', $todo['title']);
        $this->assertSame(1, (int) $todo['is_done']);

        Todo::delete($id);
        $this->assertNull(Todo::find($id));
    }
}
