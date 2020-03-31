<?php
  header('Access-Control-Allow-Origin: *');
  header('Content-Type: application/json');
  header('Accesss-Control-Allow-Methods: GET');

  include_once('../../config/database.php');
  include_once('../../models/user.php');

  $id = filter_var($_GET['id'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);

  $database = new Database();
  $db = $database->connect();

  $user = new User($db);

  if(!$id) {
    http_response_code(400);
    echo json_encode(array(
      'message' => 'Id does not fulfill requirements'
    ));
    exit;
  }
  
  $stmt = $user->user_card($id);

  $result = null;

  if($stmt['stmt_employee']->rowCount() > 0) {
    $row = $stmt['stmt_employee']->fetch();
    extract($row);

    $result = array(
      'first_name' => $jmeno,
      'last_name' => $prijmeni,
      'position' => $pozice,
      'salary' => $plat,
      'room' => $mistnost,
      'room_id' => $mistnost_id
    );
  } else {
    http_response_code(404);
    echo json_encode(array(
      'message' => 'No user info'
    ));
    die();
  }

  $result['keys'] = array();

  if($stmt['stmt_keys']->rowCount() > 0) {
    while($row = $stmt['stmt_keys']->fetch()) {
      extract($row);

      array_push($result['keys'], array(
        'id' => $mistnost_id,
        'name' => $nazev
      ));
    }
  }

  echo json_encode($result, 256);
?>