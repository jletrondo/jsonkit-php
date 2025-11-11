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

$dataToJsonize = [
    "users" => [
        [
            "id" => 1,
            "name" => "Alice",
            "email" => "alice@example.com",
            "roles" => ["admin", "user"],
            "active" => true,
            "tags" => ["beta", "team-lead"],
            "profile" => [
                "age" => 30,
                "address" => [
                    "street" => "123 Apple St",
                    "city" => "Wonderland",
                    "zip" => "12345"
                ]
            ]
        ],
        [
            "id" => 2,
            "name" => "Bob",
            "email" => "bob@example.com",
            "roles" => ["user"],
            "active" => false,
            "tags" => [],
            "profile" => [
                "age" => 25,
                "address" => [
                    "street" => "456 Banana Ave",
                    "city" => "Fruitville",
                    "zip" => "67890"
                ]
            ]
        ]
    ],
    "settings" => [
        "theme" => "dark",
        "notifications" => [
            "email" => true,
            "sms" => false,
            "push" => true
        ],
        "languages" => ["en", "es", "fr"],
        "privacy" => [
            "tracking" => "minimal",
            "ads" => false,
            "shareData" => true
        ],
        "fonts" => [
            "size" => 14,
            "family" => "Arial",
            "bold" => true
        ]
    ],
    "products" => [
        [
            "sku" => "A100",
            "name" => "Widget",
            "tags" => ["new", "sale"],
            "price" => 19.99,
            "in_stock" => 117
        ],
        [
            "sku" => "B200",
            "name" => "Gadget",
            "tags" => ["sale"],
            "price" => 29.99,
            "in_stock" => 0
        ]
    ]
];

$json = new Handler(__DIR__ . '/json/test.json'); // You can put autosave flag to the second parameter
$json->setAutoSave(true);

// Note: If the file already exists, set $overwrite to true in order to replace its entire contents with $dataToJsonize.
//       Be careful: using $overwrite = true will erase everything in the existing JSON file and start fresh with the new data.
//       If you want to backup the current JSON data before overwriting, you can always use $json->all() to get its contents.
//       and store to another json with the same logic.
$json->load($dataToJsonize, true)->save();