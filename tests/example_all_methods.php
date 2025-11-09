<?php
require __DIR__ . '/../vendor/autoload.php';
use Jess\JsonkitPhp\JsonHandler;

// Path to JSON file
$file = __DIR__ . '/data.json';

// Initialize JSON file handler
$json = new JsonHandler($file);

// --- Append new users ---
$json->append('users', ['id'=>1,'name'=>'Alice'])
     ->append('users', ['id'=>2,'name'=>'Bob'])
     ->save();

// --- Set new settings ---
$json->set('settings.theme', 'dark')
     ->set('settings.language', 'en')
     ->save();

// --- Get values ---
echo "Theme: " . $json->get('settings.theme') . PHP_EOL; // dark
echo "Language: " . $json->get('settings.language') . PHP_EOL; // en
echo "Second user: " . $json->get('users.1.name') . PHP_EOL; // Bob

// --- Remove a key ---
$json->remove('settings')->save();

// --- All data ---
print_r($json->all());
