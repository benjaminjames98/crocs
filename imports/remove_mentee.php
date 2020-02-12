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
if (!isset($mentee, $mentor))
  returnJson('please supply all inputs');

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
$query = <<<SQL
DELETE FROM mentor_relationship 
WHERE (mentee = ? AND mentor = ?) 
   OR (mentor = ? AND mentee = ?)
SQL;
$stmt = $db->prepare($query);
$stmt->bind_param('iiii', $mentee_id, $mentor_id, $mentee_id, $mentor_id);
if ($stmt->execute()) returnJson('success');
else returnJson('fail');