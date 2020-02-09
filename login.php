<?php

require_once 'imports/permission_levels/public_only.php';
require_once 'imports/permission_levels/utils.php';

if (isset($_POST['username'])) {
  // if information is returned, attempt to log in
  $username = $_POST['username'];
  $password = $_POST['password'];

  require_once 'imports/utils.php';
  $db = get_db();

  $stmt = $db->prepare("SELECT password, permissions FROM User WHERE username = ?;");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $stmt->store_result();
  if ($stmt->num_rows === 0) $error = "we can't seem to find username: ${$username}";
  else {
    $stmt->bind_result($hash, $permissions);
    $stmt->fetch();
    if (password_verify($password, $hash)) {
      // correct login
      $_SESSION['username'] = $username;
      set_permissions($permissions);
      header("Location: index.php");
      die();
    } else $error = 'wrong password';
  }
  $stmt->close();

} else {
  $_POST['username'] = '';
  $_POST['password'] = '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Login</title>
</head>
<body>
<div id="background">
  <div id="bond">
    <form id="form" method="post">
      <H2>Login</H2>
      User: <br>
      <input name="username" type="text" value="<?= $_POST['username'] ?>" required> <br>
      Password:<br>
      <input name="password" type="password" required>
      <br>
      <input class="button" type="submit" value="Login">
      <input class="button" type="Reset">
    </form>
    <?php if (isset($error)) echo "<p style='color: red'>$error</p><br>"; ?>
  </div>
</div>
</body>
</html>