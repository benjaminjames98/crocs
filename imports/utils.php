<?php


/**
 * @return mysqli
 *
 * This function is the basic DB access function used throughout the site
 */
function get_db() {
  $db = mysqli_connect('localhost', 'root', 'root', 'crocs');

  if (!$db) die(mysqli_connect_error());
  else return $db;
}

/**
 * @param $pass String - The password that is to be hashed
 *
 * @return string The hashed password
 *
 * This function is used in the signup process to generate hased passwords
 * for the DB
 */
function get_hash($pass) {
  $bytes = openssl_random_pseudo_bytes(30);
  $random_data = substr(base64_encode($bytes), 0, 22);
  $random_data = strtr($random_data, '+', '.');

  $local_salt = "$2y$12$" . $random_data;
  return crypt($pass, $local_salt);
}

function get_mentors($name) {
  $db = get_db();

  $query = <<<SQL
SELECT mentor.name
FROM user as mentor, mentor_relationship as rel, user as mentee 
WHERE mentor.id = rel.mentor AND mentee.id=rel.mentee AND mentee.name = ? AND accepted IS true 
ORDER BY name ASC;
SQL;
  $stmt = $db->prepare($query);
  $stmt->bind_param('s', $name);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($n);
  $mentors = [];
  while ($stmt->fetch()) {
    $mentors[] = ['name' => $n];
  }
  $stmt->close();

  return $mentors;
}