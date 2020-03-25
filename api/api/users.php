<?php
  header('Access-Control-Allow-Origin: *');
  header('Content-Type: application/json');

  function order_valid ($order_value) {
    $valid = array('jmeno', 'prijmeni', 'mistnost', 'telefon', 'pozice');
    return in_array(strtolower($order_value), $valid) ? $order_value : false;
  }

  function sort_valid ($sort_value) {
    $valid = array('asc', 'desc');
    return in_array(strtolower($sort_value), $valid) ? $sort_value : false;
  }

  $json = file_get_contents('php://input');
  $data = json_decode($json)->data;
  
  $order_by = filter_var($data->order_by, FILTER_CALLBACK, array("options" => "order_valid"));
  $order_by = filter_var($data->sort, FILTER_CALLBACK, array("options" => "sort_valid"));

  if(!$order_by) {
    $order_by = 'jmeno';
  }

  if(!$sort) {
    $sort = 'asc';
  }

  include_once('../config/Database.php');
  include_once('../models/User.php');

  $database = new Database();
  $db = $database->connect();

  $user = new User($db);

  $stmt = $user->read($order_by, $sort);

  if($stmt->rowCount() > 0) {
    $users = array();
    while($row = $stmt->fetch()) {
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
    http_response_code(404);
    echo json_encode(array(
      'message' => 'No users found'
    ));
  }
?>