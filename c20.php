#!/usr/bin/env php
<?php
require_once dirname(__FILE__) . '/vendor/autoload.php';

date_default_timezone_set('Australia/Brisbane');

$dotenv = new Dotenv\Dotenv(getcwd());
$dotenv->load();

$hitBtc = new \CoinMonitor\HitBtc();

if (!$hitBtc->checkForC20()) {
    die('C20 Not Available.');
}

$btcBalance = $hitBtc->getBalance('BTC');

if ($btcBalance->getAvailable() <= 0) {
    die('NO BTC Available.');
}


$hitBtc->buyC20($btcBalance->getAvailable());