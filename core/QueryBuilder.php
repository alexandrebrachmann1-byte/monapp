<?php

declare(strict_types=1);

namespace Core;

use PDO;
use PDOStatement;

/**
 * Constructeur de requêtes fluide — ce n'est PAS un ORM.
 *
 * Chaque méthode publique porte le nom de la clause SQL produite
 * (where, join, groupBy, having, orderBy…). Aucun vocabulaire métier
 * du type hasMany / belongsTo.
 *
 * Inspection pédagogique (à utiliser en TD) :
 *   echo $qb->toSql();        // SQL avec des placeholders ?
 *   print_r($qb->getBindings()); // valeurs liées, jamais concaténées
 *
 * Les fragments WHERE et HAVING ont des bindings séparés pour que
 * l'ordre des ? suive l'ordre des clauses dans le SQL final.
 */
final class QueryBuilder
{
    /** @var list<string> */
    private array $columns = ['*'];

    /** @var list<string> */
    private array $joins = [];

    /** @var list<string> */
    private array $wheres = [];

    /** @var list<string> */
    private array $groupBy = [];

    /** @var list<string> */
    private array $havings = [];

    /** @var list<string> */
    private array $orderBy = [];

    private ?int $limit = null;

    private ?int $offset = null;

    /** @var list<mixed> */
    private array $whereBindings = [];

    /** @var list<mixed> */
    private array $havingBindings = [];

    /** @var list<mixed> */
    private array $writeBindings = [];

    private string $type = 'select';

    private string $tableSql = '';

    /** @var array<string, mixed> */
    private array $writeData = [];

    /**
     * Prépare un QueryBuilder sur une table (PDO déjà ouvert).
     *
     * @param string $table nom SQL brut, sera quoté (`todos`)
     */
    public function __construct(
        private PDO $pdo,
        private string $table,
    ) {
        $this->tableSql = $this->quoteIdentifier($table);
    }

    /**
     * Clause SELECT. Sans argument : SELECT *.
     * On peut passer des expressions (COUNT(*), colonnes).
     */
    public function select(string ...$columns): self
    {
        $this->type = 'select';
        $this->columns = $columns === [] ? ['*'] : $columns;

        return $this;
    }

    /**
     * where('email', $v)  → WHERE `email` = ?
     * where('id', '>', 1) → WHERE `id` > ?
     */
    public function where(string $column, mixed $operator, mixed $value = null): self
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->addWhere('AND', $this->quoteIdentifier((string) $column) . ' ' . $operator . ' ?', [$value]);

