#!/usr/bin/php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use lib\ObedApi;


$grantData = require_once 'tokens.php';
$login = $grantData['login'];
$password = $grantData['password'];

$api = new ObedApi($login, $password);

echo $api->getMenuList('2022-11-02');
