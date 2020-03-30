<?php
  header('Access-Control-Allow-Origin: *');
  header('Content-Type: application/json');
  header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-methods, Authorization');


  

  function order_valid ($order_value) {
    $valid = array('jmeno', 'prijmeni', 'mistnost', 'telefon', 'pozice');
    return in_array(strtolower($order_value), $valid) ? $order_value : false;
  }

  function sort_valid ($sort_value) {
    $valid = array('asc', 'desc');
    return in_array(strtolower($sort_value), $valid) ? $sort_value : false;
  }

  $json = file_get_contents('php://input');
  $data = json_decode($json)->data ?? (object) array('order_by' => 'jmeno', 'sort' => 'asc');

  $order_by = filter_var($data->order_by, FILTER_CALLBACK, array("options" => "order_valid"));
  $sort = filter_var($data->sort, FILTER_CALLBACK, array("options" => "sort_valid"));

  include_once('../config/database.php');
  include_once('../models/user.php');

  $database = new Database();
  $db = $database->connect();

  $user = new User($db);

  $stmts = $user->read($order_by, $sort);
  $result = array(
    'users' => array(),
    'rooms' => array(),
    'keys' => array(),
  );

  if($stmts['stmt']->rowCount() > 0) {
    while($row = $stmts['stmt']->fetch()) {
      extract($row);

      $single_user = array(
        'id' => $clovek_id,
        'first_name' => $jmeno,
        'last_name' => $prijmeni,
        'room' => $nazev,
	      'room_id' => $mistnost,
        'telephone' => $telefon,
        'position' => $pozice,
        'salary' => $plat,
        'login' => $login,
        'admin' => $admin
      );

      array_push($result['users'], $single_user);
    }
  } else {
    http_response_code(404);
    echo json_encode(array(
      'message' => 'No users found'
    ));
  }

  if($stmts['stmt_rooms']->rowCount() > 0) {
    while($row = $stmts['stmt_rooms']->fetch()) {
      extract($row);

      $single_room = array(
        'room_id' => $mistnost_id,
        'text' => $mistnost
      );

      array_push($result['rooms'], $single_room);
    }
  } else {
    http_response_code(404);
    echo json_encode(array(
      'message' => 'No rooms found'
    ));
  }

  if($stmts['stmt_keys']->rowCount() > 0) {
    while($row = $stmts['stmt_keys']->fetch()) {
      extract($row);

      $single_key = array(
        'user_id' => $clovek,
        'room_id' => $mistnost
      );

      array_push($result['keys'], $single_key);
    }
  } else {
    http_response_code(404);
    echo json_encode(array(
      'message' => 'No keys found'
    ));
  }

  echo json_encode($result, 256);
?>