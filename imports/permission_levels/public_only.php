<?php

require_once 'utils.php';
session_start();

if (logged_in()) {
    header("Location: index.php");
    die();
}