<div>
  <?php require_once 'imports/permission_levels/permission_utils.php'; ?>

  <a href="index.php"><input class="navBar" type="button" value="home"></a>
  <?php if (logged_in()) { ?>
    <a href="dashboard.php"><input class="navBar" type="button"
                                   value="dashboard"></a>
    <a href="personal_info.php"><input class="navBar" type="button"
                                       value="personal info"></a>
    <?php if (has_permission('elder')) { ?>
      <a href="network.php"><input class="navBar" type="button"
                                   value="network"></a>
    <?php } ?>
    <a href="imports/logout.php"><input class="navBar" type="button"
                                        value="logout"></a>
  <?php } ?>
  <?php if (!logged_in()) { ?>
    <a href="login.php"><input class="navBar" type="button" value="login"></a>
  <?php } ?>
</div>