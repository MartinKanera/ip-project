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

  $json = file_get_contents('php://input');
  $data = json_decode($json)->data;

  $first_name = ucfirst(trim(filter_var($data->first_name)));
  $last_name = ucfirst(trim(filter_var($data->last_name)));
  $position  = strtolower(trim(filter_var($data->position)));
  $salary = filter_var($data->salary, FILTER_SANITIZE_NUMBER_INT);
  $room_id = filter_var($data->room_id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
  $login = strtolower(trim(filter_var($data->login)));
  $password = trim(filter_var($data->password));
  $admin = filter_var($data->admin, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0, "max_range" => 1]]);
  $selected_rooms_id = $data->selected_rooms_id ?? array(); 

  $regex = '/^[A-ZĚŠČŘŽÝÁÍÉ][a-zA-Zěščřžýáíé]{1,}$/';

  if(!$first_name || !$last_name || !$position || !$salary || !$room_id || !$login || !$password || !preg_match($regex, $first_name)
    || !preg_match($regex, $last_name) || !preg_match('/^[a-z0-9ěščřžýáíé]{2,}$/', $position) || !preg_match('/^[a-zěščřžýáíé0-9]{4,}/', $login) || !preg_match('/^[^ \n]{6,}$/', $password)) {
    http_response_code(400);
    echo json_encode(array(
      'message' => 'Parameters are not fulfilled'
    ));
    exit;
  }
  
include_once '../../shared/data.php';

  $check = verify_JWT($payload);

  if($check['admin']) {
    include_once('../../config/database.php');
    include_once('../../models/user.php');

    $database = new Database();
    $db = $database->connect();
    
    $user = new User($db);

    
    if($user->create($first_name, $last_name, $position, $salary, $room_id, $login, $password, $admin, $selected_rooms_id)) {
      http_response_code(201);
    } else {
      http_response_code(500);
      echo json_encode(array(
        'message' => 'Failed to create user'
      ));
    }
  }

?>