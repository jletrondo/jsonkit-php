<?php

require_once __DIR__ . '/../vendor/autoload.php';
use Jess\JsonkitPhp\JsonHandler as Handler;

$json = new Handler(__DIR__ . '/json/remove.json');

$data = $json->get('saved.tags');
$valueToRemove = "new";

if (in_array($valueToRemove, $data)) {
    $result = $json->removeArrayValue('saved.tags',$valueToRemove)->save();
    if($result) {
        echo "removed data.";
    }
} else {
    echo "item not found";
}



