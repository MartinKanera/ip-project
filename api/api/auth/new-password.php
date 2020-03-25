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
$old_password = $data->old_password;
$new_password = $data->new_password;

  if(!$id || !$old_password || !$new_password) {
    http_response_code(400);
    echo json_encode(array(
      'message' => 'Parameters are not fulfilled'
    ));
    exit;
  }

  include_once('../../config/Database.php');
  include_once('../../models/User.php');

  $database = new Database();
  $db = $database->connect();

  $user = new User($db);

  $old_password = trim($old_password);
  $new_password = trim($new_password);

  if($user->change_password($id, $old_password, $new_password)) {
    http_response_code(204);
  } else {
    http_response_code(400);
  }
?>