<?php
$permission_levels = ['deacon', 'elder', 'regional'];

/**
 * returns boolean value if the current user's permission is greater than or equal to the given permission string.
 *
 * @param $perm - ['deacon', 'elder', 'regional']
 * @return bool - does the user have at least this permission level
 */
function has_permission($perm) {
    global $permission_levels;
    if (!in_array($perm, $permission_levels)) return false;
    if (!logged_in()) return false;

    // convert $perm/$usr_perm to position in array
    $perm = array_search($perm, $permission_levels);
    $usr_perm = array_search($_SESSION['permissions'], $permission_levels);

    if ($usr_perm >= $perm) return true;
    else return false;
}

/**
 * returns true if logged in, otherwise false
 *
 * @return bool
 */
function logged_in() {
    if (isset($_SESSION['permissions'])) return true;
    else return false;
}

/**
 * basic setterm function for $_SESSION['permissions']
 *
 * @param $perm - permission to set the $_SESSION['permissions'] variable to
 * @return void
 */
function set_permissions($perm) {
    global $permission_levels;
    if (!in_array($perm, $permission_levels)) return;

    $_SESSION['permissions'] = $perm;
}