<?php
require __DIR__ . '/../vendor/autoload.php';
use Jess\JsonkitPhp\JsonFileManager as Handler;

// Path to JSON file
$file = __DIR__ . '/data.json';

// Initialize JSON file handler
$json = new Handler($file);

$json->setAutoSave(true);

$json->remove('settings.fonts.size');

