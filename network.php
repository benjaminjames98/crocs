<?php

require_once 'imports/permission_levels/elder_only.php';
require_once 'imports/permission_levels/utils.php';

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
  <title>Login</title>
</head>
<body>
<?php require_once 'imports/navbar_primary.php'; ?>
<?php if (isset($msg)) echo $msg; ?>
<h1>CROCS</h1>
<h2>Network</h2>

<a href="network.php?offset=<?= $offset - 4 ?>">prev</a>
<a href="network.php?offset=<?= $offset + 4 ?>">next</a>
<table>
  <?php foreach ($leaders as $l) { ?>
    <tr>
      <td><?= $l['name'] ?></td>
      <td><a href="personal_info.php?leader_name=<?= $l['name'] ?>">View</a></td>
    </tr>
  <?php } ?>
</table>
</body>
</html>