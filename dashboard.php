<?php

require_once 'imports/permission_levels/deacon_only.php';
require_once 'imports/permission_levels/utils.php';

if (isset($_POST['action'])) {
  $action = $_POST['action'];
  if ($action == 'add_person') {
    require_once 'imports/utils.php';
    $db = get_db();

    $name = strtolower($_POST['name']);
    $password = get_hash($_POST['password']);

    $query = <<<SQL
INSERT INTO user (name, password, permissions, email) VALUES (?,?,'deacon',?)
SQL;
    $stmt = $db->prepare($query);
    $stmt->bind_param("sss", $name, $password, $_POST['email']);
    // TODO error logging
    if ($stmt->execute()) $msg = "Success! An account has been created for $name";
    else  $msg = "There was a problem with that:";
    $stmt->close();
  }
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
<h2>Dashboard</h2>

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

</body>
</html>