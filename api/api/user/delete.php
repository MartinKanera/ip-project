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

  
  if(!$id) {
    http_response_code(400);
    echo json_encode(array(
      'message' => 'Parameters are not fulfilled'
    ));
    exit;
  }

  include_once '../../shared/data.php';

  $check = verify_JWT($payload);
  
  if($check['admin'] !== 1){
    http_response_code(401);
    echo json_encode(
      array(
        'message' => 'Unauthorized'
      ));
    die();
  }

  if($check['user_id'] === $id) {
    http_response_code(422);
    die();
  }

  include_once('../../config/database.php');
  include_once('../../models/user.php');

  $database = new Database();
  $db = $database->connect();

  $user = new User($db);

  if($user->delete($id)) {
    http_response_code(204);
  } else {
    http_response_code(409);
    echo json_encode(array(
      'message' => 'Failed to delete user'
    ));
  }
?>


