<?php
class MyApi
{
    private $conn, $tbl, $fields, $error_connecttion = null, $unique_fields = null;
    public function __construct($tableName, $dbName, $unique = null)
    {
        if (isset($unique))
            $this->setUnique($unique);
        $this->setTable($tableName);
        $this->connectDb("localhost", $dbName, "root", "1377");
    }
    public function connectDb(string $host, string $db, string $user, string $pass, string $options = null)
    {
        if (!isset($options)) {
            $options = [
                PDO::ATTR_EMULATE_PREPARES   => false, // turn off emulation mode for "real" prepared statements
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, //turn on errors in the form of exceptions
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, //make the default fetch be an associative array
            ];
        }
        $dsn = "mysql:host=$host;dbname=$db; charset=utf8mb4";
        set_exception_handler(function ($e) {
            $this->setErrorConnection(["error-code" => 500, "error-message" => $e->getMessage()]);
        });
        try {
            $conn = new PDO($dsn, $user, $pass, $options);
            $this->conn = $conn;
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int) $e->getCode());
        }
    }
    public function setUnique($unique)
    {
        $this->unique_fields = $unique;
    }
    public function getUnique()
    {
        return $this->unique_fields;
    }
    private function setErrorConnection($error)
    {
        $this->error_connecttion = $error;
    }
    public function getErrorConnection()
    {
        return $this->error_connecttion;
    }
    public function setTable(string $tbl)
    {
        $this->tbl = $tbl;
    }
    public function setFields(array $fields)
    {
        $this->fields = $fields;
    }
    private function existUniqueField($values)
    {
        $cond = [];
        foreach ($values as $key => $value) {
            if (in_array($key, $this->unique_fields))
                $cond[$key] = $value;
        }
        if (!$exist_field = $this->readRow($cond, 'OR'))
            return false;
        $cond_values = implode(",", $cond);
        $cond_keys = implode(",", array_keys($cond));
        $err_mess = "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '$cond_values' for keys '$cond_keys'";
        $this->setErrorConnection([
            "error-code" => 500,
            "error-message" => $err_mess
        ]);
        return true;
    }
    ////////* CRUD:
    ////* Create 
    public function create(array $values)
    {
        // var_dump($this->unique_fields);
        // var_dump(!$this->readRow([$this->unique_fields[0]=>$values[$this->unique_fields[0]]]));
        $unique=$this->unique_fields;
        if (($this->readRow([$unique[0]=>$values[$unique[0]]])))
        {
            $this->setErrorConnection([
                "error-code" => 500,
                "error-message" => "$unique[0] has already been registered"
            ]);
            return false;
        }
        if (sizeof($unique) > 0)
            if ($this->existUniqueField($values))
                return false;
        $fields = array_keys($values);
        $fields = implode(", ", $fields);
        ////*
        $param = array_values($values);
        ////*
        $num = count($values);
        $place = str_repeat('?, ', $num - 1) . " ?";
        ////*
        $query = "INSERT INTO $this->tbl ($fields) VALUES ($place)";
        $stmt = $this->conn->prepare($query);

        if ($stmt->execute($param))
            return $this->readRow(["username" => $values["username"]]);
        else return false;
    }
    ////* Read
    public function readRow($cond = null, $operator = 'AND')
    {
        if (!isset($cond)) {
            $query = "SELECT userid, username, firstname, lastname FROM $this->tbl";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
        } else {
            $values_cond = array_values($cond);
            $keys_cond = array_keys($cond);
            array_walk($keys_cond, function (&$value, $key) {
                $value .= ' = ?';
            });

            $keys_cond = count($cond) > 1 ? implode($operator, $keys_cond) : $keys_cond[0]; //! $operator = OR or AND
            $query = "SELECT userid, username, firstname, lastname FROM $this->tbl WHERE $keys_cond";
            $stmt = $this->conn->prepare($query);
            $stmt->execute($values_cond);
        }
        $data = [];
        while ($res = $stmt->fetch())
            $data[] = $res;

        if (sizeof($data) == 0) {
            $this->setErrorConnection([
                "error-code" => 404,
                "error-message" => "STATE[404]: Not found"
            ]);
            return false;
        }
        return $data;
    }

    ////* Update
    public function update(array $cond, array $newVal)
    {

        if (!$this->readRow($cond))
            return false;
        if (sizeof($this->unique_fields) > 0)
            if ($this->existUniqueField($newVal))
                return false;
        //
        $values_newVal = array_values($newVal);
        $keys_cond = array_keys($cond);
        $values_cond = array_values($cond);
        //////! append '=?' to array values
        $fields_newVal = implode(" = ?, ", array_keys($newVal)) . " = ?";
        ////!
        array_walk($keys_cond, function (&$value, $key) {
            $value .= ' = ?';
        }); ////! append '=?' to array values
        $fields_cond = count($keys_cond) > 1 ? implode(" AND ", $keys_cond) : $keys_cond[0];
        $values = array_merge($values_newVal, $values_cond); ////! merge values
        $query = "UPDATE $this->tbl SET $fields_newVal WHERE $fields_cond";
        $stmt = $this->conn->prepare($query);
        if ($stmt->execute($values))
            return $this->readRow(["username" => $newVal["username"]]);
        else return false;
    }
    ////* Delete
    public function delete(array $cond)
    {
        $row = $this->readRow($cond);
        if (!$row)
            return false;
        $values_cond = array_values($cond);
        $keys_cond = array_keys($cond);
        array_walk($keys_cond, function (&$value, $key) {
            $value .= ' = ?';
        }); ////! append '=?' to array values
        if (count($keys_cond) > 1)
            $fields_cond = implode(" AND ", $keys_cond);
        else
            $fields_cond = $keys_cond[0];
        $query = "DELETE FROM $this->tbl WHERE $fields_cond";
        $stmt = $this->conn->prepare($query);
        if ($stmt->execute($values_cond))
            return $row;
        return false;
    }
   
}
