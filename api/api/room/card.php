<?php
  header('Access-Control-Allow-Origin: *');
  header('Content-Type: application/json');

  include_once('../../config/Database.php');
  include_once('../../models/Room.php');

  $id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT,
    ["options" => ["min_range" => 1]]
  );

  $database = new Database();
  $db = $database->connect();

  $room = new Room($db);

  if(!$id) {
    http_response_code(400);
    echo json_encode(array(
      'message' => 'Id does not fulfill requirements'
    ));
    exit;
  }

  $stmt = $room->room_card($id);

  $result = array(
    'number' => null,
    'name' => null,
    'telephone' => null,
    'people' => array(),
    'average' => null,
    'keys' => array(),
  );

  if($stmt['stmt_room']->rowCount() > 0) {
    $row = $stmt['stmt_room']->fetch();

    extract($row);

    $result['number'] = $cislo;
    $result['name'] = $nazev;
    $result['telephone'] = $telefon;
  } else {
    http_response_code(404);
    echo json_encode(array(
      'message' => 'No room info'
    ));
    die();
  }

  if($stmt['stmt_people']->rowCount() > 0) {
    while($row = $stmt['stmt_people']->fetch()) {
      extract($row);

      array_push($result['people'], array(
        'first_name' => $jmeno,
        'last_name' => $prijmeni,
        'id' => $clovek_id,
      ));
    }
  } else {
    http_response_code(404);
    echo json_encode(array(
      'message' => 'No people info'
    ));
    die();
  }

  if($stmt['stmt_average']->rowCount() > 0) {
    $row = $stmt['stmt_average']->fetch();

    extract($row);

    $result['average'] = $prumer;
  } else {
    http_response_code(404);
    echo json_encode(array(
      'message' => 'No average info'
    ));
    die();
  }

  if($stmt['stmt_keys']->rowCount() > 0) {
    while($row = $stmt['stmt_keys']->fetch()) {
      extract($row);

      array_push($result['keys'], array(
        'first_name' => $jmeno,
        'last_name' => $prijmeni,
        'id' => $clovek_id,
      ));
    }
  } else {
    http_response_code(404);
    echo json_encode(array(
      'message' => 'No keys info'
    ));
    die();
  }

  echo json_encode($result);
?>