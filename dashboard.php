<?php

require_once 'imports/permission_levels/deacon_only.php';
require_once 'imports/permission_levels/permission_utils.php';
require_once 'imports/utils.php';
$db = get_db();


// form actions - add person

if (isset($_POST['action'])) {
  $action = $_POST['action'];
  if ($action == 'add_person') {

    $new_name = strtolower($_POST['name']);
    $password = get_hash($_POST['password']);

    $query = <<<SQL
INSERT INTO user (name, password, permissions, email)
VALUES (?, ?, 'deacon', ?)
SQL;
    $stmt = $db->prepare($query);
    $stmt->bind_param("sss", $new_name, $password, $_POST['email']);
    if ($stmt->execute()) $msg = "Success! An account has been created for $new_name";
    else  $msg = "There was a problem with that:";
    $stmt->close();
  }
}

// current mentoring relationships
$mentors = get_mentors($_SESSION['name']);

$query = <<<SQL
SELECT mentee.name
FROM user as mentee,
     mentor_relationship as rel,
     user as mentor
WHERE mentee.id = rel.mentee
  AND mentor.id = rel.mentor
  AND mentor.name = ?
  AND accepted IS true;
SQL;
$stmt = $db->prepare($query);
$stmt->bind_param('s', $_SESSION['name']);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($name);
$mentees = [];
while ($stmt->fetch()) {
  $mentees[] = ['name' => $name];
}
$stmt->close();

// invitations to mentoring relationships
$query = <<<SQL
SELECT mentor.name
FROM user as mentor,
     mentor_relationship as rel,
     user as mentee
WHERE mentor.id = rel.mentor
  AND mentee.id = rel.mentee
  AND mentee.name = ?
  AND accepted IS false;
SQL;
$stmt = $db->prepare($query);
$stmt->bind_param('s', $_SESSION['name']);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($name);
$offers = [];
while ($stmt->fetch()) {
  $offers[] = ['name' => $name];
}
$stmt->close();


// invitations to work on competencies
$query = <<<SQL
SELECT comp.id, mentor.name, course.name 
FROM user as mentor,
     mentor_relationship as rel,
     user as mentee,
     competency as comp,
     course as course
WHERE mentor.id = rel.mentor
  AND mentee.id = rel.mentee
  AND mentee.name = ?
  AND comp.mentor_relationship = rel.id
  AND comp.accepted IS false
  AND course.id = comp.course
ORDER BY course.name ASC;
SQL;
$stmt = $db->prepare($query);
$stmt->bind_param('s', $_SESSION['name']);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($comp_id, $mentor_name, $course_name);
$competencies = [];
while ($stmt->fetch()) {
  $competencies[] = ['comp_id' => $comp_id, 'mentor_name' => $mentor_name,
    'course_name' => $course_name];
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
<h2>Dashboard</h2>

<section>
  <h3>Your Mentors</h3>
  <table>
    <?php foreach ($mentors as $m) { ?>
      <tr>
        <td><?= $m['name'] ?></td>
        <td><input type="button" value="Remove Mentor"
                   onclick="remove_mentor_relationship(this,
                   <?= "'{$_SESSION['name']}', '{$m['name']}'" ?>);"/>
        </td>
      </tr>
    <?php } ?>
  </table>
</section>

<section>
  <h3>Your Mentees</h3>
  <table>
    <?php foreach ($mentees as $m) { ?>
      <tr>
        <td><?= $m['name'] ?></td>
        <td><a href="personal_info.php?leader_name=<?= $m['name'] ?>">View</a>
        </td>
        <td><input type="button" value="Remove Mentor"
                   onclick="remove_mentor_relationship(this,
                   <?= "'{$_SESSION['name']}', '{$m['name']}'" ?>);"/>
        </td>
      </tr>
    <?php } ?>
  </table>
</section>

