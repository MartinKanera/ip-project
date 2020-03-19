<?php
  header('Access-Control-Allow-Origin: *');
  header('Content-Type: application/json');
  header('Access-Control-Allow-Methods: POST');
  header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-methods, Authorization');

  require '../../vendor/autoload.php';
  use \Firebase\JWT\JWT;
  use \Firebase\JWT\SignatureInvalidException;

  $header = apache_request_headers();
  $key = '//Secret//super//Secret//';
  $jwt = explode(' ', $header['Authorization'])[1];

  try {
    $payload = JWT::decode($jwt, $key, array('HS256'));
  } catch (Exception $e) {
    http_response_code(401);
    echo json_encode(array(
      'message' => 'Invalid token'
    ));
    die();
  }

  $id = filter_input(
    INPUT_POST,
    'id',
    FILTER_VALIDATE_INT,
    ["options" => ["min_range" => 0]]
  );

  $number = filter_input(
    INPUT_POST,
    'number',
    FILTER_SANITIZE_NUMBER_INT,
    []
  );

  $name = filter_input(
    INPUT_POST,
    'name',
    FILTER_DEFAULT,
    []
  );

  $telephone = filter_input(
    INPUT_POST,
    'telephone',
    FILTER_SANITIZE_NUMBER_INT,
    []
  );

  if(!$id || !$number || !$name) {
    http_response_code(400);
    echo json_encode(array(
      'message' => 'Parameters are not fulfilled'
    ));
    exit;
  }

  if($payload->admin > 0) {
    include_once('../../config/Database.php');
    include_once('../../models/Room.php');

    $database = new Database();
    $db = $database->connect();

    $room = new Room($db);

    if($room->update($id, $number, $name, $telephone)) {
      echo json_encode(array(
        'message' => 'Room updated successfully'
      ));
    } else {
      echo json_encode(array(
        'message' => 'Room update failed'
      ));
    }
  }
?>