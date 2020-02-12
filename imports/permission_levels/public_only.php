<?php

require_once 'utils.php';
session_start();

if (logged_in()) {
    header("Location: dashboard.php");
    die();
}