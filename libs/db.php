<?php
namespace libs;

class DB {
    private $connection;
    private $host = 'localhost';
    private $username = 'tester';
    private $passwd = '12345678';
    private $db_name = 'microblog';
    private $charset = 'utf8';

    public function __construct($db_config = array()) {
        if (!empty($db_config)) {
            $this->host = $db_config['host'];
            $this->username = $db_config['username'];
            $this->passwd = $db_config['passwd'];
            $this->db_name = $db_config['db_name'];
            $this->charset = $db_config['charset'];
        }
        $this->db_connect();
    }

    private function db_connect() {
        $this->connection = new \mysqli($this->host, $this->username, $this->passwd, $this->db_name);

        if (mysqli_connect_errno()) {
            throw new \Exception('Could not connect to database '.$this->db_name.'\n'.mysqli_connect_error());
        }

        $this->connection->set_charset($this->charset);
    }

    public function query($sql) {
        if ( !$this->connection ) {
            return false;
        }

        $result = $this->connection->query($sql);

        if (mysqli_error($this->connection)) {
            throw new \Exception(mysqli_error($this->connection));
        }

        if ( is_bool($result) ) {
            return $result;
        }

        $data = [];
        while ( $row = mysqli_fetch_assoc($result) ) {
            array_push($data, $row);
        }

        return $data;
    }

    public function escape($str) {
        return mysqli_escape_string($this->connection, $str);
    }

    public function last_insert_id() {
    	return $this->connection->insert_id;
    }

	public function affected_rows() {
		return $this->connection->affected_rows;
	}

    public function begin_transaction() {
	    $this->connection->autocommit(FALSE);
    }

    public function commit() {
	    $this->connection->commit();
    }

	public function rollback() {
		$this->connection->rollback();
	}
}