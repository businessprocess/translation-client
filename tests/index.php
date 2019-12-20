<?php

include 'vendor/autoload.php';

$options = [
    'login' => 'pervozadiy@gmail.com',
    'password' => '12345678',
];
// you can pass any storage you want that implements \Psr\SimpleCache\CacheInterface
$client = new \Translate\ApiClient($options, new \Translate\Storage\ArrayStorage());
$wrapper = new \Translate\Psr\Wrapper($client);
$response = $wrapper->request('GET', 'users');

var_dump($response->getBody()->getContents());