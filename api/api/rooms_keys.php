<?php
  header('Access-Control-Allow-Origin: *');
  header('Content-Type: application/json');

  include_once('../config/database.php');
  include_once('../models/room.php');

  $database = new Database();
  $db = $database->connect();

  $room = new Room($db);

  $result = $room->rooms_keys();

  if($result->rowCount() > 0) {
    $rooms = array();
    while($row = $result->fetch()) {
      extract($row);

      $single_room = array(
        'id' => $mistnost_id,
        'name' => $nazev
      );

      array_push($rooms, $single_room);
    }

    echo json_encode($rooms, 256);
  } else {
    http_response_code(404);
    echo json_encode(array(
      'message' => 'No rooms found'
    ));
  }
?>