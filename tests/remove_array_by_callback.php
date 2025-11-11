<?php

require_once __DIR__ . '/../vendor/autoload.php';
use Jess\JsonkitPhp\JsonFileManager as Handler;

$json = new Handler();

$data = $json->load([
    'numbers' => ['1', '2', '3' , [ 'test' => 'hahaha ']]
])->save(__DIR__ . '/json/numbers.json');

echo "\nbefore removing...\n";
print_r($json->get('numbers'));

// useful for removing multiple array values
$json->removeArrayByCallback('numbers', function ($item) {
    return $item === '1';
});

echo "\nafter removing...\n";
print_r($json->get('numbers'));