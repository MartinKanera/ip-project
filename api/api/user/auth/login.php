<?php
  header('Access-Control-Allow-Origin: *');
  header('Content-Type: application/json');
  header('Accesss-Control-Allow-Methods: POST');
  header('Access-Control-Allow-Headers: aplication/json');

  include_once('../../config/Database.php');
  include_once('../../models/User.php');

  $database = new Database();
  $db = $database->connect();

  $user = new User($db);
  
  if(isset($_POST['login']) && isset($_POST['password'])) {
    $username = strtolower(trim($_POST['login']));
    $password = trim($_POST['password']);

    echo $user->login($username, $password);
  } else {
    echo json_encode(array(
    'message' => 'No credentials provided',
  ));
  }
?>