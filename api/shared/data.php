<?php
  function verify_JWT($payload) {
    include_once('../../config/database.php');
    include_once('../../models/user.php');

    $database = new Database();
    $db = $database->connect();

    $user = new User($db);
    
    if($result = $user->data($payload->user_id, $payload->login)) {
      return($result);
    } else {
      return false;
    }
  }
?>