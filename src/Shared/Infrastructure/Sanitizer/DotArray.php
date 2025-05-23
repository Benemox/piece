<?php

namespace App\Shared\Infrastructure\Sanitizer;

class DotArray implements \ArrayAccess, \Countable, \IteratorAggregate, \JsonSerializable
{
    /**
     * Create a new DoArray instance.
     */
    public function __construct(protected array &$items = [])
    {
    }

    /**
     * Set Array Items.
     *
     * @return void
     **/
    public function setArray(array|\ArrayAccess $items)
    {
        if ($items instanceof static) {
            $this->items = $items->all();
        } elseif (is_array($items)) {
            $this->items = $items;
        } else {
            $this->items = (array) $items;
        }
    }

    /**
     * Set Reference Array.
     *
     * @return void
     **/
    public function setReference(array &$items)
    {
        $this->items = &$items;
    }

    /**
     * Get all the stored items.
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Clear all stored items.
     */
    public function clear(): void
    {
        $this->items = [];
    }

    /**
     * Check if a given key exists.
     */
    public function has(string|int $key, ?array $arr = null)
    {
        $items = $arr ?? $this->items;

        if (is_int($key) && isset($items[$key])) {
            return true;
        } elseif (is_string($key)) {
            $key = $this->prepareKey($key);
            for ($index = 0; $index < count($key); ++$index) {
                if (is_array($items)) {
                    if ('*' == $key[$index]) {
                        ++$index;
                        $next_key = implode('.', array_slice($key, $index));

                        foreach ($items as $item) {
                            if (!$this->has($next_key, $item)) {
                                return false;
                            }
                        }

                        break;
                    } else {
                        if (!array_key_exists($key[$index], $items)) {
                            return false;
                        }
                        $items = $items[$key[$index]];
                    }
                } else {
                    return false;
                }
            }

            return true;
        }
    }

    /**
     * Return the value of a given key.
     *
     * @param array|string|int|float|null $default
     */
    public function get(string|int $key, mixed $default = null, ?array $arr = null): mixed
    {
        $items = $arr ?? $this->items;

        if (is_int($key)) {
            return isset($items[$key]) ? $items[$key] : $default;
        } else {
            $key = $this->prepareKey($key);
            $max = count($key) - 1;

            for ($index = 0; $index < count($key); ++$index) {
                if ('*' == $key[$index]) {
                    if (!is_array($items)) {
                        return $default;
                    }

                    ++$index;
                    $next_key = implode('.', array_slice($key, $index));
                    $rs = null;

                    foreach ($items as $k => $item) {
                        $item = $this->get($next_key, $default, $item);
                        $rs[] = $item;
                    }

                    $items = $rs;
                    break;
                } else {
                    $items = is_array($items) && array_key_exists($key[$index], $items) ? $items[$key[$index]] : null;
                }
            }

            // if multidimensional
            if (is_array($items) && array_is_multidimensional($items) && array_is_numeric($items) && $index = $max) {
                if (isset($items[0][0]) && \is_array($items[0][0])) {
                    foreach ($items as &$item) {
                        $item = array_merge_recursive(...$item);
                    }
                }

                $items = array_merge_recursive(...$items);
            }

            if (is_array($items) && array_is_null($items)) {
                $items = null;
            }

            return is_null($items) ? $default : $items;
        }
    }

    /**
     * Set a given value to the given key.
     *
     * @param array|int|float|string|null $value
     */
    public function set(string|int $key, mixed $value = null, ?array &$arr = null): void
    {
        if (!$arr) {
            $items = &$this->items;
        } else {
            $items = &$arr;
        }

        if (is_int($key)) {
            $items[$key] = $value;

            return;
        } elseif (is_string($key)) {
            $key = $this->prepareKey($key);
            $max = count($key) - 1;

            for ($index = 0; $index <= $max; ++$index) {
                if ($index == $max) {
                    $items[$key[$index]] = $value;
                } else {
                    if ('*' == $key[$index]) {
                        ++$index;
                        $next_key = implode('.', array_slice($key, $index));

                        if (empty($items)) {
                            $items[][$key[$index]] = null;
                        }

                        foreach ($items as &$item) {
                            $this->set($next_key, $value, $item);
                        }

                        break;
                    } else {
                        if (!isset($items[$key[$index]])) {
                            $items[$key[$index]] = null;
                        }

                        $items = &$items[$key[$index]];
                    }
                }
            }
        }
    }

