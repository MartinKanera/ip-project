<?php
class User {
    private $conn;
    private $table = 'lide';
    private $sec_table = 'mistnosti';

    public function __construct($db) {
      $this->conn = $db;
    }

    public function login($username) {
      $query = 'SELECT clovek_id, hash, admin, jmeno, prijmeni FROM ' . $this->table . ' WHERE login = :login';

      $stmt = $this->conn->prepare($query);

      $stmt->bindParam(':login', $username);

      $stmt->execute();
      
      return $stmt;
    }

    public function data($id, $login) {
      $query = 'SELECT clovek_id, login, admin, jmeno, prijmeni FROM ' . $this->table . ' WHERE login = :login AND clovek_id = :id';

      $stmt = $this->conn->prepare($query);

      $stmt->bindParam(':login', $login);
      $stmt->bindParam(':id', $id);

      $stmt->execute();

      if($stmt->rowCount() === 1) {
        $row = $stmt->fetch();
        extract($row);

        return array(
          'user_id' => $clovek_id,
          'first_name' => $jmeno,
          'last_name' => $prijmeni,
          'admin' => $admin
        );
      }

      return false;
    }

    public function change_password($id, $old_password, $new_password) {
      $query = 'SELECT hash FROM ' . $this->table . ' WHERE clovek_id = :id';

      $stmt = $this->conn->prepare($query);

      $stmt->bindParam(':id', $id);

      $stmt->execute();

      if($stmt->rowCount() > 0) {
        $row = $stmt->fetch();
        extract($row);

        unset($query, $stmt);

        if(password_verify($old_password, $hash)) {
          try {
            // TODO: implement check if new password fulfils all criteria

            $query = 'UPDATE ' . $this->table . ' SET hash = :hash WHERE clovek_id = :id';

            $stmt = $this->conn->prepare($query);

            $new_hash = password_hash($new_password, PASSWORD_BCRYPT, array('cost' => 10));

            $stmt->bindParam(':hash', $new_hash);
            $stmt->bindParam(':id', $id);

            $stmt->execute();

            return true;

          } catch (PDOException $e) {
            return false;
          }
        } 
        return false;
      }
    }

    public function read ($order_by, $sort) {
      $query = 'SELECT clovek_id, jmeno, prijmeni, plat, login, admin, nazev, mistnost_id as mistnost, telefon, pozice
                FROM ' . $this->table  . 
                ' JOIN '. $this->sec_table .' ON(mistnosti.mistnost_id = lide.mistnost)
                ORDER BY ' . $order_by . ' ' . $sort;

      $query_rooms = 'SELECT mistnost_id, nazev as mistnost FROM ' . $this->sec_table;

      $query_keys = 'SELECT clovek, mistnost FROM klice';

      $stmt = $this->conn->prepare($query);
      $stmt_rooms = $this->conn->prepare($query_rooms);
      $stmt_keys = $this->conn->prepare($query_keys);

      $stmt->execute();
      $stmt_rooms->execute();
      $stmt_keys->execute();

      return array(
        'stmt' => $stmt,
        'stmt_rooms' => $stmt_rooms,
        'stmt_keys' => $stmt_keys
      );
    }

    public function user_card ($id) {
      $query_employee = 'SELECT jmeno, prijmeni, pozice, plat, mistnosti.nazev as "mistnost", mistnost_id 
                        FROM ' . $this->table . ' 
                        JOIN '. $this->sec_table . ' 
                        ON (mistnosti.mistnost_id = lide.mistnost) 
                        WHERE clovek_id = :id';

      $query_keys = 'SELECT nazev, mistnost_id 
                    FROM ' . $this->sec_table . '
                    JOIN klice 
                    ON (mistnosti.mistnost_id = klice.mistnost) 
                    WHERE klice.clovek = :id 
                    ORDER BY nazev';

      $stmt_employee = $this->conn->prepare($query_employee);
      $stmt_keys = $this->conn->prepare($query_keys);

      $stmt_employee->bindParam(':id', $id);
      $stmt_keys->bindParam(':id', $id);

      $stmt_employee->execute();
      $stmt_keys->execute();

      return array(
        'stmt_employee' => $stmt_employee,
        'stmt_keys' => $stmt_keys
      );
    }

