<?php
  use \Firebase\JWT\JWT;
  use \Firebase\JWT\SignatureInvalidException;

  class User {
    private $conn;
    private $table = 'lide';
    private $sec_table = 'mistnosti';
    private $key = '//Secret//super//Secret//';

    public function __construct($db) {
      $this->conn = $db;
    }

    public function read ($order_by, $sort) {
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

    public function login($username, $password) {
      require '../../vendor/autoload.php';

      $query = 'SELECT clovek_id, login, hash, admin, jmeno, prijmeni FROM ' . $this->table . ' WHERE login = :login';

      $stmt = $this->conn->prepare($query);

      $stmt->bindParam(':login', $username);

      $stmt->execute();
      
      if($stmt->rowCount() > 0) {
        $row = $stmt->fetch();
        extract($row);

        if(password_verify($password, $hash)) {
          session_start();
          
          $payload = array(
            'user_id' => $clovek_id,
            'first_name' => $jmeno,
            'last_name' => $prijmeni,
            'admin' => $admin
          );

          $jwt =JWT::encode($payload, $this->key);

          return json_encode(array(
            'message' => 'Login successful',
            'jwt' => $jwt
          ));
        }
        return json_encode(array(
          'message' => 'Wrong password'
        ));
      }
      return json_encode(array(
        'message' => 'Wrong username'
      ));
    }

    public function change_password($id, $old_password, $new_password) {
      $query = 'SELECT clovek_id, hash FROM ' . $this->table . ' WHERE clovek_id = :id';

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
  } 
?>
