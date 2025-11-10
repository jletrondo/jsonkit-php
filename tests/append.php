<?php

require_once __DIR__ . '/../vendor/autoload.php';
use Jess\JsonkitPhp\JsonHandler as Handler;

$json = new Handler(__DIR__ . '/json/append.json');


// Find a value
$data = $json->find(function ($value, $key) {
    return $value === "Tennis";
});

if (empty($data)) {
    $json->appendArray('users.hobbies', 'Tennis', true)->save();
} else {
    echo "Data to append already exists.";
}

// Find a key
$json = new Handler(__DIR__ . '/json/sample.json');
$data = $json->find(function($value, $key) {
    return str_contains($key, 'users');
});
echo "\n";

if (!empty($data)) {
    print_r($data['users'][0]['id']);
} else {
    echo "Data not found.";
}
