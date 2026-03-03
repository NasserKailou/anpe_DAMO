<?php
/**
 * Connexion à la base de données MySQL - Singleton PDO
 */
namespace App\Models;

use PDO;
use PDOException;

class Database
{
    private static ?self $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            DB_HOST, DB_PORT, DB_NAME
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_STRINGIFY_FETCHES  => false,
            PDO::ATTR_PERSISTENT         => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'",
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            $this->pdo->exec("SET time_zone = '+00:00'");
            $this->pdo->exec("SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");
        } catch (PDOException $e) {
            error_log('DB Connection Error: ' . $e->getMessage());
            if (APP_DEBUG) {
                throw new \RuntimeException('Erreur de connexion à la base de données: ' . $e->getMessage());
            }
            throw new \RuntimeException('Erreur de connexion à la base de données. Veuillez contacter l\'administrateur.');
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Convertit les placeholders PostgreSQL ($1, $2, …) en placeholders PDO (?)
     * $1 répété 2 fois → 2 entrées dans le tableau de params
     */
    private function convertPlaceholders(string $sql, array $params = []): array
    {
        $newParams = [];
        $newSql = preg_replace_callback('/\$(\d+)/', function($matches) use ($params, &$newParams) {
            $index = (int)$matches[1] - 1; // $1 → index 0
            $newParams[] = $params[$index] ?? null;
            return '?';
        }, $sql);
        return [$newSql, $newParams];
    }

    /**
     * Normalise le SQL PostgreSQL → MySQL
     * - ILIKE → LIKE (MySQL case-insensitive par défaut avec utf8mb4_unicode_ci)
     * - TRUE/FALSE → 1/0
     * - NOW() reste valide en MySQL
     * - CURRENT_TIMESTAMP reste valide
     * - ::text, ::integer → supprimés (casts PG)
     * - RETURNING id → supprimé (géré via lastInsertId)
     */
    private function normalizeSql(string $sql): string
    {
        // ILIKE → LIKE (MySQL est case-insensitive par défaut)
        $sql = preg_replace('/\bILIKE\b/i', 'LIKE', $sql);

        // Supprimer les casts PostgreSQL ::type
        $sql = preg_replace('/::(?:text|integer|int|bigint|varchar|boolean|bool|date|timestamp|numeric|float|double|json|jsonb|character varying(?:\(\d+\))?)/i', '', $sql);

        // TRUE → 1, FALSE → 0 (dans les contextes SQL)
        $sql = preg_replace('/\bTRUE\b/', '1', $sql);
        $sql = preg_replace('/\bFALSE\b/', '0', $sql);

        // Supprimer RETURNING id (MySQL utilise lastInsertId)
        $sql = preg_replace('/\s+RETURNING\s+\w+\s*$/i', '', $sql);

        return $sql;
    }

    /**
     * fetchAll avec placeholders PostgreSQL $1,$2,… (conversion automatique)
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        try {
            $sql = $this->normalizeSql($sql);
            [$convertedSql, $convertedParams] = $this->convertPlaceholders($sql, $params);
            $stmt = $this->pdo->prepare($convertedSql);
            $stmt->execute($convertedParams);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->logError($e, $sql);
            throw $e;
        }
    }

    /**
     * fetchAll avec placeholders ? directs (pas de conversion $1→?)
     */
    public function fetchAllRaw(string $sql, array $params = []): array
    {
        try {
            $sql = $this->normalizeSql($sql);
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->logError($e, $sql);
            throw $e;
        }
    }

    /**
     * Exécuter une requête préparée et retourner une seule ligne
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        try {
            $sql = $this->normalizeSql($sql);
            [$convertedSql, $convertedParams] = $this->convertPlaceholders($sql, $params);
            $stmt = $this->pdo->prepare($convertedSql);
            $stmt->execute($convertedParams);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (PDOException $e) {
            $this->logError($e, $sql);
            throw $e;
        }
    }

    /**
     * fetchOne avec placeholders ? directs
     */
    public function fetchOneRaw(string $sql, array $params = []): ?array
    {
        try {
            $sql = $this->normalizeSql($sql);
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (PDOException $e) {
            $this->logError($e, $sql);
            throw $e;
        }
    }

    /**
     * Exécuter une requête et retourner une seule valeur scalaire
     */
    public function fetchScalar(string $sql, array $params = []): mixed
    {
        try {
            $sql = $this->normalizeSql($sql);
            [$convertedSql, $convertedParams] = $this->convertPlaceholders($sql, $params);
            $stmt = $this->pdo->prepare($convertedSql);
            $stmt->execute($convertedParams);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->logError($e, $sql);
            throw $e;
        }
    }

    /**
     * fetchScalar avec placeholders ? directs
     */
    public function fetchScalarRaw(string $sql, array $params = []): mixed
    {
        try {
            $sql = $this->normalizeSql($sql);
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->logError($e, $sql);
            throw $e;
        }
    }

    /**
     * Exécuter une requête (INSERT, UPDATE, DELETE)
     */
    public function execute(string $sql, array $params = []): int
    {
        try {
            $sql = $this->normalizeSql($sql);
            [$convertedSql, $convertedParams] = $this->convertPlaceholders($sql, $params);
            $stmt = $this->pdo->prepare($convertedSql);
            $stmt->execute($convertedParams);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->logError($e, $sql);
            throw $e;
        }
    }

    /**
     * execute avec placeholders ? directs
     */
    public function executeRaw(string $sql, array $params = []): int
    {
        try {
            $sql = $this->normalizeSql($sql);
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->logError($e, $sql);
            throw $e;
        }
    }

    /**
     * INSERT et retourner l'ID inséré (MySQL utilise lastInsertId)
     */
    public function insert(string $sql, array $params = []): int|string
    {
        try {
            $sql = $this->normalizeSql($sql);
            [$convertedSql, $convertedParams] = $this->convertPlaceholders($sql, $params);
            $stmt = $this->pdo->prepare($convertedSql);
            $stmt->execute($convertedParams);
            return (int) $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            $this->logError($e, $sql);
            throw $e;
        }
    }

    /**
     * Démarrer une transaction
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Valider une transaction
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * Annuler une transaction
     */
    public function rollback(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * Vérifier si une transaction est active
     */
    public function inTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }

    /**
     * Compter les résultats d'une requête
     */
    public function count(string $table, string $where = '', array $params = []): int
    {
        $sql = "SELECT COUNT(*) FROM $table";
        if ($where) {
            $sql .= " WHERE $where";
        }
        return (int) $this->fetchScalar($sql, $params);
    }

    private function logError(PDOException $e, string $sql): void
    {
        error_log(sprintf(
            '[DB Error] %s | SQL: %s',
            $e->getMessage(),
            substr($sql, 0, 200)
        ));
    }

    // Empêcher le clonage et la sérialisation
    private function __clone() {}
    public function __wakeup() { throw new \Exception("Cannot unserialize singleton"); }
}
