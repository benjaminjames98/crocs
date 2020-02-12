<?php

require_once 'imports/permission_levels/deacon_only.php';
require_once 'imports/permission_levels/utils.php';

require_once 'imports/utils.php';
$db = get_db();

if (isset($_REQUEST['leader_name'])) $name = $_REQUEST['leader_name'];
else $name = $_SESSION['name'];

if (isset($_REQUEST['action'])) {
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

      header("Location: personal_info.php?leader_name=$name");
    } else {
      $msg = 'Something went terribly wrong';
    }
  }
} else {
  $query = "SELECT name, permissions, email FROM user WHERE name = ?;";
  $stmt = $db->prepare($query);
  $stmt->bind_param('s', $name);
  $stmt->execute();
  $stmt->store_result();
  $permissions = '';
  $email = '';
  $stmt->bind_result($name, $permissions, $email);
  $stmt->fetch();
  if ($name == '') header('location: personal_info.php');
  $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Login</title>
</head>
<body>
<?php require_once 'imports/navbar_primary.php'; ?>
<?php if (isset($msg)) echo $msg; ?>
<h1>CROCS</h1>
<h2>Personal Info</h2>

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
  <select name="permissions" required>
    <option value="deacon" <?= $permissions == 'deacon' ? 'selected' : '' ?>>deacon</option>
    <option value="elder" <?= $permissions == 'elder' ? 'selected' : '' ?>>elder</option>
    <option value="regional" <?= $permissions == 'regional' ? 'selected' : '' ?>>regional</option>
  </select>
  <input type="hidden" name="action" value="permissions">
  <input class="button" type="submit" value="Save">
  <input class="button" type="Reset">
</form>

<h3>Competencies</h3>

</body>
</html>