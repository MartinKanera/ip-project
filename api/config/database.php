<?php
  class Database {
    private $host = 
    private $db_name = 
    private $username = 
    private $password = 
    private $charset = 
    private $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
    ];
    private $conn;

    public function connect() {
      $this->conn = null;

      try {
        $this->conn = new PDO('mysql:host=' . $this->host .';dbname=' . $this->db_name . ';charset=' . $this->charset, $this->username, $this->password, $this->options);
      } catch (PDOException $e) {
        echo 'Connection error: '. $e;
      }

      return $this->conn;
    }
  }
?>
