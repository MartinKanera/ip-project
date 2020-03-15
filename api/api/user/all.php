<?php
  header('Access-Control-Allow-Origin: *');
  header('Content-Type: application/json');

  include_once('../../config/Database.php');
  include_once('../../models/User.php');

  $database = new Database();
  $db = $database->connect();

  $user = new User($db);

  $result = $user->read();

  $num = $result->rowCount();

  if($num > 0) {
    $users = array();
    while($row = $result->fetch()) {
      extract($row);

      $single_user = array(
        'clovek_id' => $clovek_id,
        'jmeno' => $jmeno,
        'prijmeni' => $prijmeni,
        'mistnost' => $mistnost,
        'telefon' => $telefon,
        'pozice' => $pozice,
      );

      array_push($users, $single_user);
    }

    echo json_encode($users);
  } else {
    echo json_encode(array(
      'message' => 'No users found'
    ));
  }
?>