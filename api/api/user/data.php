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

  include_once('../../config/database.php');
  include_once('../../models/user.php');

  $database = new Database();
  $db = $database->connect();

  $user = new User($db);
  
  if($result = $user->data($payload->user_id, $payload->login)) {
    echo json_encode($result);
  } else {
    http_response_code(401);
    echo json_encode(array(
      'Message' => 'Invalid token provided'
    ));
  }
?>