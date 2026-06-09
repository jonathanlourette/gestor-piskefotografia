<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class Data
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(protected array $data = []) {}

    /**
     * Get a value by key.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Check if a key exists.
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Set a value by key.
     */
    public function set(string $key, mixed $value): self
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Validate the data against the given rules.
     *
     * @param  array<string, mixed>  $rules
     * @param  array<string, string>  $messages
     * @param  array<string, string>  $customAttributes
     *
     * @throws ValidationException
     */
    public function validate(array $rules, array $messages = [], array $customAttributes = []): self
    {
        $validator = Validator::make($this->data, $rules, $messages, $customAttributes);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $this->data = $validator->validated();

        return $this;
    }

    /**
     * Get all data as array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Get all data.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->data;
    }
}
