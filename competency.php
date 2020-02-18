<?php

require_once 'imports/permission_levels/permission_utils.php';
session_start();

if (!logged_in()) {
  header("Location: login.php");
  die();
}
require_once 'imports/utils.php';
$db = get_db();

// anyone who isn't the associated mentor can go and get stuffed
$is_mentor = false;
if (isset($_REQUEST['comp_id']) || isset($_SESSION['id'])) {
  $comp_id = $_REQUEST['comp_id'];
  $query = <<<SQL
SELECT mentor.id
FROM user as mentor,
     mentor_relationship as rel,
     competency as comp
WHERE mentor.id = rel.mentor
  AND rel.id = comp.mentor_relationship
  AND comp.id = ?
  AND comp.accepted IS true;
SQL;
  $stmt = $db->prepare($query);
  $stmt->bind_param('s', $comp_id);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($mentor_id);
  $stmt->fetch();
  if ($mentor_id === '' || !isset($mentor_id))
    $is_mentor = false;
  else if ($mentor_id == $_SESSION['id'])
    $is_mentor = true;
  $stmt->close();
} else $is_mentor = false;

if (!$is_mentor) {
  header('location: dashboard.php');
  die();
}

// page starts here
if (isset($_REQUEST['action'])) {
  // process form
  $can_understand = isset($_REQUEST['can_understand']) ?
    $_REQUEST['can_understand'] == true : false;
  $can_demonstrate = isset($_REQUEST['can_demonstrate']) ?
    $_REQUEST['can_demonstrate'] == true : false;
  $can_teach = isset($_REQUEST['can_teach']) ?
    $_REQUEST['can_teach'] == true : false;
  $project_info = isset($_REQUEST['project_info']) ?
    $_REQUEST['project_info'] : '';

  $query = <<<SQL
UPDATE competency
SET can_understand  = ?,
    can_demonstrate = ?,
    can_teach       = ?,
    project_info    = ?
WHERE id = ?;
SQL;
  $stmt = $db->prepare($query);
  $stmt->bind_param('iiisi', $can_understand, $can_demonstrate,
    $can_teach, $project_info, $comp_id);
  if ($stmt->execute())
    $msg = 'changes saved successfully';
  else {
    $msg = 'there was a problem saving that data';
  }
  $stmt->close();
}


$query = <<<SQL
SELECT course.name, mentee.name, can_understand, can_demonstrate, can_teach, project_info
FROM competency as comp,
     user as mentee,
     course as course,
     mentor_relationship as rel
WHERE mentee.id = rel.mentee
  AND rel.id = comp.mentor_relationship
  AND course.id = comp.course
  AND comp.id = ?;
SQL;
$stmt = $db->prepare($query);
$stmt->bind_param('s', $comp_id);
$stmt->execute();
$stmt->store_result();
$permissions = '';
$email = '';
$stmt->bind_result($course_name, $mentee_name, $can_understand,
  $can_demonstrate, $can_teach, $project_info);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Login</title>
  <script src="imports/js/utils.js"></script>
</head>
<body>

<?php require_once 'imports/navbar_primary.php'; ?>
<?php if (isset($msg)) echo $msg; ?>
<h1>CROCS</h1>
<h2><?= $mentee_name ?></h2>

<section>
  <h3><?= $course_name ?></h3>
  <form id="form" method="post">
    Understands: <br>
    <input name="can_understand" type="checkbox"
      <?= $can_understand ? 'checked' : '' ?>> <br>
    Can Demonstrate: <br>
    <input name="can_demonstrate" type="checkbox"
      <?= $can_demonstrate ? 'checked' : '' ?>> <br>
    Can Teach: <br>
    <input name="can_teach" type="checkbox"
      <?= $can_teach ? 'checked' : '' ?>> <br>
    <br>
    Project Info (for demonstration): <br>
    <textarea name="project_info"
              maxlength="1500"><?= $project_info ?></textarea> <br>
    <br>
    <input type="hidden" name="action" value="update_info">
    <input class="button" type="submit" value="Save">
    <input class="button" type="Reset">
  </form>
</section>

</body>