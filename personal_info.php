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
foreach ($mentors as $m) {
  if ($m['name'] === $_SESSION['name']) {
    $can_edit = true;
    $is_mentor = true;
  }
}

// page starts here
if (isset($_REQUEST['msg'])) $msg = $_REQUEST['msg'];
if (isset($_REQUEST['action']) && $can_edit) {
  $action = $_REQUEST['action'];
  if ($action == 'password') {
    $hash = get_hash($_REQUEST[$action]);
    $stmt = $db->prepare("UPDATE user SET password=? WHERE name=?;");
    $stmt->bind_param('ss', $hash, $name);
    if ($stmt->execute()) {
      $msg .= 'Success! The password has been changed';
    } else {
      $msg .= "We weren't able to complete that request. Please try again later";
    }
  } else if ($action == 'info_update') {
    $data['name'] = strtolower($_REQUEST['name']);
    $data['email'] = $_REQUEST['email'];
    $data['perms'] = $_REQUEST['permissions'];

    $query = "UPDATE user SET name=?, email=?, permissions=? WHERE name=?;";
    $stmt = $db->prepare($query);
    $stmt->bind_param('ssss', $data['name'], $data['email'], $data['perms'], $name);
    if ($stmt->execute()) {
      if ($name == $_SESSION['name']) {
        // changing own details
        $_SESSION['name'] = $data['name'];
        set_permissions($data['name']);
      }
      $name = $data['name'];
      header("Location: personal_info.php?leader_name=$name&msg=change%20successful");
    }
  } else {
    $msg = 'Something went terribly wrong';
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
       comp.can_demonstrate, comp.id, comp.project_info
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
  $can_understand, $can_demonstrate, $comp_id, $project_info);
$competencies = [];
while ($stmt->fetch()) {
  $competencies[] = ['mentor_name' => addslashes($mentor_name),
    'course_name' => addslashes($course_name),
    'can_teach' => addslashes($can_teach),
    'can_understand' => addslashes($can_understand),
    'can_demonstrate' => addslashes($can_demonstrate),
    'comp_id' => addslashes($comp_id),
    'project_info' => addslashes($project_info)];
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title><?= $name ?> - Personal Information</title>
  <script src="imports/js/utils.js"></script>
</head>
<body>
<?php require_once 'navbar_primary.php'; ?>
<?php if (isset($msg)) echo $msg; ?>
<h1>CROCS</h1>
<h2>Personal Info</h2>

<section>
  <H3>Personal Info</H3>
  <form method="post">
    <br>Name: <br>
    <input name="name" type="text" value="<?= $name ?>" required> <br>
    <br>Email: <br>
    <input name="email" type="text" value="<?= $email ?>" required> <br>
    <br>Permissions: <br>
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
    <?php } else { ?>
      <p><b><?= $permissions ?></b></p>
      <input type="hidden" name="permissions" value="<?= $permissions ?>">
    <?php } ?>
    <input type="hidden" name="action" value="info_update">
    <br>
    <input class="button" type="submit" value="Save">
    <input class="button" type="Reset">
  </form>
  <form method="post">
    <br>New Password:<br>
    <input name="password" type="password" id="password" required> <br>
    <br>Confirm Password:<br>
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

</section>

<section>
  <h3>Competencies</h3>
  <?php if ($is_mentor) { ?>
    <section>
      <select id="course_select">
        <?php foreach ($courses as $c) { ?>
          <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>>
        <?php } ?>
      </select>
      <input type="button" value="Propose Training"
             onclick="propose_training(this, <?= "'{$_SESSION['name']}', '{$name}'" ?>);"/>
    </section>
  <?php } ?>
  <section>
    <table>
      <?php foreach ($competencies as $c) { ?>
        <tr>
          <td><?= $c['course_name'] ?></td>
          <td><?= $c['mentor_name'] ?></td>
          <td><?= $c['can_understand'] ?></td>
          <td><?= $c['can_demonstrate'] ?></td>
          <td><?= $c['can_teach'] ?></td>

          <td><input type="button" value="view"
                     onclick="show_competency_info(<?= "'{$c['course_name']}',
                     '{$c['mentor_name']}','{$c['can_understand']}',
                     '{$c['can_demonstrate']}','{$c['can_teach']}',
                     '{$c['project_info']}'" ?>)">
          </td>
          <?php if ($c['mentor_name'] == $_SESSION['name']) { ?>
            <td><a
                href="competency.php?comp_id=<?= $c['comp_id'] ?>">Assess</a>
            </td>
          <?php } ?>
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
  }

  function show_competency_info(course_name, mentor_name, can_understand,
                                can_demonstrate, can_teach, project_info) {
    let msg = `<h2>${course_name}</h2>
    <h3>${mentor_name}</h3>

    <p>understands: ${can_understand}</p>
    <p>demonstrated: ${can_demonstrate}</p>
    <p>can teach: ${can_teach}</p>
    <p>${project_info}</p>`;

    show_dlg(msg);
  }

  function show_dlg(msg) {
    el('dlg_content').innerHTML = msg;
    _open('dlg');
  }
</script>

<dialog id="dlg">
  <p id="dlg_content">asd</p>
  <input id='dlg_btn' type="button" value="Close" onclick="_close('dlg')"/>
</dialog>

</body>
</html>