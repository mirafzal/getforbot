<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


require_once 'User.php';
var_dump(User::getUsersCount());
