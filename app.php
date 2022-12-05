#!/usr/bin/php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use lib\ObedApi;


$grantData = require_once 'tokens.php';
$login = $grantData['login'];
$password = $grantData['password'];
$date = '2022-12-05';

$api = new ObedApi($login, $password);

echo $api->getMenuList('8166-lascala', $date);
