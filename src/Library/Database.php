<?php

namespace App\Library;

/**
 * Work with database and several helper functions
 */
class Database
{
    private $debug = false;
    const DATE_FORMAT = "Y-m-d H:m:s";
    const TABLE_QUERY_LOGS = "query_logs";
    const TABLE_PRODUCTS = "products";
    const TABLE_PRODUCT_TYPES = "product_types";
    const TABLE_LIST_TYPES = "list_product_types";
    const TABLE_ERROR_LOGS = "error_logs";

    /**
     * MySQL Database connection handler
     *
     * @var mixed
     */
    private $connection = false;

    /**
     * Get this class handler and initialize database connection handler in internal variable
     * @return DbClass
     */
    public function __construct()
    {
        $this->connection = $this->connHandler();
        $this->debug = $_ENV["DEBUG"];
        return $this;
    }

    /**
     * Close database session when destructing app
     *
     * @return void
     */
    public function __destruct()
    {
        $this->closeDatabase();
    }

    /**
     * Get connection handler of MySQL database
     *
     * @return mixed
     */
    private function connHandler(): \mysqli
    {
        $mysqli = new \mysqli($_ENV["DB_HOST"], $_ENV["DB_USERNAME"], $_ENV["DB_PASSWORD"], $_ENV["DB_NAME"], $_ENV["DB_PORT"]);
        if ($mysqli->connect_errno) {
            echo json_encode(array(
                "status" => false,
                "result" => "Failed to connect to MySQL: " . $mysqli->connect_error
            ));
            exit;
        } else {
            return $mysqli;
        }
    }

    /**
     * Get record rows from given table by params and optional values
     *
     * @param string $pTable
     * @param array $pParams
     * @param int $pLimit
     * @param string $pSortOrder
     * @param bool $pGetSingleElement
     * @return array
     */
    public function select(string $pTable, array $pParams = [], int $pLimit = 100, string $pSortOrder = "", string $selectFields = "t.*", bool $pGetSingleElement = false): array
    {
        try {
            if (!$this->connection) {
                $this->connection = $this->connHandler();
            }
            $where = "";
            foreach ($pParams as $key => $value) {
                if (is_array($value)) {
                    if ($key == "condition") {
                        $where .= $value["field"] . ' ' . $value["cond"] . ' ' . $value["value"] . " AND ";
                    }
                } else {
                    $where .= $key . "=" . $value . " AND ";
                }
            }
            $where = rtrim($where, " AND ");
            if (count($pParams) > 0) {
                $query = "SELECT $selectFields FROM $pTable t WHERE $where $pSortOrder LIMIT $pLimit";
            } else {
                $query = "SELECT $selectFields FROM $pTable t $pSortOrder LIMIT $pLimit";
            }
            if ($this->debug) {
                $vQLP = array(
                    "filename" => __FILE__,
                    "line" => __LINE__,
                    "query" => $query,
                    "created_at" => $this->Now()
                );
                $this->insert(self::TABLE_QUERY_LOGS, $vQLP);
            }
            $result = $this->connection->query($query);
            if ($this->connection->errno) {
                throw new \Exception($this->connection->error);
            } else {
                if ($result && $result->num_rows > 0) {
                    $result->fetch_all(MYSQLI_ASSOC);
                }
                $records = array();
                if (isset($result)) {
                    foreach ($result as $item) {
                        $records[] = $item;
                    }
                }
                if (count($records) == 1 && $pGetSingleElement) {
                    return $records[0];
                } else {
                    return $records;
                }
                return $records;
            }
        } catch (\Exception $e) {
            $this->createDBLog([
                "location" => "/library/db.class.php - DbClass->select()",
                "request" => json_encode(["Table" => $pTable, "pParams" => $pParams, "pLimit" => $pLimit, "pSortOrder" => $pSortOrder]),
                "response" => $query,
                "content" => $e->getMessage(),
                "created_at" => $this->Now()
            ]);
            return false;
        }
    }

    public function fetchOneCol(): string{
        return "";
    }

    public function fetchOneRow(): array{
        return [];
    }

    public function fetchAll(string $pSelectSQLQuery): array{
        return [];
    }

