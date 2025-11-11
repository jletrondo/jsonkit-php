<?php

require_once __DIR__ . '/../vendor/autoload.php';
use Jess\JsonkitPhp\JsonFileManager as Manager;

$json = new Manager(__DIR__ . '/json/find.json');

// Find a value or a key
$data = $json->find(function ($value, $key) {
    return $value === "Tennis";
});
$json->appendArray('users.hobbies', 'Tennis', true)->save();
// if (empty($data)) {
//     $json->appendArray('users.hobbies', 'Tennis', true)->save();
// } else {
//     echo "Data to append already exists.";
// }