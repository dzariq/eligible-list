<?php

class ORM {
    private $connection;

    public function __construct($hostname, $username, $password, $database) {
        $this->connection = new mysqli($hostname, $username, $password, $database);

        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
    }

    public function query($sql) {
        return $this->connection->query($sql);
    }

    public function select($table, $columns = '*', $condition = '') {
        $sql = "SELECT $columns FROM $table";
        if ($condition != '') {
            $sql .= " WHERE $condition";
        }
        return $this->query($sql);
    }

    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $values = "'" . implode("', '", array_values($data)) . "'";
        $sql = "INSERT INTO $table ($columns) VALUES ($values)";
        return $this->query($sql);
    }

    public function update($table, $data, $condition) {
        $set = '';
        foreach ($data as $key => $value) {
            $set .= "$key = '$value', ";
        }
        $set = rtrim($set, ', ');
        $sql = "UPDATE $table SET $set WHERE $condition";
        return $this->query($sql);
    }

    public function delete($table, $condition) {
        $sql = "DELETE FROM $table WHERE $condition";
        return $this->query($sql);
    }

    public function __destruct() {
        $this->connection->close();
    }
}
?>
