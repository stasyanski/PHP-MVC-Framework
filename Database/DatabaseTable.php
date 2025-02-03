<?php
namespace Database;

class DatabaseTable {
    /**
     * The constructor is used to initialise the variables with a default value
     * it needs the PDO connection, table to query in, and the PK of this table
     * @param \PDO $pdo instance for db connection
     * @param mixed $table table to query in
     * @param mixed $primaryKey the primary key within that table
     */
    public function __construct(
        public \PDO $pdo,
        public mixed $table,
        public mixed $primaryKey
    ) {}

    /**
     * The function find takes in a field and value to find records by but also supports
     * ordering by and sorting if needed
     * @param mixed $field the column name to query for
     * @param mixed $value the value to perform restriction by in the column to query for
     * @param mixed $orderBy [optional] the column to order by is optional, used for sorting, e.g. DATETIME sort by asc or desc
     * @param string|null $sort [optional] the sorting itself, can be desc or asc
     * @return array returns an associative array of the fetched results from the db
     * @throws \InvalidArgumentException if the sort parameter is not either ascending or descending, or if field is provided but sort isn't
     * @throws \PDOException if there is an error during the execution of the stmt
     */
    public function find(mixed $field, mixed $value, mixed $orderBy = null, string|null $sort = null): array{
        // validate that the value passed is not empty before executing rest of the function
        if (empty($field) || empty($value)) {
             throw new \InvalidArgumentException('The parameters passed to find() function must not be empty in DatabaseTable.');
        }
        $sql = 'SELECT * FROM ' . $this->table . ' WHERE ' . $field . ' = :value';
        // validate that the values passed meet the requirements
        if ($field !== null && $sort !== null) {
            if (strtolower($sort) === 'asc' || strtolower($sort) === 'desc') {
                $sql.= ' ORDER BY ' . $orderBy . ' ' . strtoupper($sort);
            } else {
                throw new \InvalidArgumentException('Sort parameter must be asc or desc in find() in DatabaseTable.');
            }
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['value' => $value]);
        return $stmt->fetchAll();
    }
    // The save function has been commented out
    // It is not used anywhere in the code as of now
    // But may be useful in the future
    //
    //    public function save($record): void {
    //        if ($record[$this->primaryKey] === '') {
    //            unset($record[$this->primaryKey]);
    //        }
    //        try {
    //            $this->insert($record);
    //        }
    //        catch (\Exception) {
    //            $this->update($record);
    //        }
    //    }
    /**
     * This function retrieves all records from a db with restriction clauses on certain field and optional sorting
     * field and sorting defaults to null, they are not always needed
     * @param mixed $field [optional] the field to sort by, can be any type
     * @param string|null $sort [optional] the sorting can either be desc or asc, so either null or string
     * @return array returns an associative array of the query
     * @throws \PDOException if the stmt produced errors
     * @throws \InvalidArgumentException if the sort parameter is not either ascending or descending, or if field is provided but sort isn't
     */
    public function findAll(mixed $field = null, string|null $sort = null): array {
        $query = 'SELECT * FROM ' . $this->table;
        // validate that the value passed is not empty before executing rest of the function
        if ($field !== null && $sort !== null) {
            if (strtolower($sort) === 'asc' || strtolower($sort) === 'desc') {
                $query .= ' ORDER BY ' . $field . ' ' . strtoupper($sort);
            } else {
                throw new \InvalidArgumentException('Sort parameter must be asc or desc in findAll() in DatabaseTable.');
            }
        }
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            throw new \PDOException('FindAll() failed to execute in DatabaseTable: ' . $e->getMessage());
        }
    }
    /**
     * Updates a record in the database taking in the record to be updated
     * @param array $record associative array as parameter where keys are column names and values are insert, must include the PK for where clause
     * @return void no return.
     * @throws \PDOException if there was an error during execution of stmt
     * @throws \InvalidArgumentException if the record is empty or if it does not have a primary key passed
    */
    public function update(array $record): void {
        // validate that the value passed is not empty before executing rest of the function
        if (empty($record[$this->primaryKey])) {
            throw new \InvalidArgumentException('Primary key passed is empty update() in DatabaseTable.');
        }
        if (empty($record)) {
            throw new \InvalidArgumentException('Record passed to update() function cannot be empty in DatabaseTable.');
        }
        $query = 'UPDATE ' . $this->table . ' SET ';
        $parameters = [];
        foreach ($record as $key => $value) {
            $parameters[] = $key . ' = :' .$key;
        }
        $query .= implode(', ', $parameters);
        $query .= ' WHERE ' . $this->primaryKey . ' = :primaryKey';
        $record['primaryKey'] = $record[$this->primaryKey];
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($record);
        } catch (\PDOException $e) {
            throw new \PDOException('Failed to use update() in Database Table: ' . $e->getMessage());
        }
    }
    /**
     * This function takes an array of values to be inserted
     * into the database, keys are column names and values are inserts
     * @param array $values associative array as parameter where keys are column names and values are inserts
     * @return void no return.
     * @throws \PDOException if there was an error during execution of stmt
     * @throws \InvalidArgumentException is value passed is empty
     */
    public function insert(array $values): void {
        // validate that the value passed is not empty before executing rest of the function
        if (empty($values)) {
            throw new \InvalidArgumentException('Values passed to insert() function must not be empty in DatabaseTable');
        }
        $query = 'INSERT INTO ' . $this->table . ' (';
        $keys = array_keys($values);
        $query = $query . implode(', ', $keys);
        $query = $query . ')';
        $query = $query . ' VALUES (';
        $keyWithColon = implode(', :', $keys);
        $query = $query . ':' . $keyWithColon . ')';
        $query = $query . '';
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($values);
        } catch (\PDOException $e) {
            throw new \PDOException('Failed to use insert() in DatabaseTable: ' .$e->getMessage());
        }
    }
    /**
     * Added function to delete via passed value for primary key
     * the function takes in the value which should match the primary key
     * @param mixed $value parameter supports any type of variable passed, so any pk can be queried for
     * @return int the rowcount affected from the delete query is returned as int
     * @throws \PDOException if the stmt->execute has an error which is caught
     * @throws \InvalidArgumentException if the value passed is empty
     */
    public function delete(mixed $value): int {
        // validate that the value passed is not empty before executing rest of the function
        if (empty($value)) {
            throw new \InvalidArgumentException('Value passed to delete() function must not be empty in DatabaseTable.');
        }
        $query = 'DELETE FROM ' . $this->table . ' WHERE ' . $this->primaryKey . ' = :value';
        try {
            $stmt = $this->pdo->prepare($query);
            $values = ['value' => $value];
            $stmt->execute($values);
            // returns the rows affected to be printed in front end (e.g. 1 Article deleted, 0 Article deleted)
            return $stmt->rowCount();
        } catch (\PDOException $e) {
            throw new \PDOException('Failed to use delete() in DatabaseTable: ' . $e->getMessage());
        }
    }
}