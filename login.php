<?php

require_once 'imports/permission_levels/public_only.php';
require_once 'imports/permission_levels/permission_utils.php';

if (isset($_POST['name'])) {
  // if information is returned, attempt to log in
  $name = strtolower($_POST['name']);
  $password = $_POST['password'];

  require_once 'imports/utils.php';
  $db = get_db();

  $query = "SELECT id, password, permissions FROM user WHERE name = ?;";
  $stmt = $db->prepare($query);
  $stmt->bind_param("s", $name);
  $stmt->execute();
  $stmt->store_result();
  if ($stmt->num_rows === 0) $msg = "we can't seem to find name: ${name}";
  else {
    $stmt->bind_result($id, $hash, $permissions);
    $stmt->fetch();
    if (password_verify($password, $hash)) {
      // correct login
      $_SESSION['name'] = $name;
      $_SESSION['id'] = $id;
      set_permissions($permissions);
      header("Location: dashboard.php");
      die();
    } else $msg = 'wrong password';
  }
  $stmt->close();

} else {
  $_POST['name'] = '';
  $_POST['password'] = '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Login</title>
</head>
<body>
<?php require_once 'navbar_primary.php'; ?>
<?php if (isset($msg)) echo $msg; ?>

<form id="form" method="post">
  <H2>Login</H2>
  Name: <br>
  <input name="name" type="text" value="<?= $_POST['name'] ?>" required> <br>
  Password:<br>
  <input name="password" type="password" required>
  <br>
  <input class="button" type="submit" value="Login">
  <input class="button" type="Reset">
</form>
</body>
</html>