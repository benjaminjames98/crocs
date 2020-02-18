<?php

require_once 'imports/permission_levels/permission_utils.php';
session_start();

if (!logged_in()) {
  header("Location: login.php");
  die();
}
require_once 'imports/utils.php';
$db = get_db();

if (isset($_REQUEST['leader_name']) || isset($_REQUEST['leader_id'])) {
  if (isset($_REQUEST['leader_name'])) {
    $query = "SELECT name FROM user WHERE name = ?;";
    $stmt = $db->prepare($query);
    $stmt->bind_param('s', $_REQUEST['leader_name']);
  } else if (isset($_REQUEST['leader_id'])) {
    $query = "SELECT name FROM user WHERE id = ?;";
    $stmt = $db->prepare($query);
    $stmt->bind_param('s', $_REQUEST['leader_id']);
  }
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($name);
  $stmt->fetch();
  if ($name === '' || !isset($name)) header('location: personal_info.php');
  $stmt->close();
} else $name = $_SESSION['name'];

// figure out page permissions
$can_view = false;
$can_edit = false;
$is_mentor = false;
if (has_permission('elder')) $can_view = true;
if (has_permission('regional')) {
  $can_view = true;
  $can_edit = true;
} else if ($_SESSION['name'] === $name) {
  $can_view = true;
  $can_edit = true;
}

if (!$can_view) {
  header("Location: dashboard.php");
  die();
}

// check if current viewer is a mentor
$mentors = get_mentors($name);
if (in_array($_SESSION['name'], $mentors)) {
  $can_edit = true;
  $is_mentor = true;
}

// page starts here
if (isset($_REQUEST['msg'])) $msg = $_REQUEST['msg'];
if (isset($_REQUEST['action']) && $can_edit) {
  $action = $_REQUEST['action'];
  if (in_array($action, ['name', 'password', 'permissions', 'email'])) {
    $column = $action;
    $data = $_REQUEST[$column];
    if ($column == 'password') $data = get_hash($data);
    if ($column == 'name') $data = strtolower($data);

    $stmt = $db->prepare("UPDATE user SET $column=? WHERE name=?;");
    $stmt->bind_param('ss', $data, $name);
    if ($stmt->execute()) {
      if ($name == $_SESSION['name']) {
        // changing own details
        if ($column == 'name')
          $_SESSION['name'] = $data;
        if ($column == 'permissions')
          set_permissions($data);
      }
      if ($column == 'name') $name = $data;
      header("Location: personal_info.php?leader_name=$name&msg=change%20successful");
    } else {
      $msg = 'Something went terribly wrong';
    }
  }
} else if (isset($action) && !$can_edit) {
  $msg = 'you do not have permission to edit this page';
}

// get personal info
$query = "SELECT permissions, email FROM user WHERE name = ?;";
$stmt = $db->prepare($query);
$stmt->bind_param('s', $name);
$stmt->execute();
$stmt->store_result();
$permissions = '';
$email = '';
$stmt->bind_result($permissions, $email);
$stmt->fetch();
if ($permissions === '') header('location: personal_info.php');
$stmt->close();

// get list of existing courses
$query = "SELECT id, name FROM course";
$stmt = $db->prepare($query);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($course_id, $course_name);
$courses = [];
while ($stmt->fetch()) {
  $courses[] = ['id' => $course_id, 'name' => $course_name];
}
$stmt->close();

// get user's competency info
$query = <<<SQL
SELECT mentor.name, course.name, comp.can_teach, comp.can_understand,
       comp.can_demonstrate, comp.id
FROM user as mentee, user as mentor, course, competency as comp,
     mentor_relationship as rel
WHERE mentee.name = ?
AND mentee.id = rel.mentee
AND mentor.id = rel.mentor
AND comp.mentor_relationship = rel.id
AND comp.course = course.id
AND comp.accepted IS true;
SQL;
$stmt = $db->prepare($query);
$stmt->bind_param('s', $name);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($mentor_name, $course_name, $can_teach,
  $can_understand, $can_demonstrate, $comp_id);
$competencies = [];
while ($stmt->fetch()) {
  $competencies[] = ['mentor_name' => $mentor_name,
    'course_name' => $course_name, 'can_teach' => $can_teach,
    'can_understand' => $can_understand, 'can_demonstrate' => $can_demonstrate,
    'comp_id' => $comp_id];
}
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
<h2>Personal Info</h2>

