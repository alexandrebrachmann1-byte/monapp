<?php

declare(strict_types=1);

/**
 * Table de liaison actuelle entre users et teams.
 */
return new class {
    public function up(\PDO $pdo): void
    {
        $pdo->exec('
            CREATE TABLE IF NOT EXISTS `userteam` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `user_id` INT DEFAULT NULL,
                `team_id` INT DEFAULT NULL,
                `created_at` INT NOT NULL,
                `updated_at` INT NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ');
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS `userteam`');
    }
};