    /**
     * Get selected data in array by full select query
     *
     * @param string $pSelect
     * @return array
     */
    public function querySelect($pSelect): array
    {
        try {
            if (!$this->connection) {
                $this->connection = $this->connHandler();
            }
            if ($this->debug) {
                $vQLP = array(
                    "filename" => __FILE__,
                    "line" => __LINE__,
                    "query" => $pSelect,
                    "created_at" => $this->Now()
                );
                $this->insert(self::TABLE_QUERY_LOGS, $vQLP);
            }
            $result = $this->connection->query($pSelect);
            if ($this->connection->errno) {
                throw new \Exception($this->connection->error);
            } else {
                if ($result && $result->num_rows > 0) {
                    $result->fetch_all(MYSQLI_ASSOC);
                }
                $records = array();
                if (isset($result)) {
                    foreach ($result as $item) {
                        $records[] = $item;
                    }
                }
                return $records;
            }
        } catch (\Exception $e) {
            $this->createDBLog([
                "location" => "/library/db.class.php - DbClass->querySelect()",
                "request" => json_encode($_REQUEST),
                "response" => "Select: " . $pSelect,
                "content" => $e->getMessage(),
                "created_at" => $this->Now()
            ]);
            return false;
        }
    }

    /**
     * Execute SQL query
     *
     * @param string $pQuery
     * @return boolean
     */
    public function queryExec($pQuery): bool
    {
        try {
            if (!$this->connection) {
                $this->connection = $this->connHandler();
            }
            if ($this->debug) {
                $vQLP = array(
                    "filename" => __FILE__,
                    "line" => __LINE__,
                    "query" => $pQuery,
                    "created_at" => $this->Now()
                );
                $this->insert(self::TABLE_QUERY_LOGS, $vQLP);
            }
            $result = $this->connection->query($pQuery);
            if ($this->connection->errno) {
                throw new \Exception($this->connection->error);
            } else {
                return $result;
            }
        } catch (\Exception $e) {
            $this->createDBLog(array(
                "location" => "/library/db.class.php - DbClass->queryExec()",
                "request" => json_encode($_REQUEST),
                "response" => "QUERY: " . $pQuery,
                "content" => $e->getMessage(),
                "created_at" => $this->Now()
            ));
            return false;
        }
    }

    /**
     * insert into $pTableName values of $pParams array
     *
     * @param string $pTableName
     * @param array $pParams
     * @return int inserted id
     */
    public function insert($pTableName, $pParams)
    {
        try {
            if (!$this->connection) {
                $this->connection = $this->connHandler();
            }
            $fields = "";
            $vals = "";
            $s = "";
            $values = array();
            foreach ($pParams as $key => $value) {
                $fields .= "`" . $key . "`,";
                $vals .= "?,";
                $s .= "s";
                $values[] = $value;
            }
            $fields = rtrim($fields, ",");
            $vals = rtrim($vals, ",");
            $query = "INSERT INTO " . $pTableName . " ($fields)VALUES($vals)";
            if ($this->debug && $pTableName !== self::TABLE_QUERY_LOGS) {
                $q = "INSERT INTO " . self::TABLE_QUERY_LOGS . "(`filename`,`line`,`query`,`created_at`)VALUES('" . __FILE__ . "','" . __LINE__ . "','" . $query . "','" . $this->Now() . "');";
                $stmt = $this->connection->prepare($q);
                $stmt->execute();
            }
            $stmt = $this->connection->prepare($query);
            $stmt = $this->DynamicBindVariables($stmt, $values);
            if (!$stmt->execute()) {
                throw new \Exception($stmt->error);
            } else {
                return $this->connection->insert_id;
            }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $this->createDBLog(array(
                "location" => "/library/db.class.php - DbClass->insert()",
                "request" => json_encode(["tablename" => $pTableName, "params" => $pParams]),
                "response" => null,
                "content" => $errorMessage,
                "created_at" => $this->Now()
            ));
            return $errorMessage;
        }
    }

