<?php

// log the user out by clearing their session array and destroying the session cookie
session_start();
$_SESSION = array();
session_destroy();
header("Location: ../index.php");
die();
