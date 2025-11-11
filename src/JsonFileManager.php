<?php

namespace Jess\JsonkitPhp;

class JsonFileManager
{
    protected string $path;
    protected array $data = [];
    protected bool $autosave = false;

    public function __construct(string $path = "", bool $autosave = false)
    {
        $this->path = $path;
        $this->autosave = $autosave;
        $this->load();
    }

    public function load(array $data = []): self
    {
        if (file_exists($this->path)) {
            $json = file_get_contents($this->path);
            $this->data = json_decode($json, true) ?? [];
        } else {
            $this->data = $data;
        }
        return $this;
    }

    public function setPath($path): self
    {
        $this->path = $path;
        return $this;
    }

    public function setAutoSave(bool $autosave): void
    {
        $this->autosave = $autosave;
    }

    public function all(): array
    {
        return $this->data;
    }

    public function get(string $key, $default = null)
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

    public function set(string $key, $value)
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

    public function remove(string $key)
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
     * Remove a value from an array at a given key
     *
     * @param string $key The array key (dot notation)
     * @param mixed $value The value to remove
     * @return $this
     */
    public function removeArrayValue(string $key, $value)
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


    public function save(string $jsonPath = "", bool $pretty = true): bool
    {
        if (!empty($jsonPath)) {
            $this->setPath($jsonPath);
        }
        $flags = $pretty ? JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES : 0;
        $json = json_encode($this->data, $flags);
        return (bool) file_put_contents($this->path, $json);
    }

    public function appendArray(string $key, $value, bool $isUnique = false)
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

    public function appendUnique(string $key, $value, bool $uniqueByKey = false)
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

    public function removeArrayByCallback(string $key, callable $callback)
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