    /**
     * Binding vars for MySQLi query
     *
     * @param callback $pStatment
     * @param array $pParams
     * @return callback
     */
    private function DynamicBindVariables($pStatment, array $pParams)
    {
        if ($pParams != null) {
            $vDataTypes = '';
            foreach ($pParams as $param) {
                if (is_int($param)) {
                    $vDataTypes .= 'i';
                } elseif (is_float($param)) {
                    $vDataTypes .= 'd';
                } elseif (is_string($param)) {
                    $vDataTypes .= 's';
                } else {
                    $vDataTypes .= 'b';
                }
            }
            $vBindNames[] = $vDataTypes;
            for ($i = 0; $i < count($pParams); $i++) {
                $vBindName = 'bind' . $i;
                $$vBindName = $pParams[$i];
                $vBindNames[] = &$$vBindName;
            }
            call_user_func_array(array($pStatment, 'bind_param'), $vBindNames);
        }
        return $pStatment;
    }

    /**
     * Update $pTablename by $pParams in $pWhere clause
     *
     * @param string $pTablename
     * @param array $pParams
     * @param string $pWhere
     * @return boolean
     */
    public function update(string $pTablename, array $pParams, string $pWhere): boolean
    {
        try {
            if (!$this->connection) {
                $this->connection = $this->connHandler();
            }
            $fields = "";
            $vals = "";
            $s = "";
            $values = array();
            foreach ($pParams as $key => $value) {
                $fields .= "`" . $key . "`=?,";
                $vals = "?,";
                $s .= "s";
                $values[] = $value;
            }
            $fields = rtrim($fields, ",");
            $vals = rtrim($vals, ",");
            $query = "UPDATE " . $pTablename . " SET $fields WHERE " . $pWhere;
            if ($this->debug) {
                $vQLP = array(
                    "filename" => __FILE__,
                    "line" => __LINE__,
                    "query" => $query,
                    "created_at" => $this->Now()
                );
                $this->insert(self::TABLE_QUERY_LOGS, $vQLP);
            }
            $stmt = $this->connection->prepare($query);
            $stmt = $this->DynamicBindVariables($stmt, $values);
            $execute = $stmt->execute();
            if ($this->connection->errno) {
                throw new \Exception($this->connection->error);
            } else {
                return $this->connection->insert_id;
            }
            return $execute;
        } catch (\Exception $e) {
            $this->createDBLog(array(
                "location" => "/library/db.class.php - DbClass->update()",
                "request" => json_encode(["tablename" => $pTablename, "params" => $pParams]),
                "response" => null,
                "content" => $e->getMessage(),
                "created_at" => $this->Now()
            ));
            return false;
        }
    }

    /**
     * Delete record from database
     *
     * @param string $pTableName
     * @param int $pValue
     * @param string $pFieldName
     * @param bool $pWithPrimaryKey
     * @return void
     */
    public function delete(string $pTableName, int $pValue, string $pFieldName, bool $pWithPrimaryKey = true): void
    {
        try {
            if (!$this->connection) {
                $this->connection = $this->connHandler();
            }
            if ($pWithPrimaryKey) {
                $query = "DELETE FROM $pTableName WHERE id=" . $pValue;
            } else {
                $query = "DELETE FROM $pTableName WHERE $pFieldName=" . $pValue;
            }
            $this->queryExec($query);
            if ($this->connection->errno) {
                throw new \Exception($this->connection->error);
            }
        } catch (\Exception $e) {
            $this->createDBLog(array(
                "location" => "/library/db.class.php - DbClass->update()",
                "request" => json_encode(["tablename" => $pTableName, "query" => $query]),
                "response" => null,
                "content" => $e->getMessage(),
                "created_at" => $this->Now()
            ));
        }
    }

    /**
     * CLose database connection session
     *
     * @return void
     */
    private function closeDatabase(): void
    {
        if ($this->connection) {
            $this->connection->close();
        }
    }

    /**
     * Return date time formatted by constant DATE_FORMAT or any other format by $pFormat value
     *
     * @param string $pFormat
     * @return string
     */
    public function Now(string $pFormat = self::DATE_FORMAT): string
    {
        if (isset($pFormat)) {
            return date($pFormat);
        } else {
            return date(self::DATE_FORMAT);
        }
    }

    /**
     * Logging information in database table
     *
     * @param array $data
     * @return void
     */
    public function createDBLog(array $pParams): void
    {
        if (!$this->connection) {
            $this->connection = $this->connHandler();
        }
        $pParams["created_at"] = $this->Now();
        $pParams["ipaddr"] = $_SERVER["IP_ADDR"];
        $this->insert(self::TABLE_ERROR_LOGS, $pParams);
    }
}
