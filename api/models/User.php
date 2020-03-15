<?php
  class User {
    private $conn;
    private $table = 'lide';
    private $sec_table = 'mistnosti';

    public $clovek_id;
    public $jmeno;
    public $prijmeni;
    public $pozice;
    public $plat;
    public $mistnost;
    public $login;
    public $password;
    public $admin;
    // Foreign key
    public $mistnost_id;

    public function __construct($db) {
      $this->conn = $db;
    }

    public function read ($order_by = 'jmeno', $sort = 'asc') {
      $order_by = in_array(strtolower($order_by), array('jmeno', 'prijmeni', 'mistnost', 'telefon', 'pozice')) ? $order_by : 'jmeno';
      $sort = in_array(strtolower($sort), array('asc', 'desc')) ? $sort : 'ASC';

      $query = 'SELECT clovek_id, jmeno, prijmeni, nazev as mistnost, telefon, pozice 
                FROM ' . $this->table  . 
                ' JOIN '. $this->sec_table .' ON(mistnosti.mistnost_id = lide.mistnost)
                ORDER BY ' . $order_by . ' ' . $sort;

      $stmt = $this->conn->prepare($query);

      $stmt->execute();

      return $stmt;
    }
  } 
?>
