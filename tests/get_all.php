<?php

/**
 * Test for JsonHandler::all() method.
 *
 * Purpose: Verify that the all() method returns the entire
 * data structure loaded from the specified JSON file.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Jess\JsonkitPhp\JsonHandler;

$json = new JsonHandler(__DIR__ . '/json/sample.json');

print_r($json->all());