<?php
/**
 * Created by PhpStorm.
 * User: kudesnik
 * Date: 27.10.2018
 * Time: 0:09
 */

namespace donjose;

class PDOWrapper
{

    protected $dbh;
    protected $stmt;


    public function __construct($host,$user,$db_name,$password)
    {
        $this->dbh = new \PDO(
            "mysql:host=" . $host . ";dbname=" . $db_name,
            $user,
            $password,
            array(
                \PDO::ATTR_PERSISTENT         => true,
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
            )
        );
    }

    public function query($query) {
        $this->stmt = $this->dbh->prepare($query);
        return $this;
    }

    public function execute($data = array()) {
        if (!empty($data) && is_array($data)) {

            foreach($data as $key=>$value) {
                switch (true) {
                    case is_bool($value):
                        $var_type = \PDO::PARAM_BOOL;
                        break;
                    case is_int($value):
                        $var_type = \PDO::PARAM_INT;
                        break;
                    case is_null($value):
                        $var_type = \PDO::PARAM_NULL;
                        break;
                    default:
                        $var_type = \PDO::PARAM_STR;
                }
                $this->stmt->bindValue(":$key",$value,$var_type);
            }
        }
        $this->stmt->execute();
        return $this;
    }

    public function findAll() {
        $this->stmt->execute();
        return $this->stmt->fetchAll();
    }
    public function rowsCount($table, $condition = '') {
        $this->stmt = $this->dbh->query("SELECT count(*) FROM $table $condition");
        return $this->stmt->fetchColumn();
    }
    public function rowCount() {
        $this->stmt->execute();
        return $this->stmt->rowCount();
    }

    public function findOne() {
        $this->stmt->execute();
        return $this->stmt->fetch();
    }

    public function insert($table,array $data) {
        $sql = "INSERT INTO $table(" . implode(",",array_keys($data)).") VALUES(:" . implode(",:",array_keys($data)).")";

        $this->stmt = $this->dbh->prepare($sql);
        $this->execute($data);
        return $this->dbh->lastInsertId();
    }

    public function select($table,$condition = null,$fields = '*') {
        $sql = "SELECT $fields FROM $table";
        if (!is_null($condition)) $sql .= ' WHERE ' . $condition;
        $this->stmt = $this->dbh->prepare($sql);

        $this->execute();
        return $this;
    }

    public function update($table,array $data, $condition = null) {
        $sql = "UPDATE $table SET ";
        if (!empty($data)) {

            foreach ($data as $field => $value) {
                $binds[] = "$field = :$field";
            }
            $sql .= implode(",",$binds) . ' ';
        }
        if (!is_null($condition)) $sql .= ' WHERE ' . $condition;
        $this->query($sql)->execute($data);
        return $this->stmt->rowCount();

    }

    public function delete($table, $condition = null) {
        $sql = "DELETE FROM $table ";
        if (!is_null($condition)) $sql .= ' WHERE ' . $condition;
        $this->query($sql)->execute();
        return $this->stmt->rowCount();
    }

    public function drop($table) {
        $sql = "DROP TABLE $table";
        $this->query($sql)->execute();
        return $this->stmt->rowCount();
    }

    public function truncate($table) {
        $sql = "TRUNCATE TABLE $table";
        $this->query($sql)->execute();
        return $this->stmt->rowCount();
    }

}