<?php
  header('Access-Control-Allow-Origin: *');
  header('Content-Type: application/json');

  include_once('../../config/Database.php');
  include_once('../../models/User.php');

  $database = new Database();
  $db = $database->connect();

  $user = new User($db);

  $result = $user->read(
    $_GET['order_by'] ?? 'jmeno',
    $_GET['sort'] ?? 'asc'
  );

  if($result->rowCount() > 0) {
    $users = array();
    while($row = $result->fetch()) {
      extract($row);

      $single_user = array(
        'id' => $clovek_id,
        'first_name' => $jmeno,
        'last_name' => $prijmeni,
        'room' => $mistnost,
        'telephone' => $telefon,
        'position' => $pozice,
      );

      array_push($users, $single_user);
    }

    echo json_encode($users, 256);
  } else {
    echo json_encode(array(
      'message' => 'No users found'
    ));
  }
?>