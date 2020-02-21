<?php

require_once 'imports/permission_levels/elder_only.php';
require_once 'imports/permission_levels/permission_utils.php';

require_once 'imports/utils.php';
$db = get_db();

$offset = (isset($_REQUEST['offset'])) ? intval($_REQUEST['offset']) : 0;
if ($offset < 0) $offset = 0;

$query = "SELECT name FROM user ORDER BY name ASC LIMIT $offset, 4;";
$stmt = $db->prepare($query);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($name);
$leaders = [];
while ($stmt->fetch()) {
  $leaders[] = ['name' => $name];
}
$stmt->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Network</title>
  <script src="imports/js/utils.js"></script>
</head>
<body>
<?php require_once 'navbar_primary.php'; ?>
<?php if (isset($msg)) echo $msg; ?>
<h1>CROCS</h1>
<h2>Network</h2>

<a href="network.php?offset=<?= $offset - 4 ?>">prev</a>
<a href="network.php?offset=<?= $offset + 4 ?>">next</a>
<table>
  <?php foreach ($leaders as $l) { ?>
    <tr>
      <td><?= $l['name'] ?></td>
      <td><a href="personal_info.php?leader_name=<?= $l['name'] ?>">View</a>
      </td>
      <td><input type="button" value="Mentor"
                 onclick="request_mentee(this,
                 <?= "'{$_SESSION['name']}', '{$l['name']}'" ?>);"/>
      </td>
    </tr>
  <?php } ?>
</table>

<script>
  function request_mentee(btn, mentor, mentee) {
    if (mentor === mentee) {
      show_dlg("Sorry, but you can't mentor yourself");
      return;
    }

    btn.disabled = true;
    jsonPost('imports/request_mentee.php', {mentor: mentor, mentee: mentee},
      json => {
        if (json.msg === 'success')
          show_dlg(`success, ${mentee} has been sent a mentoring request`);
        else if (json.msg === 'already_requested')
          show_dlg(`${mentee} has been sent a mentoring request`);
        else if (json.msg === "can't mentor your mentor")
          show_dlg(`please remove ${mentee} as your mentor before you request `
            + `to mentor them`);
        else
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
  <input type="button" value="Close" onclick="_close('dlg')"/>
</dialog>
</body>
</html>