<?php

include_once 'permission_levels/deacon_only.php';
include_once "utils.php";
$db = get_db();

function returnJson($msg = '') {
  die(json_encode(['msg' => $msg]));
}

// get variables
$json = json_decode($_REQUEST['json']);
$comp_id = $json->comp_id;
$accept = $json->accept;
if (!isset($comp_id, $accept))
  returnJson('please supply all inputs');
if (!in_array($accept, ['accept', 'reject']))
  returnJson('invalid acceptance value');
$accept = $accept === 'accept';

// create connection
if ($accept) {
  $query = "UPDATE competency SET accepted = true WHERE id = ?";
  $stmt = $db->prepare($query);
  $stmt->bind_param('i', $comp_id);
  if ($stmt->execute()) returnJson('success');
  else returnJson('fail');
} else {
  $query = "DELETE FROM competency WHERE id = ? ";
  $stmt = $db->prepare($query);
  $stmt->bind_param('i', $comp_id);
  if ($stmt->execute()) returnJson('success');
  else returnJson('fail');
}