        return $this;
    }

    /** Comme where(), mais avec OR au lieu de AND. */
    public function orWhere(string $column, mixed $operator, mixed $value = null): self
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->addWhere('OR', $this->quoteIdentifier((string) $column) . ' ' . $operator . ' ?', [$value]);

        return $this;
    }

    /**
     * WHERE col IN (?, ?, …). Tableau vide → 1 = 0 (aucun résultat, SQL valide).
     *
     * @param list<mixed> $values
     */
    public function whereIn(string $column, array $values): self
    {
        if ($values === []) {
            $this->addWhere('AND', '1 = 0', []);

            return $this;
        }

        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        $this->addWhere('AND', $this->quoteIdentifier($column) . " IN ({$placeholders})", $values);

        return $this;
    }

    /** WHERE `col` IS NULL (pas de placeholder). */
    public function whereNull(string $column): self
    {
        $this->addWhere('AND', $this->quoteIdentifier($column) . ' IS NULL', []);

        return $this;
    }

    /** INNER JOIN table ON first operator second (identifiants quotés). */
    public function join(string $table, string $first, string $operator, string $second): self
    {
        $this->joins[] = 'INNER JOIN ' . $this->quoteIdentifier($table)
            . ' ON ' . $this->quoteIdentifier($first) . ' ' . $operator . ' ' . $this->quoteIdentifier($second);

        return $this;
    }

    /** LEFT JOIN … ON … (même signature que join()). */
    public function leftJoin(string $table, string $first, string $operator, string $second): self
    {
        $this->joins[] = 'LEFT JOIN ' . $this->quoteIdentifier($table)
            . ' ON ' . $this->quoteIdentifier($first) . ' ' . $operator . ' ' . $this->quoteIdentifier($second);

        return $this;
    }

    /** Clause GROUP BY (une ou plusieurs colonnes). */
    public function groupBy(string ...$columns): self
    {
        foreach ($columns as $column) {
            $this->groupBy[] = $this->quoteIdentifier($column);
        }

        return $this;
    }

    /**
     * HAVING après un GROUP BY. Bindings séparés de WHERE pour l'ordre des ?.
     */
    public function having(string $column, mixed $operator, mixed $value = null): self
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->havings[] = $this->quoteIdentifier((string) $column) . ' ' . $operator . ' ?';
        $this->havingBindings[] = $value;

        return $this;
    }

    /** ORDER BY col ASC|DESC. */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $this->orderBy[] = $this->quoteIdentifier($column) . ' ' . $direction;

        return $this;
    }

    /** LIMIT n (pagination). */
    public function limit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    /** OFFSET n (à combiner avec limit()). */
    public function offset(int $offset): self
    {
        $this->offset = $offset;

        return $this;
    }

    /** SQL inspectable (placeholders ?). Les valeurs sont dans getBindings(). */
    public function toSql(): string
    {
        return match ($this->type) {
            'insert' => $this->compileInsert(),
            'update' => $this->compileUpdate(),
            'delete' => $this->compileDelete(),
            default => $this->compileSelect(),
        };
    }

    /**
     * Valeurs des ? dans le même ordre que toSql().
     *
     * @return list<mixed>
     */
    public function getBindings(): array
    {
        return match ($this->type) {
            'insert' => $this->writeBindings,
            'update' => array_merge($this->writeBindings, $this->whereBindings),
            'delete' => $this->whereBindings,
            default => array_merge($this->whereBindings, $this->havingBindings),
        };
    }

    /**
     * Exécute le SELECT et retourne toutes les lignes (tableaux associatifs).
     *
     * @return list<array<string, mixed>>
     */
    public function get(): array
    {
        $this->type = 'select';
        $stmt = $this->execute($this->toSql(), $this->getBindings());

        return $stmt->fetchAll();
    }

    /**
     * Première ligne du SELECT, ou null. Ajoute LIMIT 1.
     *
     * @return array<string, mixed>|null
     */
    public function first(): ?array
    {
        $this->limit(1);
        $rows = $this->get();

        return $rows[0] ?? null;
    }

    /** SELECT COUNT(*) en ignorant limit/offset. */
    public function count(): int
    {
        $previousColumns = $this->columns;
        $previousLimit = $this->limit;
        $previousOffset = $this->offset;
        $this->columns = ['COUNT(*) AS aggregate'];
        $this->limit = null;
        $this->offset = null;
        $this->type = 'select';

        $stmt = $this->execute($this->compileSelect(), $this->getBindings());
        $row = $stmt->fetch();

        $this->columns = $previousColumns;
        $this->limit = $previousLimit;
        $this->offset = $previousOffset;

        return (int) ($row['aggregate'] ?? 0);
    }

    /**
     * INSERT et retourne lastInsertId.
     *
     * @param array<string, mixed> $data
     */
    public function insert(array $data): int
    {
        $this->type = 'insert';
        $this->writeData = $data;
        $this->writeBindings = array_values($data);
        $this->execute($this->toSql(), $this->getBindings());

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * UPDATE SET … + WHERE déjà posés. Retourne le nombre de lignes.
     *
     * @param array<string, mixed> $data
     */
    public function update(array $data): int
    {
        $this->type = 'update';
        $this->writeData = $data;
        $this->writeBindings = array_values($data);
        $stmt = $this->execute($this->toSql(), $this->getBindings());

        return $stmt->rowCount();
    }

    /** DELETE + WHERE déjà posés. Sans WHERE, MySQL peut refuser ou tout effacer : toujours filtrer. */
    public function delete(): int
    {
        $this->type = 'delete';
        $stmt = $this->execute($this->toSql(), $this->getBindings());

        return $stmt->rowCount();
    }

    /**
     * Empile un fragment WHERE (AND/OR) et ses bindings.
     *
     * @param list<mixed> $bindings
     */
    private function addWhere(string $boolean, string $sql, array $bindings): void
    {
        $prefix = $this->wheres === [] ? '' : " {$boolean} ";
        $this->wheres[] = $prefix . $sql;
        foreach ($bindings as $binding) {
            $this->whereBindings[] = $binding;
        }
    }

    /** Assemble SELECT … FROM … JOIN … WHERE … GROUP BY … HAVING … ORDER BY … LIMIT. */
    private function compileSelect(): string
    {
        $sql = 'SELECT ' . implode(', ', $this->columns) . ' FROM ' . $this->tableSql;
        if ($this->joins !== []) {
            $sql .= ' ' . implode(' ', $this->joins);
        }
        $sql .= $this->compileWhere();
        if ($this->groupBy !== []) {
            $sql .= ' GROUP BY ' . implode(', ', $this->groupBy);
        }
        if ($this->havings !== []) {
            $sql .= ' HAVING ' . implode(' AND ', $this->havings);
        }
        if ($this->orderBy !== []) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orderBy);
        }
        if ($this->limit !== null) {
            $sql .= ' LIMIT ' . $this->limit;
        }
        if ($this->offset !== null) {
            $sql .= ' OFFSET ' . $this->offset;
        }

        return $sql;
    }

    /** INSERT INTO table (cols) VALUES (?, …). */
    private function compileInsert(): string
    {
        $columns = array_map(fn (string $c) => $this->quoteIdentifier($c), array_keys($this->writeData));
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));

        return 'INSERT INTO ' . $this->tableSql . ' (' . implode(', ', $columns) . ') VALUES (' . $placeholders . ')';
    }

    /** UPDATE table SET col = ? … + WHERE. */
    private function compileUpdate(): string
    {
        $sets = [];
        foreach (array_keys($this->writeData) as $column) {
            $sets[] = $this->quoteIdentifier($column) . ' = ?';
        }

        return 'UPDATE ' . $this->tableSql . ' SET ' . implode(', ', $sets) . $this->compileWhere();
    }

    /** DELETE FROM table + WHERE. */
    private function compileDelete(): string
    {
        return 'DELETE FROM ' . $this->tableSql . $this->compileWhere();
    }

    /** Préfixe WHERE si au moins une condition. */
    private function compileWhere(): string
    {
        if ($this->wheres === []) {
            return '';
        }

        return ' WHERE ' . implode('', $this->wheres);
    }

    /**
     * prepare + execute : les valeurs ne sont jamais concaténées dans le SQL.
     *
     * @param list<mixed> $bindings
     */
    private function execute(string $sql, array $bindings): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);

        return $stmt;
    }

    /**
     * Entoure un identifiant de backticks MySQL. Laisse passer * et les expressions (COUNT).
     */
    private function quoteIdentifier(string $name): string
    {
        if ($name === '*' || str_contains($name, '(') || str_contains($name, ' ')) {
            return $name;
        }

        $parts = explode('.', $name);
        $quoted = array_map(static fn (string $part): string => '`' . str_replace('`', '``', $part) . '`', $parts);

        return implode('.', $quoted);
    }
}
