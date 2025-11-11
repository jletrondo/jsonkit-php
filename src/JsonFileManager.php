<?php

namespace Jess\JsonkitPhp;


/**
 * Class JsonFileManager
 *
 * Provides an interface to manage and mutate data stored in a JSON file
 * using PHP arrays and optional dot-notation key access. Supports auto-saving
 * on mutation, uniqueness checks when appending to arrays, powerful nested
 * find/filter operations, and customizable pretty-printing.
 */
class JsonFileManager
{
    /** @var string Path to the JSON file */
    protected string $path;
    /** @var array Internal data representation */
    protected array $data = [];
    /** @var bool Whether to autosave after mutations */
    protected bool $autosave = false;

    /**
     * JsonFileManager constructor.
     *
     * @param string $path Path to the JSON file.
     * @param bool $autosave If true, automatically saves on changes.
     */
    public function __construct(string $path = "", bool $autosave = false)
    {
        $this->path = $path;
        $this->autosave = $autosave;
        $this->load();
    }

    /**
     * Load JSON into memory, optionally overwriting with provided data.
     *
     * @param array $data Data to use if overwrite is true or file does not exist.
     * @param bool $overwrite If true, ignore file contents and use provided data.
     * @return self
     */
    public function load(array $data = [], bool $overwrite = false): self
    {
        if (file_exists($this->path)) {
            if (!$overwrite) {
                $json = file_get_contents($this->path);
                $this->data = json_decode($json, true) ?? [];
            } else {
                $this->data = $data;
            }
            
        } else {
            $this->data = $data;
        }
        return $this;
    }

    /**
     * Change the path to the JSON file.
     *
     * @param string $path The new file path.
     * @return self
     */
    public function setPath($path): self
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Enable or disable autosaving after mutations.
     *
     * @param bool $autosave Whether to autosave.
     */
    public function setAutoSave(bool $autosave): void
    {
        $this->autosave = $autosave;
    }

