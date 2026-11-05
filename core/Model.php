<?php

declare(strict_types=1);

namespace Core;

/**
 * Classe de base des modèles : une classe PHP = une table SQL.
 *
 * Pas de mapping objet-relationnel : les méthodes renvoient des tableaux.
 * Hors conteneur DI, usage statique : Todo::all(), Todo::insert([...]).
 * Pour du SQL entièrement écrit à la main : query($sql, $params).
 */
abstract class Model
{
    protected static string $table;

    /**
     * Nom de la table SQL liée à ce modèle.
     *
     * @return string Exemple : users, todos
     */
    public static function tableName(): string
    {
        return static::$table;
    }

    /**
     * QueryBuilder déjà ciblé sur la table du modèle (where, join, toSql…).
     */
    public static function table(): QueryBuilder
    {
        return new QueryBuilder(Database::connection(), static::$table);
    }

    /**
     * SELECT * : toutes les lignes de la table.
     *
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        return static::table()->get();
    }

    /**
     * SELECT une ligne par clé primaire id.
     *
     * @return array<string, mixed>|null null si aucune ligne
     */
    public static function find(int $id): ?array
    {
        return static::table()->where('id', '=', $id)->first();
    }

    /**
     * INSERT : crée une ligne et retourne l'id auto-incrémenté.
     *
     * @param array<string, mixed> $data colonnes => valeurs (requête préparée)
     */
    public static function insert(array $data): int
    {
        return static::table()->insert($data);
    }

    /**
     * UPDATE … WHERE id = ? : met à jour une ligne existante.
     *
     * @param array<string, mixed> $data colonnes à modifier
     * @return int nombre de lignes touchées
     */
    public static function update(int $id, array $data): int
    {
        return static::table()->where('id', '=', $id)->update($data);
    }

    /**
     * DELETE FROM … WHERE id = ?.
     *
     * @return int nombre de lignes supprimées
     */
    public static function delete(int $id): int
    {
        return static::table()->where('id', '=', $id)->delete();
    }

    /**
     * SQL brut, bindings préparés. Le SQL reste entièrement visible.
     *
     * @param array<int|string, mixed> $params
     * @return list<array<string, mixed>>
     */
    public static function query(string $sql, array $params = []): array
    {
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }
}