<section>
  <H3>Personal Info</H3>
  <form method="post">
    Name: <br>
    <input name="name" type="text" value="<?= $name ?>" required> <br>
    <input type="hidden" name="action" value="name">
    <input class="button" type="submit" value="Save">
    <input class="button" type="Reset">
  </form>
  <form method="post">
    New Password:<br>
    <input name="password" type="password" id="password" required> <br>
    Confirm Password:<br>
    <input type="password" required oninput="check(this)"> <br>
    <script type='text/javascript'>
      function check(input) {
        if (input.value !== document.getElementById('password').value) {
          input.setCustomValidity('Password Must be Matching.');
        } else {
          // input is valid -- reset the error message
          input.setCustomValidity('');
        }
      }
    </script>
    <input type="hidden" name="action" value="password">
    <input class="button" type="submit" value="Save">
    <input class="button" type="Reset">
  </form>
  <form method="post">
    Email: <br>
    <input name="email" type="text" value="<?= $email ?>" required> <br>
    <input type="hidden" name="action" value="email">
    <input class="button" type="submit" value="Save">
    <input class="button" type="Reset">
  </form>
  <form method="post">
    Permissions: <br>
    <?php if ($is_mentor) { ?>
      <select name="permissions" required>
        <option
          value="deacon" <?= $permissions == 'deacon' ? 'selected' : '' ?>>
          deacon
        </option>
        <option value="elder" <?= $permissions == 'elder' ? 'selected' : '' ?>>
          elder
        </option>
        <option
          value="regional" <?= $permissions == 'regional' ? 'selected' : '' ?>>
          regional
        </option>
      </select>
      <input type="hidden" name="action" value="permissions">
      <input class="button" type="submit" value="Save">
      <input class="button" type="Reset">
    <?php } else { ?>
      <p><?= $permissions ?></p>
    <?php } ?>
  </form>
</section>

<section>
  <h3>Competencies</h3>
  <select id="course_select">
    <?php foreach ($courses as $c) { ?>
      <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>>
    <?php } ?>
  </select>
  <section>
    <input type="button" value="Propose Training"
           onclick="propose_training(this, <?= "'{$_SESSION['name']}', '{$name}'" ?>);"/>
  </section>
  <section>
    <table>
      <?php foreach ($competencies as $c) { ?>
        <tr>
          <td><?= $c['course_name'] ?></td>
          <td><?= $c['mentor_name'] ?></td>
          <td><?= $c['can_understand'] ?></td>
          <td><?= $c['can_demonstrate'] ?></td>
          <td><?= $c['can_teach'] ?></td>
          <?php if ($c['mentor_name'] == $_SESSION['name']) { ?>
            <td><a href="competency.php?comp_id=<?= $c['comp_id'] ?>">Assess</a>
            </td>
          <?php } else echo "<td></td>"; ?>
        </tr>
      <?php } ?>
    </table>
  </section>
</section>

<script>
  function propose_training(btn, mentor, mentee) {
    if (mentor === mentee) {
      show_dlg("Sorry, but you can't mentor yourself");
      return;
    }

    let select = el("course_select");
    let course_id = select.options[select.selectedIndex].value;

    jsonPost('imports/propose_training.php',
      {mentor: mentor, mentee: mentee, course_id: course_id},
      json => {
        if (json.msg === 'success') {
          show_dlg(`success, your proposal has been sent to ${mentee}`);
          el('dlg_btn').onclick = function () {
            location.reload();
          };
        } else if (json.msg === 'already in training') {
          show_dlg(`${mentee} has to decline or cancel his current`
            + ` training before undertaking the same program again`);
        } else
          show_dlg(`Sorry. We were unable to complete that request. Please `
            + `try again at a later time, or contact support.`);
        btn.disabled = false;
      });

    function show_dlg(msg) {
      el('dlg_content').innerText = msg;
      _open('dlg');
    }
  }
</script>

<dialog id="dlg">
  <p id="dlg_content">asd</p>
  <input id='dlg_btn' type="button" value="Close" onclick="_close('dlg')"/>
</dialog>

</body>
</html>