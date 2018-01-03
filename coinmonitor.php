#!/usr/bin/env php
<?php
require_once dirname(__FILE__) . '/vendor/autoload.php';

date_default_timezone_set('Australia/Brisbane');

$dotenv = new Dotenv\Dotenv(getcwd());
$dotenv->load();

list($script, $coin, $exchange, $buyPrice, $sellTargets, $stopLoss) = $argv;

$sellTargets = explode(',', $sellTargets);

$monitor = new \CoinMonitor\CoinMonitor($coin, $exchange, $buyPrice, $sellTargets, $stopLoss);