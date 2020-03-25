<?php
  header('Access-Control-Allow-Origin: *');
  header('Content-Type: application/json');
  header('Accesss-Control-Allow-Methods: POST');
  header('Access-Control-Allow-Headers: aplication/json');

  include_once('../../config/Database.php');
  include_once('../../models/User.php');

  require '../../vendor/autoload.php';
  use \Firebase\JWT\JWT;

  $database = new Database();
  $db = $database->connect();

  $user = new User($db);

  $key = '//Secret//super//Secret//';

  $json = file_get_contents('php://input');
  $data = json_decode($json)->data;

  $login = $data->login;
  $password = $data->password;

  if(!$login || !$password) {
    http_response_code(400);
    echo json_encode(array(
      'message' => 'Parameters are not fulfilled'
    ));
    exit;
  }
  
  $username = strtolower(trim($login));
  $password = trim($password);

  $stmt = $user->login($username);

  if($stmt->rowCount() > 0) {
      $row = $stmt->fetch();
      extract($row);

      if(password_verify($password, $hash)) {
        $payload = array(
          'user_id' => $clovek_id,
          'first_name' => $jmeno,
          'last_name' => $prijmeni,
          'admin' => $admin
        );

        $jwt =JWT::encode($payload, $key);

        echo json_encode(array(
          'message' => 'Login successful',
          'jwt' => $jwt
        ));
      } else {
        http_response_code(400);
        echo json_encode(array(
          'message' => 'Wrong password'
        ));
      }
    } else {
      http_response_code(400);
      echo json_encode(array(
      'message' => 'Wrong username'
      ));
    }
?>