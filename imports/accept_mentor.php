<?php

include_once 'permission_levels/deacon_only.php';
include_once "utils.php";
$db = get_db();

function returnJson($msg = '') {
  die(json_encode(['msg' => $msg]));
}

// get variables
$json = json_decode($_REQUEST['json']);
$mentee = $json->mentee;
$mentor = $json->mentor;
$accept = $json->accept;
if (!isset($mentee, $mentor, $accept))
  returnJson('please supply all inputs');
if (!in_array($accept, ['accept', 'reject']))
  returnJson('invalid acceptance value');
$accept = $accept === 'accept';

// check that users exist
function user_id($db, $name) {
  $stmt = $db->prepare("SELECT id FROM user WHERE name = ?;");
  $stmt->bind_param("s", $name);
  $stmt->execute();
  $stmt->store_result();
  if ($stmt->num_rows === 0) $result = -1;
  else {
    $id = -1;
    $stmt->bind_result($id);
    $stmt->fetch();
    $result = $id;
  }
  $stmt->close();
  return $result;
}

$mentee_id = user_id($db, $mentee);
$mentor_id = user_id($db, $mentor);

if ($mentor_id == -1 || $mentee_id == -1)
  returnJson('fail');

// create connection
if ($accept) {
  $query = <<<SQL
UPDATE mentor_relationship
SET accepted = true
WHERE mentor = ? AND mentee = ?
SQL;
  $stmt = $db->prepare($query);
  $stmt->bind_param('ii', $mentor_id, $mentee_id);
  if ($stmt->execute()) returnJson('success');
  else returnJson('fail');
} else {
  $query = <<<SQL
DELETE FROM mentor_relationship
WHERE mentor = ? AND mentee = ? 
SQL;
  $stmt = $db->prepare($query);
  $stmt->bind_param('ii', $mentor_id, $mentee_id);
  if ($stmt->execute()) returnJson('success');
  else returnJson('fail');
}