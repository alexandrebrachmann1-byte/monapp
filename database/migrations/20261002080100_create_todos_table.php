<?php

declare(strict_types=1);

use PDO;

/**
 * Liste de tâches globale (pas de user_id) : il faut être connecté pour y accéder.
 */
return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec('
            CREATE TABLE `todos` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `title` VARCHAR(255) NOT NULL,
                `is_done` TINYINT(1) NOT NULL DEFAULT 0,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS `todos`');
    }
};
