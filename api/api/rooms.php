<?php
  header('Access-Control-Allow-Origin: *');
  header('Content-Type: application/json');
  header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-methods, Authorization');


  include_once('../config/database.php');
  include_once('../models/room.php');

  function order_valid ($order_value) {
    $valid = array('nazev', 'cislo', 'telefon');
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

  if(!$order_by) {
    $order_by = 'nazev';
  }

  if(!$sort) {
    $sort = 'asc';
  }

  $database = new Database();
  $db = $database->connect();

  $room = new Room($db);

  $stmt = $room->read($order_by, $sort);

  if($stmt->rowCount() > 0) {
    $rooms = array();
    while($row = $stmt->fetch()) {
      extract($row);

      $single_room = array(
        'id' => $mistnost_id,
        'name' => $nazev,
        'number' => $cislo,
        'telephone' => $telefon,
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