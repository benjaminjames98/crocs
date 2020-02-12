<?php

require_once 'permission_utils.php';
session_start();

if (!logged_in()) {
    header("Location: login.php");
    die();
} else if (!has_permission('elder')) {
    header("Location: dashboard.php");
    die();
}