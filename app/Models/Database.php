<?php
/**
 * Connexion à la base de données PostgreSQL - Singleton PDO
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
            'pgsql:host=%s;port=%s;dbname=%s;options=--search_path=%s',
            DB_HOST, DB_PORT, DB_NAME, DB_SCHEMA
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => true,   // MUST be true for $1 → ? conversion
            PDO::ATTR_STRINGIFY_FETCHES  => false,
            PDO::ATTR_PERSISTENT         => false,
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            $this->pdo->exec("SET NAMES 'UTF8'");
            $this->pdo->exec("SET TIME ZONE 'UTC'");
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
     * Convertir les placeholders PostgreSQL ($1, $2, …) en placeholders PDO (?)
     * Gère les répétitions : $1 utilisé 2 fois → duplique le paramètre dans le tableau
     * Retourne [sql_converti, params_réorganisés]
     */
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
     * Exécuter une requête préparée et retourner tous les résultats
     * Utilise les paramètres positionnels PostgreSQL ($1, $2, ...)
     */
    /**
     * fetchAll avec placeholders PostgreSQL $1,$2,… (conversion automatique)
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        try {
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
     * fetchAll avec placeholders ? directs (pas de conversion)
     */
    public function fetchAllRaw(string $sql, array $params = []): array
    {
        try {
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
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->logError($e, $sql);
            throw $e;
        }
    }

    /**
     * INSERT et retourner l'ID inséré
     */
    public function insert(string $sql, array $params = []): int|string
    {
        try {
            // PostgreSQL: utiliser RETURNING id
            if (stripos($sql, 'RETURNING') === false) {
                $sql .= ' RETURNING id';
            }
            [$convertedSql, $convertedParams] = $this->convertPlaceholders($sql, $params);
            $stmt = $this->pdo->prepare($convertedSql);
            $stmt->execute($convertedParams);
            $result = $stmt->fetch();
            return $result['id'] ?? 0;
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
