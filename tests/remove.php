<?php

/**
 * Test for JsonFileManager::remove() method.
 *
 * Purpose: Verify that the remove() method correctly removes the specified key
 * and its associated data from the loaded JSON structure.
 */

require_once __DIR__ . '/../vendor/autoload.php';
use Jess\JsonkitPhp\JsonFileManager as Handler;

$json = new Handler(__DIR__ . '/json/sample.json');

$result = $json->remove('saved')->save();

if($result) {
    echo "removed data.";
}