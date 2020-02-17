<?php

include_once 'permission_levels/elder_only.php';
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

// check that connection doesn't already exist
function relationship_exists($db, $mentee_id, $mentor_id) {
  $query = <<<SQL
SELECT id
FROM mentor_relationship
WHERE mentee = ? and mentor = ? AND accepted IS false;
SQL;
  $stmt = $db->prepare($query);
  $stmt->bind_param("ii", $mentee_id, $mentor_id);
  $stmt->execute();
  $stmt->store_result();
  if ($stmt->num_rows === 0) $result = false;
  else $result = true;
  $stmt->close();
  return $result;
}

if (relationship_exists($db, $mentee_id, $mentor_id)) {
  returnJson('already_requested');
} else if (relationship_exists($db, $mentor_id, $mentee_id)) {
  returnJson("can't mentor your mentor");
}

// create connection
$query = "INSERT INTO mentor_relationship (mentee, mentor) values (?, ?)";
$stmt = $db->prepare($query);
$stmt->bind_param('ii', $mentee_id, $mentor_id);
if ($stmt->execute()) returnJson('success');
else returnJson('fail');