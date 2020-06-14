<?php

require_once 'Texts.php';
require_once 'Products.php';
require_once 'Categories.php';
require_once 'User.php';

$texts = new Texts('uz');
$products = new Products('uz');
$categories = new Categories('uz');
$user = new User(635793263);

echo '<pre>';
var_dump($user->getLatitude());
echo '</pre>';

echo '<pre>';
var_dump($user->getLongitude());
echo '</pre>';

