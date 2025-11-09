<?php
require __DIR__ . '/../vendor/autoload.php';
use Jess\JsonkitPhp\JsonHandler as Handler;

// Path to JSON file
$file = __DIR__ . '/data.json';

// Initialize JSON file handler
$json = new Handler($file);

$json->setAutoSave(true);

// // Trying to add duplicate
// $json->appendUnique('colors', [
//     'a' => 'b'
// ])->save();

$json->remove('settings.fonts.size');

// $json->remove('users.1')->save();

// $json->removeWhere('users', function ($user) {
//     return $user['id'] === 2;
// })->save();

// $json->remove('settings.fonts')->save();

// $json->set('test.key', 'value');

// $json->remove('users.0.roles');

// $json->removeWhere('users.0.roles', function () {

// });