<?php
    class func{
        private $conn, $tbl, $fields;
        public function __construct($tableName, $dbName)
        {
            $this->setTable($tableName);
            $this->connectDb("localhost", $dbName, "root", "1377");
        }
        public function connectDb(string $host, string $db, string $user, string $pass, string $options=null)
        {
            if(!isset($options)){
                $options = [
                    PDO::ATTR_EMULATE_PREPARES   => false, // turn off emulation mode for "real" prepared statements
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, //turn on errors in the form of exceptions
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, //make the default fetch be an associative array
                ];
            }
            // $this->conn = config_func($host, $db, $user, $pass, $options);
            $dsn="mysql:host=$host;dbname=$db; charset=utf8mb4";
            set_exception_handler(function($e) {
                error_log($e->getMessage());
                exit('Something weird happened'); //something a user can understand
            });
            try{
                $conn = new PDO($dsn, $user, $pass, $options);
                $this->conn = $conn;
            }catch(\PDOException $e){
                throw new \PDOException($e->getMessage(), (int)$e->getCode());
                die();
            }
        }
        public function setTable(string $tbl)
        {
            $this->tbl=$tbl;
        }
        public function setFields(array $fields)
        {
            $this->fields=$fields;
        }
        ////////* CRUD:
        ////* Create 
        public function create(array $values)
        {
            $fields=array_keys($values);
            foreach($fields as $field){
                if(!in_array($field, $this->fields))
                {
                    die("$field not found !!");
                    return;
                }
            }
            $fields=implode(", ", $fields);
            ////*
            $param=array_values($values);
            ////*
            $num=count($values);
            $place=str_repeat('?, ', $num-1)." ?";
            ////*
            $query="INSERT INTO $this->tbl ($fields) VALUES ($place)";
            $stmt=$this->conn->prepare($query);
            $stmt->execute($param);
        }
        ////* Read
        public function readRow($cond=null)
        {
            if(!isset($cond))
            {
                $query="SELECT * FROM $this->tbl";
                $stmt=$this->conn->prepare($query);
                $stmt->execute();
                $data=array();
                while($res=$stmt->fetch())
                    $data[]=$res;
                if(isset($data[0])) return $data;
                else return false;
            }
            else
            {
                $values_cond=array_values($cond);
                $keys_cond=array_keys($cond);
                array_walk($keys_cond, function(&$value, $key) { $value .= ' = ?'; } );
                if(count($cond)>1)
                    $keys_cond=implode(" AND ", $keys_cond);
                else
                    $keys_cond=$keys_cond[0];
                $query="SELECT * FROM $this->tbl WHERE $keys_cond";
                $stmt=$this->conn->prepare($query);
                $stmt->execute($values_cond);
            }            
            return $stmt->fetch();
        }    
        
        ////* Update
        public function update(array $cond, array $newVal)
        {
            $values_newVal = array_values($newVal);
            $keys_cond=array_keys($cond);
            //////! append '=?' to array values
            $fields_newVal = implode(" = ?, ",array_keys($newVal))." = ?";
            ////!
            array_walk($keys_cond, function(&$value, $key) { $value .= ' = ?'; } ); ////! append '=?' to array values
            $fields_cond=count($keys_cond)>1 ? implode(" AND ", $keys_cond) : $keys_cond[0];
            $values_cond=array_values($cond);
            $values=array_merge($values_newVal, $values_cond); ////! merge values
            $query="UPDATE $this->tbl SET $fields_newVal WHERE $fields_cond";
            $stmt=$this->conn->prepare($query);
            $stmt->execute($values);
        }
        ////* Delete
        public function delete(array $cond)
        {
            $values_cond=array_values($cond);
            $keys_cond=array_keys($cond);
            array_walk($keys_cond, function(&$value, $key) { $value .= ' = ?'; } ); ////! append '=?' to array values
            if(count($keys_cond)>1)
                $fields_cond=implode(" AND ", $keys_cond);
            else
                $fields_cond=$keys_cond[0];
            $query="DELETE FROM $this->tbl WHERE $fields_cond";
            $stmt=$this->conn->prepare($query);
            $stmt->execute($values_cond);
        }
    }
?>