<?php

declare(strict_types=1);

/**
 * Aligne la table users sur le schéma actuellement utilisé en local.
 */
return new class {
    public function up(\PDO $pdo): void
    {
        if (!$this->hasColumn($pdo, 'users', 'role')) {
            $pdo->exec("
                ALTER TABLE `users`
                ADD COLUMN `role` VARCHAR(255) NULL COMMENT 'admin / gestion / ouvrier' AFTER `password_hash`
            ");
        }

        if (!$this->hasColumn($pdo, 'users', 'job')) {
            $pdo->exec("
                ALTER TABLE `users`
                ADD COLUMN `job` VARCHAR(255) NULL COMMENT 'Metier de l''ouvrier' AFTER `role`
            ");
        }
    }

    public function down(\PDO $pdo): void
    {
        if ($this->hasColumn($pdo, 'users', 'job')) {
            $pdo->exec("ALTER TABLE `users` DROP COLUMN `job`");
        }

        if ($this->hasColumn($pdo, 'users', 'role')) {
            $pdo->exec("ALTER TABLE `users` DROP COLUMN `role`");
        }
    }

    private function hasColumn(\PDO $pdo, string $table, string $column): bool
    {
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM information_schema.columns
            WHERE table_schema = DATABASE()
              AND table_name = ?
              AND column_name = ?
        ");
        $stmt->execute([$table, $column]);

        return (int) $stmt->fetchColumn() > 0;
    }
};
