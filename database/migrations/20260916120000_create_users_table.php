<?php

declare(strict_types=1);

/**
 * SQL brut volontaire : pas de schéma abstrait.
 * Table des comptes (auth uniquement, plus de CRUD public).
 * Exécutée par : php framework migrate
 */
return new class {
    public function up(PDO $pdo): void
    {
        $pdo->exec('
            CREATE TABLE `users` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `username` VARCHAR(100) NOT NULL UNIQUE,
                `email` VARCHAR(190) NOT NULL UNIQUE,
                `password_hash` VARCHAR(255) NOT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ');

        $stmt = $pdo->prepare('
            INSERT INTO `users` (`username`, `email`, `password_hash`)
            VALUES (?, ?, ?)
        ');

        $stmt->execute([
            'fmdev',
            'fmdeveloppeur@gmail.com',
            password_hash('@AdminDev@27', PASSWORD_DEFAULT),
        ]);
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS `users`');
    }
};
