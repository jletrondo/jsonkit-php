<?php

/**
 * Test for JsonFileManager::setAutoSave() method.
 *
 * This script verifies that when the autosave flag is set to true,
 * any mutation operation (such as set()) automatically persists changes
 * to the JSON file, eliminating the need for an explicit ->save() call.
 */

require_once __DIR__ . '/../vendor/autoload.php';
use Jess\JsonkitPhp\JsonFileManager as Handler;

$json = new Handler(__DIR__ . '/json/sample.json'); // You can put autosave flag to the second parameter
$json->setAutoSave(true);

$json->set('saved', [
    'name' => "Jason",
    'age'  => 24,
    'address' => "PH"
]);
if (!empty($json->get('saved'))) {
    echo "Autosave works!";
}


