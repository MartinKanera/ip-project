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

  $id = filter_var($data->id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
  $first_name = filter_var($data->first_name);
  $last_name = filter_var($data->last_name);
  $position  = filter_var($data->position);
  $salary = filter_var($data->salary, FILTER_SANITIZE_NUMBER_INT);
  $room_id = filter_var($data->room_id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
  $login = filter_var($data->login);
  $password = $data->password ?? '';
  $admin = filter_var($data->admin, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0, "max_range" => 1]]);
  $selected_rooms_id = $data->selected_rooms_id ?? array();

  if(!$first_name || !$last_name || !$position || !$salary || !$room_id || !$login || $admin === false || $admin === null) {
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
    
    if($user->update($id, $first_name, $last_name, $position, $salary, $room_id, $login, $password, $admin, $selected_rooms_id)) {
      http_response_code(204);
    } else {
      http_response_code(500);
      echo json_encode(array(
        'message' => 'Failed to update user'
      ));
    }
  }
?>