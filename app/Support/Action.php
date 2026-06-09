<?php

declare(strict_types=1);

namespace App\Support;

abstract class Action
{
    protected Data $data;

    /**
     * Set the data for the action.
     */
    public function setData(array $data): self
    {
        $this->data = new Data($data);

        return $this;
    }

    /**
     * Perform the action logic. Must be implemented by subclasses.
     */
    abstract public function perform(): mixed;

    /**
     * Execute the action with the given data.
     */
    public function execute(array $data): mixed
    {
        $this->setData($data);

        return $this->perform();
    }
}
