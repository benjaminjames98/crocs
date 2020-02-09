<?php

require_once 'utils.php';
session_start();

if (!logged_in()) {
    header("Location: login.php");
    die();
} else if (!has_permission('certified')) {
    header("Location: index.php");
    die();
}