<?php

namespace Jess\JsonkitPhp;

class JsonHandler
{
    protected string $path;
    protected array $data = [];
    protected bool $autosave = false;

    public function __construct(string $path, bool $autosave = false)
    {
        $this->path = $path;
        $this->autosave = $autosave;
        $this->load();
    }

    public function setAutoSave(bool $autosave): void
    {
        $this->autosave = $autosave;
    }

    protected function load(): void
    {
        if (file_exists($this->path)) {
            $json = file_get_contents($this->path);
            $this->data = json_decode($json, true) ?? [];
        } else {
            $this->data = [];
        }
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

    /**
     * Return $this instead of static for PHP 7.4 compatibility
     */
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
            if (!isset($data[$k])) return $this;

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
    public function removeValue(string $key, $value)
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


    public function save(bool $pretty = true): bool
    {
        $flags = $pretty ? JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES : 0;
        $json = json_encode($this->data, $flags);
        return (bool) file_put_contents($this->path, $json);
    }

    public function append(string $key, $value)
    {
        $current = $this->get($key, []);
        if (!is_array($current)) {
            throw new \RuntimeException("Cannot append to non-array key: {$key}");
        }

        $current[] = $value;
        $this->set($key, $current);

        if ($this->autosave) {
            $this->save();
        }

        return $this;
    }

    /**
     * Append a value to an array key but ensure uniqueness.
     *
     * @param string $key The array key (dot notation supported)
     * @param mixed $value Value to append
     * @param bool $uniqueByKey If true, prevents duplicate keys in associative arrays
     *
     * @return $this
     * @throws \RuntimeException If duplicate is found
     */
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

    /**
     * Remove array items at a given key that match a condition.
     *
     * @param string $key Dot notation key pointing to an array
     * @param callable $callback Function that receives each item and returns true to remove it
     * @return $this
     * @throws \RuntimeException
     */
    public function removeWhere(string $key, callable $callback)
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
}