    /**
     * Delete the given key.
     */
    public function delete(string|int $key, ?array &$arr = null): bool
    {
        if (!$arr) {
            $items = &$this->items;
        } else {
            $items = &$arr;
        }

        if (is_int($key) && isset($items[$key])) {
            unset($items[$key]);

            return true;
        } elseif (is_string($key)) {
            $key = $this->prepareKey($key);
            $max = count($key) - 1;

            for ($index = 0; $index <= $max; ++$index) {
                if ($index == $max) {
                    if (isset($items[$key[$index]])) {
                        unset($items[$key[$index]]);

                        return true;
                    }
                } else {
                    if ('*' == $key[$index]) {
                        ++$index;
                        $next_key = implode('.', array_slice($key, $index));
                        $rs = true;

                        foreach ($items as &$item) {
                            if (!$this->delete($next_key, $item)) {
                                $rs = false;
                            }
                        }

                        return $rs;
                    } elseif (isset($items[$key[$index]])) {
                        $items = &$items[$key[$index]];
                    }
                }
            }
        }

        return false;
    }

    /**
     * Check if the given key's value is empty.
     */
    public function isEmpty(?string $key = null): bool
    {
        if (!$key) {
            return empty($this->items);
        }

        return empty($this->get($key ?? '*'));
    }

    /**
     * Check if the given keys are integers from 0 to N.
     */
    public function isNumericKeys(): bool
    {
        return array_is_numeric($this->items);
    }

    /**
     * Check if the array is a multidimensional.
     */
    public function isMultidimensional(): bool
    {
        return array_is_multidimensional($this->items);
    }

    /**
     * Check if the array contains Null values only.
     */
    public function isNulledValues(): bool
    {
        return array_is_null($this->items);
    }

    /**
     * Return the value of a given key as JSON.
     */
    public function toJson(int|string|null $key = null, int $options = 0): string
    {
        return json_encode($key ? $this->get($key ?? '*') : $this->all(), $options);
    }

    /**
     * Prepare Key to Array of Keys.
     */
    private function prepareKey(string $key): array
    {
        $key = rtrim(
            trim($key, '. '),
            '.*'
        );

        return empty($key) ? [] : explode('.', $key);
    }

    /**
     * Check if a given key exists.
     *
     * @param int|string $key
     */
    public function offsetExists($key): bool
    {
        return $this->has($key);
    }

    /**
     * Return the value of a given key.
     *
     * @param int|string $key
     */
    public function offsetGet($key): mixed
    {
        return $this->get($key);
    }

    /**
     * Set a given value to the given key.
     *
     * @param int|string $key
     */
    public function offsetSet($key, $value): void
    {
        if (is_null($key)) {
            $this->items[] = $value;

            return;
        }

        $this->set($key, $value);
    }

    /**
     * Delete the given key.
     *
     * @param int|string $key
     */
    public function offsetUnset($key): void
    {
        $this->delete($key);
    }

    /**
     * Return the number of items in a given key.
     */
    public function count(?string $key = null): int
    {
        return count($this->get($key ?? '*'));
    }

    /**
     * Get an iterator for the stored items.
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * Return items for JSON serialization.
     */
    public function jsonSerialize(): mixed
    {
        return $this->items;
    }
}

/**
 * Check if the given array is a numeric array.
 *
 * @param array $arr array
 */
function array_is_numeric(array $arr): bool
{
    return array_keys($arr) === range(0, count($arr) - 1);
}

/**
 * Check if the given array contains Null values only.
 *
 * @param array $arr array
 */
function array_is_null(array $arr): bool
{
    return empty(array_filter($arr, function ($v) {
        return null !== $v;
    }));
}

/**
 * Check if the given array is a multidimensional.
 *
 * @param array $arr array
 */
function array_is_multidimensional(array $arr): bool
{
    return count($arr) !== count($arr, COUNT_RECURSIVE);
}
