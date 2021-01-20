<?php

require "./vendor/autoload.php";

use Goldencloud\Client;

$route = "/tax-api/invoice/wait-open/v1";

$config = [
    'host' => 'HOST',
    'appkey' => 'APPKEY',
    'secret' => 'SECRET',
    'route' => $route

];

$arg = [
    "taxpayer_num" => "20181010000000865115391398242493",
    "machine_no" => "125523523523",
    "invoice_type_code" => "026"
];

$client = new \Goldencloud\Client($config);
$res = $client->postObject($arg);
var_dump($res);