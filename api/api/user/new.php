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

  $first_name = filter_input(
    INPUT_POST,
    'first_name',
    FILTER_DEFAULT,
    []
  );

  $last_name = filter_input(
    INPUT_POST,
    'last_name',
    FILTER_DEFAULT,
    []
  );

  $position = filter_input(
    INPUT_POST,
    'position',
    FILTER_DEFAULT,
    []
  );

  $salary = filter_input(
    INPUT_POST,
    'salary',
    FILTER_SANITIZE_NUMBER_INT,
    []
  );

  $room_id = filter_input(
    INPUT_POST,
    'room_id',
    FILTER_VALIDATE_INT,
    ["options" => ["min_range" => 0]]
  );

  $login = filter_input(
    INPUT_POST,
    'login',
    FILTER_DEFAULT,
    []
  );

  $password = filter_input(
    INPUT_POST,
    'password',
    FILTER_DEFAULT,
    []
  );

  $admin = filter_input(
    INPUT_POST,
    'admin',
    FILTER_VALIDATE_INT,
    ["options" => ["min_range" => 0]]
  );

  $selected_rooms_id = filter_input(
    INPUT_POST,
    'selected_rooms_id',
    FILTER_DEFAULT,
    []
  );

  if(!$first_name || !$last_name || !$position || !$salary || !$room_id || !$login || $password === false || $password === null) {
    http_response_code(400);
    echo json_encode(array(
      'message' => 'Parameters are not fulfilled'
    ));
    exit;
  }

  if($payload->admin > 0) {
    include_once('../../config/Database.php');
    include_once('../../models/User.php');

    $database = new Database();
    $db = $database->connect();
    
    $user = new User($db);

    
    if($user->create($first_name, $last_name, $position, $salary, $room_id, $login, $password, $admin, $selected_rooms_id)) {
      http_response_code(201);
      echo json_encode(array(
        'message' => 'User created successfully'
      ));
    } else {
      echo json_encode(array(
        'message' => 'Failed to create user'
      ));
    }
  }

?>