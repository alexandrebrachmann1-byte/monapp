<?php

declare(strict_types=1);

use PDO;

/**
 * Jetons API : on stocke un hash SHA-256, jamais le jeton en clair.
 */
return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec('
            CREATE TABLE `api_tokens` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT UNSIGNED NOT NULL,
                `token_hash` CHAR(64) NOT NULL UNIQUE,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT `fk_api_tokens_user`
                    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS `api_tokens`');
    }
};
