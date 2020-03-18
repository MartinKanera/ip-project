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
  
  if(isset($_POST['id']) && isset($_POST['old_password']) && isset($_POST['new_password'])) {
    $id = $_POST['id'];
    $old_password = trim($_POST['old_password']);
    $new_password = trim($_POST['new_password']);

    echo $user->change_password($id, $old_password, $new_password);
  } else {
    http_response_code(401);
    echo json_encode(array(
    'message' => 'Fill all fields',
  ));
  }
?>