    public function create ($first_name, $last_name, $position, $salary, $room_id, $login, $password, $admin, $selected_rooms_id) {
      $query = 'INSERT INTO ' . $this->table . ' 
                SET jmeno = :first_name, prijmeni = :last_name, pozice = :position, plat = :salary, mistnost = :room_id, login = :login, hash = :hash, admin = :admin';

      $stmt = $this->conn->prepare($query);

      $first_name = ucfirst($first_name);
      $last_name = ucfirst($last_name);
      $position = mb_strtolower($position);
      $login = mb_strtolower(trim($login));
      $hash = password_hash(trim($password), PASSWORD_BCRYPT, array('cost' => 10));

      $stmt->bindParam(':first_name', $first_name);
      $stmt->bindParam(':last_name', $last_name);
      $stmt->bindParam(':position', $position);
      $stmt->bindParam(':salary', $salary);
      $stmt->bindParam(':room_id', $room_id);
      $stmt->bindParam(':login', $login);
      $stmt->bindParam(':hash', $hash);
      $stmt->bindParam(':admin', $admin);

      if($stmt->execute()) {
        if(!$selected_rooms_id) return true;

        $query = 'SELECT clovek_id FROM ' . $this->table . ' WHERE login = :login';

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':login', $login);

        $stmt->execute();

        $row = $stmt->fetch();

        extract($row);

        $query = 'INSERT INTO klice (clovek, mistnost)
                  VALUES';

        foreach($selected_rooms_id as $room_id) {
          $query .= ' ('. $clovek_id .', ' . $room_id .'),';
        }

        $query = substr($query, 0, strlen($query) - 1);

        $stmt = $this->conn->prepare($query);

        if($stmt->execute()) {
          return true;
        } 

        return false;
      }

      return false;
    }

    public function update ($id, $first_name, $last_name, $position, $salary, $room_id, $login, $password, $admin, $selected_rooms_id) {
      $query = 'UPDATE ' . $this->table . ' 
                SET jmeno = :first_name, prijmeni = :last_name, pozice = :position, plat = :salary, mistnost = :room_id, login = :login,' . 
                (!$password ? '' : ' hash = :hash,')
                . ' admin = :admin WHERE clovek_id = :id';

      $stmt = $this->conn->prepare($query);

      $first_name = ucfirst($first_name);
      $last_name = ucfirst($last_name);
      $position = mb_strtolower($position);
      $login = mb_strtolower(trim($login));
      $hash = password_hash(trim($password), PASSWORD_BCRYPT, array('cost' => 10));

      $stmt->bindParam(':first_name', $first_name);
      $stmt->bindParam(':last_name', $last_name);
      $stmt->bindParam(':position', $position);
      $stmt->bindParam(':salary', $salary);
      $stmt->bindParam(':room_id', $room_id);
      $stmt->bindParam(':login', $login);
      if($password) $stmt->bindParam(':hash', $hash);
      $stmt->bindParam(':admin', $admin);
      $stmt->bindParam(':id', $id);

      if($stmt->execute()) {
        $query = 'DELETE FROM klice WHERE clovek = :id';

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $id);

        if($stmt->execute()) {
          if(count($selected_rooms_id) > 0) {
            $query = 'INSERT INTO klice (clovek, mistnost) VALUES';

            $count = count($selected_rooms_id);
            foreach($selected_rooms_id as $index => $room_id) {
              $query .= ' ('. $id .', ' . $room_id .')' . ($count - 1 == $index ? '' : ',');
            }

            $stmt = $this->conn->prepare($query);

            if($stmt->execute()) {
              return true;
            } 

            return false;

          } else {
            return true;
          }
        }

        return false;
      }
      
      return false;
    }

    public function delete ($id) {

      $query = 'DELETE FROM klice WHERE clovek = :id';

      $stmt = $this->conn->prepare($query);

      $stmt->bindParam(':id', $id);

      if($stmt->execute()) {
        $query = 'DELETE FROM ' . $this->table . ' WHERE clovek_id = :id';

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $id);

        if($stmt->execute()) return true;
        
        return false;
      }

      return false;
    }
  }
?>
