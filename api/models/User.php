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

            return json_encode(array(
              'message' => 'Password changed successfully',
            ));

          } catch (PDOException $e) {
            return json_encode(array(
              'message' => $e->getMessage(),
            ));

          }
        } else {
          return json_encode(array(
              'message' => 'Wrong password provided',
          ));
        }
      }
    }

    public function read ($order_by, $sort) {
      $query = 'SELECT clovek_id, jmeno, prijmeni, nazev as mistnost, telefon, pozice 
                FROM ' . $this->table  . 
                ' JOIN '. $this->sec_table .' ON(mistnosti.mistnost_id = lide.mistnost)
                ORDER BY ' . $order_by . ' ' . $sort;

      $stmt = $this->conn->prepare($query);

      $stmt->execute();

      return $stmt;
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

    public function create ($first_name, $last_name, $position, $salary, $room_id, $login, $password, $admin, $selected_rooms_id = array()) {
      $query = 'INSERT INTO ' . $this->table . ' 
                SET jmeno = :first_name, prijmeni = :last_name, pozice = :position, plat = :salary, mistnost = :room_id, login = :login, hash = :hash, admin = :admin';

      $stmt = $this->conn->prepare($query);

      $first_name = ucfirst($first_name);
      $last_name = ucfirst($last_name);
      $position = mb_strtolower($position);
      $login = mb_strtolower(trim($login));
      $hash = password_hash($password, PASSWORD_BCRYPT, array('cost' => 10));

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
  }
?>