    /**
     * Get all data as an array.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Get a value using dot-notation, with fallback default value if missing.
     *
     * @param string $key Dot-notation key.
     * @param mixed $default Default value if key is missing.
     * @return mixed
     */
    public function get(string $key, $default = null): mixed
    {
        $keys = explode('.', $key);
        $value = $this->data;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Set a value at the given dot-notation key. Autosaves if enabled.
     *
     * @param string $key Dot-notation key.
     * @param mixed $value Value to set.
     * @return $this
     */
    public function set(string $key, $value): self
    {
        $keys = explode('.', $key);
        $data =& $this->data;

        foreach ($keys as $k) {
            if (!isset($data[$k]) || !is_array($data[$k])) {
                $data[$k] = [];
            }
            $data =& $data[$k];
        }

        $data = $value;

        if ($this->autosave) {
            $this->save();
        }

        return $this;
    }

    /**
     * Remove a key using dot-notation. Autosaves if enabled.
     *
     * @param string $key Dot-notation key.
     * @return $this
     */
    public function remove(string $key): self
    {
        $keys = explode('.', $key);
        $data =& $this->data;

        foreach ($keys as $i => $k) {
            if (!isset($data[$k])) { return $this; }

            if ($i === count($keys) - 1) {
                unset($data[$k]);

                if ($this->autosave) {
                    $this->save();
                }

                return $this;
            }

            $data =& $data[$k];
        }
        return $this;
    }

    /**
     * Remove a value from an array at a given key.
     *
     * @param string $key The array key (dot notation)
     * @param mixed $value The value to remove
     * @return $this
     * @throws \RuntimeException If key does not point to an array
     */
    public function removeArrayValue(string $key, $value): self
    {
        $current = $this->get($key, []);

        if (!is_array($current)) {
            throw new \RuntimeException("Cannot remove value from non-array key: {$key}");
        }

        // Remove matching values
        $current = array_filter($current, function($item) use ($value) {
            return $item !== $value;
        });

        // Reindex array to keep numeric keys consistent
        $current = array_values($current);

        $this->set($key, $current);

        if ($this->autosave) {
            $this->save();
        }

        return $this;
    }

    /**
     * Save the internal data to the JSON file.
     *
     * @param string $jsonPath Optional path to save to (updates path).
     * @param bool $pretty Whether to pretty-print the JSON.
     * @return bool True on success.
     */
    public function save(string $jsonPath = "", bool $pretty = true): bool
    {
        if (!empty($jsonPath)) {
            $this->setPath($jsonPath);
        }
        $flags = $pretty ? JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES : 0;
        $json = json_encode($this->data, $flags);
        return (bool) file_put_contents($this->path, $json);
    }

    /**
     * Append a value to an array at a dot-notation key.
     * Optionally checks for uniqueness before appending.
     *
     * @param string $key Dot-notation key for the array.
     * @param mixed $value Value to append.
     * @param bool $isUnique If true, throws if value already exists.
     * @return $this
     * @throws \RuntimeException If key does not point to an array or value exists.
     */
    public function appendArray(string $key, $value, bool $isUnique = false): self
    {
        $current = $this->get($key, []);
        if (!is_array($current)) {
            throw new \RuntimeException("Cannot append to non-array key: {$key}");
        }

        if ($isUnique && in_array($value, $current)) {
            throw new \RuntimeException("Duplicate entry found for key: {$key}");
        }

        $current[] = $value;
        $this->set($key, $current);

        if ($this->autosave) {
            $this->save();
        }

        return $this;
    }

    /**
     * Append a value to an array at a dot-notation key with advanced uniqueness options.
     *
     * @param string $key Array key to append to.
     * @param mixed $value Item to append.
     * @param bool $uniqueByKey If true and value is array, check uniqueness by key/values.
     * @return $this
     * @throws \RuntimeException If duplicate value or entry detected.
     */
    public function appendUnique(string $key, $value, bool $uniqueByKey = false): self
    {
        $current = $this->get($key, []);

        if (!is_array($current)) {
            throw new \RuntimeException("Cannot append to non-array key: {$key}");
        }

        if ($uniqueByKey && is_array($value)) {
            // Check if any existing array has the same keys/values
            foreach ($current as $item) {
                if (is_array($item) && $item === $value) {
                    throw new \RuntimeException("Duplicate entry found for key: {$key}");
                }
            }
        } else {
            // Simple value uniqueness
            if (in_array($value, $current, true)) {
                throw new \RuntimeException("Duplicate value '{$value}' found for key: {$key}");
            }
        }

        $current[] = $value;
        $this->set($key, $current);

        if ($this->autosave) {
            $this->save();
        }

        return $this;
    }

    /**
     * Remove items from an array by a callback at a given key.
     *
     * @param string $key Array key.
     * @param callable $callback Callback to decide removal: returns true to remove.
     * @return $this
     * @throws \RuntimeException If key does not point to an array.
     */
    public function removeArrayByCallback(string $key, callable $callback): self
    {
        $current = $this->get($key, []);

        if (!is_array($current)) {
            throw new \RuntimeException("Cannot remove items from non-array key: {$key}");
        }

        // Filter out items where callback returns true
        $current = array_filter($current, function($item) use ($callback) {
            return !$callback($item);
        });

        // Reindex numeric arrays
        $current = array_values($current);

        $this->set($key, $current);

        if ($this->autosave) {
            $this->save();
        }

        return $this;
    }

    /**
     * Finds all items matching a callback and returns them in a
     * new PHP array that preserves their original nested structure.
     *
     * @param callable $callback The filter function: fn($value, $key)
     * @return array A new array containing only the found items.
     */
    public function find(callable $callback): array
    {
        // 1. Get the flat list of [path => value] results
        $foundItems = $this->findPaths($callback);
        
        $rebuiltArray = [];

        // 2. Iterate and rebuild the array structure
        foreach ($foundItems as $key => $value) {
            $keys = explode('.', $key);
            $data =& $rebuiltArray;

            // This logic is borrowed from the set() method
            while (count($keys) > 1) {
                $k = array_shift($keys);
                if (!isset($data[$k]) || !is_array($data[$k])) {
                    $data[$k] = [];
                }
                $data =& $data[$k];
            }
            
            // Set the value at the final, nested location
            $data[array_shift($keys)] = $value;
        }

        return $rebuiltArray;
    }

    /**
     * Recursively finds all key/value pairs that match a filter callback
     * and returns a flat array of [path => value].
     *
     * @param callable $callback The filter function: fn($value, $key)
     * @return array An associative array of [dot.notation.key => value]
     */
    public function findPaths(callable $callback): array
    {
        // Start the recursive search on the entire data set
        return $this->findRecursive($callback, $this->data);
    }

    /**
     * Helper function for recursive finding.
     *
     * @param callable $callback Callback to match found data.
     * @param array $data Current array to traverse.
     * @param string $parentKey Dot-notation parent path.
     * @return array List of [dot.notation.key => value] matched items.
     */
    protected function findRecursive(callable $callback, array $data, string $parentKey = ''): array
    {
        $results = [];

        foreach ($data as $key => $value) {
            // Construct the dot-notation key for the current item
            $currentKey = $parentKey ? $parentKey . '.' . $key : (string)$key;

            // Run the user's callback
            if ($callback($value, $currentKey)) {
                $results[$currentKey] = $value;
            }

            // Recurse into sub-arrays
            if (is_array($value)) {
                // Merge results from nested calls
                $results = array_merge(
                    $results,
                    $this->findRecursive($callback, $value, $currentKey)
                );
            }
        }

        return $results;
    }
}