<section>
  <h3>Offers to Mentor You</h3>
  <table>
    <?php foreach ($offers as $o) { ?>
      <tr>
        <td><?= $o['name'] ?></td>
        <td><input type="button" value="accept"
                   onclick="accept_mentor_relationship(this,
                   <?= "'{$o['name']}', '{$_SESSION['name']}', 'accept'" ?>);"/>
        </td>
        <td><input type="button" value="reject"
                   onclick="accept_mentor_relationship(this,
                   <?= "'{$o['name']}', '{$_SESSION['name']}', 'reject'" ?>);"/>
        </td>
      </tr>
    <?php } ?>
  </table>
</section>

<section>
  <h3>Invitations to Work on Competencies</h3>
  <table>
    <?php foreach ($competencies as $c) { ?>
      <tr>
        <td><?= $c['course_name'] ?></td>
        <td><?= $c['mentor_name'] ?></td>
        <td><input type="button" value="accept"
                   onclick="accept_competency_invitation(this,
                   <?= "'{$c['comp_id']}', 'accept'" ?>);"/>
        </td>
        <td><input type="button" value="reject"
                   onclick="accept_competency_invitation(this,
                   <?= "'{$c['comp_id']}', 'reject'" ?>);"/>
        </td>
      </tr>
    <?php } ?>
  </table>
</section>

<section>
  <h3>Add Person</h3>
  <form id="add_person" method="post">
    User: <br>
    <input name="name" type="text" required> <br>
    Password:<br>
    <input name="password" type="password" required> <br>
    email: <br>
    <input name="email" type="email" required>
    <br>
    <input type="hidden" name="action" value="add_person">
    <input class="button" type="submit" value="Add Person">
    <input class="button" type="Reset">
  </form>
</section>

<script>
  function accept_mentor_relationship(btn, mentor, mentee, accept) {
    btn.disabled = true;
    jsonPost('imports/accept_mentor.php', {
        mentor: mentor,
        mentee: mentee,
        accept: accept
      },
      json => {
        if (json.msg === 'success') {
          if (accept === 'accept')
            show_dlg(`success, you have accepted ${mentee}'s mentoring offer`);
          else if (accept === 'reject')
            show_dlg(`success, you have rejected ${mentee}'s mentoring offer`);
          el('dlg_btn').onclick = function () {
            location.reload();
          };
        } else {
          show_dlg(`Sorry. We were unable to complete that request. Please `
            + `try again at a later time, or contact support.`);
          btn.disabled = false;
        }
      }
    );

    function show_dlg(msg) {
      el('dlg_content').innerText = msg;
      _open('dlg');
    }
  }

  function remove_mentor_relationship(btn, mentor, mentee) {
    // mentor/mentee are interchangable
    if (mentor === mentee) {
      show_dlg("Sorry, but you can't remove yourself from mentoring");
      return;
    }

    btn.disabled = true;
    jsonPost('imports/remove_mentee.php', {mentor: mentor, mentee: mentee},
      json => {
        if (json.msg === 'success') {
          show_dlg(`success, your mentoring relationship with ${mentee} has `
            + ` been ended`);
          el('dlg_btn').onclick = function () {
            location.reload();
          };
        } else
          show_dlg(`Sorry. We were unable to complete that request. Please `
            + `try again at a later time, or contact support.`);
        btn.disabled = false;
      }
    );

    function show_dlg(msg) {
      el('dlg_content').innerText = msg;
      _open('dlg');
    }

  }


  function accept_competency_invitation(btn, comp_id, accept) {
    btn.disabled = true;
    jsonPost('imports/accept_competency_invitation.php',
      {comp_id: comp_id, accept: accept},
      json => {
        if (json.msg === 'success') {
          if (accept === 'accept')
            show_dlg(`success, you are now working on your competency`);
          else if (accept === 'reject')
            show_dlg(`success, that competency invitation has been rejected`);
          el('dlg_btn').onclick = function () {
            location.reload();
          };
        } else
          show_dlg(`Sorry. We were unable to complete that request. Please `
            + `try again at a later time, or contact support.`);
        btn.disabled = false;
      }
    );

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