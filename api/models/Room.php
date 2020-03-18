<?php
  class Room {
    private $table = 'mistnosti';
    private $sec_table = 'lide';

    private $conn;

    public function __construct($db) {
      $this->conn = $db;
    }

    public function read ($order_by, $sort) {
      $query = 'SELECT mistnost_id, nazev, cislo, telefon 
                FROM ' . $this->table  . 
                ' ORDER BY ' . $order_by . ' ' . $sort;

      $stmt = $this->conn->prepare($query);

      $stmt->execute();

      return $stmt;
    }

    public function create ($number, $name, $telephone) {
      $error = 'Wrong data type provided';

      $name = trim($name);

      $query = 'INSERT INTO ' . $this->table . ' SET cislo = :number, nazev = :name, telefon = :telephone';

      $stmt = $this->conn->prepare($query);

      $stmt->bindParam(':number', $number);
      $stmt->bindParam(':name', $name);
      $stmt->bindParam(':telephone', $telephone);

      if($stmt->execute()) {
        return true;
      }

      return false;
    }

    public function rooms_keys () {
      $query = 'SELECT nazev, mistnost_id FROM ' . $this->table . ' ORDER BY nazev';
      
      $stmt = $this->conn->prepare($query);

      $stmt->execute();

      return $stmt;
    }

    public function room_card ($id) {
      $query_room = 'SELECT cislo, nazev, telefon FROM ' . $this->table .' WHERE mistnost_Id = :id';
      $query_people = 'SELECT prijmeni, jmeno, clovek_id FROM ' . $this->table . ' JOIN ' . $this->sec_table .' ON (mistnost_id = lide.mistnost) WHERE mistnost_Id = :id ORDER BY prijmeni';
      $query_average = 'SELECT AVG(lide.plat) as "prumer" FROM ' . $this->table .' JOIN ' . $this->sec_table .' ON (mistnost_id = lide.mistnost) WHERE mistnost_Id = :id';
      $query_keys = 'SELECT prijmeni, jmeno, clovek_id FROM ' . $this->sec_table . ' JOIN klice ON (lide.clovek_id = klice.clovek) WHERE klice.mistnost= :id ORDER BY prijmeni';
      
      $stmt_room = $this->conn->prepare($query_room);
      $stmt_people = $this->conn->prepare($query_people);
      $stmt_average = $this->conn->prepare($query_average);
      $stmt_keys = $this->conn->prepare($query_keys);

      $stmt_room->bindParam(':id', $id);
      $stmt_people->bindParam(':id', $id);
      $stmt_average->bindParam(':id', $id);
      $stmt_keys->bindParam(':id', $id);

      $stmt_room->execute();
      $stmt_people->execute();
      $stmt_average->execute();
      $stmt_keys->execute();

      return array(
        'stmt_room' => $stmt_room,
        'stmt_people' => $stmt_people,
        'stmt_average' => $stmt_average,
        'stmt_keys' => $stmt_keys,
      );
    }
  }
?>