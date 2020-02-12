<?php

require_once 'permission_utils.php';
session_start();

if (logged_in()) {
    header("Location: dashboard.php");
    die();
}