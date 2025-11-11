# JsonFileManager PHP Library

**JsonFileManager** is a lightweight PHP class for simple, robust manipulation of JSON files—read, write, update, search, and remove using dot notation.

---

## Installation

Install via [Composer](https://getcomposer.org/):

```bash
composer require jess/jsonkit-php
```

## Usage

### Quick Start

```php

use Jess\JsonkitPhp\JsonFileManager;

// Initialize from a file path
$handler = new JsonFileManager('data.json', true);

// Get all data
$data = $handler->all();

// Get a value (dot notation supported)
$name = $handler->get('user.name');

// Set a value
$handler->set('user.age', 30);

// Append a value to an array
$handler->appendArray('tags', 'php');

// Remove a value from an array
$handler->removeArrayValue('tags', 'php');

// Remove where condition
$handler->removeWhere('users', function($user) {
    return $user['active'] === false;
});



// Save changes
$handler->save();

// You can load array data and save it to a json file.
$arr = [
     'numbers' => ['1', '2', '3']
];
$data = $handler->load($arr)->save(__DIR__ . 'test.json');

```

### Main Methods

| Method                                  | Description                                          |
|------------------------------------------|------------------------------------------------------|
| `all()`                                 | Get all JSON data as an array.                       |
| `get($key, $default = null)`            | Get value by dot notation key.                       |
| `set($key, $value)`                     | Set value at the given key.                          |
| `remove($key)`                          | Remove an entry by dot notation key.                 |
| `appendArray($key, $value, $unique = false)` | Append value to an array at key (optionally unique). |
| `removeArrayValue($key, $value)`        | Remove value from array at key.                      |
| `removeWhere($key, $callback)`          | Remove items from array at key matching callback.    |
| `save($jsonPath = "", $pretty = true)`  | Save changes (pretty print by default).              |

---
### Features

- Dot notation for deeply nested keys.
- Autosave option for automatic persistence.
- Array manipulation helpers.
- Fast in-memory operations with optional file persistence.
- Flexible initialization (from file or array).

---

### License

MIT