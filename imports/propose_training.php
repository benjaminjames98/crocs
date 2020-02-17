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
$course_id = $json->course_id;
if (!isset($mentee, $mentor, $course_id))
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

// check that mentor is indeed mentor
$query = <<<SQL
SELECT id FROM mentor_relationship 
WHERE mentee = ? AND mentor = ? AND accepted IS true;
SQL;
$stmt = $db->prepare($query);
$stmt->bind_param("ii", $mentee_id, $mentor_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows !== 1) {
  returnJson("you need to be a person's mentor before you can train them");
}
$stmt->bind_result($relationship_id);
$stmt->fetch();
$stmt->close();

// check that training isn't already taking place
$query = "SELECT id FROM competency WHERE mentor_relationship = ? AND course = ?;";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $relationship_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows !== 0) {
  returnJson('already in training');
}
$stmt->close();

// create connection
$query = "INSERT INTO competency (mentor_relationship, course) VALUES (?, ?)";
$stmt = $db->prepare($query);
$stmt->bind_param('ii', $relationship_id, $course_id);
if ($stmt->execute()) returnJson('success');
else returnJson('fail');