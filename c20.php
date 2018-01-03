#!/usr/bin/env php
<?php
require_once dirname(__FILE__) . '/vendor/autoload.php';

date_default_timezone_set('Australia/Brisbane');

$dotenv = new Dotenv\Dotenv(getcwd());
$dotenv->load();

$monitor = new \CoinMonitor\CoinMonitor();

$monitor->checkHitBtcForC20();